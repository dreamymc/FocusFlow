---
name: systematic-debugging
description: "Use when encountering any bug, test failure, or unexpected behavior, before proposing fixes"
risk: unknown
source: community
date_added: "2026-02-27"
---

# Systematic Debugging

## Overview

Random fixes waste time and create new bugs. Quick patches mask underlying issues.

**Core principle:** ALWAYS find root cause before attempting fixes. Symptom fixes are failure.

## The Iron Law

```
NO FIXES WITHOUT ROOT CAUSE INVESTIGATION FIRST
```

If you haven't completed Phase 1 (Root Cause Investigation), you cannot propose fixes.

## The Four Phases

You MUST complete each phase before proceeding to the next.

### Phase 1: Root Cause Investigation
**BEFORE attempting ANY fix:**
1. **Read Error Messages Carefully**: Read stack traces completely, note line numbers, files, and error codes.
2. **Reproduce Consistently**: Establish steps to trigger it reliably.
3. **Check Recent Changes**: Git diffs, recent commits, dependency, and config changes.
4. **Gather Evidence in Multi-Component Systems**: Log what data enters/exits component boundaries to isolate the failing component.
5. **Trace Data Flow**: Trace backward from where the bad value originated.

### Phase 2: Pattern Analysis
**Find the pattern before fixing:**
1. **Find Working Examples**: Locate similar working code in the same codebase.
2. **Compare Against References**: Read reference implementations completely.
3. **Identify Differences**: List every difference, however small, between working and broken.
4. **Understand Dependencies**: Check settings, config, and environment assumptions.

### Phase 3: Hypothesis and Testing
**Scientific method:**
1. **Form Single Hypothesis**: State clearly: "I think X is the root cause because Y."
2. **Test Minimally**: Make the smallest possible change to test the hypothesis (one variable at a time).
3. **Verify Before Continuing**: If it worked, move to Phase 4. If not, form a new hypothesis; do not layer fixes.

### Phase 4: Implementation
**Fix the root cause, not the symptom:**
1. **Create Failing Test Case**: Simplest possible reproduction in an automated test.
2. **Implement Single Fix**: Address the identified root cause with one change.
3. **Verify Fix**: Ensure tests pass and no other tests are broken.
4. **If Fix Fails**: Return to Phase 1. If 3+ fixes fail, stop and question the architecture.
