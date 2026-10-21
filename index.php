
<?php

require_once __DIR__ . '/includes/functions.php';

// Redirect users who are already logged in.
$user = current_user();

if ($user) {
    if ($user['role'] === 'registrar') {
        redirect('/registrarconnect/registrar/dashboard.php');
    }

    redirect('/registrarconnect/student/dashboard.php');
}

// Redirect guests to the login page.
redirect('/registrarconnect/auth/login.php');

?>
