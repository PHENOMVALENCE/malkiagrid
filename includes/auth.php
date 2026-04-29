<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

function auth_normalize_phone(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    if ($digits === '') {
        return '';
    }

    if (str_starts_with($digits, '0')) {
        $digits = '255' . substr($digits, 1);
    }
    if (!str_starts_with($digits, '255')) {
        $digits = '255' . $digits;
    }

    return '+' . $digits;
}

function auth_throttle_bucket(string $type, string $identity): string
{
    return $type . ':' . hash('sha256', strtolower(trim($identity)));
}

function auth_is_throttled(string $bucket, int $maxAttempts = 5, int $windowSeconds = 300): bool
{
    start_secure_session();
    $data = $_SESSION['_login_throttle'][$bucket] ?? null;
    if (!is_array($data)) {
        return false;
    }

    $first = (int) ($data['first'] ?? 0);
    $count = (int) ($data['count'] ?? 0);

    if ($first <= 0 || (time() - $first) > $windowSeconds) {
        unset($_SESSION['_login_throttle'][$bucket]);
        return false;
    }

    return $count >= $maxAttempts;
}

function auth_mark_failed_attempt(string $bucket): void
{
    start_secure_session();
    $data = $_SESSION['_login_throttle'][$bucket] ?? null;
    if (!is_array($data) || !isset($data['first'], $data['count'])) {
        $_SESSION['_login_throttle'][$bucket] = [
            'first' => time(),
            'count' => 1,
        ];
        return;
    }

    $windowSeconds = 300;
    $first = (int) $data['first'];
    $count = (int) $data['count'];

    if ((time() - $first) > $windowSeconds) {
        $_SESSION['_login_throttle'][$bucket] = [
            'first' => time(),
            'count' => 1,
        ];
        return;
    }

    $_SESSION['_login_throttle'][$bucket]['count'] = $count + 1;
}

function auth_clear_failed_attempts(string $bucket): void
{
    start_secure_session();
    unset($_SESSION['_login_throttle'][$bucket]);
}

/**
 * Member login: supports M-ID, phone, email + password.
 * Uses users table only.
 */
function authenticate_user(string $identity, string $password): array
{
    $identity = trim($identity);
    $bucket = auth_throttle_bucket('user', $identity);

    if (auth_is_throttled($bucket)) {
        return ['ok' => false, 'reason' => 'throttled'];
    }

    $pdo = db();
    $phone = auth_normalize_phone($identity);
    $email = strtolower($identity);
    $mId = strtoupper($identity);

    $stmt = $pdo->prepare(
        'SELECT id, m_id, password_hash, status
         FROM users
         WHERE m_id = :m_id OR phone = :phone OR email = :email
         LIMIT 1'
    );
    $stmt->execute([
        ':m_id' => $mId,
        ':phone' => $phone,
        ':email' => $email,
    ]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!is_array($user) || !isset($user['password_hash']) || !password_verify($password, (string) $user['password_hash'])) {
        auth_mark_failed_attempt($bucket);
        return ['ok' => false, 'reason' => 'invalid_credentials'];
    }

    auth_clear_failed_attempts($bucket);

    $status = (string) ($user['status'] ?? 'pending');
    if (in_array($status, ['suspended', 'deleted'], true)) {
        return ['ok' => false, 'reason' => $status === 'deleted' ? 'deleted' : 'suspended', 'status' => $status];
    }

    session_regenerate_id(true);
    unset($_SESSION['admin_id'], $_SESSION['admin_role']);

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_m_id'] = (string) $user['m_id'];
    $_SESSION['role'] = 'user';

    return ['ok' => true, 'status' => $status];
}

/**
 * Admin login: email + password, admins table only, status must be active.
 */
function authenticate_admin(string $email, string $password): array
{
    $email = strtolower(trim($email));
    $bucket = auth_throttle_bucket('admin', $email);

    if (auth_is_throttled($bucket)) {
        return ['ok' => false, 'reason' => 'throttled'];
    }

    $stmt = db()->prepare(
        'SELECT id, email, password_hash, role, status
         FROM admins
         WHERE email = :email
         LIMIT 1'
    );
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!is_array($admin) || !isset($admin['password_hash']) || !password_verify($password, (string) $admin['password_hash'])) {
        auth_mark_failed_attempt($bucket);
        return ['ok' => false, 'reason' => 'invalid_credentials'];
    }

    if ((string) ($admin['status'] ?? '') !== 'active') {
        auth_mark_failed_attempt($bucket);
        return ['ok' => false, 'reason' => 'disabled'];
    }

    auth_clear_failed_attempts($bucket);

    session_regenerate_id(true);
    unset($_SESSION['user_id'], $_SESSION['user_m_id']);

    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_role'] = (string) ($admin['role'] ?? 'admin');
    $_SESSION['role'] = 'admin';

    return ['ok' => true, 'status' => 'active'];
}

function logout_all(): void
{
    start_secure_session();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'] ?? '/',
            $params['domain'] ?? '',
            (bool) ($params['secure'] ?? false),
            (bool) ($params['httponly'] ?? true)
        );
    }

    session_destroy();
}

function is_user_logged_in(): bool
{
    start_secure_session();

    return (string) ($_SESSION['role'] ?? '') === 'user' && isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id']);
}

function is_admin_logged_in(): bool
{
    start_secure_session();

    return (string) ($_SESSION['role'] ?? '') === 'admin' && isset($_SESSION['admin_id']) && is_numeric($_SESSION['admin_id']);
}

function auth_user(): ?array
{
    if (!is_user_logged_in()) {
        return null;
    }

    $user = current_user();
    if (!is_array($user)) {
        return null;
    }

    $fullName = trim((string) (($user['first_name'] ?? '') . ' ' . ($user['middle_name'] ?? '') . ' ' . ($user['surname'] ?? '')));

    return [
        'user_id' => (int) ($user['id'] ?? 0),
        'm_id' => (string) ($user['m_id'] ?? ''),
        'full_name' => $fullName !== '' ? $fullName : 'Mwanachama',
        'email' => (string) ($user['email'] ?? ''),
        'status' => (string) ($user['status'] ?? ''),
    ];
}

function auth_admin(): ?array
{
    if (!is_admin_logged_in()) {
        return null;
    }

    $admin = current_admin();
    if (!is_array($admin)) {
        return null;
    }

    $fullName = trim((string) ($admin['full_name'] ?? 'Msimamizi'));

    return [
        'admin_id' => (int) ($admin['id'] ?? 0),
        'full_name' => $fullName !== '' ? $fullName : 'Msimamizi',
        'email' => (string) ($admin['email'] ?? ''),
        'role' => (string) ($admin['role'] ?? 'admin'),
        'status' => (string) ($admin['status'] ?? ''),
    ];
}

