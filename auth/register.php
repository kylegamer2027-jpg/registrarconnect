
<?php

require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $name = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $student_no = trim($_POST['student_no'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate required fields
    if (
        $name === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8 ||
        $student_no === ''
    ) {
        set_flash(
            'Please complete all fields. Enter a valid email, student number, and password with at least 8 characters.',
            'error'
        );

        redirect('/registrarconnect/auth/register.php');
    }

    try {
        $pdo = db();

        // Check whether the email or student number already exists.
        $check = $pdo->prepare(
            'SELECT id
             FROM users
             WHERE email = ? OR student_no = ?
             LIMIT 1'
        );

        $check->execute([$email, $student_no]);

        if ($check->fetch(PDO::FETCH_ASSOC)) {
            set_flash(
                'This email address or student number is already registered. Please use different details.',
                'error'
            );

            redirect('/registrarconnect/auth/register.php');
        }

        // Create the student account.
        $query = $pdo->prepare(
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

        set_flash(
            'Account created successfully. You can now log in.',
            'success'
        );

        redirect('/registrarconnect/auth/login.php');

    } catch (PDOException $ex) {

        // Log the technical error without exposing database details to users.
        error_log('RegistrarConnect registration error: ' . $ex->getMessage());

        // Handle duplicate-key errors separately.
        if ($ex->getCode() === '23000') {
            set_flash(
                'The email address or student number may already be registered. Please check your details.',
                'error'
            );
        } else {
            set_flash(
                'We could not create your account because of a database error. Please try again later.',
                'error'
            );
        }

        redirect('/registrarconnect/auth/register.php');
    }
}

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

    <form method="post" action="/registrarconnect/auth/register.php">

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
            maxlength="150"
            required
            autocomplete="name"
        >

        <label for="student_no">Student number</label>
        <input
            type="text"
            id="student_no"
            name="student_no"
            maxlength="60"
            required
        >

        <label for="email">Email address</label>
        <input
            type="email"
            id="email"
            name="email"
            maxlength="150"
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
