# /phase-start [N]
> Begins a build phase with full agent orchestration.

## Steps
1. Read `PLAN.md` and confirm Phase N tasks.
2. Update PLAN.md: set Phase N status to 🔄 In progress.
3. Invoke `architect` agent: provide Phase N task list, request ADR output.
4. Wait for ADR. Review it. If unclear, ask ONE clarifying question.
5. Check ADR for Parallel Split section.
   - If parallel tasks exist: open instructions for Terminal B/C (print to user).
   - If sequential: proceed with main session.
6. Invoke `tdd-engineer` agent for each task set. Confirm tests are RED.
7. Invoke `backend-engineer` agent per task. Confirm tests go GREEN.
8. Invoke `code-reviewer` agent. If changes requested, fix and re-review.
9. If phase involves auth/billing: invoke `security-auditor`.
10. Run `php artisan test` — must exit 0.
11. Update PLAN.md: check off all completed tasks, set status ✅.
12. `git tag v0.X.0-phase-N`

---

# /tdd-loop [feature-name]
> Runs the full TDD red-green-refactor cycle for a single feature.

## Steps
1. Load `.agents/skills/laravel-conventions.md` and `.agents/skills/pest-tdd.md`.
2. Invoke `tdd-engineer`: write tests for [feature-name]. Confirm RED.
3. Commit: `git commit -m "test: [feature-name] red"`
4. Invoke `backend-engineer`: implement until GREEN. No test modifications.
5. Commit: `git commit -m "feat: [feature-name] green"`
6. Invoke `code-reviewer`: review diff. Address any CHANGES REQUESTED.
7. Commit: `git commit -m "refactor: [feature-name] clean"`
8. Print: "✅ TDD loop complete for [feature-name]"

---

# /review
> Runs code review + security audit before a major commit.

## Steps
1. Run `git diff --staged` or `git diff HEAD~1` to get the diff.
2. Invoke `code-reviewer` agent with the diff.
3. If auth/billing/API touched: invoke `security-auditor` agent in background.
4. If both approve: print "✅ Ready to commit".
5. If changes requested: print the list and STOP. Do not commit.

---

# /parallel-check [phase-N]
> Determines what can be parallelized in a phase and prints Terminal B/C instructions.

## Steps
1. Read Phase N tasks from PLAN.md.
2. For each task, identify: which files it touches, which tasks it depends on.
3. Group into: (a) can run in parallel (no shared files, no dependencies) vs (b) must be sequential.
4. Output a ready-to-paste instruction block for Terminal B and Terminal C.
5. Remind user to write their name in `LOCK.md` before starting parallel sessions.

---

# /ship
> Final pre-deploy checklist runner.

## Steps
1. `php artisan test --coverage --min=80` — must pass.
2. `./vendor/bin/phpstan analyse --level=5` — must show 0 errors.
3. `php artisan route:list` — scan for unnamed routes, print them.
4. `php artisan horizon:status` — confirm queues healthy.
5. Run `security-auditor` agent on full codebase.
6. Confirm `README.md` has: setup instructions, live demo link, architecture diagram.
7. `docker compose up` in a new terminal — confirm cold boot works.
8. Print: "🚀 Cleared for deploy" or list blocking issues.
