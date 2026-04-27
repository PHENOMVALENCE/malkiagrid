# FRONTEND EXTRACTION REPORT

## Scope And Method
- Audited all runtime PHP frontend pages under project root, `user`, `admin`, and shared includes.
- Audited shared templates/layouts in `includes` and role shells in `user/includes` and `admin/includes`.
- Audited frontend assets in `assets/css`, `assets/js`, `assets/images`, `assets/icons`, plus CDN dependencies loaded by templates.
- Included static prototype HTML (`public`, `dashboard`, `admin`, and `ui-*.html`) as non-runtime references.
- Kept backend logic unchanged; this report documents current state for rebuild planning.

## Frontend Inventory (Runtime Pages)

### Public + Shared Pages
| Page | File Path | Purpose | Role | Main Sections | Forms | Buttons / Actions | Tables/Cards/Widgets | Linked CSS | Linked JS | Images/Icons | Backend Dependencies |
|---|---|---|---|---|---|---|---|---|---|---|---|
| Landing | `index.php` | Public marketing and entry point | public | Hero, about, how it works, features, benefits, partners, FAQ, CTA | None | Register, login, section anchors, dashboard shortcut (if logged in) | Marketing cards, feature blocks, FAQ accordion | Global layout CSS via `includes/header.php` | `mgrid-i18n.js`, `mgrid-core.js`, `mgrid-ui.js` | `assets/images/logos/logo.png`, Tabler icons | `includes/init.php`, `includes/header.php`, `includes/footer.php` |
| Login | `login.php` | Unified sign-in for user/admin | shared | Auth panel, language toggle, support text | Login form | Sign in, forgot-like redirect links, register link | Auth card | Global + auth CSS via `header.php` | Global JS via `footer.php` | Brand logo, icon font | `includes/init.php`, auth queries, role redirect |
| Register | `register.php` | Member onboarding and account creation | public | Registration form, M-ID messaging, legal/help text | Registration form | Create account, login link | Auth card | Global + auth CSS via `header.php` | Global JS via `footer.php` | Brand logo, icon font | `includes/init.php`, `includes/m_id_generator.php`, inserts into users/profile/mscore seed data |
| Default page | `default.php` | Legacy/default app placeholder | shared | Minimal content block | None/Minimal | Basic navigation actions | Basic content card | Global layout CSS | Global JS | Brand assets | `includes/header.php`, `includes/footer.php` |
| Utility page | `h.php` | Helper/demo utility output | shared | Utility content | Context-dependent | Context-dependent | Minimal | Global layout CSS | Global JS | Minimal | `includes/init.php` and helper calls |
| Document viewer | `document_view.php` | Serves uploaded user document for preview/download | shared | File stream/output | None | Open/download | None | N/A (direct output/utility) | N/A | Uploaded files | Auth checks, document lookup |
| Funding document viewer | `funding_document_view.php` | Serves funding support files | shared | File stream/output | None | Open/download | None | N/A (direct output/utility) | N/A | Uploaded files | Auth checks, funding file lookup |
| Logout | `logout.php` | Session termination and redirect | shared | N/A | None | Logout | None | N/A | N/A | N/A | Session/auth dependency |

### User Pages
| Page | File Path | Purpose | Role | Main Sections | Forms | Buttons / Actions | Tables/Cards/Widgets | Linked CSS | Linked JS | Images/Icons | Backend Dependencies |
|---|---|---|---|---|---|---|---|---|---|---|---|
| User dashboard | `user/dashboard.php` | Member summary and quick actions | user | Welcome, profile summary, key stats, quick links | Usually none | Navigate to profile/docs/funding/opportunities | KPI cards, summary cards | Global dashboard stack from `header.php` | Global JS + `sidebarmenu.js` | Tabler icons, logo in shell | `user/includes/init_member.php`, member/profile/mscore helpers |
| Profile | `user/profile.php` | View/edit member profile | user | Personal info, business info, profile completeness | Profile update form | Save profile, edit actions | Form cards | Global dashboard CSS | Global JS + sidebar JS | Tabler icons | `init_member.php`, profile update backend |
| M-SCORE | `user/my_mscore.php` | Show score and breakdown | user | Current score, factors, recommendations/history | Usually none | View details, refresh navigation | Score cards/widgets | Global dashboard CSS | Global JS | Chart/icon widgets | `includes/mscore_helper.php`, scoring reads |
| Verify ID | `user/verify-id.php` | Identity verification upload/status | user | Verification status, upload instructions | ID upload form | Upload/submit | Status cards | Global dashboard CSS | Global JS | Document/ID icons | `init_member.php`, verification status, upload handler |
| Documents list | `user/my_documents.php` | Document repository and status | user | Filter/list area, status badges, pagination | Filter/search form | Upload new, re-upload, view document | Table/list cards | Global dashboard CSS | Global JS | File icons | `includes/document_helpers.php`, `document_view.php` |
| Upload document | `user/upload_document.php` | Submit a new document | user | Upload form, document type, notes | Document upload form | Submit upload, cancel | Upload card | Global dashboard CSS | Global JS | File upload icons | `save_document.php`, document type lookup |
| Re-upload document | `user/reupload_document.php` | Upload replacement document version | user | Current document context, replacement form | Re-upload form | Submit replacement | Form card | Global dashboard CSS | Global JS | File icons | `save_document.php`, document id + ownership checks |
| Opportunities | `user/opportunities.php` | Browse opportunities | user | Filters, opportunity grid/list | Filter form | View detail, apply | Opportunity cards | Global dashboard CSS | Global JS | Opportunity icons | `includes/opportunities_helper.php` |
| Opportunity detail | `user/opportunity_detail.php` | Opportunity details + apply | user | Detail summary, eligibility, deadlines | Apply action/form | Apply now, back/list actions | Detail card | Global dashboard CSS | Global JS | Icons/badges | Opportunity + application checks |
| My opportunities | `user/my_opportunities.php` | User applications tracking | user | Application list/history | Filter/search (if present) | View status/detail | Table/cards | Global dashboard CSS | Global JS | Status icons | Applications queries |
| Trainings | `user/trainings.php` | Browse trainings | user | Filters, training cards/list | Filter form | View details, register | Training cards | Global dashboard CSS | Global JS | Training icons | `includes/trainings_helper.php` |
| Training detail | `user/training_detail.php` | Training details and registration | user | Program details, schedule, eligibility | Register action/form | Register, back/list | Detail card | Global dashboard CSS | Global JS | Icons | training helper + registration checks |
| My trainings | `user/my_trainings.php` | Registration history/status | user | Registered trainings list | Optional filters | View statuses | Table/cards | Global dashboard CSS | Global JS | Status icons | registration queries |
| Funding overview | `user/funding_overview.php` | Funding readiness and quick entry | user | Eligibility summary, readiness score, recent applications | Optional quick form/filter | Start application, view applications | KPI cards, status cards | Global dashboard CSS | Global JS | Funding icons | `includes/mfund_helper.php`, `includes/mfund_eligibility_helper.php` |
| Apply funding | `user/apply_funding.php` | New funding application form | user | Application form, required docs, declarations | Funding application form + uploads | Submit application | Form cards | Global dashboard CSS | Global JS | Upload/file icons | `save_funding_application.php` |
| My funding applications | `user/my_funding_applications.php` | Funding application list | user | Applications table/list, statuses | Filter/search (if present) | Open application detail | Table/cards | Global dashboard CSS | Global JS | Status badges | funding app queries |
| Funding application detail | `user/funding_application_detail.php` | Funding detail + timeline + repayments | user | Application summary, timeline, disbursement/repayment info | Usually none | View docs, back, print/export-like actions | Detail cards, repayment table | Global dashboard CSS | Global JS | Status/icons | `includes/repayment_helper.php`, funding detail queries |
| Benefits | `user/benefits.php` | Browse benefit offers | user | Category filter, active offers listing | Filter form | View detail, claim | Benefit cards | Global dashboard CSS | Global JS | Gift/icons | `includes/mbenefits_helper.php` |
| Benefit detail | `user/benefit_detail.php` | Offer detail and claim path | user | Offer details, eligibility, provider info | Claim form/action | Claim benefit | Detail cards | Global dashboard CSS | Global JS | Benefit icons | claim checks + offer lookup |
| My benefits | `user/my_benefits.php` | Claimed benefit history | user | Claims list, statuses | Filter/search (if present) | Open claim detail | Table/cards | Global dashboard CSS | Global JS | Status icons | benefit claim queries |
| Benefit claim detail | `user/benefit_claim_detail.php` | Single claim tracking | user | Claim metadata, status progression | Usually none | Back/list actions | Detail card/status widget | Global dashboard CSS | Global JS | Icons | claim detail lookup |
| Notifications | `user/notifications.php` | Notification inbox | user | Filter controls, notification list | Filter/mark-read actions | Mark read/open target | Notification list/cards | Global dashboard CSS | Global JS | Bell/icons | `includes/notification_helper.php`, `user/mark_notification_read.php` |
| Settings | `user/settings.php` | Account preferences | user | Profile/account settings areas | Settings form(s) | Save settings | Settings cards | Global dashboard CSS | Global JS | Settings icons | user account update dependencies |
| Notification dropdown partial | `user/notification_dropdown_include.php` | Topbar live notification widget | shared | Bell icon + dropdown list | Mark read quick actions | Open message, mark read | Dropdown widget | Inherits shell CSS | Inherits shell JS | Bell/icon assets | notification helper data |

### Admin Pages
| Page | File Path | Purpose | Role | Main Sections | Forms | Buttons / Actions | Tables/Cards/Widgets | Linked CSS | Linked JS | Images/Icons | Backend Dependencies |
|---|---|---|---|---|---|---|---|---|---|---|---|
| Admin dashboard | `admin/dashboard.php` | Admin operational overview | admin | KPI cards, member summaries, recent activities | Usually none | Navigate to queues/modules | KPI cards, activity widgets | Global dashboard CSS | Global JS + sidebar JS | Tabler icons/logo | `admin/includes/init_admin.php`, analytics helper calls |
| Users list | `admin/users.php` | Member directory and status visibility | admin | Filters, users table | Filter form | View user, status actions | Data table/cards | Global dashboard CSS | Global JS | User/status icons | user/member queries |
| User view | `admin/user-view.php` | Detailed member profile + actions | admin | User details, verification context, score/flags | Action forms | Activate/suspend/update actions | Detail cards | Global dashboard CSS | Global JS | Icons | admin auth + user state updates |
| M-SCORE monitor | `admin/admin_mscores.php` | Monitor all member scores | admin | Filters, score table, summary | Filter/recalculate actions | Recalculate single/bulk | Table/cards | Global dashboard CSS | Global JS | Score icons | `recalculate_mscore.php`, `bulk_recalculate_mscores.php` |
| M-SCORE detail | `admin/admin_mscore_detail.php` | Deep dive into one member score | admin | Factor breakdown, historical score context | Optional action form | Recalculate/back | Detail cards | Global dashboard CSS | Global JS | Chart/icons | mscore helper/engine |
| Documents queue | `admin/admin_documents.php` | Verification queue | admin | Filters, pending docs table | Filter form | Review document | Table/cards/status badges | Global dashboard CSS | Global JS | File/status icons | document helper queries |
| Review document | `admin/review_document.php` | Approve/reject/return docs | admin | Document preview, metadata, decision panel | Decision form | Approve/reject/request resubmission | Review cards + notes | Global dashboard CSS | Global JS | File preview/icons | `update_document_status.php`, `document_view.php` |
| Funding applications | `admin/admin_funding_applications.php` | Loan/funding queue | admin | Filters, applications list | Filter form | Open review | Table/cards | Global dashboard CSS | Global JS | Funding icons | funding helper queries |
| Funding review | `admin/admin_funding_review.php` | Approve/decline/disburse case | admin | Applicant profile, scoring, docs, decision history | Decision + disbursement forms | Approve/decline/disburse/update status | Detail cards, logs, repayment info | Global dashboard CSS | Global JS | Status icons | `update_funding_status.php`, `record_disbursement.php` |
| Manage repayments | `admin/manage_repayments.php` | Track and record repayments | admin | Repayment schedule, payment history | Repayment entry form | Record repayment | Repayment table/cards | Global dashboard CSS | Global JS | Currency/icons | `record_repayment.php`, repayment helper |
| Benefits list | `admin/admin_benefits.php` | Benefit offer management | admin | Filters, offers list | Filter/status forms | Add/edit/archive offer | Table/cards | Global dashboard CSS | Global JS | Benefit icons | benefits helper/module |
| Add benefit | `admin/add_benefit.php` | Create benefit offer | admin | Benefit form fields | Create form | Save benefit | Form card | Global dashboard CSS | Global JS | Icons | insert benefit logic |
| Edit benefit | `admin/edit_benefit.php` | Update existing benefit | admin | Prefilled benefit form | Update form | Save changes | Form card | Global dashboard CSS | Global JS | Icons | update benefit logic |
| Benefit claims | `admin/admin_benefit_claims.php` | Claims review and status updates | admin | Claims table/filters | Status update form | Approve/reject/fulfill | Table/status widgets | Global dashboard CSS | Global JS | Status icons | `admin/update_benefit_claim_status.php` |
| Benefit categories | `admin/manage_benefit_categories.php` | Manage benefit categories | admin | Category list + editor | Add/edit forms | Save/update/delete | Table/cards | Global dashboard CSS | Global JS | Icons | category CRUD |
| Benefit providers | `admin/manage_benefit_providers.php` | Manage providers | admin | Provider list + editor | Add/edit forms | Save/update/delete | Table/cards | Global dashboard CSS | Global JS | Icons | provider CRUD |
| Opportunities list | `admin/admin_opportunities.php` | Opportunity management | admin | Filters, opportunities table | Filter/status forms | Add/edit/archive opportunity | Table/cards | Global dashboard CSS | Global JS | Opportunity icons | opportunities module |
| Add opportunity | `admin/add_opportunity.php` | Create opportunity | admin | Opportunity form | Create form | Save | Form card | Global dashboard CSS | Global JS | Icons | insert opportunity |
| Edit opportunity | `admin/edit_opportunity.php` | Edit opportunity | admin | Prefilled opportunity form | Update form | Save changes | Form card | Global dashboard CSS | Global JS | Icons | update opportunity |
| Opportunity applications | `admin/admin_applications.php` | Review opportunity applications | admin | Applications list/filters | Status action forms | Approve/reject/view | Table/cards | Global dashboard CSS | Global JS | Icons | application status helper |
| Opportunity categories | `admin/manage_opportunity_categories.php` | Category taxonomy management | admin | Category table/editor | Add/edit forms | Save/update/delete | Table/cards | Global dashboard CSS | Global JS | Icons | category CRUD |
| Trainings list | `admin/admin_trainings.php` | Training catalog management | admin | Training list/filters | Filter/status forms | Add/edit/archive | Table/cards | Global dashboard CSS | Global JS | Icons | trainings helper |
| Add training | `admin/add_training.php` | Create training item | admin | Training form | Create form | Save | Form card | Global dashboard CSS | Global JS | Icons | insert training |
| Edit training | `admin/edit_training.php` | Edit training item | admin | Prefilled training form | Update form | Save changes | Form card | Global dashboard CSS | Global JS | Icons | update training |
| Training registrations | `admin/admin_training_registrations.php` | Manage registrations/completion | admin | Registration table and controls | Completion update forms | Mark complete/in-progress | Table/cards | Global dashboard CSS | Global JS | Icons | `admin/update_training_completion.php` |
| Announcements | `admin/admin_announcements.php` | Announcement management list | admin | Announcement list | Filter/search (if present) | Create/view/manage | Table/cards | Global dashboard CSS | Global JS | Bell/icons | announcement helper |
| Create announcement | `admin/create_announcement.php` | Add announcement | admin | Announcement editor form | Create form | Publish/save | Form card | Global dashboard CSS | Global JS | Icons | create announcement logic |
| View announcement | `admin/view_announcement.php` | Announcement details | admin | Announcement content + metadata | Optional action forms | Edit/back actions | Detail card | Global dashboard CSS | Global JS | Icons | announcement lookup |
| Analytics | `admin/admin_analytics.php` | Charts and metrics | admin | Metric cards, trend charts | Optional filter forms | Change period, refresh | Chart widgets/cards | Global dashboard CSS + chart page extras | Global JS + Chart.js CDN | Chart/icon assets | `includes/analytics_helper.php` |
| Reports | `admin/admin_reports.php` | Reporting summaries and exports | admin | Report cards, summary tables | Report filter forms | Export CSV/report | Tables, KPI cards | Global dashboard CSS | Global JS | Report/icons | `includes/reporting_helper.php`, `admin/export_report.php` |
| Export endpoint | `admin/export_report.php` | CSV/report file response | shared | File output utility | Input params form/query | Export/download | None | N/A | N/A | N/A | report generation backend |
| Admin accounts | `admin/admin_accounts.php` | Admin team account management | admin | Admin list, add/edit account modal/form | Add/edit/delete forms | Create/update/delete admin | Data table/cards | Global dashboard CSS + DataTables CDN CSS | Global JS + DataTables CDN JS | Icons | admin account CRUD |
| Platform settings | `admin/platform_settings.php` | System-level settings hub | admin | Setting groups, quick links | Settings forms (if enabled) | Save settings/manage links | Settings cards | Global dashboard CSS | Global JS | Settings/icons | settings read/write dependencies |

## Frontend Inventory (Non-runtime Prototype HTML)
- Public prototypes: `index.html`, `login.html`, `register.html`, `pending-verification.html`
- Dashboard prototypes: `dashboard/home.html`, `dashboard/profile.html`, `dashboard/score.html`, `dashboard/documents.html`, `dashboard/loans.html`, `dashboard/partners.html`, `dashboard/benefits.html`, `dashboard/settings.html`
- Admin prototypes: `admin/home.html`, `admin/users.html`, `admin/user-detail.html`, `admin/loans.html`, `admin/partners.html`, `admin/benefits.html`, `admin/reports.html`
- Template/demo pages: removed during frontend cleanup (kept only active template pages).

## Full Layout Map

### Root Layout Entry
- `includes/header.php`
  - Resolves layout mode: `public`, `auth`, `user`, `admin`
  - Injects `includes/navbar.php` for public
  - Injects `includes/sidebar.php` + `includes/topbar.php` for user/admin
  - Injects `includes/lang_toggle.php` in all layout contexts
- `includes/footer.php`
  - Closes wrappers for current layout
  - Injects public footer via `includes/public_footer.php` for public
  - Loads global scripts and optional page-level extras (`$mgrid_footer_extra`)

### Role Shell Wrappers
- User: `user/includes/shell_open.php` and `user/includes/shell_close.php`
- Admin: `admin/includes/shell_open.php` and `admin/includes/shell_close.php`
- Both rely on common `includes/header.php`/`includes/footer.php` and set role context variables.

### Navigation Partials
- Public nav: `includes/navbar.php`
- App side nav: `includes/sidebar.php` (role-switched)
- App top bar: `includes/topbar.php`
- Public footer: `includes/public_footer.php`
- User topbar notifications widget: `user/notification_dropdown_include.php`

### Initialization/Auth Partials
- Bootstrap: `includes/init.php`
- Auth checks: `includes/auth.php`
- User guard: `user/includes/init_member.php`
- Admin guard: `admin/includes/init_admin.php`
- Localization: `includes/i18n.php`, `includes/lang_toggle.php`

## Header / Navbar Structure
- Brand area: logo + M GRID text lockup.
- Public menu links: About, How, Features, Benefits, Partners, FAQ.
- Language switch control included in nav.
- Auth CTA zone:
  - Anonymous: Login + Register
  - Authenticated: Dashboard shortcut (routes to user/admin dashboard by account type)
- Two navbar variants in same file: vanilla custom nav and Bootstrap navbar variant.

## Sidebar Structure

### User Sidebar
- Overview: Dashboard, M-Profile, M-SCORE
- Identity: ID Verification, Documents
- Opportunities: Opportunities, Trainings, M-Fund (Loans), M-Manufaa
- Partner placeholder: M-Washirika (`javascript:void(0)`)
- Account: Notifications, Settings
- Session: Logout

### Admin Sidebar
- Dashboard section
- Member ops: users, M-SCORE monitoring, document verification
- Platform ops: loan applications, benefits, opportunities, trainings, announcements, analytics, reports
- System: admin accounts, platform settings
- Legacy placeholders still present: pending verification, score management, partners (non-routed)
- Session: Logout

## Footer Structure
- Public footer branding message
- Partner attribution section
- Resources list: register, login, FAQ
- Copyright strip
- Variant rendered for vanilla vs bootstrap public mode.

## Architecture By Domain

### Authentication Pages
- `login.php`, `register.php`, `logout.php`
- Shared auth styling and language toggle; role-based post-login routing.

### Dashboard Layouts
- User/admin both share sidebar + topbar + content canvas.
- Dashboard widgets are card-first with stat summaries and module shortcuts.

### Public Landing
- Single page (`index.php`) section-anchor layout with marketing modules.

### User Domain Pages
- `M-ID / verification`: `user/verify-id.php`, document upload/list/detail flows.
- `M-Profile`: `user/profile.php`
- `M-SCORE`: `user/my_mscore.php`
- `M-Fund`: funding overview/apply/list/detail pages.
- `Opportunities`: browse/detail/applications flows.
- `Benefits`: browse/detail/claims flows.
- `Settings/notifications`: account controls and message center.

### Admin Domain Pages
- Member management and verification queues.
- M-SCORE monitoring and recalculation.
- Funding review/disbursement/repayment management.
- Benefits, opportunities, trainings full CRUD + pipeline management.
- Announcements, analytics, reports, and admin account/system settings.

### Partner/Service Pages
- No dedicated partner runtime module yet.
- Sidebar references exist as placeholders.

## Current Navigation Map
- Public:
  - `index.php` -> `login.php` / `register.php`
  - In-page anchors for marketing sections
- Auth:
  - `login.php` -> `user/dashboard.php` or `admin/dashboard.php`
  - New users may route through `user/verify-id.php` depending on status
- User:
  - Sidebar-driven module navigation across profile, score, identity, opportunities, trainings, funding, benefits, notifications, settings
- Admin:
  - Sidebar-driven operations/navigation across member ops, queues, analytics/reports, system settings

## Current User Journey
1. Visitor lands on `index.php`.
2. Registers via `register.php` or logs in via `login.php`.
3. Authenticated member enters `user/dashboard.php` (or verification-first path to `user/verify-id.php`).
4. Completes identity/docs (`verify-id.php`, `my_documents.php`, upload/re-upload).
5. Uses growth modules:
   - Profile + M-SCORE (`profile.php`, `my_mscore.php`)
   - Opportunities/trainings (browse -> detail -> apply/register -> tracking)
   - Funding (overview -> apply -> applications -> detail/repayment)
   - Benefits (catalog -> detail -> claim -> claim detail/history)
6. Manages notifications/settings.
7. Logs out via `logout.php`.

## Current Admin Journey
1. Admin logs in via `login.php`.
2. Lands on `admin/dashboard.php`.
3. Works member/document/funding queues.
4. Manages catalogs (benefits, opportunities, trainings) and lifecycle statuses.
5. Oversees M-SCORE, announcements, analytics, reports.
6. Manages admin accounts/platform settings.
7. Logs out.

## CSS Asset Map

### Core Runtime CSS (loaded from `includes/header.php`)
- `assets/css/styles.min.css` (or `assets/css/public-vanilla.min.css` for public vanilla mode)
- `assets/css/mgrid-theme.css`
- `assets/css/mgrid-premium-ui.css`
- `assets/css/mgrid-auth.css`
- `assets/css/mgrid-dashboard.css`
- `assets/css/mgrid-public-polish.css`
- External:
  - Tabler icons webfont CDN
  - Google Fonts (Cormorant Garamond, JetBrains Mono, Montserrat)

### Page-specific CSS
- DataTables CDN CSS added in `admin/admin_accounts.php`

### Legacy/Prototype CSS (not guaranteed in runtime flow)
- `assets/css/mgrid-variables.css`
- `assets/css/mgrid-overrides.css`
- `assets/css/mgrid-components.css`
- `assets/css/mgrid-animations.css`
- `assets/css/m-grid.css`

## JS Asset Map

### Core Runtime JS (loaded from `includes/footer.php`)
- `assets/js/mgrid-i18n.js`
- `assets/js/mgrid-core.js`
- `assets/js/mgrid-ui.js`
- `assets/js/sidebarmenu.js` (user/admin layouts only)
- Conditionally for non-vanilla public:
  - `assets/libs/jquery/dist/jquery.min.js`
  - `assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js`
  - Iconify CDN

### Page-specific JS
- `admin/admin_accounts.php`: DataTables CDN JS
- `admin/admin_analytics.php`: Chart.js CDN

### Existing JS Files Not Clearly Wired To Current Runtime Includes
- `assets/js/mgrid-landing.js`
- `assets/js/mgrid-login.js`
- `assets/js/mgrid-dashboard.js`
- `assets/js/mgrid-admin-home.js`
- `assets/js/mgrid-loans.js`
- `assets/js/mgrid-profile.js`
- `assets/js/mgrid-score-page.js`
- `assets/js/mgrid-charts.js`
- `assets/js/dashboard.js`
- `assets/js/app.min.js`

## Reusable Components List
- Global layout shell (`header.php` + `footer.php`)
- Public navbar component (`navbar.php`)
- App sidebar component (`sidebar.php`) with role mode switch
- App topbar component (`topbar.php`)
- Public footer component (`public_footer.php`)
- Language toggle component (`lang_toggle.php`)
- Notification dropdown component (`notification_dropdown_include.php`)
- Reusable card sections for stats, filters, and action panels
- Reusable table/list blocks with status badges and row actions
- Reusable form card pattern for add/edit workflows

## Duplicated UI Patterns
- Repeated nav constructs:
  - Public navbar appears in two style variants in one file.
  - Sidebar role branching packs many unrelated sections in one large template.
- Repeated card patterns:
  - KPI cards duplicated across user/admin dashboards and reporting pages.
- Repeated button/action patterns:
  - Similar approve/reject/update status actions repeated in documents/funding/claims/applications.
- Repeated form styles:
  - Create/edit forms across benefit/opportunity/training/category/provider/admin account pages.
- Repeated dashboard widgets:
  - Summary metrics cards repeated with minor naming differences.
- Repeated table layouts:
  - Filter + table + pagination + row actions repeated throughout admin and member modules.
- Repeated alert/message boxes:
  - Status flash/feedback blocks repeated with inconsistent formatting.

## Problems In Current Frontend
- Mixed architecture:
  - Runtime pages and old prototype/template pages coexist in root, causing ambiguity.
- Heavy global asset loading:
  - Nearly all runtime CSS loaded globally for every page, increasing payload and coupling.
- Inconsistent page ownership:
  - Some action endpoints and shared utilities live at root; domain grouping is partial.
- Placeholder navigation items:
  - Partner routes and some admin links are visible but non-functional.
- Repeated view patterns:
  - Similar list/detail/form pages re-implemented independently without a component system.
- Mixed language/text handling:
  - i18n present but copy and fallback usage are inconsistent across pages.
- Tight backend/frontend coupling:
  - Many pages mix rendering, querying, and action handling assumptions in one place.

## Recommendations For Clean Rebuild
- Establish domain-first foldering (public/auth/user/admin/includes/assets) with strict ownership.
- Introduce reusable PHP view partials for:
  - stat cards, filter bars, data tables, status badges, form sections, alerts.
- Split asset bundles:
  - `main.css/js` for global basics, `dashboard.css/js` for app shell, module-specific bundles only where needed.
- Standardize route/navigation contracts:
  - remove placeholder links or map them to actual pages.
- Create page template standards:
  - each page defines purpose, sections, actions, and dependency checklist.
- Isolate backend interactions:
  - keep page controllers separate from rendering templates where practical.
- Define UI consistency tokens:
  - spacing, card styles, buttons, badges, table states, form controls, and empty/error states.
- Keep prototype HTML isolated in a `legacy_prototypes` or docs-only area during migration.

