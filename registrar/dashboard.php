
<?php

/*
|--------------------------------------------------------------------------
| RegistrarConnect — Registrar Dashboard
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/functions.php';

/*
|--------------------------------------------------------------------------
| Access Control
|--------------------------------------------------------------------------
*/

require_role('registrar');

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

$counts = [
    'total' => 0,
    'pending' => 0,
    'processing' => 0,
    'completed' => 0,
];

$requests = [];

try {
    $countsQuery = db()->query("
        SELECT
            COUNT(*) AS total,
            COALESCE(
                SUM(status IN ('Pending', 'Under Review')),
                0
            ) AS pending,
            COALESCE(
                SUM(status = 'Processing'),
                0
            ) AS processing,
            COALESCE(
                SUM(status IN ('Ready for Release', 'Completed')),
                0
            ) AS completed
        FROM document_requests
    ");

    $counts = $countsQuery->fetch(PDO::FETCH_ASSOC) ?: $counts;

    /*
    |--------------------------------------------------------------------------
    | Latest Document Requests
    |--------------------------------------------------------------------------
    */

    $requestsQuery = db()->query("
        SELECT
            r.id,
            r.document_type AS document_name,
            r.created_at,
            r.status,
            u.full_name AS student_name,
            u.student_no
        FROM document_requests AS r
        INNER JOIN users AS u
            ON u.id = r.student_id
        ORDER BY r.created_at DESC
        LIMIT 10
    ");

    $requests = $requestsQuery->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $ex) {
    error_log(
        'Registrar dashboard database error: ' . $ex->getMessage()
    );

    http_response_code(500);

    $dashboardError =
        'Unable to load dashboard information. Please try again later.';
}

/*
|--------------------------------------------------------------------------
| Page Header
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
        Manage Requests <span aria-hidden="true">›</span>
    </a>

</div>

<?php if (!empty($dashboardError)): ?>

    <div class="alert error">
        <?= e($dashboardError) ?>
    </div>

<?php else: ?>

    <!-- Dashboard Statistics -->
    <div class="stats-grid">

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
                View all <span aria-hidden="true">›</span>
            </a>
        </div>

        <?php require __DIR__ . '/../includes/request_table.php'; ?>

    </section>

<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
