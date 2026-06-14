# Agent Instructions

## Critical Memory Reminder 🚨
BEFORE taking ANY action, you **MUST** run the `using-superpowers` skill. 
You have dozens of global skills. Do not ignore them.
- Fixing a bug? **MUST** use `systematic-debugging`.
- Finishing a task? **MUST** use `requesting-code-review`.
- Pre-push audit? **MUST** use `codebase-audit-pre-push`.

## Package Manager
Use **composer** and **pnpm**: `composer require`, `php artisan test`, `pnpm run dev`

## File-Scoped Commands
| Task | Command |
|------|---------|
| Test | `php artisan test --filter=TestClassName` |
| Route Check | `php artisan route:list` |


## Workflows
Follow the loops defined in `GEMINI.md` and `PLAN.md`.
Use `dispatching-parallel-agents` when possible.
