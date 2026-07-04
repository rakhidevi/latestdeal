# cPanel Infrastructure Mapping

**ID:** REQ-ARC-003
**Status:** Completed
**Last Updated:** 2026-06-29

## The Problem
Standard cPanel hosting exposes the `public_html` directory directly to the internet. If you upload a standard Laravel project directly into `public_html`, sensitive files (like `.env` and `storage/`) can be publicly accessed, leading to severe security breaches.

## The Solution (Directory Mapping)
The application must be deployed split-directory style or via a secure symlink:

### Method A: Symlink (Preferred)
1. Upload the entire Laravel project to `/home/username/latestdeal_core`.
2. Delete the default `/home/username/public_html` folder.
3. Create a symlink: `ln -s /home/username/latestdeal_core/public /home/username/public_html`.

### Method B: Split Directories (Fallback)
1. Upload all Laravel files (except the `public` folder) into `/home/username/latestdeal_core`.
2. Upload the contents of Laravel's `public` folder into `/home/username/public_html`.
3. Modify `/home/username/public_html/index.php` to point to the new path:
   ```php
   require __DIR__.'/../latestdeal_core/vendor/autoload.php';
   require_once __DIR__.'/../latestdeal_core/bootstrap/app.php';
   ```

*This ensures maximum security on standard shared hosting.*
