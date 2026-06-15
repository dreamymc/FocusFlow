---
name: codebase-audit-pre-push
description: "Deep audit before GitHub push: removes junk files, dead code, security holes, and optimization issues. Checks every file line-by-line for production readiness."
category: development
risk: safe
source: community
date_added: "2026-03-05"
---

# Pre-Push Codebase Audit

As a senior engineer, perform the final review before pushing code to GitHub.

## Audit Process

### 1. Clean Up Junk Files
- **Delete immediately:** OS files (`.DS_Store`), logs (`*.log`), temp files (`*.tmp`, `*.cache`), IDE configs (`.idea/`, `.vscode/` if gitignored), test artifacts.
- **Check for secrets:** Ensure `.env` files are not committed, scan for hardcoded keys, passwords, or tokens.

### 2. Verify `.gitignore`
- Ensure `.gitignore` is comprehensive, blocking environment variables, dependencies, and caches. Verify `.env.example` contains structure but no actual values.

### 3. Audit Source Files
- **Dead Code (remove):** Commented-out code blocks, unused imports, unused variables, and unreachable returns.
- **Code Quality:** Remove debug logs (`console.log`, `print`, `dd()`, `dump()`). Ensure clear naming.
- **Logic Issues:** Check for missing error handling, unhandled promises, and infinite loops.

### 4. Security Check
- **Injection:** Verify parameterized query usage for database queries.
- **Authorization:** Confirm authentication/authorization is checked on the server side, not just in the UI (no IDOR).
- **Errors:** Ensure no database schemas or stack traces are leaked in production error responses.

### 5. Performance & Scalability
- Ensure no N+1 database queries.
- Verify heavy long-running operations are pushed to queues.
- Optimize images and styles where applicable.
