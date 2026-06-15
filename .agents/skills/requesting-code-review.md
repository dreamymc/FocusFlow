---
name: requesting-code-review
description: "Use when completing tasks, implementing major features, or before merging to verify work meets requirements"
risk: unknown
source: community
date_added: "2026-02-27"
---

# Requesting Code Review

Dispatch a code-reviewer subagent to catch issues before they cascade.

**Core principle:** Review early, review often.

## When to Request Review
- After each task in subagent-driven development.
- After completing a major feature.
- Before merging to the main branch.

## How to Request
1. **Get git SHAs:**
   ```bash
   BASE_SHA=$(git rev-parse HEAD~1)
   HEAD_SHA=$(git rev-parse HEAD)
   ```
2. **Dispatch code-reviewer subagent** with:
   - What was implemented.
   - Plan or requirements to check against.
   - Base and Head SHAs.
   - Summary description.
3. **Act on feedback:**
   - Fix Critical and Important issues before proceeding.
   - Note Minor issues.
