# Rollback Strategy

**ID:** REQ-DEP-003
**Status:** Completed
**Last Updated:** 2026-06-29

## Code Rollback
If a deployment via Git push fails or breaks production:
1. Revert the commit locally: `git revert <commit_hash>`.
2. Push the reverted commit to `main`.
3. The cPanel web-hook pulls the stable code automatically.

## Database Rollback
If a migration destroys data:
1. Put the application in maintenance mode: `php artisan down`.
2. Restore the latest SQL dump from the backup storage.
3. Bring application up: `php artisan up`.
