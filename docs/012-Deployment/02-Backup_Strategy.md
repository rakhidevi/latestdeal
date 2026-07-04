# Backup Strategy

**ID:** REQ-DEP-002
**Status:** Completed
**Last Updated:** 2026-06-29

## Strategy
- **Database Backups:** Utilizing standard cPanel backup tools or Laravel's Spatie Backup package to dump the MySQL database daily at 02:00 AM.
- **Storage:** Backups must be retained on the local server for 7 days, and explicitly off-loaded to external storage (e.g., AWS S3 or Google Drive) to prevent data loss if the MilesWeb server crashes.
- **Images:** Generated deal images are considered ephemeral and can be recreated. They do not require strict offsite backups, saving storage costs.
