
<?php

require_once __DIR__ . '/../includes/functions.php';

/*
|--------------------------------------------------------------------------
| Redirect Authenticated Users
|--------------------------------------------------------------------------
*/

if (current_user()) {
    $dashboard = current_user()['role'] === 'registrar'
        ? '/registrarconnect/registrar/dashboard.php'
        : '/registrarconnect/student/dashboard.php';

    redirect($dashboard);
}

/*
|--------------------------------------------------------------------------
| Handle Login Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $query = db()->prepare(
        'SELECT id, full_name, email, student_no, password_hash, role
         FROM users
         WHERE email = ?
         AND is_active = 1
         LIMIT 1'
    );

    $query->execute([$email]);

    $user = $query->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);

        unset($user['password_hash']);

        $_SESSION['user'] = $user;

        $dashboard = $user['role'] === 'registrar'
            ? '/registrarconnect/registrar/dashboard.php'
            : '/registrarconnect/student/dashboard.php';

        redirect($dashboard);
    }

    set_flash('Invalid email or password.', 'error');

    redirect('/registrarconnect/auth/login.php');
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

    <a class="auth-brand" href="/registrarconnect/">
        ▤ RegistrarConnect
    </a>

    <h1>Welcome back</h1>

    <p>
        Log in to request and track your academic documents.
    </p>

    <form method="post">

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
            autocomplete="username"
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
        <a href="/registrarconnect/auth/register.php">
            Create an account
        </a>
    </p>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
