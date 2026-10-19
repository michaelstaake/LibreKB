# LibreKB

LibreKB: Knowledge Base Web App - Free, Open Source, Self Hosted, PHP/MySQL.

The project home is the [GitHub repository](https://github.com/michaelstaake/LibreKB).

### Features

- 100% free and open source.
- Runs on pretty much any server, hosting account, or using Docker.
- Installs in minutes.
- Easy to customize branding to match your project or business.
- Site settings are grouped into General, Appearance, Editor, API, and Maintenance.
- Optional custom CSS for the public site, from Appearance settings.
- Choose which buttons appear on the article editor toolbar.
- Manage users with predefined user groups.
- Easily edit articles with TinyMCE.
- Nest categories to any depth.
- Collapse and filter categories and articles on the admin dashboard.
- Attach files to articles for readers to download.
- Password resets via email.
- Responsive design based on Bootstrap.
- Optional read-only JSON API for articles and categories.

### Need Help?

Ask questions and share ideas in [GitHub Discussions](https://github.com/michaelstaake/LibreKB/discussions).

### Update Checks

Removed in 2.0.1 based on community feedback. Please check for updates manually.

### Read-only API

LibreKB 2.2.0 can expose the public knowledge base as JSON for other applications. The API is disabled until an administrator turns it on under Settings. It accepts GET only. Every response uses `Content-Type: application/json; charset=utf-8`.

An article or category that the public site would hide is hidden here too. Disabled articles are omitted. An enabled article inside a disabled category, or inside a category whose parent is disabled, is omitted. Disabled categories are omitted. During maintenance the API returns 503. While the API setting is disabled it also returns 503.

If an API key is saved, every request must send that key in the `X-API-Key` header or the `api_key` query parameter. If no key is saved and the knowledge base is public, the API is public read-only. If the knowledge base is private, a key is required even when the field was left blank: the API then returns 403 until a key is set. A missing or wrong key returns 401. Other methods return 405. Unknown API paths return 404. Browser clients send an OPTIONS preflight, which answers 204. `Access-Control-Allow-Origin` uses the CORS origin setting (`*` unless an administrator sets one origin).

Article HTML is the same cleaned HTML the site renders. It is included when an article is requested by id or slug. Attachment objects include the file name, size in bytes, MIME type, and a download URL on the existing `/attachment/{id}` route. Search results and category article lists include that attachment metadata and omit the HTML body.

```bash
curl -s "https://kb.example.com/api/v1/articles/search?q=password&page=1&per_page=20"
curl -s "https://kb.example.com/api/v1/articles/1" -H "X-API-Key: YOUR_KEY"
curl -s "https://kb.example.com/api/v1/articles/slug/reset-password" -H "X-API-Key: YOUR_KEY"
curl -s "https://kb.example.com/api/v1/categories"
curl -s "https://kb.example.com/api/v1/categories/3"
curl -s "https://kb.example.com/api/v1/categories/slug/getting-started"
```

`page` starts at 1. `per_page` defaults to 20 and cannot be higher than 50. Search matches article titles and content only.

### Upgrading to 2.3.0

Copy the new files over a 2.2.0 install and keep `config.php`. Then open `/upgrade` and confirm. That changes the settings value column from VARCHAR(255) to TEXT and adds an empty `custom_css` setting. Existing categories, articles, users, and logs are not rewritten. The API stays disabled until you enable it in Settings.

The admin dashboard list of categories and articles starts collapsed. Open a category with its chevron, or use the filter to jump to a matching category or article. Open branches are remembered in the browser.

Site Settings is split into General, Appearance, Editor, API, and Maintenance. Each section has its own address under `/admin/settings/` and its own Save button. `/admin/settings` opens General. Saving one section leaves the others unchanged.

Custom CSS is an optional text box on Appearance. It is printed after the built-in styles on the public site only. Admin pages do not use it. The sheet is limited to 20,000 characters and cannot contain a less-than character, a null byte, `@import`, `expression(`, `javascript:`, `vbscript:`, `-moz-binding`, or `behavior:`.

The Editor section chooses the article toolbar. Leave it empty, or save the original selection, to keep the built-in buttons: undo, redo, bold, italic, underline, paragraph style, font size, lists, link, table, source code, find and replace, and word count. Extra buttons are font color, highlight, alignment, strikethrough, subscript, superscript, block quote, a horizontal line, and an image by web address. Image upload and captions are not available. A TinyMCE plugin is loaded only when a button that needs it is turned on.

A new install from `/install` already includes the TEXT column, the custom CSS setting, and the editor toolbar setting.

### Upgrading to 2.2.0

Copy the new files over an existing install and keep `config.php`. Then open `/upgrade` and confirm. That adds the API settings (`api_enabled`, `api_key`, `api_cors_origin`) and, when upgrading from 2.0.1, the attachments table. Existing categories, articles, users, and logs are not rewritten. The API stays disabled until you enable it in Settings.

A new install from `/install` already includes these settings.

### Upgrading to 2.1.0

Copy the new files over an existing 2.0.1 install and keep `config.php`. Then open `/upgrade` and confirm. That adds the `attachments` table and does not change categories, articles, users, settings, or logs.

A new install from `/install` already includes the table.

Uploaded files are stored in `storage/uploads/attachments/` under a random name. Apache is blocked from serving that directory directly (see `.htaccess`). On nginx, deny access to `/storage/` and let LibreKB send downloads itself. PHP `upload_max_filesize` should be at least 12M and `post_max_size` large enough for the files you attach (the Docker image sets this).

Deleting a category is refused while it still has articles or subcategories. Move or delete that content first so nothing is orphaned.

Article HTML is cleaned on save and again when a page is rendered, including articles saved by older versions. Formatting from the editor is kept (headings, lists, links, tables, images, code). Scripts and `javascript:` links are removed. This uses [HTML Purifier](http://htmlpurifier.org/) 4.19.1 in `vendor/htmlpurifier/`. The first page view writes a definition cache under `storage/cache/htmlpurifier/`, which PHP must be able to create. If that directory is not writable, cleaning still runs, just without the cache.

Logout, and deleting a category, article, attachment, or user, are POST forms with a per-session token. A bookmarked GET to those old URLs no longer works. Session cookies are HttpOnly and SameSite=Lax, and Secure when the site is served over HTTPS (including `X-Forwarded-Proto: https` behind a proxy). HTML responses send `X-Frame-Options: DENY`.