const fs = require('fs');
const path = require('path');
const marked = require('marked');

function buildPage(templatePath, outPath, fragmentPath){
  if (!fs.existsSync(templatePath)){
    console.error('Missing template:', templatePath);
    process.exit(1);
  }
  const tpl = fs.readFileSync(templatePath, 'utf8');
  let frag = '';
  if (fs.existsSync(fragmentPath)){
    frag = fs.readFileSync(fragmentPath, 'utf8');
    if (fragmentPath.endsWith('.md')){
      // convert markdown to HTML
      frag = marked.parse(frag);
    }
  }
  const out = tpl.replace('<!-- CONTENT_PLACEHOLDER -->', frag);
  fs.writeFileSync(outPath, out, 'utf8');
  console.log('Built', outPath);
}

const templates = path.join(__dirname, 'templates');
const content = path.join(__dirname, 'content');
const pageTpl = path.join(templates, 'page.html');

const pages = [
  { frag: 'home.html', out: 'index.html' },
  { frag: 'about.html', out: 'about.html' },
  { frag: 'szpp.html', out: 'szpp.html' },
  { frag: 'contact.html', out: 'contact.html' },
];

pages.forEach(p => buildPage(pageTpl, path.join(__dirname, p.out), path.join(content, p.frag)));
