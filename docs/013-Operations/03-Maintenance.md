# Routine Maintenance & Pruning

**ID:** REQ-OPS-003
**Status:** Completed
**Last Updated:** 2026-06-29

## Context
Deal aggregation generates massive amounts of obsolete data over time. To prevent the MySQL database on shared hosting from filling up and slowing down, automated pruning is required.

## Scheduled Pruning Tasks
The following Laravel console commands must be scheduled in `app/Console/Kernel.php` (or `routes/console.php` in Laravel 11):

1. **Prune Expired Deals:** Delete deals that have been expired for more than 30 days.
   `$schedule->command('deals:prune --days=30')->daily();`
2. **Prune Telescope/Horizon Logs:** (If used) Delete old debugging logs.
3. **Prune Failed Jobs:** Clean up the `failed_jobs` table for jobs older than 7 days.
   `$schedule->command('queue:prune-failed --hours=168')->daily();`
4. **Aggressive Image Pruning:** To prevent cPanel shared hosting from hitting inode/disk quotas, forcefully delete all composite deal images older than 72 hours from `storage/app/public/deals`. Deals are ephemeral, and older deals do not require images to remain indexed.
