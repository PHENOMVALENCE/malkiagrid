<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/auth.php';

logout_all();
if ((string) ($_GET['reason'] ?? '') === 'idle') {
    flash_error('Umetolewa kwa kutotumia mfumo kwa muda. Tafadhali ingia tena.');
}
redirect('login.php');

