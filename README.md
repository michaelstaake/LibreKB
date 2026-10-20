# LibreKB

LibreKB: Knowledge Base Web App - Free, Open Source, Self Hosted, PHP/MySQL.

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

Report problems and request features in [GitHub Issues](https://github.com/michaelstaake/LibreKB/issues).

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

### Upgrading

Overwrite the files with the new release and keep your `config.php`. Then visit `/upgrade`.

