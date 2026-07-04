# Playbook: Handling Telegram API Bans

**ID:** REQ-PBK-002
**Status:** Completed
**Last Updated:** 2026-06-29

## Context
If a publisher pushes deals too aggressively, Telegram may temporarily ban their bot (`429 Too Many Requests` or `403 Forbidden`).

## Standard Operating Procedure (SOP)
1. The `PublishDealToTelegramJob` catches the `429/403` exception.
2. The job logs the error and immediately marks the `Publisher_Integration` record in the database as `status = 'paused'`.
3. An automated email (or dashboard notification) is sent to the Publisher informing them of the pause.
4. The Publisher must manually acknowledge the limit and re-enable their integration in the dashboard.
