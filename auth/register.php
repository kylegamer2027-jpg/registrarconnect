
<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| RegistrarConnect - Student Registration
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/functions.php';

$registerPath = '/registrarconnect/auth/register.php';
$loginPath = '/registrarconnect/auth/login.php';

/*
|--------------------------------------------------------------------------
| Handle Registration Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate the CSRF token.
        verify_csrf();

        // Read and normalize submitted fields.
        $name = trim((string) ($_POST['full_name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $studentNo = trim((string) ($_POST['student_no'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        /*
        |--------------------------------------------------------------------------
        | Validate Required Fields
        |--------------------------------------------------------------------------
        */

        if (
            $name === '' ||
            strlen($name) > 150 ||
            $email === '' ||
            strlen($email) > 150 ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            $studentNo === '' ||
            strlen($studentNo) > 60 ||
            strlen($password) < 8
        ) {
            set_flash(
                'Please complete all fields. Use a valid email, a student number, and a password with at least 8 characters.',
                'error'
            );

            redirect($registerPath);
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Connect to Database
        |--------------------------------------------------------------------------
        */

        $pdo = db();

        /*
        |--------------------------------------------------------------------------
        | Check Existing Email or Student Number
        |--------------------------------------------------------------------------
        */

        $check = $pdo->prepare(
            'SELECT id
             FROM users
             WHERE email = ? OR student_no = ?
             LIMIT 1'
        );

        $check->execute([$email, $studentNo]);

        if ($check->fetch(PDO::FETCH_ASSOC)) {
            set_flash(
                'This email address or student number is already registered. Please use different details.',
                'error'
            );

            redirect($registerPath);
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Create Student Account
        |--------------------------------------------------------------------------
        */

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        if ($passwordHash === false) {
            throw new RuntimeException('Password hashing failed.');
        }

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
            $studentNo,
            $passwordHash,
            'student'
        ]);

        /*
        |--------------------------------------------------------------------------
        | Registration Successful
        |--------------------------------------------------------------------------
        */

        set_flash(
            'Account created successfully. You can now log in.',
            'success'
        );

        redirect($loginPath);
        exit;

    } catch (PDOException $ex) {
        // Keep database details out of the public page.
        error_log('RegistrarConnect registration database error: ' . $ex->getMessage());

        // SQLSTATE 23000 commonly indicates a duplicate-key constraint.
        if ($ex->getCode() === '23000') {
            set_flash(
                'The email address or student number may already be registered. Please check your details.',
                'error'
            );
        } else {
            set_flash(
                'We could not create your account. Please try again later.',
                'error'
            );
        }

        redirect($registerPath);
        exit;

    } catch (Throwable $ex) {
        error_log('RegistrarConnect registration error: ' . $ex->getMessage());

        set_flash(
            'Something went wrong while creating your account. Please try again.',
            'error'
        );

        redirect($registerPath);
        exit;
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

    <form
        method="post"
        action="<?= e($registerPath) ?>"
    >

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
            autocomplete="off"
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
        <a href="<?= e($loginPath) ?>">
            Log in
        </a>
    </p>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
