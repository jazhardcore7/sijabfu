# SIJABFU — UI Revamp Plan

*Status: proposed, waiting for a choice of design system and "go". Base branch: `revision/pdf-plan-2026` at `d224b29`. Implementation branch: `revision/ui-revamp`.*

Scope: appearance and usability only. Routes, controllers, models, policies, migrations, middleware, permissions, validation, config, `.env`, and every Indonesian label and status name stay unchanged.

Source of the design candidates: the `ui-ux-pro-max` skill (`--design-system` with variance 2, motion 2, density 7; typography and UX searches; Laravel stack search). Contrast ratios below were computed with the WCAG 2.x formula.

---

## 1. Design-system candidates

All three searches returned the same style, **Minimalism & Swiss Style**: flat, grid-based, high-contrast, sans-serif, low motion. The skill lists it as best for enterprise apps and dashboards, with low accessibility risk. The palettes it returned were generic, so each candidate below keeps that style and maps the palette onto BPS blue (`#003B73` navy, `#0077B6` mid), taken from the existing views.

### A. Swiss Institutional (recommended)
- **Source:** the skill's "government public service workflow dashboard" result: Swiss style, "High contrast navy + blue" palette.
- **Feel:** white cards on a light grey background, navy sidebar, one blue accent, thin borders, almost no shadow.
- **Font:** **Plus Jakarta Sans**, which the app already loads. It renders Indonesian well and has tabular numbers (`font-variant-numeric: tabular-nums`) for tables and counts. Keeping it adds no dependency. The skill's own government pick, *Atkinson Hyperlegible*, would mean a new CDN font, which needs your approval.
- **Why it fits:** formal and neutral, matches the BPS identity, makes tables and status badges stand out, and has the lowest accessibility risk.

| Role | Light | Dark |
|---|---|---|
| Background | `#F4F6F9` | `#0B1220` |
| Surface (card) | `#FFFFFF` | `#111A2E` |
| Surface alt (table head, hover) | `#F8FAFC` | `#16213A` |
| Border | `#E2E8F0` | `#26354F` |
| Text | `#0F172A` (17.8:1) | `#E6EDF5` (15.9:1) |
| Text muted | `#475569` (7.6:1) | `#9FB0C6` (7.8:1) |
| Heading | `#003B73` (10.4:1) | `#93C5FD` (9.6:1) |
| Primary (button fill) | `#00509D`, white text 8.0:1 | `#60A5FA`, `#0B1220` text 7.4:1 |
| Link / focus ring | `#00509D` / `#0077B6` (4.9:1) | `#7CB8FF` (8.4:1) / `#7CB8FF` |
| Sidebar | `#003B73` | `#0A1628` |
| Danger | `#B91C1C` | `#FCA5A5` |

### B. Data-dense Operations
- **Source:** the "enterprise admin dashboard data tables" result: Swiss style, "dark tech + status colors", data-dense but easy to scan. Its green accent is replaced by BPS blue.
- **Feel:** compact rows (36 px instead of 44 px), smaller type (14 px body), dark mode as the default look, status colours more prominent.
- **Font:** the skill suggests Fira Sans, which would need approval. Plus Jakarta Sans works too.
- **Palette:** the same tokens as A, with the dark palette as the main one, 8 px spacing steps, and a 6 px radius.
- **Why not first:** it suits Tim JF's monitoring work, but Pegawai use the app occasionally and need larger targets and calmer screens.

### C. Corporate Trust (soft)
- **Source:** the skill's typography result "Corporate Trust" (Lexend + Source Sans 3; for enterprise, government, accessibility) with A's palette. The skill's teal/orange palette was rejected because it isn't BPS.
- **Feel:** larger type (17 px body), rounder cards (16 px radius), more white space.
- **Font:** two families, which breaks the "one font" rule, and both are new CDN fonts that need approval.
- **Why not first:** two new fonts, and more scrolling on data-heavy pages.

**Recommendation: A**, with Plus Jakarta Sans and no new dependency.

---

## 2. Status colours (same in badges, tracker and charts)

Every badge keeps its text label (for example "Perbaikan"), so colour is never the only signal. Text contrast is on the tinted badge background; bar contrast is against the card surface (3:1 minimum for non-text).

| Status | Badge light (text / tint) | Badge dark (text / tint) | Bar / dot light | Bar / dot dark |
|---|---|---|---|---|
| verifikasi | `#1D4ED8` / `#DBEAFE` (5.5:1) | `#BFDBFE` / `#1E3A5F` (8.1:1) | `#2563EB` (5.2:1) | `#60A5FA` (6.8:1) |
| perbaikan | `#92400E` / `#FEF3C7` (6.4:1) | `#FDE68A` / `#3F2D0A` (10.6:1) | `#D97706` (3.2:1) | `#FBBF24` (10.4:1) |
| disetujui | `#166534` / `#DCFCE7` (6.5:1) | `#BBF7D0` / `#123A24` (10.5:1) | `#16A34A` (3.3:1) | `#4ADE80` (10.0:1) |
| ditetapkan | `#5B21B6` / `#EDE9FE` (7.6:1) | `#DDD6FE` / `#2E2452` (10.2:1) | `#7C3AED` (5.7:1) | `#A78BFA` (6.4:1) |
| selesai | `#115E59` / `#CCFBF1` (6.7:1) | `#99F6E4` / `#0D3B37` (9.8:1) | `#0D9488` (3.7:1) | `#2DD4BF` (9.3:1) |

Today each page maps statuses to colours differently (`badge-step-1…5` is defined three times, and the trackers use their own done/active colours). The revamp gives every status one class: `.status-badge[data-status="…"]`.

---

## 3. Audit of the current UI

### 3.1 Pages (GET routes) and their views
| Route | View | Role |
|---|---|---|
| `/login` | `auth/login` (stand-alone) | guest |
| `/auth/sso/redirect` | `auth/sso-fake` (stand-alone) | guest |
| `/dashboard` (pegawai) | `landing` + `dashboard/_pegawai-panel` | pegawai |
| `/dashboard` (staff) | `admin/dashboard` + `dashboard/_staf-panel` | tim_jf, super_admin |
| `/pengajuan` | `modules/form-usulan` | pegawai |
| `/pelacakan-status` | `modules/progress-berkas` | pegawai |
| `/regulasi` | `modules/regulasi` | all |
| `/tim-kerja`, `/tim-kerja/{user}` | `modules/tim-kerja`, `modules/tim-kerja-profile` | all |
| `/chat` | `modules/chat` + `chat/_conversation` | pegawai |
| `/alur-layanan` | `modules/alur-layanan` | all |
| `/verifikasi`, `/verifikasi/{usulan}` | `verifikasi/index`, `verifikasi/show` (+ `_styles`) | tim_jf, super_admin |
| `/monitoring` | `admin/monitoring` | tim_jf, super_admin |
| `/admin/regulasi` | `admin/regulasi` | tim_jf, super_admin |
| `/messenger`, `/messenger/{thread}` | `messenger/index` + `chat/_conversation` | tim_jf, super_admin |
| `/admin/users` | `admin/users` | super_admin |
| `/admin/sso-sync` | `admin/sso-sync` | super_admin |
| shared | `layouts/app`, `partials/sidebar`, `partials/notifications`, `partials/theme`, `modules/partials/legacy-links` | all |
| redirects only | `/`, `/admin`, `/admin/monitoring`, `/form-usulan`, `/progress-berkas`, `/uji-kompetensi`, file and notification routes | — |

### 3.2 Repeated inline CSS
- **3,928 lines** of `<style>` across 22 views. The largest: `admin/monitoring` 599, `admin/dashboard` 524, `layouts/app` 504, `landing` 414, `auth/login` 399, `modules/form-usulan` 351, `modules/progress-berkas` 274, `modules/regulasi` 229.
- **57 class selectors are defined in more than one file**, for example `.badge-step-1…5` (3 files), `.card`, `.badge`, `.hero-banner`, `.hero-heading`, `.top-navbar`, `.footer-bar`, `.main-container`, `.search-icon`, `.pb-progress-track`, `.pb-hero-name`.
- **30 different button classes** for about 5 real button types: `btn-filter`, `btn-reset`, `btn-update-submit`, `btn-rollback`, `fu-btn-submit`, `fu-btn-secondary`, `vb-btn`, `doc-btn`, `download-btn`, `preview-btn`, `mon-btn-link`, `btn-hapus`, `btn-save-status`, `dash-btn-sync`, and more.
- **Three separate style sets** for the same patterns: `vb-*` (Verifikasi, Users, SSO, Regulasi admin), `pb-*` (Pelacakan and Pengajuan), and the admin dashboard's own.

### 3.3 Usability problems
1. **Emoji used as icons: 189**, in the sidebar, cards, buttons and headings. They render differently on each OS, screen readers announce them, and they sit badly with SVG. The skill flags this as an anti-pattern.
2. **Heavy gradients and decorative motion:** gradient heroes, buttons and sidebar; `animate-fade-in` on almost every block, so content flashes in on load. Both go against the design direction.
3. **Small type:** many labels are 10–11.5 px (table meta, badges, chart labels), below the 12 px minimum.
4. **Inconsistent buttons:** heights from 28 to 48 px, mixed radius, several touch targets under 44 px (small links in tables such as "Edit", "Hapus", "Preview").
5. **Focus states:** only native outlines; custom buttons and `<summary>` dropdowns have no visible focus style.
6. **Tables on mobile:** fixed since `d224b29` (each table scrolls inside its container), but rows are dense and there's no mobile card layout.
7. **Empty states:** present on most lists (usulan, regulasi, chat, messenger, users, sync history) but each styled differently. Missing on the dashboard link cards and the staff chart panels when there's no data.
8. **Loading and error states:** chat sending shows nothing while waiting, and failures only show an alert. Form errors are a single list at the top; there's no error message beside each field on Regulasi admin or Daftar User.
9. **Hero sections:** both dashboards and the login page still open with a large marketing-style hero and illustration, which pushes the working content down.
10. **Fonts:** both Plus Jakarta Sans and Inter are loaded; Inter is barely used.

---

## 4. Design tokens (recommended candidate A)

Delivered in **one shared stylesheet**, replacing the variables in `partials/theme.blade.php`. The existing `--c-*` names stay as aliases so nothing breaks during the change.

```css
:root, [data-theme="light"] {
  color-scheme: light;
  /* colour: base */
  --c-bg: #F4F6F9;  --c-surface: #FFFFFF;  --c-surface-2: #F8FAFC;  --c-surface-3: #F1F5F9;
  --c-border: #E2E8F0;  --c-border-soft: #F1F5F9;  --c-border-strong: #CBD5E1;
  --c-text: #0F172A;  --c-text-2: #334155;  --c-text-muted: #475569;  --c-text-faint: #64748B;
  --c-heading: #003B73;
  /* colour: brand and actions */
  --c-primary: #00509D;  --c-primary-hover: #003B73;  --c-on-primary: #FFFFFF;
  --c-link: #00509D;  --c-focus: #0077B6;
  --c-sidebar: #003B73;  --c-sidebar-text: #E6EDF5;  --c-sidebar-active: rgba(255,255,255,.14);
  --c-danger: #B91C1C;  --c-danger-bg: #FEE2E2;  --c-success: #166534;  --c-success-bg: #DCFCE7;
  --c-warning: #92400E;  --c-warning-bg: #FEF3C7;  --c-info: #1D4ED8;  --c-info-bg: #DBEAFE;
  /* status (badge text / tint / chart bar) */
  --s-verifikasi: #1D4ED8; --s-verifikasi-bg: #DBEAFE; --s-verifikasi-bar: #2563EB;
  --s-perbaikan:  #92400E; --s-perbaikan-bg:  #FEF3C7; --s-perbaikan-bar:  #D97706;
  --s-disetujui:  #166534; --s-disetujui-bg:  #DCFCE7; --s-disetujui-bar:  #16A34A;
  --s-ditetapkan: #5B21B6; --s-ditetapkan-bg: #EDE9FE; --s-ditetapkan-bar: #7C3AED;
  --s-selesai:    #115E59; --s-selesai-bg:    #CCFBF1; --s-selesai-bar:    #0D9488;
}
[data-theme="dark"] {
  color-scheme: dark;
  --c-bg: #0B1220;  --c-surface: #111A2E;  --c-surface-2: #16213A;  --c-surface-3: #1C2944;
  --c-border: #26354F;  --c-border-soft: #1F2C46;  --c-border-strong: #33456A;
  --c-text: #E6EDF5;  --c-text-2: #CBD5E1;  --c-text-muted: #9FB0C6;  --c-text-faint: #8193AD;
  --c-heading: #93C5FD;
  --c-primary: #60A5FA;  --c-primary-hover: #93C5FD;  --c-on-primary: #0B1220;
  --c-link: #7CB8FF;  --c-focus: #7CB8FF;
  --c-sidebar: #0A1628;  --c-sidebar-text: #E6EDF5;  --c-sidebar-active: rgba(124,184,255,.16);
  --c-danger: #FCA5A5;  --c-danger-bg: #3B1414;  --c-success: #BBF7D0;  --c-success-bg: #123A24;
  --c-warning: #FDE68A;  --c-warning-bg: #3F2D0A;  --c-info: #BFDBFE;  --c-info-bg: #1E3A5F;
  --s-verifikasi: #BFDBFE; --s-verifikasi-bg: #1E3A5F; --s-verifikasi-bar: #60A5FA;
  --s-perbaikan:  #FDE68A; --s-perbaikan-bg:  #3F2D0A; --s-perbaikan-bar:  #FBBF24;
  --s-disetujui:  #BBF7D0; --s-disetujui-bg:  #123A24; --s-disetujui-bar:  #4ADE80;
  --s-ditetapkan: #DDD6FE; --s-ditetapkan-bg: #2E2452; --s-ditetapkan-bar: #A78BFA;
  --s-selesai:    #99F6E4; --s-selesai-bg:    #0D3B37; --s-selesai-bar:    #2DD4BF;
}
:root {
  /* type: one family, numbers aligned in tables */
  --font: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
  --fs-xs: 12px; --fs-sm: 13px; --fs-base: 15px; --fs-md: 16px; --fs-lg: 18px; --fs-xl: 22px; --fs-2xl: 28px;
  --fw-regular: 400; --fw-medium: 500; --fw-semibold: 600; --fw-bold: 700;
  --lh-tight: 1.25; --lh-base: 1.5;
  /* spacing (4 px steps) */
  --sp-1: 4px; --sp-2: 8px; --sp-3: 12px; --sp-4: 16px; --sp-5: 20px; --sp-6: 24px; --sp-8: 32px; --sp-10: 40px;
  /* radius */
  --r-sm: 6px; --r-md: 8px; --r-lg: 12px; --r-pill: 999px;
  /* shadow (flat: borders do most of the work) */
  --sh-1: 0 1px 2px rgba(15,23,42,.06); --sh-2: 0 4px 12px rgba(15,23,42,.08); --sh-pop: 0 10px 30px rgba(15,23,42,.16);
  /* layout and motion */
  --sidebar-w: 260px; --topbar-h: 60px; --control-h: 40px; --touch: 44px;
  --dur: 150ms; --ease: cubic-bezier(.2,0,0,1);
}
@media (prefers-reduced-motion: reduce) { :root { --dur: 0ms; } }
```

---

## 5. Components

Each is a shared CSS class, and a Blade component where markup repeats. Existing classes, ids and `data-*` attributes that tests or scripts use stay on the elements.

| Component | Variants and states | Replaces |
|---|---|---|
| **Button** `.btn` | `--primary`, `--secondary`, `--ghost`, `--danger`, `--sm`; hover, focus-visible ring (2 px `--c-focus`, 2 px offset), disabled, loading (`aria-busy`) | the 30 `*-btn` classes |
| **Card** `.card` | header, body, footer; `--flush` for tables | `fu-card`, `vb-card`, `pb-detail-card`, `doc-card`, `dash-card` |
| **Status badge** `x-status-badge` | the five statuses plus "Draf"; always text, colour from `--s-*` | `badge-step-*`, `status-badge-pill`, `vb-badge`, `pb-badge-aktif` |
| **Table** `.table` inside `.table-wrap` | scrolls inside its container, sticky header, zebra on hover, tabular numbers, 44 px rows (36 px with `--dense`) | `vb-table`, `usulan-table`, `regulasi-table` |
| **Form field** `x-field` | label, input/select/textarea/file, hint, error beside the field (`aria-describedby`), required marker | `fu-field`, `reg-field`, `vb-input` |
| **Alert** `.alert` | `--success`, `--error`, `--warning`, `--info`, with an icon and `role="status"` or `role="alert"` | `alert-success-bar`, `vb-alert`, `alert-floating`, `catatan-card` |
| **Modal** `x-modal` | uses `<dialog>`, with focus trap and Esc to close; used for confirmations (hapus regulasi, rollback, sinkronkan) instead of `confirm()` | `onsubmit="return confirm(…)"` |
| **Tracker** `x-tracker` | horizontal 4 stages (vertical on mobile); states done / active / perbaikan / pending, each with a text label | `pb-lanes`, `mini-tracker`, `dash-steps` |
| **Empty state** `x-empty-state` | icon, title, text, optional action | `empty-state`, `tim-empty`, `messenger-empty` |
| **Icon** `x-icon` | about 30 inline SVG icons (stroke 1.75, 20 px) with `aria-hidden` and a label when used alone | the 189 emoji |
| **Page header** `.page-head` | title, subtitle, actions on the right | `page-header`, `header-card`, `vb-header` |
| **Stat tile** `.stat` | label, value (tabular numbers), status dot | `stat-card` |
| **Bar chart** `dashboard/_bar-chart` | same markup and JSON; bars use `--s-*-bar` for status charts | inline gradients |

---

## 6. Page-by-page changes

| Page | Changes |
|---|---|
| Layout (`layouts/app`) | Flat navy sidebar with SVG icons and a clear active state; collapses below 1024 px with an overlay and an `aria-expanded` toggle. 60 px top bar with breadcrumb, bell, theme toggle, profile with level badge. Remove `animate-fade-in` motion. Load one stylesheet. |
| Login + SSO page | Remove the hero and illustration band; centred card with the BPS emblem, LOGIN SSO as the main action, NIP/password form below when enabled. Errors in an `.alert`. |
| Pegawai dashboard | Progress tracker, status summary and notifications first; replace the marketing hero with a compact welcome row; the 5 module cards become a simple card grid with SVG icons. |
| Pengajuan Usulan | Form sections as `.card`s; `x-field` with errors beside each field; upload slots as a checklist with status (uploaded, belum diunggah, perlu perbaikan); sticky action row with Simpan and Kirim Usulan / Kirim Ulang. |
| Pelacakan Status | Header card with the status badge and MS/TMS; `x-tracker`; timeline as a list; surat/sertifikat as a download card; empty and draft states use `x-empty-state`. |
| Regulasi (all) | Search row; category tabs; one `.table` per category; Preview/Unduh as `.btn--sm`; "File belum tersedia" as a muted badge. |
| Tim Kerja + profile | Member cards with initials avatar; profile as a definition list; contact block as a card. |
| Chat Box / Messenger | Message bubbles using tokens; "sending…" state on the send button; inline error instead of `alert()`; thread list with an unread badge; on mobile the thread list and conversation stack. |
| Staff dashboard | Compact header instead of the hero; stat tiles and three charts with status colours; link cards as three plain lists; usulan table in `.table-wrap`. |
| Verifikasi list/detail | Shared `.table` and filters; detail as two cards (data, berkas); pemeriksaan radios as a segmented control; MS/TMS buttons as `.btn--primary` / `.btn--danger` with a confirmation modal. |
| Monitoring | Stat tiles as filters; usulan cards with `x-tracker` and an action panel; rollback in a modal. |
| Regulasi admin | Upload form in a card with `x-field`; edit in a modal instead of `<details>`; delete via the confirmation modal. |
| Daftar User | Filter row; table with role select and status switch per row; inline success and error. |
| Sinkronisasi Data SSO | Run card with the driver label; history table; confirmation modal. |
| Notification dropdown | Popover with a header, a list using unread dot and text, "Tandai semua dibaca", and an empty state; keyboard reachable. |

---

## 7. Batch order

| Batch | Contents | Pages to check |
|---|---|---|
| **A** | Tokens and the shared stylesheet; the layout (sidebar, top bar, notification bell markup, theme toggle); base components (button, card, badge, table, field, alert, modal, tracker, empty state, icon); remove repeated inline CSS from every view that uses the layout | every page with the layout, in both themes, at 375 px |
| **B** | Pegawai: dashboard, Pengajuan Usulan, Pelacakan Status, Regulasi, Tim Kerja (+ profile), Chat Box | 6 pages |
| **C** | Tim Pembinaan JF: dashboard, Verifikasi (list and detail), Monitoring, Regulasi admin, Messenger | 6 pages |
| **D** | Super Admin: dashboard, Daftar User, Sinkronisasi Data SSO; login and SSO page; notification dropdown | 5 pages |

After each batch: `php artisan test`, one commit, a report, then stop and wait.

### Classes, ids and file contents the tests depend on (kept)
`id="formPengajuan"`, `id="loginForm"`, `class="level-badge"` with `data-level`, `class="notif-count"` with `data-count`, `<span class="profile-name">`, `class="usulan-item-card"`, `class="regulasi-group`, `class="stat-count"`, `<section class="hero-section">` (the pegawai panel must stay above it), `<nav class="sidebar-nav"` with `<div class="nav-icon">`, every `data-*` attribute (`data-layout`, `data-menu-route`, `data-status-key`, `data-stage`/`data-state`, `data-action`, `data-section`, `data-chart-json`, …), and `select-status` (asserted as absent).

Some tests read view **files** directly: `ThemeTest` (no hard-coded neutral colours), `LayoutRegressionTest` (`.main-content { min-width: 0 }`, the `.profile-name` mobile rule, `--c-heading`/`--c-hero-bg` tokens in `partials/theme`, `var(--c-hero-bg)` in login and the admin dashboard, `.legacy-card {` in the page). Once CSS moves into the shared stylesheet, these checks will point at the new file instead of the views. That's a change to purely visual selectors, and I'll report each one.

---

## 8. Decisions needed before "go"

1. **Which design system:** A (recommended), B or C.
2. **Font:** keep Plus Jakarta Sans from the Google Fonts link that's already there (default), and stop loading Inter. Switching to Atkinson Hyperlegible, Fira Sans, or self-hosting a font needs your approval.
3. **How the stylesheet is delivered:**
   - **Default:** a plain file at `public/css/sijabfu.css`, linked with `asset()`. No build step, so it works exactly like today.
   - **Alternative:** `resources/css/app.css` through `@vite`, which the skill's Laravel guidance prefers for cache-busting. It needs `npm run build` on every deploy, and the tests need `withoutVite()`.
   - Neither adds a dependency.
4. **Icons:** replace the 189 emoji with about 30 inline SVG icons written into a Blade component. The paths would come from the Lucide set (ISC licence), copied into the repo rather than installed. Say if you'd prefer the emoji kept.
5. **Hero sections:** remove the marketing heroes on the two dashboards and the login page, as described in §6. The text stays the same; only the layout changes.
6. **Confirmation dialogs:** replace the browser `confirm()` pop-ups (hapus regulasi, sinkronkan, rollback) with a styled `<dialog>` modal. It's the same action with the same text.
