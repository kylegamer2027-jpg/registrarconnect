
<?php

require_once __DIR__ . '/../includes/functions.php';

require_role('registrar');

/*
|--------------------------------------------------------------------------
| Status Filter
|--------------------------------------------------------------------------
*/

$allowed = [
    'Pending',
    'Under Review',
    'Processing',
    'Ready for Release',
    'Completed',
    'Rejected',
];

$status = trim($_GET['status'] ?? '');

$filterStatus = in_array($status, $allowed, true)
    ? $status
    : '';

/*
|--------------------------------------------------------------------------
| Retrieve Document Requests
|--------------------------------------------------------------------------
*/

$requests = [];
$loadError = false;

try {
    $sql = "
        SELECT
            r.id,
            r.student_id,
            r.document_type AS document_name,
            r.created_at,
            r.status,
            u.full_name AS student_name,
            u.student_no
        FROM document_requests AS r
        INNER JOIN users AS u
            ON u.id = r.student_id
    ";

    $params = [];

    if ($filterStatus !== '') {
        $sql .= ' WHERE r.status = ?';
        $params[] = $filterStatus;
    }

    $sql .= ' ORDER BY r.created_at DESC';

    $query = db()->prepare($sql);
    $query->execute($params);

    $requests = $query->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $ex) {
    error_log(
        'Registrar requests page error: ' . $ex->getMessage()
    );

    $loadError = true;
}

/*
|--------------------------------------------------------------------------
| Page Header
|--------------------------------------------------------------------------
*/

$page_title = 'Manage Requests';

require __DIR__ . '/../includes/header.php';

?>

<!-- Page Heading -->
<div class="page-heading">

    <h1>Manage Requests</h1>

    <p>
        Review student submissions and update their processing status.
    </p>

</div>

<!-- Status Filters -->
<div class="filter-row">

    <a
        class="filter-chip <?= $filterStatus === '' ? 'active' : '' ?>"
        href="/registrarconnect/registrar/requests.php"
        <?= $filterStatus === '' ? 'aria-current="page"' : '' ?>
    >
        All
    </a>

    <?php foreach ($allowed as $s): ?>

        <a
            class="filter-chip <?= $filterStatus === $s ? 'active' : '' ?>"
            href="/registrarconnect/registrar/requests.php?status=<?= urlencode($s) ?>"
            <?= $filterStatus === $s ? 'aria-current="page"' : '' ?>
        >
            <?= e($s) ?>
        </a>

    <?php endforeach; ?>

</div>

<!-- Requests Table -->
<section class="panel">

    <?php if ($loadError): ?>

        <div class="alert error">
            Unable to load document requests. Please try again later.
        </div>

    <?php else: ?>

        <?php require __DIR__ . '/../includes/request_table.php'; ?>

    <?php endif; ?>

</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
