
<?php

require_once __DIR__ . '/functions.php';

$user = current_user();
$flash = get_flash();

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        <?= e($page_title ?? APP_NAME) ?> | <?= APP_NAME ?>
    </title>

    <link
        rel="stylesheet"
        href="/registrarconnect/assets/style.css"
    >
</head>

<body>

<?php if ($user): ?>

    <!-- Sidebar -->
    <aside class="sidebar">

        <a
            class="brand"
            href="<?= $user['role'] === 'registrar'
                ? '/registrarconnect/registrar/dashboard.php'
                : '/registrarconnect/student/dashboard.php' ?>"
        >
            <span class="brand-icon">▤</span>

            <span>
                <b>RegistrarConnect</b>
                <small>CELTECH COLLEGE</small>
            </span>
        </a>

        <p class="menu-label">MAIN MENU</p>

        <nav>
            <?php
            $nav = $user['role'] === 'registrar'
                ? [
                    [
                        '▦',
                        'Dashboard',
                        '/registrarconnect/registrar/dashboard.php',
                    ],
                    [
                        '▤',
                        'Manage Requests',
                        '/registrarconnect/registrar/requests.php',
                    ],
                    [
                        '♙',
                        'Profile',
                        '/registrarconnect/student/profile.php',
                    ],
                ]
                : [
                    [
                        '▦',
                        'Dashboard',
                        '/registrarconnect/student/dashboard.php',
                    ],
                    [
                        '▤',
                        'Request Documents',
                        '/registrarconnect/student/request.php',
                    ],
                    [
                        '▣',
                        'My Requests',
                        '/registrarconnect/student/requests.php',
                    ],
                    [
                        '♧',
                        'Profile',
                        '/registrarconnect/student/profile.php',
                    ],
                ];

            foreach ($nav as $item):
                [$icon, $label, $url] = $item;

                $active = (
                    ($_SERVER['REQUEST_URI'] ?? '') === $url
                );
            ?>

                <a
                    class="nav-link <?= $active ? 'active' : '' ?>"
                    href="<?= e($url) ?>"
                >
                    <span><?= e($icon) ?></span>
                    <?= e($label) ?>
                </a>

            <?php endforeach; ?>
        </nav>

        <!-- Help Card -->
        <div class="help-card">
            <span class="help-icon">?</span>

            <b>Need assistance?</b>

            <p>
                Your registrar support team is ready to help.
            </p>

            <a href="mailto:registrar@celtech.edu.ph">
                Get help
            </a>
        </div>

        <!-- Logout -->
        <a
            class="nav-link logout"
            href="/registrarconnect/auth/logout.php"
        >
            ⇥　Logout
        </a>

    </aside>

    <!-- Top Navigation Bar -->
    <header class="topbar">

        <div class="search-placeholder">
            ⌕　 Search requests or documents...
        </div>

        <div class="user-menu">

            <span class="bell">♧</span>

            <span class="avatar">
                <?= e(strtoupper(substr($user['full_name'], 0, 1))) ?>
            </span>

            <span>
                <b><?= e($user['full_name']) ?></b>
                <small><?= e(ucfirst($user['role'])) ?></small>
            </span>

        </div>

    </header>

    <!-- Main Content -->
    <main class="main-content">

        <?php if ($flash): ?>
            <div class="alert <?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

<?php else: ?>

    <!-- Guest / Authentication Layout -->
    <main class="auth-shell">

        <?php if ($flash): ?>
            <div class="alert <?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

<?php endif; ?>
