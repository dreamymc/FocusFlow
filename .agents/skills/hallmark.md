---
name: hallmark
description: "Anti-AI-slop design skill for greenfield pages, audits, redesigns, and components in the FocusFlow workspace."
version: 1.0.0
---

# Hallmark (Local Design Discipline)

Make the UIs generated for FocusFlow look designed, not generated.

## Visual & Component Principles

### 1. Locked Tokens
Every color and font declaration must reference a named Tailwind class or custom CSS property defined in `DESIGN_SYSTEM.md` or `resources/css/app.css` (e.g. `bg-primary`, `font-display`). **Never inline hex, rgb, or OKLCH values in markup.**

### 2. The 8-State Interactive Element Rule
Every interactive component (buttons, input fields, dropdown items, switches) must implement clear styling for all **8 states**:
1. **default** (standard state)
2. **hover** (cursor hover)
3. **focus** (`focus-visible` ring/outline)
4. **active** (click/press action)
5. **disabled** (opacity, cursor forbidden)
6. **loading** (spinners, skeleton placeholders, processing state)
7. **error** (invalid border, error message text)
8. **success** (green border, positive checkmark feedback)

### 3. Responsive Constraints
- **No horizontal scroll** on mobile sizes.
- Section layouts must collapse to a single column on mobile.
- Use early returns and flex-wrap on header and nav elements.
- Never hardcode element heights if they contain wrapping text.
