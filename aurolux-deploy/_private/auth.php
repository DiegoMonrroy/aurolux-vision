<?php
// Autenticación por sesión (demo). Sin base de datos.

function auth_start(): void {
    if (session_status() === PHP_SESSION_NONE) {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
              || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        session_name('AUROLUXSESS');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $https,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function auth_user(): ?array {
    auth_start();
    return $_SESSION['user'] ?? null;
}

function auth_require(): void {
    if (!auth_user()) {
        header('Location: login.php');
        exit;
    }
}

function auth_login(string $name, string $email): void {
    auth_start();
    session_regenerate_id(true);
    $_SESSION['user']  = ['name' => $name, 'email' => $email];
    $_SESSION['fails'] = 0;
}

function auth_logout(): void {
    auth_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// Límite simple de intentos: 5 fallos => 60 s de espera.
function auth_locked(): bool {
    auth_start();
    if (($_SESSION['fails'] ?? 0) >= 5) {
        if (time() - ($_SESSION['last_fail'] ?? 0) < 60) return true;
        $_SESSION['fails'] = 0;
    }
    return false;
}

function auth_fail(): void {
    auth_start();
    $_SESSION['fails']     = ($_SESSION['fails'] ?? 0) + 1;
    $_SESSION['last_fail'] = time();
    usleep(400000);
}

function csrf_token(): string {
    auth_start();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_check(string $t): bool {
    auth_start();
    return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}
