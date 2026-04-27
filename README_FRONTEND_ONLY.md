# Frontend-only Package

This folder contains UI-facing files copied from the current M GRID app while excluding backend processing endpoints and DB/auth business logic handlers.

Included:
- Public/auth/user/admin view pages
- Shared UI includes (header/footer/navbar/sidebar/topbar/lang)
- Frontend assets (CSS/JS/images/icons/libs)
- Language packs and prototype HTML references
- Frontend extraction/rebuild documentation

Not included:
- Action endpoints such as save/update/record/apply/claim/export handlers
- Core backend bootstrap and DB/auth helper stack (`includes/init.php`, `includes/db.php`, `includes/auth.php`, and domain helper backends)

Goal: preserve original design/layout for frontend rebuild work.

## Frontend-only run mode

This project can now be opened as a static frontend package:

- Root entrypoint is `index.html`
- Auth entrypoints are `login.html` and `register.html`
- Verification entrypoint is `pending-verification.html`
- Apache is configured to prefer HTML via `.htaccess` (`DirectoryIndex index.html index.htm`)

No backend runtime is required for UI preview.
