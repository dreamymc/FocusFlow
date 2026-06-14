# FocusFlow — Phased Build Plan

> **How to use this file:**
> - Read GEMINI.md first. Always.
> - Update `[ ]` → `[x]` as tasks complete.
> - Commit after each completed task: `git commit -m "feat(phaseN): [task name]"`
> - Don't start Phase N+1 until Phase N loop exits cleanly.

---

## ◻ Phase 0 — Setup & Scaffolding
**Goal:** Working Laravel install, Git initialized, CI skeleton, all packages installed.
**Agents:** `architect` (plan) → `backend-engineer` (execute) → no parallel work yet.
**Loop:** No TDD loop here — setup tasks only. Verify with `php artisan serve`.

### Tasks:
- [x] `P0-01` Create Laravel 11 project, initialize Git, create `main` + `dev` branches
- [x] `P0-02` Install all production packages: Sanctum, Reverb, Horizon, Cashier, Spatie Permission, Tenancy
- [x] `P0-03` Install dev packages: Pest, pest-plugin-laravel, Laravel IDE Helper
- [x] `P0-04` Configure `.env.example` (PostgreSQL, Redis, Stripe, Reverb, Slack webhook)
- [x] `P0-05` Set up GitHub Actions CI: runs `php artisan test` on every push
- [x] `P0-06` Add this `.agents/` folder structure to the repo and commit
- [x] `P0-07` Write `ARCHITECTURE.md` — one-page decision log (multi-tenancy strategy, API versioning)

**Phase 0 Exit Conditions:**
- [x] `php artisan serve` starts without errors
- [x] `php artisan test` shows 0 tests, 0 failures (baseline)
- [x] GitHub Actions pipeline passes on first push
- [x] Tag: `git tag v0.1.0-phase-0`

---

## ◻ Phase 1 — Auth & Multi-Tenancy
**Goal:** Users can register, login, create workspaces. All routes are workspace-scoped.
**Agents:** `architect` → `tdd-engineer` (tests first!) → `backend-engineer` → `security-auditor`
**Loop type:** Full TDD loop per task. Red → commit → Green → commit → Review → commit.

### Parallel Opportunity:
> Open Terminal B after `P1-01` migrations are committed.
> Terminal A: implements `Workspace` model + scoping middleware
> Terminal B: implements `User` registration + Sanctum token issuance
> (Non-overlapping files ✅)

### Tasks:
- [x] `P1-01` Migrations: `users`, `workspaces`, `workspace_user` pivot (with `role` enum column)
- [x] `P1-02` Enums: `WorkspaceRole` (Admin, Member, Viewer), `InviteStatus`
- [x] `P1-03` Models: `User`, `Workspace` with relationships + `$fillable`
- [x] `P1-04` `RegisterAction` + `CreateWorkspaceAction` — one class per use-case
- [x] `P1-05` `RegisterController`, `LoginController`, `LogoutController` in `Api/V1/Auth/`
- [x] `P1-06` FormRequests: `RegisterRequest`, `LoginRequest` with validation rules
- [x] `P1-07` Sanctum setup: token issuance on login, revocation on logout
- [x] `P1-08` Spatie Permission: seed roles (`admin`, `member`, `viewer`) per workspace
- [x] `P1-09` `WorkspaceScope` middleware — all subsequent requests scoped to workspace
- [x] `P1-10` `InviteMemberAction` + `AcceptInviteAction` (email invite flow)
- [x] `P1-11` API Resources: `UserResource`, `WorkspaceResource`
- [x] `P1-12` Feature tests (Pest): register, login, logout, create workspace, invite member
- [x] `P1-13` Security audit: OWASP top 10 check on auth endpoints

**Phase 1 Exit Conditions:**
- [x] `php artisan test --filter=Auth` → all green
- [x] Postman collection: auth flows all return correct status codes
- [x] `security-auditor` agent approved
- [x] Tag: `git tag v0.2.0-phase-1`

---

## ◻ Phase 2 — Task Management (REST API Core)
**Goal:** Full CRUD for Projects and Tasks, versioned REST API with proper resources.
**Agents:** `architect` → parallel `tdd-engineer` + `backend-engineer` → `code-reviewer`
**Loop type:** TDD loop per resource. Architect signs off on structure first.

### Parallel Opportunity:
> After P2-01 migrations committed:
> Terminal A: Project CRUD (controller + actions + tests)
> Terminal B: Task CRUD (controller + actions + tests)
> Terminal C: Tag/Label system (optional, lower priority)
> (File paths are non-overlapping ✅)

### Tasks:
- [x] `P2-01` Migrations: `projects`, `tasks`, `task_assignees`, `labels`, `task_label` pivot
- [x] `P2-02` Enums: `TaskStatus` (Backlog, InProgress, InReview, Done), `TaskPriority`
- [x] `P2-03` Models: `Project`, `Task`, `Label` with Eloquent relationships
- [x] `P2-04` Eloquent scopes: `scopeForWorkspace()`, `scopeAssignedTo()`, `scopeByStatus()`
- [x] `P2-05` Actions: `CreateProjectAction`, `UpdateProjectAction`, `DeleteProjectAction`
- [x] `P2-06` Actions: `CreateTaskAction`, `UpdateTaskAction`, `MoveTaskAction`, `DeleteTaskAction`
- [x] `P2-07` Controllers: `ProjectController`, `TaskController` (RESTful, thin, call Actions)
- [x] `P2-08` FormRequests: `StoreProjectRequest`, `StoreTaskRequest`, `UpdateTaskRequest`
- [x] `P2-09` API Resources: `ProjectResource`, `TaskResource`, `TaskCollection` (with pagination)
- [x] `P2-10` Route versioning: all routes under `/api/v1/workspaces/{workspace}/...`
- [x] `P2-11` Rate limiting: 60 req/min per token via `RateLimiter::for('api', ...)`
- [x] `P2-12` Filtering: `?status=in_progress&assignee=me&sort=priority` query params
- [x] `P2-13` Feature tests: full CRUD for Project and Task, authorization tests per role
- [x] `P2-14` Authorization: Policies (`ProjectPolicy`, `TaskPolicy`) — Viewer can't write

**Phase 2 Exit Conditions:**
- [x] `php artisan test --filter=Task,Project` → all green
- [x] `php artisan route:list` shows no unnamed routes
- [x] API returns consistent JSON structure: `{data: ..., meta: ..., links: ...}`
- [x] Tag: `git tag v0.3.0-phase-2`

---

## ◻ Phase 3 — Real-Time Features (WebSockets)
**Goal:** Live task updates pushed to all workspace members via Laravel Reverb.
**Agents:** `architect` → `backend-engineer` (Reverb channels) || `backend-engineer` (Vue/Echo)
**Loop type:** TDD loop with fake broadcasting assertions. Then manual smoke test.

> ⚠️ **SEQUENTIAL FIRST:** Architect must define channel authorization strategy before any code.
> Channels are: `workspace.{id}` (private) and `task.{id}` (presence).

### Tasks:
- [x] `P3-01` Configure Laravel Reverb: `config/reverb.php`, `.env` Reverb keys
- [x] `P3-02` Events to broadcast: `TaskMoved`, `TaskAssigned`, `TaskCommented`
- [x] `P3-03` Implement `BroadcastServiceProvider` channel authorization
- [x] `P3-04` Private channel: `workspace.{workspaceId}` — members only
- [x] `P3-05` Presence channel: `task.{taskId}` — shows who's viewing a task
- [x] `P3-06` Fire `TaskMoved` event inside `MoveTaskAction` (not the controller)
- [x] `P3-07` Frontend: install `laravel-echo`, `pusher-js`, configure Echo with Reverb
- [x] `P3-08` Vue composable: `useTaskUpdates(taskId)` — subscribes to channel updates
- [x] `P3-09` Real-time notification bell component (Vue) consuming `workspace.{id}` channel
- [x] `P3-10` Feature tests: assert `Broadcasting::assertPushed(TaskMoved::class)` on task move

### Parallel Opportunity:
> After P3-01 and P3-02 are committed:
> Terminal A: P3-03 through P3-06 (backend broadcast setup)
> Terminal B: P3-07 through P3-09 (frontend Echo setup)
> (Completely different files: app/Events/ vs resources/js/ ✅)

**Phase 3 Exit Conditions:**
- [ ] `php artisan test --filter=Broadcast` → all green
- [ ] Manual test: open two browser tabs → move task in Tab A → Tab B updates without refresh
- [ ] Tag: `git tag v0.4.0-phase-3`

---

## ◻ Phase 4 — Integrations & Billing
**Goal:** Slack notifications, Stripe subscription plans, email digests.
**Agents:** `architect` → parallel `integration-specialist` + `backend-engineer` → `security-auditor`
**Loop type:** TDD loop for each integration. Mocked HTTP calls in tests.

### Parallel Opportunity (AFTER architecture review):
> Terminal A: Stripe/Cashier (billing + webhook handler)
> Terminal B: Slack webhook + email digest (event-driven)
> (Only shared concern: `User`/`Workspace` model — coordinate P4-01 first)

### Stripe Billing Tasks:
- [x] `P4-01` `plans` table migration: `free`, `pro` tiers with feature flags
- [x] `P4-02` Laravel Cashier setup: `Billable` trait on `Workspace` model
- [x] `P4-03` `SubscribeWorkspaceAction`, `CancelSubscriptionAction`
- [x] `P4-04` Stripe webhook handler: `WebhookController` handles `invoice.paid`, `customer.subscription.deleted`
- [x] `P4-05` Gate: `workspace.pro` — blocks member invites beyond 3 on free plan
- [x] `P4-06` Billing portal route (Cashier's `billingPortal()` redirect)
- [x] `P4-07` Tests: mock Stripe webhooks using `Http::fake()`, test gate enforcement

### Slack + Email Tasks:
- [x] `P4-08` `SlackNotificationService` — sends webhook when `TaskCompleted` event fires
- [x] `P4-09` `TaskCompleted` event → `SendSlackNotification` listener (queued)
- [x] `P4-10` `WeeklyDigestMail` — mailable sent every Monday via `schedule()`
- [x] `P4-11` Queue worker via Laravel Horizon (Redis): configure supervisor groups
- [x] `P4-12` Tests: fake HTTP for Slack, use `Mail::fake()` for digest

**Phase 4 Exit Conditions:**
- [ ] `php artisan test --filter=Billing,Integration,Slack` → all green
- [ ] `security-auditor` reviews Stripe webhook signature verification
- [ ] `php artisan horizon:status` shows queues healthy
- [ ] Tag: `git tag v0.5.0-phase-4`

---

## ◻ Phase 5 — Hardening & Test Coverage
**Goal:** ≥80% test coverage, zero security findings, production-ready error handling.
**Agents:** `tdd-engineer` + `security-auditor` in parallel, `code-reviewer` after
**Loop type:** Coverage loop — run Pest with `--coverage`, add tests until ≥80%.

### Tasks:
- [x] `P5-01` Add missing unit tests: all Action classes, all Service classes
- [x] `P5-02` Add missing feature tests: role authorization matrix (all roles × all endpoints)
- [x] `P5-03` Add missing tests: WebSocket channel auth (unauthorized user denied)
- [x] `P5-04` `Handler.php` — custom JSON error responses (no HTML stack traces in API)
- [x] `P5-05` Throttle brute-force on `/api/v1/login`: 5 attempts / 60 seconds
- [x] `P5-06` SQL injection audit: confirm all queries use Eloquent or query builder bindings
- [x] `P5-07` Add `X-Content-Type-Options`, `X-Frame-Options` headers via middleware
- [x] `P5-08` Sanctum token expiry: tokens expire after 30 days (configurable)
- [x] `P5-09` Run `php artisan test --coverage` → must hit ≥80%
- [x] `P5-10` Fix all PHPStan level 5 violations: `./vendor/bin/phpstan analyse`

**Phase 5 Exit Conditions:**
- [x] Test coverage ≥ 80%
- [x] PHPStan level 5: no errors
- [x] `security-auditor` sign-off
- [x] Tag: `git tag v0.6.0-phase-5`

---

## ◻ Phase 6 — Portfolio Polish
**Goal:** The repo impresses a recruiter who opens it cold.
**Agents:** background `docs-writer` while `backend-engineer` polishes UX
**Loop type:** Review loop — show recruiter checklist, tick each box.

### Tasks:
- [ ] `P6-01` `README.md` — hero section, feature list, architecture diagram (ASCII or Mermaid), setup guide
- [ ] `P6-02` `docs/API.md` — full endpoint reference with example requests/responses
- [ ] `P6-03` `docs/ARCHITECTURE.md` — why multi-tenancy this way, why Actions not fat controllers
- [ ] `P6-04` Postman collection: exported and committed to `docs/postman/`
- [ ] `P6-05` Demo seeder: `php artisan db:seed --class=DemoSeeder` populates realistic data
- [ ] `P6-06` Docker Compose: `docker-compose.yml` → one command to boot PostgreSQL + Redis + Reverb
- [ ] `P6-07` Record 2-min Loom demo video, link from README
- [ ] `P6-08` Deploy to production (Railway or Fly.io), link live demo in README

**Phase 6 Exit Conditions:**
- [ ] `docker compose up && php artisan serve` — working from cold clone in < 5 minutes
- [ ] README renders cleanly on GitHub with no broken links
- [ ] Live demo URL works
- [ ] Tag: `git tag v1.0.0` 🎉

---

## 📊 Progress Tracker

| Phase | Status | Commit | Tag |
|-------|--------|--------|-----|
| 0 — Setup | ✅ Complete | — | v0.1.0-phase-0 |
| 1 — Auth | ✅ Complete | — | v0.2.0-phase-1 |
| 2 — API | ✅ Complete | — | v0.3.0-phase-2 |
| 3 — WebSockets | ✅ Complete | — | v0.4.0-phase-3 |
| 4 — Integrations | ✅ Complete | — | v0.5.0-phase-4 |
| 5 — Hardening | ✅ Complete | — | v0.6.0-phase-5 |
| 6 — Polish | ⬜ Not started | — | — |

> Update status: ⬜ Not started → 🔄 In progress → ✅ Complete
