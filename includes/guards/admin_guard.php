<?php
declare(strict_types=1);

require_once __DIR__ . '/../init.php';

if (!is_admin_logged_in()) {
    flash_error('Tafadhali ingia kama msimamizi kwanza.');
    redirect(url('login.php'));
}

