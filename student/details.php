
<?php
require_once __DIR__ . '/../includes/functions.php';

require_role('student');

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(404);
    exit('Request not found.');
}

// Retrieve the request belonging to the logged-in student.
$q = db()->prepare(
    'SELECT r.*, d.name AS document_name
     FROM document_requests r
     LEFT JOIN document_types d ON d.id = r.document_id
     WHERE r.id = ? AND r.student_id = ?
     LIMIT 1'
);

$q->execute([$id, current_user()['id']]);
$r = $q->fetch(PDO::FETCH_ASSOC);

if (!$r) {
    http_response_code(404);
    exit('Request not found.');
}

// Retrieve the request status history.
$q = db()->prepare(
    'SELECT h.*, u.full_name AS staff_name
     FROM request_status_history h
     LEFT JOIN users u ON u.id = h.changed_by
     WHERE h.request_id = ?
     ORDER BY h.created_at DESC'
);

$q->execute([$id]);
$history = $q->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Request Details';

require __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <a href="/registrarconnect/student/requests.php">
        ← Back to My Requests
    </a>

    <h1>Request Details</h1>

    <p>
        <?= e(create_request_id($r['id'])) ?>
        · Submitted
        <?= e(date('M d, Y h:i A', strtotime($r['created_at']))) ?>
    </p>
</div>

<div class="detail-grid">

    <!-- Request Information -->
    <section class="panel form-panel">
        <h2><?= e($r['document_name'] ?? 'Document not available') ?></h2>

        <p>
            <span class="status <?= e(status_class($r['status'])) ?>">
                <?= e($r['status']) ?>
            </span>
        </p>

        <dl class="details">
            <dt>Purpose</dt>
            <dd><?= e($r['purpose'] ?? 'Not specified') ?></dd>

            <dt>Academic year / term</dt>
            <dd>
                <?= e($r['academic_period'] ?: 'Not specified') ?>
            </dd>

            <dt>Additional details</dt>
            <dd>
                <?= nl2br(e($r['student_remarks'] ?: 'None')) ?>
            </dd>

            <dt>Registrar remarks</dt>
            <dd>
                <?= nl2br(e($r['registrar_remarks'] ?: 'No remarks provided')) ?>
            </dd>
        </dl>
    </section>

    <!-- Status History -->
    <section class="panel form-panel">
        <h2>Status History</h2>

        <?php if ($history): ?>
            <ol class="history-list">
                <?php foreach ($history as $h): ?>
                    <li>
                        <b><?= e($h['new_status']) ?></b>

                        <small>
                            <?= e(date(
                                'M d, Y h:i A',
                                strtotime($h['created_at'])
                            )) ?>
                            ·
                            <?= e($h['staff_name'] ?? 'System') ?>
                        </small>

                        <?php if (!empty($h['remarks'])): ?>
                            <p><?= e($h['remarks']) ?></p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php else: ?>
            <p>No status history is available yet.</p>
        <?php endif; ?>
    </section>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
