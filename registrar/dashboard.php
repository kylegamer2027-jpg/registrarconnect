
<?php

require_once __DIR__ . '/../includes/functions.php';

require_role('registrar');

// 1. Get dashboard statistics
$counts = db()->query("
    SELECT
        COUNT(*) AS total,
        SUM(status IN ('Pending', 'Under Review')) AS pending,
        SUM(status = 'Processing') AS processing,
        SUM(status IN ('Ready for Release', 'Completed')) AS completed
    FROM document_requests
")->fetch();

// 2. Get the latest document requests
$query = db()->query("
    SELECT
        r.*,
        r.document_type AS document_name,
        u.full_name AS student_name,
        u.student_no
    FROM document_requests AS r
    JOIN users AS u
        ON u.id = r.student_id
    ORDER BY r.created_at DESC
    LIMIT 10
");

$requests = $query->fetchAll();

// 3. Load the page header
$page_title = 'Registrar Dashboard';

require __DIR__ . '/../includes/header.php';

?>


/*
|--------------------------------------------------------------------------
| Latest Document Requests
|--------------------------------------------------------------------------
*/



$query = db()->query("
    SELECT
        r.*,
        r.document_type AS document_name,
        u.full_name AS student_name,
        u.student_no

    FROM document_requests AS r

    JOIN users AS u
        ON u.id = r.student_id

    ORDER BY r.created_at DESC

    LIMIT 10
");

$requests = $query->fetchAll();


/*
|--------------------------------------------------------------------------
| Page Layout
|--------------------------------------------------------------------------
*/

$page_title = 'Registrar Dashboard';

require __DIR__ . '/../includes/header.php';

?>

<!-- Welcome Section -->
<div class="welcome-row">

    <div>
        <span class="eyebrow">REGISTRAR WORKSPACE</span>

        <h1>Request Management</h1>

        <p>
            Review student submissions and keep document requests moving.
        </p>
    </div>

    <a
        class="btn primary"
        href="/registrarconnect/registrar/requests.php"
    >
        Manage Requests　›
    </a>

</div>

<!-- Dashboard Statistics -->
<div class="stats-grid">

    <!-- Total Requests -->
    <div class="stat-card">
        <span class="stat-icon blue">▤</span>

        <div>
            <small>Total Requests</small>
            <p>All records</p>
        </div>

        <strong>
            <?= (int) ($counts['total'] ?? 0) ?>
        </strong>
    </div>

    <!-- Pending / Under Review -->
    <div class="stat-card">
        <span class="stat-icon amber">◷</span>

        <div>
            <small>Pending / Review</small>
            <p>Needs attention</p>
        </div>

        <strong>
            <?= (int) ($counts['pending'] ?? 0) ?>
        </strong>
    </div>

    <!-- Processing -->
    <div class="stat-card">
        <span class="stat-icon red">✣</span>

        <div>
            <small>Processing</small>
            <p>In progress</p>
        </div>

        <strong>
            <?= (int) ($counts['processing'] ?? 0) ?>
        </strong>
    </div>

    <!-- Ready / Completed -->
    <div class="stat-card">
        <span class="stat-icon green">✓</span>

        <div>
            <small>Ready / Completed</small>
            <p>Processed</p>
        </div>

        <strong>
            <?= (int) ($counts['completed'] ?? 0) ?>
        </strong>
    </div>

</div>

<!-- Latest Requests Panel -->
<section class="panel">

    <div class="panel-heading">

        <div>
            <h2>Latest Requests</h2>
            <p>Most recent student transactions.</p>
        </div>

        <a href="/registrarconnect/registrar/requests.php">
            View all　›
        </a>

    </div>

    <?php require __DIR__ . '/../includes/request_table.php'; ?>

</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
