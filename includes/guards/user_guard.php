<?php
declare(strict_types=1);

require_once __DIR__ . '/../init.php';

if (!is_user_logged_in()) {
    flash_error('Tafadhali ingia kama mwanachama kwanza.');
    redirect(url('login.php'));
}

