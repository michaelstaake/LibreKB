# LibreKB

LibreKB: Knowledge Base Web App - Free, Open Source, Self Hosted, PHP/MySQL.

Check out the official website at [LibreKB.com](https://librekb.com/) for more information.

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

Docs: [docs.LibreKB.com](https://docs.librekb.com/)

Forum: [GitHub Discussions for LibreKB](https://github.com/michaelstaake/LibreKB/discussions)

### Update Checks

Removed in 2.0.1 based on community feedback. Please check for updates manually.

### Upgrading to 2.1.0

Copy the new files over an existing 2.0.1 install and keep `config.php`. Then open `/upgrade` and confirm. That adds the `attachments` table and does not change categories, articles, users, settings, or logs.

A new install from `/install` already includes the table.

Uploaded files are stored in `storage/uploads/attachments/` under a random name. Apache is blocked from serving that directory directly (see `.htaccess`). On nginx, deny access to `/storage/` and let LibreKB send downloads itself. PHP `upload_max_filesize` should be at least 12M and `post_max_size` large enough for the files you attach (the Docker image sets this).

Deleting a category is refused while it still has articles or subcategories. Move or delete that content first so nothing is orphaned.