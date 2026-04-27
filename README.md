# M GRID Frontend (Kiswahili Primary)

Mradi huu ni toleo la **frontend-only** la M GRID, lililoboreshwa kwa muonekano wa kisasa na mtiririko wa usajili/uhakiki.

## Muhtasari

- Lugha kuu: **Kiswahili**
- Lugha ya pili: **English** (kupitia toggle EN/SW)
- Hakuna backend ya lazima kwa preview ya UI
- Imejengwa kwa HTML + CSS + vanilla JS

## Entry Points

- `index.html` -> `public/index.html`
- `login.html` -> `public/login.html`
- `register.html` -> `public/register.html`
- `pending-verification.html` -> `public/pending-verification.html`

## Mtiririko wa Auth (Frontend Demo)

1. Usajili unaanzia `public/register.html`
2. Baada ya usajili, mtumiaji anaelekezwa `public/pending-verification.html`
3. Mtumiaji anapakia picha ya NIDA, hali inakuwa "pending_review"
4. Login ya user:
   - ikiwa si `verified` -> anarudishwa pending verification page
   - ikiwa `verified` -> anaingia `dashboard/home.html`
5. Login ya admin -> `dashboard/admin-home.html`

> Kumbuka: Hii ni demo ya frontend; status ya verification inahifadhiwa kwenye `localStorage`.

## Dashboard Pages

### User

- `dashboard/home.html`
- `dashboard/profile.html`
- `dashboard/score.html`
- `dashboard/documents.html`
- `dashboard/loans.html`
- `dashboard/partners.html`
- `dashboard/benefits.html`
- `dashboard/settings.html`

### Admin

- `dashboard/admin-home.html`

## Styling

Theme kuu ya muonekano:

- `assets/css/mgrid-reference-theme.css`

Mafaili ya msingi ya style:

- `assets/css/mgrid-variables.css`
- `assets/css/mgrid-overrides.css`
- `assets/css/mgrid-components.css`
- `assets/css/mgrid-animations.css`

## Scripts Muhimu

- `assets/js/mgrid-core.js` -> logic za auth flow, pending verification flow, interactions za UI
- `assets/js/mgrid-i18n.js` -> strings za lugha (SW/EN) na language toggle

## Jinsi ya Ku-run (Local)

Kwa kuwa ni frontend-only:

1. Fungua project ndani ya browser kupitia server ya local (mf. XAMPP `htdocs`)
2. Tembelea `http://localhost/m-grid1/`

## Note

Faili za `.php` bado zipo kwenye repository kwa historia/rejea, lakini muundo wa sasa wa UI umeandaliwa ku-run kama frontend-only.
"# malkiagrid" 
