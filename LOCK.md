# LOCK.md — Parallel CLI Coordination

> This file prevents two parallel CLI sessions from writing to the same files.
> Any CLI session that "owns" a domain MUST write its terminal name here first.
> Clear your entry when done. Last write wins — coordinate with your terminals.

---

## Active Sessions

| Terminal A (Backend) | database/migrations/, app/Models/, app/Enums/, app/Actions/, app/Http/ | Phase 1 & 2 Implementation | In Progress |
| — | — | — | — |

---

## Instructions for Parallel Work

### Before Starting Your Session
1. Open this file.
2. Add a row for your terminal with the exact directories/files you will touch.
3. Confirm no other row overlaps with yours.
4. If overlap exists: STOP. Coordinate with the other terminal first.

### After Finishing Your Session
1. Remove your row from the table.
2. Commit: `git commit -m "chore: parallel session [terminal] complete"`
3. The orchestrator (Terminal A) runs `php artisan test` to merge-verify.

---

## Parallel-Safe Zone Examples

```
Phase 2 — Safe to parallelize after P2-01 migrations committed:

Terminal A: app/Actions/Project*, app/Http/Controllers/Api/V1/ProjectController.php,
            app/Http/Requests/StoreProjectRequest.php, tests/Feature/Api/V1/ProjectTest.php

Terminal B: app/Actions/Task*, app/Http/Controllers/Api/V1/TaskController.php,
            app/Http/Requests/StoreTaskRequest.php, tests/Feature/Api/V1/TaskTest.php

Terminal C: (background) docs/API.md

NO OVERLAP ✅
```

## DANGER ZONES — Never Parallelize

- `database/migrations/` — always one CLI at a time
- `config/` — always one CLI at a time
- `.env` — always one CLI at a time
- `app/Models/*.php` when adding relationships — coordinate first
- `routes/api.php` — always one CLI at a time
