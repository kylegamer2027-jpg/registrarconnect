
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
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

/*
|--------------------------------------------------------------------------
| Navigation
|--------------------------------------------------------------------------
*/

function redirect(string $path): never
{
    // Prevent header injection and redirects to external websites.
    if (
        $path === '' ||
        str_contains($path, "\r") ||
        str_contains($path, "\n") ||
        !str_starts_with($path, '/') ||
        str_starts_with($path, '//')
    ) {
        $path = '/registrarconnect/auth/login.php';
    }

    header('Location: ' . $path);
    exit;
}

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

function current_user(): ?array
{
    if (
        !isset($_SESSION['user']) ||
        !is_array($_SESSION['user'])
    ) {
        return null;
    }

    return $_SESSION['user'];
}

function require_login(): void
{
    if (current_user() === null) {
        redirect('/registrarconnect/auth/login.php');
    }
}

function require_role(string $role): void
{
    require_login();

    if (($_SESSION['user']['role'] ?? '') !== $role) {
        http_response_code(403);

        exit(
            '403 Forbidden: You are not authorized to view this page.'
        );
    }
}

/*
|--------------------------------------------------------------------------
| CSRF Protection
|--------------------------------------------------------------------------
*/

function csrf_token(): string
{
    if (
        !isset($_SESSION['csrf']) ||
        !is_string($_SESSION['csrf']) ||
        $_SESSION['csrf'] === ''
    ) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function verify_csrf(): void
{
    $sessionToken = $_SESSION['csrf'] ?? '';
    $submittedToken = $_POST['csrf'] ?? '';

    if (
        !is_string($sessionToken) ||
        !is_string($submittedToken) ||
        $sessionToken === '' ||
        $submittedToken === '' ||
        !hash_equals($sessionToken, $submittedToken)
    ) {
        http_response_code(419);

        exit(
            'Your session token expired. Refresh the page and try again.'
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
        'type' => $type,
    ];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;

    unset($_SESSION['flash']);

    if (!is_array($flash)) {
        return null;
    }

    return $flash;
}

/*
|--------------------------------------------------------------------------
| Request Status
|--------------------------------------------------------------------------
*/

function status_class(string $status): string
{
    return strtolower(
        str_replace(' ', '-', trim($status))
    );
}

/*
|--------------------------------------------------------------------------
| Request Reference Number
|--------------------------------------------------------------------------
*/

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
