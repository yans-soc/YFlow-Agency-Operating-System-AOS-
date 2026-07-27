# YFlow — Frontend UI/UX System

> Enterprise UI/UX + Frontend Architecture specification for **YFlow — Agency Operating System (AOS)**.
> Derived strictly from the **existing, frozen backend** (Laravel + Sanctum, `/api/v1`). Backend logic is production-ready and **must not be modified**. This document defines the presentation layer only.
>
> Verified against source: `backend/routes/api.php`, `app/Http/Controllers/Api/*`, `app/Http/Resources/*`, `app/Http/Requests/*`, `app/Policies/*`, `database/migrations/*`, `config/auth.php`.

---

## 1. Project Overview

YFlow is an **Agency Operating System**: a single workspace where an agency runs projects, workflows (stage pipelines), tasks, people/roster, notes, files, calendar, notifications, and release/version management.

**Backend facts (source of truth):**

- **API base:** all app endpoints under `/api/v1`. Frontend `api` client already targets this base (do not re-prefix paths in services).
- **Auth:** Laravel Sanctum **bearer token** (`Authorization: Bearer <token>`). Endpoints: `register`, `login`, `logout`, `me`. Protected routes wrapped in `auth:sanctum`.
- **Tenancy:** everything is scoped to a **Workspace**. A user belongs to a workspace; resources (projects, tasks, people, etc.) carry `workspace_id` and are policy-gated per workspace.
- **Identifiers:** all primary keys are **UUID strings** (not incrementing integers). Frontend types must treat every `id` as `string`.
- **Response envelope:** Laravel API Resources wrap payloads. Single resource → `{ "data": {...} }`. Collections/paginated → `{ "data": [...], "links": {...}, "meta": { current_page, last_page, per_page, total, ... } }`. The frontend `unwrap()` helper normalizes both.
- **Errors:** validation → `422 { message, errors: { field: [msg] } }`; auth → `401`; forbidden (policy) → `403`; not found → `404`; rate limit → `429`.

**Design goal:** a polished, fast, accessible (WCAG AA) operator console. Dark-mode first-class. Minimal cognitive load. Keyboard-friendly. Consistent shell across every module.

---

## 2. User Roles

Roles are workspace-scoped and enforced by **Policies** on the backend. The UI adapts affordances to the caller's permissions; it never assumes access — it **hides/disables** actions the API would reject and gracefully handles a `403`.

| Role | Capability (as gated by policies) | UI implications |
|------|-----------------------------------|-----------------|
| **Owner / Admin** | Full CRUD across workspace: projects, workflows, people, releases, settings. | Sees admin nav (Release Management, Settings, People management). Destructive actions enabled. |
| **Member** | Create/participate in projects & tasks; manage own notes/files; read people. | Standard nav. Admin-only areas hidden. Can act on assigned/owned entities. |
| **Viewer (read-only)** | Read access to permitted resources. | Write controls hidden or disabled with tooltip “You don’t have permission”. |

> **UI rule:** Permission is discovered from the backend, not hardcoded. Use policy-driven flags where the API exposes them; otherwise attempt the action and surface `403` as a friendly toast. **No client-side authorization is authoritative.**

---

## 3. Information Architecture

```
Auth (public)
 ├─ Login
 └─ Register

App Shell (protected — Sidebar + Header + content)
 ├─ Dashboard                      (workspace overview / KPIs)
 ├─ Projects
 │   ├─ Project List               (grid/table, filter, search, paginate)
 │   └─ Project Detail             (overview • board • tasks • members • files • notes • activity)
 ├─ Workflows
 │   ├─ Workflow List
 │   └─ Workflow Builder           (ordered stages)
 ├─ Tasks                          (cross-project list + Kanban board)
 ├─ Calendar                       (events)
 ├─ People                         (roster: persons, positions, skills, departments, teams)
 ├─ Notes
 ├─ Files
 ├─ Focus                          (personal “my tasks/today” view)
 ├─ Notifications
 └─ Admin
     ├─ Release Management         (admin only)
     ├─ Changelog                  (public-facing release notes)
     └─ Settings                   (workspace / profile / appearance)
```

**Navigation model:** persistent left **Sidebar** (primary nav, collapsible), top **Header** (workspace switcher, global search, current version badge, notifications bell, user menu, theme toggle). Content area uses **Breadcrumbs** for depth ≥ 2.

---

## 4. Sitemap (routes)

| Path | Page | Access | Primary API |
|------|------|--------|-------------|
| `/login` | LoginPage | public | `POST /auth/login` |
| `/register` | RegisterPage | public | `POST /auth/register` |
| `/` → `/dashboard` | DashboardPage | auth | `GET /dashboard` |
| `/projects` | ProjectListPage | auth | `GET /projects` |
| `/projects/:id` | ProjectDetailPage | auth | `GET /projects/{id}` |
| `/workflows` | WorkflowListPage | auth | `GET /workflows` |
| `/workflows/:id` | WorkflowBuilder | auth | `GET /workflows/{id}` |
| `/tasks` | TaskListPage / Board | auth | `GET /tasks` |
| `/calendar` | CalendarPage | auth | `GET /calendar-events` |
| `/people` | PeoplePage | auth | `GET /people` |
| `/notes` | NotesPage | auth | `GET /notes` |
| `/files` | FilesPage | auth | `GET /files` |
| `/focus` | FocusPage | auth | `GET /tasks?assignee=me` (or focus endpoint) |
| `/notifications` | NotificationsPage | auth | `GET /notifications` |
| `/admin/releases` | ReleaseManagementPage | admin | `GET/POST /releases` |
| `/changelog` | ChangelogPage | auth | `GET /releases` (published) |
| `/settings` | SettingsPage | auth | `GET/PUT /workspaces/{id}`, `GET /auth/me` |

> Route model bindings on the backend use `{task}`, `{project}`, `{workflow}` etc. as **UUID** segments.

---

## 5. User Flow (core journeys)

**5.1 Authentication**
```
Landing → Login ──valid──▶ store bearer token (memory + guarded persistence) ──▶ fetch /auth/me ──▶ Dashboard
                └─invalid─▶ inline 422 field errors + error toast
Register → auto-login (token returned) → onboarding/dashboard
Logout → POST /auth/logout → clear token + query cache → Login
```

**5.2 Project → Task (primary work loop)**
```
Dashboard ▶ Projects ▶ [select project] ▶ Project Detail ▶ Board tab
Board ▶ “New Task” ▶ TaskModal (form) ▶ POST /tasks ▶ optimistic add → invalidate list
Drag card across stage ▶ POST /tasks/{id}/move-stage {stage_id} ▶ optimistic move → reconcile
Toggle done ▶ POST /tasks/{id}/toggle-complete ▶ optimistic strike-through
```

**5.3 Workflow authoring**
```
Workflows ▶ New Workflow ▶ name/desc ▶ POST /workflows
Workflow Builder ▶ add/reorder stages ▶ persist stage order ▶ workflow available to projects
```

**5.4 Release / Version**
```
Header shows current version badge (GET /releases/current or equivalent)
Admin ▶ Release Management ▶ New Release ▶ ReleaseFormModal ▶ POST /releases ▶ publish
Users ▶ Changelog ▶ read published notes
```

---

## 6. Design System

**Foundation:** Tailwind CSS + shadcn/ui (Radix primitives), tokens exposed as CSS variables in `src/index.css`, dark mode via `class` strategy on `<html>`.

### 6.1 Color (semantic tokens — HSL CSS vars)
| Token | Light | Dark | Use |
|-------|-------|------|-----|
| `--background` / `--foreground` | near-white / slate-900 | slate-950 / slate-50 | app canvas & text |
| `--card` / `--card-foreground` | white / slate-900 | slate-900 / slate-50 | surfaces |
| `--primary` | indigo/violet 600 | violet 500 | primary actions, active nav |
| `--secondary` / `--muted` | slate-100 | slate-800 | subtle surfaces, secondary btn |
| `--accent` | teal/sky | teal/sky | highlights, focus rings |
| `--destructive` | red-600 | red-500 | delete/danger |
| `--success` | emerald-600 | emerald-500 | success states/toasts |
| `--warning` | amber-500 | amber-400 | warnings |
| `--border` / `--input` / `--ring` | slate-200 | slate-800 | dividers, fields, focus |

Status/priority palettes for tasks/projects map to `success/warning/destructive/muted` for consistency (no bespoke one-off colors).

### 6.2 Typography
- **Family:** Inter (UI), `ui-monospace` for code/IDs/version strings.
- **Scale:** display 30/36 · h1 24/32 · h2 20/28 · h3 16/24 · body 14/20 · small 12/16.
- **Weight:** 400 body, 500 labels, 600 headings/emphasis. Numeric tabular for tables/metrics.

### 6.3 Spacing & Grid
- 4px base scale (`1=4px … 6=24px … 8=32px`).
- Content max-width ~1280px; page padding 24px desktop / 16px mobile.
- 12-col responsive grid; cards snap to 1/2/3/4 columns by breakpoint.

### 6.4 Radius / Elevation / Motion
- Radius: `sm 6px`, `md 8px` (default), `lg 12px`, `full` for pills/avatars.
- Shadow: subtle `sm` on cards, `md` on popovers/menus, `lg` on modals.
- Motion: 150–200ms ease-out; Framer Motion for board drag, modal/drawer, list reorder. Respect `prefers-reduced-motion`.

### 6.5 Iconography
- **lucide-react**, 16/20/24px, `currentColor`, 1.5–2 stroke. Icons paired with text labels (icon-only buttons require `aria-label`).

---

## 7. Global Components

| Component | Purpose | Key states / variants |
|-----------|---------|-----------------------|
| **AppShell** | Sidebar + Header + `<Outlet/>`. | collapsed sidebar, mobile drawer nav. |
| **Sidebar** | Primary nav, role-aware items, active highlight. | expanded / icon-only / mobile sheet. |
| **Header** | Workspace switcher, global search, **VersionBadge**, notifications, theme toggle, user menu. | scrolled shadow, unread badge. |
| **Breadcrumb** | Location context depth ≥ 2. | truncation on small screens. |
| **Button** | Actions. | `primary / secondary / outline / ghost / destructive / link`; sizes `sm/md/lg/icon`; `loading` (spinner + disabled), `disabled`. |
| **Card** | Surface for entities/metrics. | interactive (hover lift), static. |
| **DataTable** | Server-driven table: sortable headers, row actions, selection, pagination footer. | loading (skeleton rows), empty, error. |
| **FormField** | Label + control + hint + error, wired to RHF + Zod. | default/focused/error/disabled. |
| **Modal (Dialog)** | Create/edit/confirm. | open/closing, submitting, destructive-confirm. |
| **Drawer (Sheet)** | Detail/quick-edit/mobile nav. | left/right, mobile full-height. |
| **Toast (sonner)** | Global feedback. | success/error/warning/info/loading; auto-dismiss + close. |
| **Badge / Chip** | Status, priority, role, version, counts. | color per semantic token; removable (filters). |
| **Tabs** | Section switch on detail pages. | horizontal, scrollable on mobile. |
| **Avatar** | People/assignees. | image / initials fallback / stacked group with `+N`. |
| **EmptyState** | Zero-data guidance. | icon + title + subtext + primary CTA. |
| **Skeleton** | Loading placeholders. | text/card/table/avatar shapes. |
| **Pagination** | Page controls bound to `meta`. | prev/next disabled at bounds, page numbers. |
| **SearchInput** | Debounced query (300ms). | with clear button, loading spinner. |
| **FilterBar** | Facet selects + active filter chips + reset. | responsive collapse into “Filters” popover. |
| **ConfirmDialog** | Guard destructive/irreversible ops. | requires explicit confirm; danger styling. |
| **Charts** | Dashboard viz (recharts). | responsive container, loading/empty. |

---

## 8. Page Designs

> Each page: **Goal · Wireframe · Components · API Integration · States · Validation · Responsive**. All list endpoints support `?page`, `?per_page`; index endpoints support search/filter/sort query params as implemented per controller (e.g. tasks by project/stage/assignee/status). Types are UUID strings.

### 8.1 Login / Register
- **Goal:** authenticate; obtain bearer token.
- **Wireframe:**
```
┌───────────────────────────────┐
│           YFlow                │
│   ┌───────────────────────┐   │
│   │ Email  [___________]  │   │
│   │ Pass   [___________]  │   │
│   │ [ Sign in ]           │   │
│   │ New here? Register    │   │
│   └───────────────────────┘   │
└───────────────────────────────┘
```
- **Components:** centered Card, FormField ×2, Button (loading), inline error, link to opposite page.
- **API:** `POST /auth/login`, `POST /auth/register`, then `GET /auth/me`.
- **States:** loading (button spinner) · error (422 field errors + toast, 401 “invalid credentials”) · success (redirect to `/dashboard`).
- **Validation (Zod, mirrors backend rules):** email required/email; password required (min per backend). Register adds name required; confirm password matches.
- **Responsive:** single column, full-width card ≤ 420px, comfortable tap targets.

### 8.2 Dashboard
- **Goal:** at-a-glance workspace health + entry points.
- **Wireframe:**
```
Breadcrumb: Dashboard
[ KPI: Projects ] [ KPI: Active Tasks ] [ KPI: Overdue ] [ KPI: People ]
┌────────── Recent Projects ──────────┐  ┌── My Tasks / Activity ──┐
│ card · card · card                  │  │ list rows …             │
└─────────────────────────────────────┘  └─────────────────────────┘
[ Chart: tasks by stage / throughput ]
```
- **Components:** KPI Cards, Chart(s), recent-entity lists, EmptyState per section.
- **API:** `GET /dashboard` (aggregates). If a KPI or series is **not** in the response → see §12 (do not fabricate; degrade section to EmptyState/hidden).
- **States:** skeleton KPIs+cards · empty (“No projects yet — Create your first project”) · error (retry banner).
- **Responsive:** KPIs 4→2→1 columns; charts stack below.

### 8.3 Project List
- **Goal:** browse/find/create projects.
- **Wireframe:**
```
Projects                              [ + New Project ]
[ Search…........ ] [ Status ▾ ] [ Sort ▾ ]  (active chips)
┌ Grid of ProjectCards ─────────────────────────────┐
│ ▦ name · status badge · progress · members         │
└────────────────────────────────────────────────────┘
[ ‹ Prev  1 2 3  Next › ]
```
- **Components:** FilterBar, SearchInput, ProjectCard/DataTable toggle, ProjectModal, Pagination, EmptyState.
- **API:** `GET /projects?page&per_page&search&status&sort`, `POST /projects`, `PUT/DELETE /projects/{id}`.
- **States:** loading skeleton grid · empty CTA · error retry · creating (modal submitting) · optimistic add.
- **Validation:** name required (min/max per `StoreProjectRequest`); status ∈ allowed enum; dates valid; members are existing person UUIDs.
- **Responsive:** grid 4→2→1; FilterBar collapses to “Filters” popover on mobile.

### 8.4 Project Detail
- **Goal:** single workspace for one project.
- **Wireframe:**
```
Breadcrumb: Projects / Acme Rebrand
[ Header: name · status · owner · edit · … ]
[ Tabs: Overview | Board | Tasks | Members | Files | Notes | Activity ]
──────────────────────────────────────────────
(Board)  [ Stage A ][ Stage B ][ Stage C ]  ← Kanban columns of TaskCards
```
- **Components:** Tabs, KanbanBoard (Framer Motion DnD), TaskCard, member Avatars, Files/Notes lists, Activity feed, edit Modal.
- **API:** `GET /projects/{id}` (nested members/counts per ProjectResource); tasks via `GET /tasks?project={id}`; board moves `POST /tasks/{id}/move-stage`; `POST /tasks/{id}/toggle-complete`.
- **States:** loading (tab skeletons) · empty per tab · error · saving (optimistic board updates with rollback on failure).
- **Responsive:** Board horizontally scrollable on mobile; tabs become scrollable pill row.

### 8.5 Tasks (list + board)
- **Goal:** cross-project task management.
- **Components:** view toggle (Table/Board), FilterBar (project, stage, assignee, status, priority, due), DataTable, TaskCard, TaskModal, Pagination.
- **API:** `GET /tasks?project&stage&assignee&status&priority&search&sort&page`; `POST /tasks`; `PUT /tasks/{id}`; `DELETE /tasks/{id}`; `POST /tasks/{id}/move-stage {stage_id}`; `POST /tasks/{id}/toggle-complete`.
- **States:** loading · empty (“No tasks match filters — Clear filters”) · error · optimistic create/move/toggle.
- **Validation (`StoreTaskRequest`):** title required; project_id required (existing UUID); stage_id valid for project; assignees valid person UUIDs; priority/status enums; due_date valid/future where required.
- **Responsive:** Table → stacked cards on mobile; Board columns scroll horizontally.

### 8.6 Workflows + Builder
- **Goal:** define reusable ordered stage pipelines.
- **Wireframe:**
```
Workflow: Delivery Pipeline
[ + Add Stage ]
1 ▤ Intake        ⇅  ✎  🗑
2 ▤ In Progress   ⇅  ✎  🗑
3 ▤ Review        ⇅  ✎  🗑
4 ▤ Done          ⇅  ✎  🗑
```
- **Components:** WorkflowList (cards), WorkflowBuilder (sortable stage rows), inline rename, ConfirmDialog on delete.
- **API:** `GET/POST /workflows`, `GET/PUT/DELETE /workflows/{id}`; stages per WorkflowStageResource (ordered by position).
- **States:** loading · empty (“Create your first workflow”) · saving order · error rollback.
- **Validation (`StoreWorkflowRequest`):** name required; ≥1 stage; unique stage names within workflow; positions contiguous.
- **Responsive:** single-column list; drag handles remain reachable.

### 8.7 People (roster)
- **Goal:** manage persons, positions, skills, departments, teams.
- **Components:** DataTable (person, position, department, skills as chips, status), person Drawer (detail/edit), FilterBar (department/position/skill), SearchInput.
- **API:** `GET /people?search&department&position&page`; `POST/PUT/DELETE /people/{id}`; supporting `GET /departments`, `GET /positions`, `GET /skills`.
- **States:** loading table skeleton · empty · error · saving.
- **Validation (`StorePersonRequest`):** name/email required, email valid/unique; position/department UUIDs; skills array of valid UUIDs.
- **Responsive:** table → cards; Drawer becomes full-screen sheet on mobile.

### 8.8 Notes / Files
- **Goal:** capture knowledge & attachments (often tied to a project).
- **Components:** list/grid, editor Modal/Drawer (notes), file uploader + list with type/size, ConfirmDialog delete.
- **API:** Notes `GET/POST/PUT/DELETE /notes`; Files `GET/POST/DELETE /files`.
- **States:** loading · empty · error · uploading (progress) · optimistic add/remove.
- **Validation:** note title/body per `Note` request rules; file constraints (type/size) enforced by backend — surface `422` clearly.
- **Responsive:** grid 3→2→1; editor full-screen on mobile.

### 8.9 Calendar
- **Goal:** view/create events.
- **Components:** month/week toggle, event pills, create/edit Modal, mini day list on mobile.
- **API:** `GET /calendar-events?from&to`; `POST/PUT/DELETE /calendar-events/{id}`.
- **States:** loading grid · empty day · error.
- **Validation:** title required; start ≤ end; valid dates.
- **Responsive:** month grid on desktop; agenda list on mobile.

### 8.10 Focus (personal)
- **Goal:** “what should I do now” — my tasks/today.
- **Components:** grouped task lists (Today/Overdue/Upcoming), quick-complete, quick-add.
- **API:** `GET /tasks?assignee=me` (+ due filters) — or dedicated focus endpoint if present.
- **States:** loading · empty (“You’re all caught up 🎉”) · error.
- **Responsive:** single column, large tap targets.

### 8.11 Notifications
- **Goal:** review + act on system notifications.
- **Components:** list rows (read/unread), mark-read, mark-all, filter (unread/all), header bell with unread count.
- **API:** `GET /notifications`; `POST /notifications/{id}/read` (+ mark-all per controller); unread count for badge.
- **States:** loading · empty (“No notifications”) · error · optimistic mark-read.
- **Responsive:** full-width rows; bell dropdown on desktop, page on mobile.

### 8.12 Release Management + Changelog
- **Goal (admin):** publish releases; (all) read changelog.
- **Components:** Release DataTable (version, status, date), ReleaseFormModal, VersionBadge (Header), Changelog timeline.
- **API:** `GET/POST /releases`, `PUT /releases/{id}` (publish); current version endpoint for badge; published releases for Changelog.
- **States:** loading · empty · error · publishing.
- **Validation (`StoreReleaseRequest`):** version required + semver format; status enum; notes required to publish.
- **Responsive:** table → cards; timeline stacks.

### 8.13 Settings
- **Goal:** workspace + profile + appearance.
- **Components:** Tabs (Workspace / Profile / Appearance), forms, theme toggle (light/dark/system), destructive zone with ConfirmDialog.
- **API:** `GET/PUT /workspaces/{id}`, `GET /auth/me`; profile update endpoint if present.
- **States:** loading · saving · success toast · error field mapping.
- **Validation:** workspace name required; email valid; theme persisted locally.
- **Responsive:** tabs → accordion on mobile.

---

## 9. Component Library (reusable specs)

> Contract: **Props · States · Variants · Accessibility · Keyboard**. All interactive components built on Radix (shadcn/ui) → accessible by default.

**Button**
- Props: `variant`, `size`, `loading`, `disabled`, `asChild`, `leftIcon/rightIcon`, `type`.
- States: default/hover/active/focus-visible/disabled/loading.
- A11y: real `<button>`; `aria-busy` when loading; icon-only requires `aria-label`.
- Keyboard: `Enter`/`Space` activate; visible focus ring (`--ring`).

**DataTable**
- Props: `columns`, `data`, `isLoading`, `sort`, `onSortChange`, `pagination(meta)`, `onPageChange`, `rowActions`, `onRowClick`, `emptyState`.
- States: loading skeleton rows · empty slot · error slot · selected rows.
- A11y: `<table>` semantics, `<th scope>`, `aria-sort` on active column, row action menus labeled.
- Keyboard: header sort togglable via `Enter`; row menu via `Menu`/arrow keys.

**Modal (Dialog)**
- Props: `open`, `onOpenChange`, `title`, `description`, `footer`, `size`, `isSubmitting`, `destructive`.
- States: opening/closing (Framer), submitting (disabled + spinner).
- A11y: focus trap, `role="dialog"`, `aria-labelledby/-describedby`, returns focus to trigger.
- Keyboard: `Esc` closes (unless submitting), `Tab` cycles within.

**Drawer (Sheet)** — same contract as Modal with `side` prop; full-height on mobile; swipe-to-close optional.

**FormField (RHF + Zod)**
- Props: `name`, `label`, `hint`, `required`, `error`, control slot.
- States: default/focus/error/disabled.
- A11y: `<label htmlFor>`, `aria-invalid`, `aria-describedby` → hint/error; error text `role="alert"`.
- Keyboard: standard field nav; error focus on submit-fail (focus first invalid).

**Toast (sonner)** — `success/error/warning/info/loading`; auto-dismiss (4s) + manual close; `richColors`; screen-reader announced (polite/assertive for errors). Wired to Axios error interceptor for global API failures.

**Badge/Chip** — `variant` per semantic token; removable chips expose `aria-label="Remove <label>"`; `Backspace`/`Delete` removes when focused.

**Tabs** — Radix Tabs: `role="tablist"`, arrow-key navigation, `aria-selected`, content `role="tabpanel"`.

**Pagination** — bound to `meta` (`current_page`, `last_page`); disabled boundaries; buttons labeled “Previous/Next page”.

**EmptyState** — `icon`, `title`, `description`, `action`; used consistently for every zero-data surface.

**Avatar / AvatarGroup** — image with initials fallback; group truncates to `+N` with accessible label listing names.

---

## 10. Recommended Frontend Stack (with rationale)

> The repo already standardizes on this stack (`frontend/package.json`). Rationale below explains **why each fits YFlow**.

| Tech | Why it’s the right choice for YFlow |
|------|--------------------------------------|
| **React + TypeScript (Vite)** | Component model + strict types match UUID/enum-heavy API contracts; fast HMR dev loop. (Repo uses Vite SPA — see §12 note on Next.js.) |
| **React Router** | SPA routing with protected-route guards mirrors the sitemap; no SSR needed for an authenticated internal console. |
| **Tailwind CSS** | Token-driven, dark-mode via `class`, rapid consistent styling without CSS drift. |
| **shadcn/ui (Radix)** | Accessible primitives (dialog, tabs, menu, tooltip) → WCAG AA “for free”, fully themeable to our tokens. |
| **TanStack Query** | Server-state caching, background refetch, optimistic updates for board moves/toggles, automatic invalidation — perfect for a CRUD-heavy paginated API. |
| **React Hook Form + Zod** | Performant forms + schema validation that **mirrors backend Form Request rules**, giving instant client feedback while backend stays authoritative. |
| **Zustand** | Lightweight global state for auth/session, theme, UI (sidebar collapsed) without Redux overhead. |
| **Framer Motion** | Smooth Kanban drag, modal/drawer transitions, list reorder; honors `prefers-reduced-motion`. |
| **sonner** | Ergonomic accessible toasts; integrated with the Axios interceptor for global error surfacing. |
| **lucide-react** | Consistent, tree-shakeable icon set. |
| **Axios** | Central client with base `/api/v1`, bearer-token interceptor, and response `unwrap()` normalization. |

> **Next.js note:** the brief lists Next.js, but the existing codebase is a **Vite React SPA**. For an internal, fully-authenticated operator tool there is no SSR/SEO requirement, so staying on Vite is the correct, lower-friction choice. Migrating to Next.js would be a **future** option (see §13), not a requirement — and would not change any backend contract.

---

## 11. Accessibility Checklist (WCAG AA)

- [x] Color contrast ≥ 4.5:1 text / 3:1 large & UI — verify tokens in both themes.
- [x] Every interactive element keyboard-reachable; visible focus ring (`--ring`), never removed.
- [x] Radix-based dialogs/menus/tabs → focus trap, roles, `aria-*`, focus restoration.
- [x] Forms: `<label>` associations, `aria-invalid`, `aria-describedby`, `role="alert"` errors, focus first invalid on submit.
- [x] Icon-only buttons have `aria-label`; decorative icons `aria-hidden`.
- [x] Toasts announced to screen readers (assertive for errors).
- [x] Images/avatars have alt or initials fallback; charts have text summary/`aria-label`.
- [x] Respect `prefers-reduced-motion` (disable non-essential animation).
- [x] Semantic landmarks: `header`, `nav`, `main`; skip-to-content link.
- [x] Target size ≥ 44px on touch; adequate spacing.
- [x] Dark mode maintains all contrast requirements.

---

## 12. Responsive Strategy

**Breakpoints (Tailwind):** `sm 640 · md 768 · lg 1024 · xl 1280 · 2xl 1536`. **Mobile-first**: base styles target small screens, enhance upward.

| Zone | Mobile (<768) | Tablet (768–1024) | Desktop (>1024) |
|------|----------------|-------------------|-----------------|
| Sidebar | off-canvas Sheet (hamburger) | icon-only rail | expanded rail |
| Header | compact: logo + search icon + bell + menu | full | full |
| Grids (projects/cards) | 1 col | 2 col | 3–4 col |
| Tables | stacked cards / horizontal scroll | responsive table | full table |
| Kanban board | horizontal scroll, snap columns | scroll | all columns visible |
| Modals | full-screen sheet | centered dialog | centered dialog |
| FilterBar | “Filters” popover | inline (wrap) | inline |
| Tabs | scrollable pill row | inline | inline |

Principles: content-first, no horizontal page scroll (except deliberate board), sticky primary CTA on mobile, thumb-reachable actions, debounced search to limit mobile network chatter.

---

## 13. Future Improvements & Backend Gaps

> Per the brief: never assume backend implementation. Where a UI ideal needs data/endpoints not confirmed in the current contract, it is flagged as a **suggestion** — no backend change is made here.

**Presentation of over-broad responses (no backend change):** when a Resource returns more than a view needs (e.g. full nested relations on a list), the UI **selectively renders** essentials (name, status, counts, avatars) and defers the rest to the detail page/drawer — reducing cognitive load without altering payloads.

**Potential gaps to confirm with backend team (do not assume):**
1. **Dashboard aggregates** — confirm exact fields/series `GET /dashboard` returns; any missing KPI/chart series should be added server-side or the section degrades to EmptyState. *(Suggested: `projects_count`, `active_tasks`, `overdue_tasks`, `tasks_by_stage[]`.)*
2. **“My tasks / assignee=me” filter** — confirm the tasks index supports an `assignee=me` (or current-user) filter for Focus; otherwise a dedicated endpoint is suggested.
3. **Unread notification count** — confirm a lightweight count endpoint/field for the header badge to avoid fetching the full list.
4. **Global search** — header search ideally hits a unified search endpoint; if absent, scope search per-module using existing `?search=` params (suggested: `/search?q=`).
5. **Stage reorder persistence** — confirm the endpoint/shape for saving workflow stage order (bulk positions vs per-stage).
6. **File upload constraints** — confirm accepted MIME types / max size to pre-validate client-side and show accurate hints.
7. **Permission flags in responses** — exposing `can: { update, delete }` per resource would let the UI show/hide actions precisely instead of attempting + handling `403` (suggested, optional).

**Roadmap (frontend-only):**
- Code-split routes (bundle is ~538 kB; lazy-load heavy pages/board to trim initial JS).
- Command palette (⌘K) for fast navigation/actions.
- Realtime board/notifications via websockets (if backend broadcasts — events like `TaskMoved`, `NotificationSent` already exist).
- Saved views / filter presets per user.
- Optional Next.js migration only if SSR/SEO or a public marketing surface becomes a requirement.

---

### Implementation status delivered alongside this spec
- **Data layer hardened:** `lib/api` central Axios client (base `/api/v1`, bearer interceptor, `unwrap()` + typed `Paginated<T>`), and a **global error toast** interceptor.
- **Global toasts:** `sonner` installed and `<Toaster/>` mounted at app root (dark-mode aware).
- **Task service corrected** to real backend contract: UUID string IDs, no double `/api/v1` prefix, and verified endpoints `POST /tasks/{id}/move-stage` and `POST /tasks/{id}/toggle-complete`.
- **Build verified green** (`tsc && vite build`, 2133 modules, 0 errors).

*Document version: 1.0 · Scope: frontend presentation only · Backend: frozen, unmodified.*
