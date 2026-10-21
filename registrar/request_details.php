
<?php

require_once __DIR__ . '/../includes/functions.php';

require_role('registrar');

/*
|--------------------------------------------------------------------------
| Request ID Validation
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(404);
    exit('Request not found.');
}

/*
|--------------------------------------------------------------------------
| Allowed Request Statuses
|--------------------------------------------------------------------------
*/

$statuses = [
    'Pending',
    'Under Review',
    'Processing',
    'Ready for Release',
    'Completed',
    'Rejected',
];

/*
|--------------------------------------------------------------------------
| Handle Status Update
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $newStatus = trim($_POST['status'] ?? '');
    $remarks = trim($_POST['registrar_remarks'] ?? '');

    if (!in_array($newStatus, $statuses, true)) {
        set_flash('Invalid status selected.', 'error');

        redirect(
            '/registrarconnect/registrar/request_details.php?id=' . $id
        );
    }

    if (strlen($remarks) > 2000) {
        set_flash('Remarks must not exceed 2000 characters.', 'error');

        redirect(
            '/registrarconnect/registrar/request_details.php?id=' . $id
        );
    }

    try {
        $pdo = db();
        $pdo->beginTransaction();

        // Lock the request while its status is being updated.
        $stmt = $pdo->prepare(
            'SELECT status
             FROM document_requests
             WHERE id = ?
             FOR UPDATE'
        );

        $stmt->execute([$id]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current) {
            $pdo->rollBack();

            http_response_code(404);
            exit('Request not found.');
        }

        $oldStatus = $current['status'];

        // Update status and registrar remarks.
        $stmt = $pdo->prepare(
            'UPDATE document_requests
             SET status = ?,
                 registrar_remarks = ?,
                 updated_at = NOW()
             WHERE id = ?'
        );

        $stmt->execute([
            $newStatus,
            $remarks !== '' ? $remarks : null,
            $id,
        ]);

        // Save history only if the status has changed.
        if ($newStatus !== $oldStatus) {
            $stmt = $pdo->prepare(
                'INSERT INTO request_status_history
                    (request_id, old_status, new_status, changed_by, remarks)
                 VALUES (?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $id,
                $oldStatus,
                $newStatus,
                current_user()['id'],
                $remarks !== ''
                    ? $remarks
                    : 'Status updated by registrar',
            ]);
        }

        $pdo->commit();

        set_flash('Request updated successfully.', 'success');

    } catch (Throwable $ex) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log(
            'Request details update error: ' . $ex->getMessage()
        );

        set_flash(
            'Unable to update this request. Please check the database configuration and try again.',
            'error'
        );
    }

    redirect(
        '/registrarconnect/registrar/request_details.php?id=' . $id
    );
}

/*
|--------------------------------------------------------------------------
| Retrieve Request Details
|--------------------------------------------------------------------------
|
| This query uses document_type directly, matching the dashboard query.
| If your database stores document names in document_types instead,
| replace r.document_type with the appropriate JOIN.
|
*/

try {
    $stmt = db()->prepare(
        'SELECT
            r.*,
            r.document_type AS document_name,
            u.full_name AS student_name,
            u.email AS student_email,
            u.student_no
         FROM document_requests AS r
         INNER JOIN users AS u
             ON u.id = r.student_id
         WHERE r.id = ?
         LIMIT 1'
    );

    $stmt->execute([$id]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$r) {
        http_response_code(404);
        exit('Request not found.');
    }

    /*
    |--------------------------------------------------------------------------
    | Retrieve Status History
    |--------------------------------------------------------------------------
    */

    $stmt = db()->prepare(
        'SELECT
            h.*,
            u.full_name AS staff_name
         FROM request_status_history AS h
         LEFT JOIN users AS u
             ON u.id = h.changed_by
         WHERE h.request_id = ?
         ORDER BY h.created_at DESC'
    );

    $stmt->execute([$id]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $ex) {
    error_log(
        'Request details retrieval error: ' . $ex->getMessage()
    );

    http_response_code(500);
    exit('Unable to load request details. Please try again later.');
}

/*
|--------------------------------------------------------------------------
| Page Header
|--------------------------------------------------------------------------
*/

$page_title = 'Review Request';

require __DIR__ . '/../includes/header.php';

?>

<div class="page-heading">

    <a href="/registrarconnect/registrar/requests.php">
        ← Back to Requests
    </a>

    <h1>Review <?= e(create_request_id((int) $r['id'])) ?></h1>

    <p>
        Review student information and record processing updates.
    </p>

</div>

<div class="detail-grid">

    <!-- Request Information -->
    <section class="panel form-panel">

        <h2>Request Information</h2>

        <dl class="details">

            <dt>Student</dt>
            <dd><?= e($r['student_name'] ?? 'Unknown Student') ?></dd>

            <dt>Student number</dt>
            <dd><?= e($r['student_no'] ?? 'Not provided') ?></dd>

            <dt>Email</dt>
            <dd><?= e($r['student_email'] ?? 'Not provided') ?></dd>

            <dt>Document</dt>
            <dd><?= e($r['document_name'] ?? 'Unknown document') ?></dd>

            <dt>Purpose</dt>
            <dd><?= e($r['purpose'] ?? 'Not specified') ?></dd>

            <dt>Academic period</dt>
            <dd><?= e($r['academic_period'] ?? 'Not specified') ?></dd>

            <dt>Student remarks</dt>
            <dd><?= nl2br(e($r['student_remarks'] ?? 'None')) ?></dd>

            <dt>Submitted</dt>
            <dd>
                <?php
                $createdTimestamp = !empty($r['created_at'])
                    ? strtotime($r['created_at'])
                    : false;
                ?>

                <?= $createdTimestamp !== false
                    ? e(date('M d, Y h:i A', $createdTimestamp))
                    : 'Date unavailable' ?>
            </dd>

            <dt>Current status</dt>
            <dd>
                <span class="status <?= e(status_class($r['status'])) ?>">
                    <?= e($r['status']) ?>
                </span>
            </dd>

        </dl>

    </section>

    <!-- Status Update -->
    <section class="panel form-panel">

        <h2>Update Processing Status</h2>

        <form
            method="post"
            action="/registrarconnect/registrar/request_details.php?id=<?= (int) $id ?>"
        >

            <input
                type="hidden"
                name="csrf"
                value="<?= e(csrf_token()) ?>"
            >

            <label for="status">Status</label>

            <select name="status" id="status" required>
                <?php foreach ($statuses as $status): ?>
                    <option
                        value="<?= e($status) ?>"
                        <?= ($r['status'] ?? '') === $status ? 'selected' : '' ?>
                    >
                        <?= e($status) ?>
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

        <?php if (!empty($history)): ?>

            <ol class="history-list">

                <?php foreach ($history as $entry): ?>

                    <li>

                        <b>
                            <?= e($entry['old_status'] ?: 'New request') ?>
                            → <?= e($entry['new_status']) ?>
                        </b>

                        <small>
                            <?php
                            $historyTimestamp = !empty($entry['created_at'])
                                ? strtotime($entry['created_at'])
                                : false;
                            ?>

                            <?= $historyTimestamp !== false
                                ? e(date('M d, Y h:i A', $historyTimestamp))
                                : 'Date unavailable' ?>

                            · <?= e($entry['staff_name'] ?? 'System') ?>
                        </small>

                        <?php if (!empty($entry['remarks'])): ?>
                            <p><?= e($entry['remarks']) ?></p>
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
