<?php

declare(strict_types=1);

$u = auth_actor();
if ($u === null) {
    return;
}
?>
<header class="app-header">
  <nav class="mgrid-topbar">
    <button type="button" class="btn btn-mgrid btn-mgrid-ghost d-lg-none" id="mgridSidebarToggle" aria-label="Toggle sidebar">
      <i class="ti ti-menu-2"></i>
    </button>
    <div class="mgrid-topbar-breadcrumb">
      <?php
        $tbPath = basename((string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH));
        $showMemberDash = (($mgrid_sidebar_context ?? '') === 'user' && auth_user() !== null && $tbPath !== 'dashboard.php');
      ?>
      <?php if ($showMemberDash): ?>
        <a class="btn btn-sm btn-mgrid btn-mgrid-outline me-2 d-inline-flex align-items-center gap-1" href="<?= e(url('user/dashboard.php')) ?>">
          <i class="ti ti-smart-home"></i><span data-i18n="topbar.back_dashboard">Dashibodi</span>
        </a>
      <?php endif; ?>
      <span data-i18n="topbar.signed_in_as" class="d-none">Signed in as</span>
      <span data-i18n="<?= ($mgrid_sidebar_context ?? 'user') === 'admin' ? 'topbar.role_admin' : 'topbar.role_member' ?>"><?= ($mgrid_sidebar_context ?? 'user') === 'admin' ? 'Admin' : 'Member' ?></span>
      <i class="ti ti-chevron-right"></i>
      <span class="mgrid-topbar-breadcrumb-current"><?= e((string) ($mgrid_page_title ?? (function_exists('__') ? __('topbar.fallback_page') : 'Home'))) ?></span>
    </div>
    <div class="mgrid-topbar-actions">
      <?php if (($mgrid_sidebar_context ?? '') === 'user' && auth_user() !== null): ?>
        <?php require __DIR__ . '/../user/notification_dropdown_include.php'; ?>
      <?php endif; ?>
      <?php require __DIR__ . '/lang_toggle.php'; ?>
      <div class="dropdown">
        <button
          class="btn btn-mgrid btn-mgrid-ghost p-0 border-0"
          type="button"
          id="mgridAccountMenu"
          data-bs-toggle="dropdown"
          aria-expanded="false"
          aria-label="Account menu"
        >
          <span class="mgrid-sidebar-avatar"><?= e(strtoupper(substr($u['full_name'], 0, 1))) ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="mgridAccountMenu">
          <?php if (($mgrid_sidebar_context ?? '') === 'user' && auth_user() !== null): ?>
            <li>
              <a class="dropdown-item d-flex align-items-center gap-2" href="<?= e(url('user/profile.php')) ?>">
                <i class="ti ti-user-circle"></i><span data-i18n="sidebar.m_profile">M PROFILE</span>
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
          <?php endif; ?>
          <li>
            <a class="dropdown-item d-flex align-items-center gap-2" href="<?= e(url('logout.php')) ?>">
              <i class="ti ti-logout"></i><span data-i18n="sidebar.logout">Logout</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</header>
