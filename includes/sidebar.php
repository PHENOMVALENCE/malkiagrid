<?php

declare(strict_types=1);

/**
 * Dashboard sidebar — context: "user" or "admin" via $mgrid_sidebar_context.
 */
$ctx = $mgrid_sidebar_context ?? 'user';
$isAdmin = $ctx === 'admin';
$current = basename((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
$actor = auth_actor();
$initial = strtoupper(substr((string) ($actor['full_name'] ?? 'M'), 0, 1));
$actorId = (string) ($actor['m_id'] ?? $actor['admin_code'] ?? '');

$isActive = static function (array $files) use ($current): bool {
    return in_array($current, $files, true);
};

$anyActive = static function (array $groups) use ($isActive): bool {
    foreach ($groups as $files) {
        if ($isActive($files)) {
            return true;
        }
    }
    return false;
};
?>
<aside class="mgrid-sidebar" id="mgridSidebar">
  <div class="mgrid-sidebar-logo">
    <div class="mgrid-sidebar-logo-mark">
      <img src="<?= e(asset('images/logos/logo.png')) ?>" alt="Nembo ya Malkia Grid" />
    </div>
    <a href="<?= e($isAdmin ? url('admin/dashboard.php') : url('user/dashboard.php')) ?>" class="text-decoration-none">
      <div class="mgrid-sidebar-logo-name">M GRID</div>
      <span class="mgrid-sidebar-logo-sub">Wanawake wakikua kwa nguvu</span>
    </a>
    <button class="btn btn-sm text-white d-lg-none ms-auto" id="mgridSidebarClose" type="button" aria-label="Funga menyu ya pembeni">
      <i class="ti ti-x"></i>
    </button>
  </div>
  <nav class="mgrid-sidebar-nav">
    <?php if ($isAdmin): ?>
      <div class="mgrid-nav-section-label" data-i18n="admin.sec_dashboard">Dashibodi</div>
      <a class="mgrid-nav-link <?= $isActive(['dashboard.php']) ? 'is-active' : '' ?>" href="<?= e(url('admin/dashboard.php')) ?>">
        <i class="ti ti-layout-dashboard"></i><span data-i18n="sidebar.dashboard">Muhtasari</span>
      </a>
      <div class="mgrid-nav-section-label" data-i18n="admin.sec_members">Wanachama</div>
      <?php
        $adminMembersFiles = [
            ['users.php'],
            ['admin_mscores.php','admin_mscore_detail.php'],
            ['admin_documents.php'],
            ['pending-verification.php'],
            ['score-management.php'],
        ];
        $adminMembersOpen = $anyActive($adminMembersFiles);
      ?>
      <details class="mgrid-nav-group" <?= $adminMembersOpen ? 'open' : '' ?>>
        <summary class="mgrid-nav-link mgrid-nav-link--parent <?= $adminMembersOpen ? 'is-active' : '' ?>">
          <i class="ti ti-users"></i>
          <span data-i18n="admin.sec_members">Wanachama</span>
          <i class="ti ti-chevron-down mgrid-nav-caret" aria-hidden="true"></i>
        </summary>
        <div class="mgrid-nav-sub">
          <a class="mgrid-nav-link mgrid-nav-link--sub <?= $isActive(['users.php']) ? 'is-active' : '' ?>" href="<?= e(url('admin/users.php')) ?>">
            <i class="ti ti-users"></i><span data-i18n="sidebar.members">Wanachama wote</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub <?= $isActive(['admin_mscores.php','admin_mscore_detail.php']) ? 'is-active' : '' ?>" href="<?= e(url('admin/admin_mscores.php')) ?>">
            <i class="ti ti-chart-dots-3"></i><span data-i18n="admin.link_mscore">M-SCORE Monitoring</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub <?= $isActive(['admin_documents.php']) ? 'is-active' : '' ?>" href="<?= e(url('admin/admin_documents.php')) ?>">
            <i class="ti ti-file-certificate"></i><span data-i18n="admin.link_documents">Document Verification</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub <?= $isActive(['pending-verification.php']) ? 'is-active' : '' ?>" href="javascript:void(0)" onclick="return false;">
            <i class="ti ti-shield-check"></i><span data-i18n="admin.link_pending">Pending Verification</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub <?= $isActive(['score-management.php']) ? 'is-active' : '' ?>" href="javascript:void(0)" onclick="return false;">
            <i class="ti ti-chart-arcs"></i><span data-i18n="admin.link_score_mgmt">M-Score Management</span>
          </a>
        </div>
      </details>
      <div class="mgrid-nav-section-label" data-i18n="admin.sec_platform">Platform</div>
      <?php
        $adminPlatformFiles = [
            ['admin_funding_applications.php','admin_funding_review.php','manage_repayments.php'],
            ['admin_benefits.php','add_benefit.php','edit_benefit.php','admin_benefit_claims.php','manage_benefit_categories.php','manage_benefit_providers.php','update_benefit_claim_status.php'],
            ['admin_opportunities.php','add_opportunity.php','edit_opportunity.php','admin_applications.php','manage_opportunity_categories.php'],
            ['admin_trainings.php','add_training.php','edit_training.php','admin_training_registrations.php','update_training_completion.php'],
            ['admin_announcements.php','create_announcement.php','view_announcement.php'],
            ['admin_analytics.php'],
            ['admin_reports.php','export_report.php'],
        ];
        $adminPlatformOpen = $anyActive($adminPlatformFiles);
      ?>
      <details class="mgrid-nav-group" <?= $adminPlatformOpen ? 'open' : '' ?>>
        <summary class="mgrid-nav-link mgrid-nav-link--parent <?= $adminPlatformOpen ? 'is-active' : '' ?>">
          <i class="ti ti-building-store"></i>
          <span data-i18n="admin.sec_platform">Platform</span>
          <i class="ti ti-chevron-down mgrid-nav-caret" aria-hidden="true"></i>
        </summary>
        <div class="mgrid-nav-sub">
          <a class="mgrid-nav-link mgrid-nav-link--sub" href="javascript:void(0)" onclick="return false;">
            <i class="ti ti-handshake"></i><span data-i18n="admin.link_partners">Washirika</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub <?= $isActive(['admin_funding_applications.php','admin_funding_review.php','manage_repayments.php']) ? 'is-active' : '' ?>" href="<?= e(url('admin/admin_funding_applications.php')) ?>">
            <i class="ti ti-cash-banknote"></i><span data-i18n="admin.link_loans">Loan Applications</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub <?= $isActive(['admin_benefits.php','add_benefit.php','edit_benefit.php','admin_benefit_claims.php','manage_benefit_categories.php','manage_benefit_providers.php','update_benefit_claim_status.php']) ? 'is-active' : '' ?>" href="<?= e(url('admin/admin_benefits.php')) ?>">
            <i class="ti ti-gift"></i><span data-i18n="admin.link_benefits">M-Manufaa</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub <?= $isActive(['admin_opportunities.php','add_opportunity.php','edit_opportunity.php','admin_applications.php','manage_opportunity_categories.php']) ? 'is-active' : '' ?>" href="<?= e(url('admin/admin_opportunities.php')) ?>">
            <i class="ti ti-briefcase"></i><span data-i18n="admin.link_opportunities">Opportunities</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub <?= $isActive(['admin_trainings.php','add_training.php','edit_training.php','admin_training_registrations.php','update_training_completion.php']) ? 'is-active' : '' ?>" href="<?= e(url('admin/admin_trainings.php')) ?>">
            <i class="ti ti-school"></i><span data-i18n="admin.link_trainings">Trainings</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub <?= $isActive(['admin_announcements.php','create_announcement.php','view_announcement.php']) ? 'is-active' : '' ?>" href="<?= e(url('admin/admin_announcements.php')) ?>">
            <i class="ti ti-bell-ringing"></i><span data-i18n="admin.link_announcements">Announcements</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub <?= $isActive(['admin_analytics.php']) ? 'is-active' : '' ?>" href="<?= e(url('admin/admin_analytics.php')) ?>">
            <i class="ti ti-chart-line"></i><span data-i18n="admin.link_analytics">Analytics</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub <?= $isActive(['admin_reports.php','export_report.php']) ? 'is-active' : '' ?>" href="<?= e(url('admin/admin_reports.php')) ?>">
            <i class="ti ti-file-analytics"></i><span data-i18n="admin.link_reports">Reports</span>
          </a>
        </div>
      </details>
      <div class="mgrid-nav-section-label" data-i18n="admin.sec_system">System</div>
      <?php
        $adminSystemFiles = [
            ['admin_accounts.php'],
            ['platform_settings.php'],
        ];
        $adminSystemOpen = $anyActive($adminSystemFiles);
      ?>
      <details class="mgrid-nav-group" <?= $adminSystemOpen ? 'open' : '' ?>>
        <summary class="mgrid-nav-link mgrid-nav-link--parent <?= $adminSystemOpen ? 'is-active' : '' ?>">
          <i class="ti ti-settings"></i>
          <span data-i18n="admin.sec_system">System</span>
          <i class="ti ti-chevron-down mgrid-nav-caret" aria-hidden="true"></i>
        </summary>
        <div class="mgrid-nav-sub">
          <a class="mgrid-nav-link mgrid-nav-link--sub <?= $isActive(['admin_accounts.php']) ? 'is-active' : '' ?>" href="<?= e(url('admin/admin_accounts.php')) ?>">
            <i class="ti ti-user-star"></i><span data-i18n="admin.link_team">Timu ya utawala</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub <?= $isActive(['platform_settings.php']) ? 'is-active' : '' ?>" href="<?= e(url('admin/platform_settings.php')) ?>">
            <i class="ti ti-settings"></i><span data-i18n="sidebar.settings">Settings</span>
          </a>
        </div>
      </details>
    <?php else: ?>
      <?php
        $memberOverviewFiles = [
            ['dashboard.php'],
            ['profile.php'],
            ['my_mscore.php'],
        ];
        $memberIdentityFiles = [
            ['verify-id.php'],
            ['my_documents.php','upload_document.php','reupload_document.php'],
        ];
        $memberOpportunityFiles = [
            ['opportunities.php','opportunity_detail.php','apply_opportunity.php','my_opportunities.php'],
            ['trainings.php','training_detail.php','register_training.php','my_trainings.php'],
            ['funding_overview.php','apply_funding.php','my_funding_applications.php','funding_application_detail.php'],
            ['benefits.php','benefit_detail.php','claim_benefit.php','my_benefits.php','benefit_claim_detail.php'],
        ];
        $memberAccountFiles = [
            ['notifications.php','mark_notification_read.php'],
            ['settings.php'],
        ];
        $memberOverviewOpen = $anyActive($memberOverviewFiles);
        $memberIdentityOpen = $anyActive($memberIdentityFiles);
        $memberOpportunityOpen = $anyActive($memberOpportunityFiles);
        $memberAccountOpen = $anyActive($memberAccountFiles);
      ?>

      <details class="mgrid-nav-group" <?= $memberOverviewOpen ? 'open' : '' ?>>
        <summary class="mgrid-nav-link mgrid-nav-link--parent <?= $memberOverviewOpen ? 'is-active' : '' ?>">
          <i class="ti ti-smart-home"></i>
          <span data-i18n="sidebar.section_overview">Muhtasari</span>
          <i class="ti ti-chevron-down mgrid-nav-caret" aria-hidden="true"></i>
        </summary>
        <div class="mgrid-nav-sub">
          <a class="mgrid-nav-link mgrid-nav-link--sub <?= $isActive(['dashboard.php']) ? 'is-active' : '' ?>" href="<?= e(url('user/dashboard.php')) ?>">
            <i class="ti ti-smart-home"></i><span data-i18n="sidebar.dashboard">Dashibodi</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub <?= $isActive(['profile.php']) ? 'is-active' : '' ?>" href="<?= e(url('user/profile.php')) ?>">
            <i class="ti ti-user-circle"></i><span data-i18n="sidebar.m_profile">M PROFILE</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub is-locked" href="javascript:void(0)" onclick="return false;" aria-disabled="true" title="Imefungwa kwa sasa">
            <i class="ti ti-lock"></i><span data-i18n="sidebar.m_score">M SCORE</span>
          </a>
        </div>
      </details>

      <details class="mgrid-nav-group" <?= $memberIdentityOpen ? 'open' : '' ?>>
        <summary class="mgrid-nav-link mgrid-nav-link--parent <?= $memberIdentityOpen ? 'is-active' : '' ?>">
          <i class="ti ti-id-badge-2"></i>
          <span data-i18n="sidebar.section_identity">Utambulisho</span>
          <i class="ti ti-chevron-down mgrid-nav-caret" aria-hidden="true"></i>
        </summary>
        <div class="mgrid-nav-sub">
          <a class="mgrid-nav-link mgrid-nav-link--sub is-locked" href="javascript:void(0)" onclick="return false;" aria-disabled="true" title="Imefungwa kwa sasa">
            <i class="ti ti-lock"></i><span data-i18n="sidebar.id_verification">ID Verification</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub is-locked" href="javascript:void(0)" onclick="return false;" aria-disabled="true" title="Imefungwa kwa sasa">
            <i class="ti ti-lock"></i><span data-i18n="sidebar.documents">Documents</span>
          </a>
        </div>
      </details>

      <details class="mgrid-nav-group" <?= $memberOpportunityOpen ? 'open' : '' ?>>
        <summary class="mgrid-nav-link mgrid-nav-link--parent <?= $memberOpportunityOpen ? 'is-active' : '' ?>">
          <i class="ti ti-briefcase"></i>
          <span data-i18n="sidebar.section_opportunities">Fursa</span>
          <i class="ti ti-chevron-down mgrid-nav-caret" aria-hidden="true"></i>
        </summary>
        <div class="mgrid-nav-sub">
          <a class="mgrid-nav-link mgrid-nav-link--sub is-locked" href="javascript:void(0)" onclick="return false;" aria-disabled="true" title="Imefungwa kwa sasa">
            <i class="ti ti-lock"></i><span data-i18n="sidebar.opportunities_link">Opportunities</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub is-locked" href="javascript:void(0)" onclick="return false;" aria-disabled="true" title="Imefungwa kwa sasa">
            <i class="ti ti-lock"></i><span data-i18n="sidebar.trainings">Trainings</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub is-locked" href="javascript:void(0)" onclick="return false;" aria-disabled="true" title="Imefungwa kwa sasa">
            <i class="ti ti-lock"></i><span data-i18n="sidebar.m_fund">M-Fund (Loans)</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub is-locked" href="javascript:void(0)" onclick="return false;" aria-disabled="true" title="Imefungwa kwa sasa">
            <i class="ti ti-lock"></i><span data-i18n="sidebar.m_benefits">M-Manufaa</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub is-locked" href="javascript:void(0)" onclick="return false;" aria-disabled="true" title="Imefungwa kwa sasa">
            <i class="ti ti-lock"></i><span data-i18n="sidebar.m_partners">M-Washirika</span>
          </a>
        </div>
      </details>

      <details class="mgrid-nav-group" <?= $memberAccountOpen ? 'open' : '' ?>>
        <summary class="mgrid-nav-link mgrid-nav-link--parent <?= $memberAccountOpen ? 'is-active' : '' ?>">
          <i class="ti ti-user"></i>
          <span data-i18n="sidebar.section_account">Akaunti</span>
          <i class="ti ti-chevron-down mgrid-nav-caret" aria-hidden="true"></i>
        </summary>
        <div class="mgrid-nav-sub">
          <a class="mgrid-nav-link mgrid-nav-link--sub <?= $isActive(['notifications.php','mark_notification_read.php']) ? 'is-active' : '' ?>" href="<?= e(url('user/notifications.php')) ?>">
            <i class="ti ti-bell"></i><span data-i18n="sidebar.notifications">Notifications</span>
          </a>
          <a class="mgrid-nav-link mgrid-nav-link--sub <?= $isActive(['settings.php']) ? 'is-active' : '' ?>" href="<?= e(url('user/settings.php')) ?>">
            <i class="ti ti-settings"></i><span data-i18n="sidebar.settings">Settings</span>
          </a>
        </div>
      </details>
    <?php endif; ?>
    <a class="mgrid-nav-link" href="<?= e(url('logout.php')) ?>">
      <i class="ti ti-logout"></i><span data-i18n="sidebar.logout">Toka</span>
    </a>
  </nav>
  <div class="mgrid-sidebar-user">
    <div class="mgrid-sidebar-avatar"><?= e($initial) ?></div>
    <div>
      <div class="mgrid-sidebar-user-name"><?= e((string) ($actor['full_name'] ?? 'Mwanachama')) ?></div>
      <div class="mgrid-sidebar-user-mid"><?= e($actorId) ?></div>
    </div>
  </div>
</aside>
