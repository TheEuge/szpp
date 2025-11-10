<?php
// save.php - session and CSRF protected saving endpoint with HTML sanitization
require_once __DIR__ . '/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'bad json']); exit; }

$page = preg_replace('/[^a-z0-9_-]/i','', $data['page'] ?? '');
$allowed = ['home','szpp','about','contact'];
if (!in_array($page, $allowed)) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'invalid page']); exit; }

$html = $data['html'] ?? '';

// basic HTML sanitization: allow only a whitelist of tags and attributes
function sanitize_html($html){
  // allow these tags and attributes
  $allowed_tags = ['a','b','strong','i','em','p','ul','ol','li','br','h1','h2','h3','h4','h5','h6','dl','dt','dd','blockquote','code','pre','span','img'];
  $allowed_attrs = ['href','title','alt','src','width','height','class','id','style'];

  // Prefer DOMDocument when available (more robust). Fallback to a conservative
  // stripper if PHP lacks the DOM extension (common on minimal installs).
  if (class_exists('DOMDocument')){
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();

    $xpath = new DOMXPath($doc);
    foreach ($xpath->query('//*') as $node){
      $tag = $node->nodeName;
      if ($tag === 'html' || $tag === 'body' || $tag === 'div') continue;
      if (!in_array($tag, $allowed_tags)){
        // replace node with its children
        $frag = $doc->createDocumentFragment();
        while ($node->firstChild) $frag->appendChild($node->removeChild($node->firstChild));
        $node->parentNode->replaceChild($frag, $node);
        continue;
      }
      // sanitize attributes
      if ($node->hasAttributes()){
        foreach (iterator_to_array($node->attributes) as $attr){
          if (!in_array($attr->name, $allowed_attrs)){
            $node->removeAttribute($attr->name);
            continue;
          }
          // basic href/src validation: disallow javascript: pseudo and data: URIs
          if (in_array($attr->name, ['href','src'])){
            $val = trim($attr->value);
            if (stripos($val, 'javascript:') === 0 || stripos($val, 'data:') === 0) $node->removeAttribute($attr->name);
          }
        }
      }
    }

    // extract body innerHTML
    $body = $doc->getElementsByTagName('body')->item(0);
    $out = '';
    foreach ($body->childNodes as $child) $out .= $doc->saveHTML($child);
    return $out;
  }

  // Fallback sanitizer when DOMDocument isn't available.
  error_log('save.php: DOMDocument not available, using fallback sanitizer');
  // Remove comments
  $html = preg_replace('/<!--.*?-->/s', '', $html);
  // Strip disallowed tags but keep allowed tags (strip_tags will remove attributes too)
  $allowString = '';
  foreach ($allowed_tags as $t) $allowString .= '<' . $t . '>';
  $s = strip_tags($html, $allowString);

  // Now sanitize attributes: rebuild opening tags keeping only allowed attributes
  $s = preg_replace_callback('/<([a-zA-Z][a-zA-Z0-9]*)\b([^>]*)>/i', function($m) use ($allowed_tags, $allowed_attrs){
    $tag = strtolower($m[1]);
    $attrstr = $m[2] ?? '';
    if (!in_array($tag, $allowed_tags)) return ''; // strip unknown tags entirely
    $newattrs = '';
    if (preg_match_all('/([a-zA-Z0-9_:-]+)\s*=\s*("([^"]*)"|' . "'([^']*)'" . '|([^\s>]+))/i', $attrstr, $am, PREG_SET_ORDER)){
      foreach($am as $a){
        $an = strtolower($a[1]);
        $av = isset($a[3]) && $a[3] !== '' ? $a[3] : (isset($a[4]) && $a[4] !== '' ? $a[4] : $a[5]);
        if (!in_array($an, $allowed_attrs)) continue;
        // sanitize href/src values
        if (in_array($an, ['href','src'])){
          $val = trim($av);
          if (preg_match('/^\s*javascript:/i', $val)) continue;
          if (preg_match('/^\s*data:/i', $val)) continue; // disallow data: URIs in fallback
        }
        $escaped = htmlspecialchars($av, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $newattrs .= ' ' . $an . '="' . $escaped . '"';
      }
    }
    return '<' . $tag . $newattrs . '>';
  }, $s);

  return $s;
}

$clean = sanitize_html($html);

$dir = __DIR__ . '/content';
if (!is_dir($dir)) mkdir($dir, 0755, true);
$file = "$dir/$page.html";
// backup
if (file_exists($file)) copy($file, "$file.bak." . time());
if (file_put_contents($file, $clean) === false) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'write failed']);
} else {
  echo json_encode(['ok'=>true]);
}
