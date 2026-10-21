
<?php

require_once __DIR__ . '/../config/database.php';

/*
|--------------------------------------------------------------------------
| Output Escaping
|--------------------------------------------------------------------------
*/

function e($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

/*
|--------------------------------------------------------------------------
| Navigation and Authentication
|--------------------------------------------------------------------------
*/

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        redirect('/registrarconnect/auth/login.php');
    }
}

function require_role(string $role): void
{
    require_login();

    if (($_SESSION['user']['role'] ?? '') !== $role) {
        http_response_code(403);
        exit('403 Forbidden: You are not authorized to view this page.');
    }
}

/*
|--------------------------------------------------------------------------
| CSRF Protection
|--------------------------------------------------------------------------
*/

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function verify_csrf(): void
{
    if (
        !hash_equals(
            $_SESSION['csrf'] ?? '',
            $_POST['csrf'] ?? ''
        )
    ) {
        http_response_code(419);
        exit(
            'Your session token expired. Go back, refresh, and try again.'
        );
    }
}

/*
|--------------------------------------------------------------------------
| Flash Messages
|--------------------------------------------------------------------------
*/

function set_flash(
    string $message,
    string $type = 'success'
): void {
    $_SESSION['flash'] = [
        'message' => $message,
        'type'    => $type,
    ];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;

    unset($_SESSION['flash']);

    return $flash;
}

/*
|--------------------------------------------------------------------------
| Request Status and Reference Number
|--------------------------------------------------------------------------
*/

function status_class(string $status): string
{
    return strtolower(
        str_replace(' ', '-', $status)
    );
}

function create_request_id(int $id): string
{
    return 'RC-'
        . date('Y')
        . '-'
        . str_pad(
            (string) $id,
            4,
            '0',
            STR_PAD_LEFT
        );
}
