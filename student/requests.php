
<?php
require_once __DIR__ . '/../includes/functions.php';

require_role('student');

try {
    // Load only the logged-in student's requests.
    $sql = '
        SELECT
            r.id,
            r.document_type AS document_name,
            r.created_at,
            r.status
        FROM document_requests r
        WHERE r.student_id = ?
        ORDER BY r.created_at DESC
    ';

    $q = db()->prepare($sql);
    $q->execute([current_user()['id']]);

    $requests = $q->fetchAll();

} catch (PDOException $e) {
    error_log('Load student requests failed: ' . $e->getMessage());

    http_response_code(500);
    exit('Unable to load your requests. Please try again later.');
}

$page_title = 'My Requests';

require __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <h1>My Requests</h1>
    <p>Review the status and history of your academic document requests.</p>

    <a class="btn primary" href="/registrarconnect/student/request.php">
        + Request a Document
    </a>
</div>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>REQUEST ID</th>
                    <th>DOCUMENT TYPE</th>
                    <th>DATE REQUESTED</th>
                    <th>STATUS</th>
                    <th>ACTION</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($requests)): ?>
                    <?php foreach ($requests as $r): ?>
                        <tr>
                            <td>
                                <?= e(create_request_id((int) $r['id'])) ?>
                            </td>

                            <td>
                                <?= e($r['document_name'] ?? 'Unknown document') ?>
                            </td>

                            <td>
                                <?= e(
                                    date('M d, Y', strtotime($r['created_at']))
                                ) ?>
                            </td>

                            <td>
                                <span class="status <?= e(status_class($r['status'])) ?>">
                                    <?= e($r['status']) ?>
                                </span>
                            </td>

                            <td>
                                <a href="/registrarconnect/student/details.php?id=<?= (int) $r['id'] ?>">
                                    View details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                No requests found. Click "Request a Document"
                                to submit your first request.
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
