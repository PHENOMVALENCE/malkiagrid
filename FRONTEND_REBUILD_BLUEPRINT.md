# FRONTEND REBUILD BLUEPRINT

## Objective
Rebuild the current M GRID frontend cleanly using plain PHP, HTML, CSS, JavaScript, and Bootstrap, while preserving current backend behavior and feature coverage.

## Target Structure (Approved Baseline)
```text
/public
   index.php
   about.php
   services.php
   benefits.php
   contact.php

/auth
   login.php
   register.php
   forgot-password.php

/user
   dashboard.php
   profile.php
   m-id.php
   m-score.php
   documents.php
   m-fund.php
   opportunities.php
   benefits.php
   settings.php

/admin
   dashboard.php
   users.php
   documents.php
   verification.php
   m-score.php
   funding.php
   partners.php
   benefits.php
   reports.php
   settings.php

/includes
   header.php
   footer.php
   sidebar-user.php
   sidebar-admin.php
   navbar.php
   auth.php
   db.php
   functions.php

/assets
   /css
      main.css
      dashboard.css
      responsive.css
   /js
      main.js
      dashboard.js
   /images
   /icons
```

## Mapping From Current Pages To Rebuild Pages

### Public + Auth Mapping
- `index.php` -> `/public/index.php`
- Public section content currently in `index.php` -> split into `/public/about.php`, `/public/services.php`, `/public/benefits.php`, `/public/contact.php` (or keep sections + route pages using shared partials)
- `login.php` -> `/auth/login.php`
- `register.php` -> `/auth/register.php`
- Add new `/auth/forgot-password.php` (currently missing as dedicated runtime page)

### User Mapping
- `user/dashboard.php` -> `/user/dashboard.php`
- `user/profile.php` -> `/user/profile.php`
- `user/verify-id.php` + ID-specific flow -> `/user/m-id.php`
- `user/my_mscore.php` -> `/user/m-score.php`
- `user/my_documents.php` + `upload_document.php` + `reupload_document.php` -> `/user/documents.php` (subviews/tabs/actions)
- `user/funding_overview.php` + `apply_funding.php` + `my_funding_applications.php` + `funding_application_detail.php` -> `/user/m-fund.php`
- `user/opportunities.php` + `opportunity_detail.php` + `my_opportunities.php` + `trainings*` pages -> `/user/opportunities.php` (use subtabs for opportunities/trainings) or keep trainings as child section in same page
- `user/benefits.php` + `benefit_detail.php` + `my_benefits.php` + `benefit_claim_detail.php` -> `/user/benefits.php`
- `user/settings.php` + notifications controls -> `/user/settings.php`

### Admin Mapping
- `admin/dashboard.php` -> `/admin/dashboard.php`
- `admin/users.php` + `user-view.php` -> `/admin/users.php`
- `admin/admin_documents.php` + `review_document.php` -> `/admin/documents.php`
- Document/identity workflow controls -> `/admin/verification.php`
- `admin/admin_mscores.php` + `admin_mscore_detail.php` -> `/admin/m-score.php`
- `admin/admin_funding_applications.php` + `admin_funding_review.php` + `manage_repayments.php` -> `/admin/funding.php`
- Current partner placeholders + future partner management -> `/admin/partners.php`
- `admin/admin_benefits.php` + related claims/categories/providers -> `/admin/benefits.php`
- `admin/admin_reports.php` + analytics + exports -> `/admin/reports.php`
- `admin/platform_settings.php` + admin accounts/system settings -> `/admin/settings.php`

## Proposed Layout Architecture

### Shared Includes
- `includes/header.php`
  - Handles `<head>`, meta tags, common CSS, and layout bootstrapping.
- `includes/navbar.php`
  - Public/global top navigation only.
- `includes/sidebar-user.php`
  - User-only sidebar links.
- `includes/sidebar-admin.php`
  - Admin-only sidebar links.
- `includes/footer.php`
  - Shared footer and common JS includes.

### Layout Modes
- Public/Auth pages:
  - `header.php` -> page content -> `footer.php`
- Dashboard pages:
  - `header.php` -> role sidebar -> topbar block -> page content -> `footer.php`

### Partial Components To Create
- `includes/partials/alerts.php`
- `includes/partials/cards.php`
- `includes/partials/table-toolbar.php`
- `includes/partials/status-badge.php`
- `includes/partials/empty-state.php`
- `includes/partials/pagination.php`

## Navigation Blueprint

### Public Navbar
- Home, About, Services, Benefits, Contact
- Language toggle
- Login/Register (anonymous) or Dashboard (authenticated)

### User Sidebar
- Dashboard
- Profile
- M-ID
- M-Score
- Documents
- M-Fund
- Opportunities
- Benefits
- Settings
- Logout

### Admin Sidebar
- Dashboard
- Users
- Documents
- Verification
- M-Score
- Funding
- Partners
- Benefits
- Reports
- Settings
- Logout

## Page Composition Standards
- Every page should follow:
  1. `Page Header` (title + subtitle + quick actions)
  2. `Filter/Toolbar` (where applicable)
  3. `Primary Content` (cards/table/form)
  4. `Secondary Panel` (status/history/help)
  5. `Feedback Area` (alerts/messages)
- Use consistent Bootstrap utility spacing and custom design tokens.

## CSS Blueprint

### `assets/css/main.css`
- Global reset/variables
- Typography
- Buttons, forms, alerts, badges
- Public page blocks

### `assets/css/dashboard.css`
- Sidebar, topbar, dashboard grid
- Cards, tables, widget styles
- Role-specific accents (user/admin)

### `assets/css/responsive.css`
- Breakpoints
- Sidebar collapse/mobile nav behavior
- Table overflow rules

## JavaScript Blueprint

### `assets/js/main.js`
- Public navbar interactions
- Language switch trigger
- Generic UI helpers (alerts, modals, confirmations)

### `assets/js/dashboard.js`
- Sidebar toggle + active states
- Table utilities (filter helpers, empty-state handling)
- Dashboard widgets/charts initializers
- Notification dropdown behaviors

## Reusable UI Pattern Catalog (For Implementation)
- Stat card (`metric`, `label`, `trend`)
- Action card (`title`, `description`, `CTA`)
- Form section card (`section heading`, `fields`, `actions`)
- Data table module (`toolbar`, `thead`, `tbody`, `row actions`)
- Status badge variants (`pending`, `approved`, `rejected`, `active`, `inactive`)
- Timeline/log list (for funding/review pages)
- Empty state with CTA
- Toast/alert feedback blocks

## Migration Phases (No Backend Logic Change)

### Phase 1: Skeleton And Includes
- Create target directories and base include files.
- Add shared header/footer/navbar/sidebar scaffolds.
- Add base CSS/JS files.

### Phase 2: Public + Auth Migration
- Move/rebuild landing and auth pages first.
- Ensure role-based redirects still work.

### Phase 3: User Area Migration
- Rebuild user dashboard and grouped feature pages.
- Preserve existing form endpoints and query contracts.

### Phase 4: Admin Area Migration
- Rebuild queue-heavy pages (users/docs/funding/benefits/reports/settings).
- Preserve existing admin action endpoints.

### Phase 5: Consolidation
- Remove duplicate UI markup in favor of shared partials.
- Introduce strict naming and file ownership conventions.
- Decommission prototype/legacy templates after validation.

## Guardrails
- Do not delete or rename existing files during extraction/planning stage.
- Do not alter backend processing logic yet.
- Keep endpoint names stable until routing migration is explicitly approved.
- Introduce new files incrementally and keep old pages operational until parity is reached.

## Definition Of Done For Rebuild
- All required pages exist in the new structure.
- Shared layouts/components drive all major UI blocks.
- Navigation is role-correct and free of dead links/placeholders.
- CSS and JS are layered and minimally loaded per context.
- Existing user/admin flows remain functional end-to-end.
- Another developer can continue implementation using this blueprint without guessing.

