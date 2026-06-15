# FocusFlow Design System Specification

This document details the exact design system tokens, typography rules, layout variables, and UI patterns for **FocusFlow**. All components built in subsequent phases must adhere strictly to these definitions.

---

## 🎨 Color Palette

### Core Palette
- **Primary (Indigo):** `#6366F1` (brand primary, button backgrounds, links, active states)
- **Primary Dark:** `#4F46E5` (button hover, active states)
- **Primary Light:** `#EEF2FF` (active backgrounds, accent highlights)
- **Secondary (Violet):** `#8B5CF6` (secondary highlights, alternative tags/states)
- **Secondary Dark:** `#7C3AED` (secondary hover)
- **Secondary Light:** `#F5F3FF` (secondary hover bg)

### Surfaces
- **Surface:** `#FFFFFF` (card background, dialogs, navbar background)
- **Surface 2 (Page Bg):** `#F8FAFC` (main background of the entire dashboard area)
- **Surface 3 (Kanban Bg):** `#F1F5F9` (background color for Kanban board lists/columns)
- **Sidebar:** `#FAFBFF` (barely-there blue-white tint for the navigation sidebar)

### Text Colors
- **Text Primary:** `#0F172A` (titles, body text, high-contrast labels)
- **Text Secondary:** `#475569` (subheadings, secondary labels, description text)
- **Text Muted:** `#94A3B8` (placeholder text, disabled labels, tertiary info)

### Borders & Dividers
- **Border:** `#E2E8F0` (standard card borders, input borders, dividers)
- **Border Strong:** `#CBD5E1` (borders that require stronger visibility/separators)

### Accent Colors (Notion-style labels & icons)
These 8 colors are used for workspace/project initial icons, tags, and category labels:
- **Red:** `#EF4444`
- **Orange:** `#F97316`
- **Yellow:** `#F59E0B`
- **Green:** `#10B981`
- **Blue:** `#3B82F6`
- **Purple:** `#8B5CF6`
- **Pink:** `#EC4899`
- **Gray:** `#6B7280`

### Kanban Status Colors
- **Backlog:** `#6B7280` (gray)
- **In Progress:** `#3B82F6` (blue)
- **In Review:** `#F59E0B` (amber)
- **Done:** `#10B981` (emerald)

---

## ✍️ Typography

- **Display Font:** `Plus Jakarta Sans` (weights: `600`, `700`, `800`)
  - *Usage:* Page titles, workspace names, project titles, main logo text.
- **UI Font:** `Inter` (weights: `300`, `400`, `500`, `600`)
  - *Usage:* Body copy, form labels, button labels, lists, metadata.
- **Mono Font:** `JetBrains Mono` (weights: `400`, `500`)
  - *Usage:* Task unique identifiers, timestamps, code snippets, logs.

---

## 📐 Layout & UI Rules

- **Sidebar Width:** `256px` (`var(--ff-sidebar-width)`) - fixed side navigation
- **Navbar Height:** `56px` - fixed top header
- **Page Content Padding:** `px-6 py-8` (horizontal padding `1.5rem`, vertical padding `2rem`)
- **Cards:** `rounded-lg border border-border bg-surface shadow-sm`
- **Inputs:** `rounded-md border border-border`
- **Badges:** `rounded-full` (capsule badges for status, members count, etc.)
- **Avatars:** `rounded-full` (circle avatars for team members)

---

## ✨ Signature Element: Colored Initial Icon

Every workspace and project has a signature square color icon that utilizes one of the **8 Accent Colors**:
- **Design:** A square with `rounded-lg` (or `rounded-md` for small sizes).
- **Background:** Set using `bg-accent-{color}` or equivalent color tokens.
- **Content:** The first letter of the workspace/project name, capitalized, bold, in `font-display` (Plus Jakarta Sans) and white.
- **Sizes:**
  - **Small:** `24px` (used in project sidebar navigation)
  - **Medium:** `32px` (default size, workspace listing)
  - **Large:** `40px` (used in headers and project views)
