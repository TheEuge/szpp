Simple site with lightweight in-place editor (PHP)

Files:
- `index.php`, `about.php`, `szpp.php`, `contact.php` - public pages (include fragments from `/content`)
- `content/*.html` - editable fragments included by the public pages
- `admin.php` - simple WYSIWYG editor (protect this page)
- `save.php` - endpoint that writes edited fragments (protected by a shared secret inside the file)
- `css/style.css` - styles

Quick start (PHP host)

1. Upload the project to a PHP-enabled web host (Apache, Nginx+PHP-FPM, etc.).
2. Protect `admin.php` with HTTP Basic Auth or restrict access by IP (recommended).
3. Edit the secret in `save.php` and `admin.php` (search for `change-this-secret`) and set the same strong token.
4. Open `/admin.php` in the browser, select a page, edit content and press Save.

Notes
- `save.php` makes a timestamped backup of each fragment as `content/<page>.html.bak.<ts>` before write.
- Sanitize or review HTML from editors before allowing untrusted users.
- If your host does not support PHP, use the static copy/paste approach instead.

How to view locally (static preview)

If you just want to preview the site locally without PHP, open the original `.html` files (they still exist) or run a static server:

```bash
python3 -m http.server 8000
```

Then open http://localhost:8000
# szpp
