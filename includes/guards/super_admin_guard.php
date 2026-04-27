<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_guard.php';

$admin = current_admin();
$isSuper = false;

if (is_array($admin)) {
    $isSuper = (isset($admin['role']) && (string) $admin['role'] === 'super_admin')
        || (!empty($admin['is_super_admin']) && (int) $admin['is_super_admin'] === 1);
}

if (!$isSuper) {
    flash_error('Huna ruhusa ya super admin.');
    redirect('/admin/dashboard.php');
}

