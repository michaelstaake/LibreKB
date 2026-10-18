# LibreKB

LibreKB: Knowledge Base Web App - Free, Open Source, Self Hosted, PHP/MySQL.

The project home is the [GitHub repository](https://github.com/michaelstaake/LibreKB).

### Features

- 100% free and open source.
- Runs on pretty much any server, hosting account, or using Docker.
- Installs in minutes.
- Easy to customize branding to match your project or business.
- Manage users with predefined user groups.
- Easily edit articles with TinyMCE.
- Nest categories to any depth.
- Attach files to articles for readers to download.
- Password resets via email.
- Responsive design based on Bootstrap.

### Need Help?

Ask questions and share ideas in [GitHub Discussions](https://github.com/michaelstaake/LibreKB/discussions).

### Update Checks

Removed in 2.0.1 based on community feedback. Please check for updates manually.

### Upgrading to 2.1.0

Copy the new files over an existing 2.0.1 install and keep `config.php`. Then open `/upgrade` and confirm. That adds the `attachments` table and does not change categories, articles, users, settings, or logs.

A new install from `/install` already includes the table.

Uploaded files are stored in `storage/uploads/attachments/` under a random name. Apache is blocked from serving that directory directly (see `.htaccess`). On nginx, deny access to `/storage/` and let LibreKB send downloads itself. PHP `upload_max_filesize` should be at least 12M and `post_max_size` large enough for the files you attach (the Docker image sets this).

Deleting a category is refused while it still has articles or subcategories. Move or delete that content first so nothing is orphaned.

Article HTML is cleaned on save and again when a page is rendered, including articles saved by older versions. Formatting from the editor is kept (headings, lists, links, tables, images, code). Scripts and `javascript:` links are removed. This uses [HTML Purifier](http://htmlpurifier.org/) 4.19.1 in `vendor/htmlpurifier/`. The first page view writes a definition cache under `storage/cache/htmlpurifier/`, which PHP must be able to create. If that directory is not writable, cleaning still runs, just without the cache.

Logout, and deleting a category, article, attachment, or user, are POST forms with a per-session token. A bookmarked GET to those old URLs no longer works. Session cookies are HttpOnly and SameSite=Lax, and Secure when the site is served over HTTPS (including `X-Forwarded-Proto: https` behind a proxy). HTML responses send `X-Frame-Options: DENY`.