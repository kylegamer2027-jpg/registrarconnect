
<?php

require_once __DIR__ . '/includes/functions.php';

// Check whether a user is logged in.
$user = current_user();

// Redirect logged-in users to their dashboard.
if ($user) {
    if (($user['role'] ?? '') === 'registrar') {
        redirect('/registrarconnect/registrar/dashboard.php');
    }

    if (($user['role'] ?? '') === 'student') {
        redirect('/registrarconnect/student/dashboard.php');
    }

    // Handle an unexpected or unsupported role.
    redirect('/registrarconnect/auth/login.php');
}

// Guests must log in first.
redirect('/registrarconnect/auth/login.php');
