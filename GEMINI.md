# FocusFlow — Agentic Orchestration File
> Read this file COMPLETELY before taking any action. It is your operating manual.

---

## 🧠 Project Identity

**FocusFlow** is a multi-tenant team productivity SaaS built in Laravel 11.
It is the primary portfolio project for a summer internship applicant.
Every architectural decision should be explainable in an interview.
Code must be production-quality: clean, tested, documented.

**Stack:** Laravel 11 · PHP 8.3 · PostgreSQL · Redis · Laravel Reverb (WebSockets)
· Laravel Horizon · Laravel Cashier (Stripe) · Sanctum · Pest PHP · Inertia.js + Vue 3

**GitHub:** `git init` on first run · commit after every completed phase task.

---

## 📚 Skills — Load Before Coding

Before writing ANY code, load the relevant skill. Skills are in `.agents/skills/`.

| Task | Load Skill |
|------|------------|
| Any Laravel PHP work | `.agents/skills/laravel-conventions.md` |
| Writing tests | `.agents/skills/pest-tdd.md` |
| API routes/resources | `.agents/skills/api-design.md` |
| WebSockets / Reverb | `.agents/skills/websockets-reverb.md` |
| Stripe / billing | `.agents/skills/stripe-cashier.md` |
| Any auth/security work | `.agents/skills/security.md` |

**Rule:** Never generate a file without the matching skill loaded. This is non-negotiable.

---

## 🤖 Sub-Agent Routing Rules

Antigravity spawns subagents dynamically. Specify the role and context inline (e.g. backend-engineer, tdd-engineer) instead of using static agent files.

### Parallel Dispatch — ALL conditions must be met:
- 3+ tasks that touch **completely different files/domains**
- No shared database schema changes pending
- No env configuration changes mid-flight
- Each task has a clear, isolated success condition

### Sequential Dispatch — ANY condition triggers:
- Task B needs output, schema, or artifact from Task A
- Both tasks write to the same file
- A migration must run before a model can be used
- Scope is unclear — understand first, then build

### Background Dispatch:
- Documentation writing while implementation continues
- Security audits that don't block the build
- Codebase analysis and research tasks

**Invocation quality rule:** Never send a sub-agent vague instructions.
Every dispatch must include: (1) exact files involved, (2) expected output, (3) success criteria.

Bad: `"Implement authentication"`
Good: `"Implement email/password registration in app/Http/Controllers/Auth/RegisterController.php
using Sanctum tokens. Output: controller, FormRequest, test in tests/Feature/Auth/RegisterTest.php.
Success: all tests pass, token returned in JSON response."`


---

## 🔁 Loop Engineering

This project is built with **loops, not one-shot prompts**.

### The TDD Loop (use for every feature):
```
1. tdd-engineer writes tests → tests MUST fail first
2. Commit failing tests: git commit -m "test: [feature] red"
3. backend-engineer implements until green
4. Commit: git commit -m "feat: [feature] green"
5. code-reviewer reviews → fixes → re-review
6. Commit: git commit -m "refactor: [feature] clean"
7. Loop exits when: all tests pass + reviewer approves
```

### The Phase Loop (macro loop for each build phase):
```
1. architect reviews phase spec → outputs ADR (Architecture Decision Record)
2. Spawn parallel sub-agents for independent tasks (if routing rules allow)
3. Sequential sub-agents for dependent tasks
4. tdd-engineer writes integration tests for the whole phase
5. security-auditor reviews anything touching auth/billing/API
6. All tests pass → phase complete → update PLAN.md status
7. git tag: v0.X.0-phase-N
```

### Loop Exit Conditions (NEVER skip these):
- ✅ `php artisan test` exits 0
- ✅ No `dd()`, `var_dump()`, or debug code in committed files
- ✅ `php artisan route:list` shows no orphaned routes
- ✅ `php artisan ide-helper:generate` runs clean
- ✅ code-reviewer sub-agent has approved the diff

### Runaway Loop Prevention:
- Max 3 retries on any single failing test before escalating to user
- If a sub-agent task exceeds 15 minutes without a commit, STOP and report
- Never mutate the database schema mid-phase without a backup migration plan

---

## 🪄 Parallel CLI Sessions

You can accelerate builds significantly by opening **multiple terminal windows**
at the same project directory. Each terminal runs its own `agents` session.
They share the filesystem, so coordination is critical.

### How to split work safely:

**Terminal A (Orchestrator)** — runs the main session, reads GEMINI.md, dispatches tasks:
```bash
cd focusflow && agy
```

**Terminal B (Backend)** — implements a feature independently:
```bash
cd focusflow && agy --max-turns 30
# Prompt: "Load .agents/agents/backend-engineer.md. Implement [Task X].
#          Only touch: app/Actions/, app/Services/, app/Http/Controllers/.
#          Do NOT touch migrations or .env."
```

**Terminal C (Tests)** — writes and runs tests in parallel:
```bash
cd focusflow && agy --max-turns 20
# Prompt: "Load .agents/agents/tdd-engineer.md. Write Pest tests for [Task X].
#          Only touch: tests/Feature/ and tests/Unit/.
#          Do NOT touch app/ code."
```

### Parallel-safe rules:
- **Never** run two agents that modify migrations simultaneously
- **Never** run two agents that modify `.env` or `config/` simultaneously
- **Always** assign strictly non-overlapping file paths to each CLI
- Use a `LOCK.md` file in root: any CLI that "owns" a domain writes its name there first
- After parallel work, the Orchestrator (Terminal A) merges and runs `php artisan test`

### Recommended Parallel Splits by Phase:

| Phase | Terminal A | Terminal B | Terminal C |
|-------|------------|------------|------------|
| Phase 2 | Workspace CRUD + migrations | Task model + API | Pest tests for both |
| Phase 3 | Reverb channels setup | Frontend Echo setup | WebSocket integration tests |
| Phase 4 | Slack integration | Stripe billing | Tests + security audit |

---

## 📁 Required Directory Structure

```
focusflow/
├── app/
│   ├── Actions/           ← One class per use case (e.g. CreateTaskAction.php)
│   ├── Services/          ← Business logic, stateful services
│   ├── DTOs/              ← Readonly PHP 8.3 data transfer objects
│   ├── Enums/             ← PHP 8.3 backed enums (TaskStatus, WorkspaceRole)
│   ├── Events/            ← Domain events (TaskCompleted, MemberInvited)
│   ├── Listeners/         ← Side effects (SendSlackNotification, etc.)
│   ├── Http/
│   │   ├── Controllers/Api/V1/   ← Versioned API controllers only
│   │   ├── Resources/            ← API Resource transformers
│   │   └── Requests/             ← FormRequest per action
│   └── Models/
├── tests/
│   ├── Feature/           ← HTTP/API integration tests
│   └── Unit/              ← Action/Service/DTO unit tests
├── .agents/
│   ├── agents/
│   ├── commands/
│   └── skills/
├── GEMINI.md              ← This file
└── PLAN.md                ← Phase tracker (update status after each task)
```

---

## ⚙️ Environment Bootstrap

On first session, run this before anything else:
```bash
composer create-project laravel/laravel focusflow
cd focusflow
composer require laravel/sanctum laravel/reverb laravel/horizon laravel/cashier
composer require spatie/laravel-permission stancl/tenancy
composer require --dev pestphp/pest pestphp/pest-plugin-laravel
npx skills add PauloFelipeM/agent-laravel-skills   # community Laravel skills
```

---

## 🧭 Current Phase

> **Update this line as you complete phases.**

`CURRENT PHASE: 0 — Setup & Scaffolding`

Completed phases: none
