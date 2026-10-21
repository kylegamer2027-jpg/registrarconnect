
<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| RegistrarConnect - Login
|--------------------------------------------------------------------------
*/

// Load application functions and configuration.
require_once __DIR__ . '/../includes/functions.php';

// Do not display PHP errors to website visitors.
// Check your hosting error log if a server error occurs.
ini_set('log_errors', '1');
ini_set('display_errors', '0');
error_reporting(E_ALL);

/*
|--------------------------------------------------------------------------
| Application Paths
|--------------------------------------------------------------------------
|
| The application is installed in /registrarconnect on InfinityFree.
| If you later move it directly into htdocs, update this base path.
|
*/

$basePath = '/registrarconnect';

$loginPath = $basePath . '/auth/login.php';
$registerPath = $basePath . '/auth/register.php';
$registrarDashboard = $basePath . '/registrar/dashboard.php';
$studentDashboard = $basePath . '/student/dashboard.php';

/*
|--------------------------------------------------------------------------
| Redirect Authenticated Users
|--------------------------------------------------------------------------
*/

$user = current_user();

if ($user) {
    $dashboard = (($user['role'] ?? '') === 'registrar')
        ? $registrarDashboard
        : $studentDashboard;

    redirect($dashboard);
    exit;
}

/*
|--------------------------------------------------------------------------
| Handle Login Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate the CSRF token.
        verify_csrf();

        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        if (
            $email === '' ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            $password === ''
        ) {
            set_flash('Please enter a valid email and password.', 'error');
            redirect($loginPath);
            exit;
        }

        // Look up an active user by email.
        $query = db()->prepare(
            'SELECT id, full_name, email, student_no, password_hash, role
             FROM users
             WHERE email = ?
               AND is_active = 1
             LIMIT 1'
        );

        $query->execute([$email]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        // Verify the password without exposing account details.
        if (
            !$user ||
            !isset($user['password_hash']) ||
            !password_verify($password, $user['password_hash'])
        ) {
            set_flash('Invalid email or password.', 'error');
            redirect($loginPath);
            exit;
        }

        // Only allow recognized roles.
        if (!in_array($user['role'], ['registrar', 'student'], true)) {
            error_log('Login blocked: unrecognized role for user ID ' . $user['id']);

            set_flash('Unable to sign in. Please contact the administrator.', 'error');
            redirect($loginPath);
            exit;
        }

        // Regenerate the session ID after successful authentication.
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('Session is not active.');
        }

        session_regenerate_id(true);

        // Do not store the password hash in the session.
        unset($user['password_hash']);

        $_SESSION['user'] = $user;

        $dashboard = $user['role'] === 'registrar'
            ? $registrarDashboard
            : $studentDashboard;

        redirect($dashboard);
        exit;

    } catch (Throwable $exception) {
        // Keep technical details in the server error log, not the page.
        error_log(
            'RegistrarConnect login error: ' .
            $exception->getMessage()
        );

        set_flash(
            'We could not complete your login. Please try again. If the problem continues, contact the administrator.',
            'error'
        );

        redirect($loginPath);
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Display Login Page
|--------------------------------------------------------------------------
*/

$page_title = 'Login';

require __DIR__ . '/../includes/header.php';
?>

<div class="auth-card">

    <a class="auth-brand" href="<?= e($basePath . '/') ?>">
        ▤ RegistrarConnect
    </a>

    <h1>Welcome back</h1>

    <p>
        Log in to request and track your academic documents.
    </p>

    <form method="post" action="<?= e($loginPath) ?>">

        <input
            type="hidden"
            name="csrf"
            value="<?= e(csrf_token()) ?>"
        >

        <label for="email">Email address</label>

        <input
            type="email"
            id="email"
            name="email"
            required
            maxlength="254"
            autocomplete="username"
            value="<?= e((string) ($_POST['email'] ?? '')) ?>"
        >

        <label for="password">Password</label>

        <input
            type="password"
            id="password"
            name="password"
            required
            autocomplete="current-password"
        >

        <button type="submit" class="btn primary full">
            Log in
        </button>

    </form>

    <p class="auth-switch">
        New student?
        <a href="<?= e($registerPath) ?>">
            Create an account
        </a>
    </p>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
