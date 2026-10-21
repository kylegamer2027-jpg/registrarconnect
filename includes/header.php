
<?php

require_once __DIR__ . '/functions.php';

$user = current_user();
$flash = get_flash();

$isLoggedIn = $user !== null;

$page_title = $page_title ?? APP_NAME;

$currentPath = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
) ?: '/';

$basePath = '/registrarconnect';

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= e($page_title) ?> | <?= e(APP_NAME) ?></title>

    <link
        rel="stylesheet"
        href="<?= $basePath ?>/assets/style.css"
    >
</head>

<body>

<?php if ($isLoggedIn): ?>

    <?php
    $isRegistrar = ($user['role'] ?? '') === 'registrar';

    $dashboardUrl = $isRegistrar
        ? $basePath . '/registrar/dashboard.php'
        : $basePath . '/student/dashboard.php';

    $nav = $isRegistrar
        ? [
            [
                '▦',
                'Dashboard',
                $basePath . '/registrar/dashboard.php',
            ],
            [
                '▤',
                'Manage Requests',
                $basePath . '/registrar/requests.php',
            ],
            [
                '♙',
                'Profile',
                $basePath . '/registrar/profile.php',
            ],
        ]
        : [
            [
                '▦',
                'Dashboard',
                $basePath . '/student/dashboard.php',
            ],
            [
                '▤',
                'Request Documents',
                $basePath . '/student/request.php',
            ],
            [
                '▣',
                'My Requests',
                $basePath . '/student/requests.php',
            ],
            [
                '♙',
                'Profile',
                $basePath . '/student/profile.php',
            ],
        ];
    ?>

    <aside class="sidebar">

        <a class="brand" href="<?= e($dashboardUrl) ?>">
            <span class="brand-icon">▤</span>

            <span>
                <b>RegistrarConnect</b>
                <small>CELTECH COLLEGE</small>
            </span>
        </a>

        <p class="menu-label">MAIN MENU</p>

        <nav>
            <?php foreach ($nav as $item): ?>
                <?php
                [$icon, $label, $url] = $item;

                $active = ($currentPath === $url);
                ?>

                <a
                    class="nav-link <?= $active ? 'active' : '' ?>"
                    href="<?= e($url) ?>"
                    <?= $active ? 'aria-current="page"' : '' ?>
                >
                    <span><?= e($icon) ?></span>
                    <?= e($label) ?>
                </a>

            <?php endforeach; ?>
        </nav>

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

        <a
            class="nav-link logout"
            href="<?= $basePath ?>/auth/logout.php"
        >
            <span>⇥</span>
            Logout
        </a>

    </aside>

    <header class="topbar">

        <div class="search-placeholder">
            ⌕　Search requests or documents...
        </div>

        <div class="user-menu">

            <span class="bell" aria-label="Notifications">♧</span>

            <span class="avatar">
                <?= e(strtoupper(
                    substr((string) ($user['full_name'] ?? 'U'), 0, 1)
                )) ?>
            </span>

            <span>
                <b><?= e($user['full_name'] ?? 'User') ?></b>
                <small>
                    <?= e(ucfirst($user['role'] ?? 'student')) ?>
                </small>
            </span>

        </div>

    </header>

    <main class="main-content">

<?php else: ?>

    <main class="auth-shell">

<?php endif; ?>

    <?php if ($flash): ?>
        <div class="alert <?= e($flash['type'] ?? 'success') ?>">
            <?= e($flash['message'] ?? '') ?>
        </div>
    <?php endif; ?>
