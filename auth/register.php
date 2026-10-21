
<?php

require_once __DIR__ . '/../includes/functions.php';

/*
|--------------------------------------------------------------------------
| Handle Registration Form Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    // Retrieve and sanitize form inputs
    $name = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $student_no = trim($_POST['student_no'] ?? '');
    $password = $_POST['password'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | Validate Required Fields
    |--------------------------------------------------------------------------
    */

    if (
        $name === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8 ||
        $student_no === ''
    ) {
        set_flash(
            'Complete all fields. Use a valid email and a password with at least 8 characters.',
            'error'
        );

        redirect('/registrarconnect/auth/register.php');
    }

    /*
    |--------------------------------------------------------------------------
    | Create Student Account
    |--------------------------------------------------------------------------
    */

    try {

        $query = db()->prepare(
            'INSERT INTO users (
                full_name,
                email,
                student_no,
                password_hash,
                role
            ) VALUES (?, ?, ?, ?, ?)'
        );

        $query->execute([
            $name,
            $email,
            $student_no,
            password_hash($password, PASSWORD_DEFAULT),
            'student'
        ]);

        set_flash('Account created. You can now log in.');

        redirect('/registrarconnect/auth/login.php');

    } catch (PDOException $ex) {

        set_flash(
            'Unable to create account. Check whether your email or student number is already registered.',
            'error'
        );

        redirect('/registrarconnect/auth/register.php');
    }
}

/*
|--------------------------------------------------------------------------
| Display Registration Page
|--------------------------------------------------------------------------
*/

$page_title = 'Create Account';

require __DIR__ . '/../includes/header.php';

?>

<div class="auth-card">

    <a class="auth-brand" href="/registrarconnect/">
        ▤ RegistrarConnect
    </a>

    <h1>Create your account</h1>

    <p>
        Register as a student to request academic documents.
    </p>

    <form method="post">

        <input
            type="hidden"
            name="csrf"
            value="<?= e(csrf_token()) ?>"
        >

        <label for="full_name">Full name</label>

        <input
            type="text"
            id="full_name"
            name="full_name"
            required
            autocomplete="name"
        >

        <label for="student_no">Student number</label>

        <input
            type="text"
            id="student_no"
            name="student_no"
            required
        >

        <label for="email">Email address</label>

        <input
            type="email"
            id="email"
            name="email"
            required
            autocomplete="email"
        >

        <label for="password">Password</label>

        <input
            type="password"
            id="password"
            name="password"
            minlength="8"
            required
            autocomplete="new-password"
        >

        <button type="submit" class="btn primary full">
            Create account
        </button>

    </form>

    <p class="auth-switch">
        Already registered?
        <a href="/registrarconnect/auth/login.php">
            Log in
        </a>
    </p>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
