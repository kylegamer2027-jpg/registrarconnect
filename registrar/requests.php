
<?php
require_once __DIR__ . '/../includes/functions.php';

require_role('registrar');

$status = trim($_GET['status'] ?? '');

$allowed = [
    'Pending',
    'Under Review',
    'Processing',
    'Ready for Release',
    'Completed',
    'Rejected'
];

// Build the request query.
$sql = '
    SELECT
        r.*,
        d.name AS document_name,
        u.full_name AS student_name,
        u.student_no
    FROM document_requests r
    LEFT JOIN document_types d
        ON d.id = r.document_id
    JOIN users u
        ON u.id = r.student_id
';

$params = [];

if (in_array($status, $allowed, true)) {
    $sql .= ' WHERE r.status = ?';
    $params[] = $status;
}

$sql .= ' ORDER BY r.created_at DESC';

$q = db()->prepare($sql);
$q->execute($params);

$requests = $q->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Manage Requests';

require __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <h1>Manage Requests</h1>
    <p>Review submissions and update their processing status.</p>
</div>

<div class="filter-row">
    <a
        class="filter-chip"
        href="/registrarconnect/registrar/requests.php"
    >
        All
    </a>

    <?php foreach ($allowed as $s): ?>
        <a
            class="filter-chip"
            href="?status=<?= urlencode($s) ?>"
        >
            <?= e($s) ?>
        </a>
    <?php endforeach; ?>
</div>

<section class="panel">
    <?php require __DIR__ . '/../includes/request_table.php'; ?>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
