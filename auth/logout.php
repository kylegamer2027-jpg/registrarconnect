
<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| RegistrarConnect - Logout
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/functions.php';

/*
|--------------------------------------------------------------------------
| Clear Session Data
|--------------------------------------------------------------------------
*/

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$_SESSION = [];

/*
|--------------------------------------------------------------------------
| Remove Session Cookie
|--------------------------------------------------------------------------
*/

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        [
            'expires'  => time() - 42000,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]
    );
}

/*
|--------------------------------------------------------------------------
| Destroy Session
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

/*
|--------------------------------------------------------------------------
| Redirect to Login Page
|--------------------------------------------------------------------------
*/

header('Location: /registrarconnect/auth/login.php', true, 303);
exit;
