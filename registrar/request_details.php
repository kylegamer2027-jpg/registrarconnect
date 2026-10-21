
<?php
require_once __DIR__ . '/../includes/functions.php';

require_role('registrar');

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(404);
    exit('Request not found.');
}

// Retrieve request and student information.
$q = db()->prepare(
    'SELECT
        r.*,
        d.name AS document_name,
        u.full_name AS student_name,
        u.email AS student_email,
        u.student_no
     FROM document_requests r
     LEFT JOIN document_types d ON d.id = r.document_id
     JOIN users u ON u.id = r.student_id
     WHERE r.id = ?
     LIMIT 1'
);

$q->execute([$id]);
$r = $q->fetch(PDO::FETCH_ASSOC);

if (!$r) {
    http_response_code(404);
    exit('Request not found.');
}

$statuses = [
    'Pending',
    'Under Review',
    'Processing',
    'Ready for Release',
    'Completed',
    'Rejected'
];

// Update request status and registrar remarks.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $new = $_POST['status'] ?? '';
    $remarks = trim($_POST['registrar_remarks'] ?? '');

    if (!in_array($new, $statuses, true)) {
        set_flash('Invalid status selected.', 'error');
        redirect('/registrarconnect/registrar/request_details.php?id=' . $id);
    }

    try {
        db()->beginTransaction();

        // Lock and retrieve the latest request status.
        $q = db()->prepare(
            'SELECT status
             FROM document_requests
             WHERE id = ?
             FOR UPDATE'
        );
        $q->execute([$id]);
        $current = $q->fetch(PDO::FETCH_ASSOC);

        if (!$current) {
            throw new RuntimeException('Request no longer exists.');
        }

        $oldStatus = $current['status'];

        $q = db()->prepare(
            'UPDATE document_requests
             SET status = ?, registrar_remarks = ?, updated_at = NOW()
             WHERE id = ?'
        );

        $q->execute([
            $new,
            $remarks !== '' ? $remarks : null,
            $id
        ]);

        // Record status history only when the status changes.
        if ($new !== $oldStatus) {
            $q = db()->prepare(
                'INSERT INTO request_status_history
                    (request_id, old_status, new_status, changed_by, remarks)
                 VALUES (?, ?, ?, ?, ?)'
            );

            $q->execute([
                $id,
                $oldStatus,
                $new,
                current_user()['id'],
                $remarks !== '' ? $remarks : 'Status updated by registrar'
            ]);
        }

        db()->commit();

        set_flash('Request updated successfully.');

    } catch (Throwable $e) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }

        error_log('Registrar request update failed: ' . $e->getMessage());

        set_flash(
            'Unable to update request. Please try again.',
            'error'
        );
    }

    redirect('/registrarconnect/registrar/request_details.php?id=' . $id);
}

// Retrieve request status history.
$q = db()->prepare(
    'SELECT
        h.*,
        u.full_name AS staff_name
     FROM request_status_history h
     LEFT JOIN users u ON u.id = h.changed_by
     WHERE h.request_id = ?
     ORDER BY h.created_at DESC'
);

$q->execute([$id]);
$history = $q->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Review Request';

require __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <a href="/registrarconnect/registrar/requests.php">
        ← Back to Requests
    </a>

    <h1>Review <?= e(create_request_id($r['id'])) ?></h1>

    <p>Review student information and record processing updates.</p>
</div>

<div class="detail-grid">

    <!-- Request Information -->
    <section class="panel form-panel">
        <h2>Request Information</h2>

        <dl class="details">
            <dt>Student</dt>
            <dd><?= e($r['student_name']) ?></dd>

            <dt>Student number</dt>
            <dd><?= e($r['student_no'] ?? 'Not provided') ?></dd>

            <dt>Email</dt>
            <dd><?= e($r['student_email']) ?></dd>

            <dt>Document</dt>
            <dd><?= e($r['document_name'] ?? 'Unknown document') ?></dd>

            <dt>Purpose</dt>
            <dd><?= e($r['purpose']) ?></dd>

            <dt>Academic period</dt>
            <dd><?= e($r['academic_period'] ?: 'Not specified') ?></dd>

            <dt>Student remarks</dt>
            <dd><?= nl2br(e($r['student_remarks'] ?: 'None')) ?></dd>

            <dt>Submitted</dt>
            <dd>
                <?= e(date('M d, Y h:i A', strtotime($r['created_at']))) ?>
            </dd>
        </dl>
    </section>

    <!-- Status Update -->
    <section class="panel form-panel">
        <h2>Update Processing Status</h2>

        <form method="post">
            <input
                type="hidden"
                name="csrf"
                value="<?= e(csrf_token()) ?>"
            >

            <label for="status">Status</label>

            <select name="status" id="status" required>
                <?php foreach ($statuses as $s): ?>
                    <option
                        value="<?= e($s) ?>"
                        <?= $r['status'] === $s ? 'selected' : '' ?>
                    >
                        <?= e($s) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="registrar_remarks">Registrar remarks</label>

            <textarea
                name="registrar_remarks"
                id="registrar_remarks"
                rows="5"
                maxlength="2000"
                placeholder="Reason for rejection, missing requirements, or release instructions."
            ><?= e($r['registrar_remarks'] ?? '') ?></textarea>

            <button type="submit" class="btn primary">
                Save Status Update
            </button>
        </form>

        <hr>

        <h3>Status History</h3>

        <?php if ($history): ?>
            <ol class="history-list">
                <?php foreach ($history as $h): ?>
                    <li>
                        <b>
                            <?= e($h['old_status'] ?: 'New request') ?>
                            → <?= e($h['new_status']) ?>
                        </b>

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
