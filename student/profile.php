
<?php
require_once __DIR__ . '/../includes/functions.php';

require_login();

$u = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? 'profile';

    // Change password
    if ($action === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $q = db()->prepare(
            'SELECT password_hash FROM users WHERE id = ?'
        );
        $q->execute([$u['id']]);
        $row = $q->fetch(PDO::FETCH_ASSOC);

        if (!$row || !password_verify($current, $row['password_hash'])) {
            set_flash('Current password is incorrect.', 'error');
            redirect('/registrarconnect/student/profile.php');
        }

        if (strlen($new) < 8 || $new !== $confirm) {
            set_flash(
                'New passwords must match and contain at least 8 characters.',
                'error'
            );
            redirect('/registrarconnect/student/profile.php');
        }

        if (password_verify($new, $row['password_hash'])) {
            set_flash(
                'Your new password must be different from your current password.',
                'error'
            );
            redirect('/registrarconnect/student/profile.php');
        }

        try {
            $q = db()->prepare(
                'UPDATE users SET password_hash = ? WHERE id = ?'
            );

            $q->execute([
                password_hash($new, PASSWORD_DEFAULT),
                $u['id']
            ]);

            set_flash('Password changed successfully.');
        } catch (Throwable $e) {
            error_log('Password update failed: ' . $e->getMessage());

            set_flash(
                'Unable to change your password. Please try again.',
                'error'
            );
        }

        redirect('/registrarconnect/student/profile.php');
    }

    // Update profile information
    if ($action === 'profile') {
        $name = trim($_POST['full_name'] ?? '');

        if ($name === '') {
            set_flash('Name cannot be blank.', 'error');
            redirect('/registrarconnect/student/profile.php');
        }

        if (mb_strlen($name) > 150) {
            set_flash('Name must not exceed 150 characters.', 'error');
            redirect('/registrarconnect/student/profile.php');
        }

        try {
            $q = db()->prepare(
                'UPDATE users SET full_name = ? WHERE id = ?'
            );

            $q->execute([$name, $u['id']]);

            // Update the current session.
            $_SESSION['user']['full_name'] = $name;

            set_flash('Profile updated successfully.');
        } catch (Throwable $e) {
            error_log('Profile update failed: ' . $e->getMessage());

            set_flash(
                'Unable to update your profile. Please try again.',
                'error'
            );
        }

        redirect('/registrarconnect/student/profile.php');
    }

    set_flash('Invalid action.', 'error');
    redirect('/registrarconnect/student/profile.php');
}

$page_title = 'Profile';

require __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <h1>My Profile</h1>
    <p>Manage your account information and password.</p>
</div>

<div class="detail-grid">

    <!-- Account Information -->
    <section class="panel form-panel">
        <h2>Account Information</h2>

        <form method="post">
            <input
                type="hidden"
                name="csrf"
                value="<?= e(csrf_token()) ?>"
            >

            <input type="hidden" name="action" value="profile">

            <label for="full_name">Full name</label>
            <input
                type="text"
                id="full_name"
                name="full_name"
                value="<?= e($u['full_name'] ?? '') ?>"
                maxlength="150"
                required
            >

            <label for="email">Email address</label>
            <input
                type="email"
                id="email"
                value="<?= e($u['email'] ?? '') ?>"
                disabled
            >

            <label for="student_no">Student number</label>
            <input
                type="text"
                id="student_no"
                value="<?= e($u['student_no'] ?? 'Not available') ?>"
                disabled
            >

            <button type="submit" class="btn primary">
                Save changes
            </button>
        </form>
    </section>

    <!-- Change Password -->
    <section class="panel form-panel">
        <h2>Change Password</h2>

        <form method="post">
            <input
                type="hidden"
                name="csrf"
                value="<?= e(csrf_token()) ?>"
            >

            <input type="hidden" name="action" value="password">

            <label for="current_password">Current password</label>
            <input
                type="password"
                id="current_password"
                name="current_password"
                autocomplete="current-password"
                required
            >

            <label for="new_password">New password</label>
            <input
                type="password"
                id="new_password"
                name="new_password"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <label for="confirm_password">Confirm new password</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <button type="submit" class="btn primary">
                Change password
            </button>
        </form>
    </section>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
