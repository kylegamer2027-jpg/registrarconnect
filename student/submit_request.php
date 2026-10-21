
<?php
require_once __DIR__ . '/../includes/functions.php';

require_role('student');

$request_url = '/registrarconnect/student/request.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($request_url);
}

verify_csrf();

$type = filter_input(INPUT_POST, 'document_type_id', FILTER_VALIDATE_INT);
$type = ($type !== false && $type !== null && $type > 0) ? $type : 0;

$purpose = trim($_POST['purpose'] ?? '');
$period = trim($_POST['academic_period'] ?? '');
$remarks = trim($_POST['student_remarks'] ?? '');

$allowed_purposes = [
    'Employment',
    'Scholarship application',
    'School transfer',
    'Personal copy',
    'Further studies',
    'Other'
];

// Validate submitted fields on the server.
if ($type <= 0 || !in_array($purpose, $allowed_purposes, true)) {
    set_flash('Please select a valid document type and purpose.', 'error');
    redirect($request_url);
}

if (
    strlen($period) > 100 ||
    strlen($remarks) > 1000
) {
    set_flash(
        'Academic period must be at most 100 characters and additional details at most 1000 characters.',
        'error'
    );
    redirect($request_url);
}

try {
    // Reuse one database connection throughout the transaction.
    $pdo = db();

    // Confirm the selected document type exists.
    $check = $pdo->prepare(
        'SELECT id FROM document_types WHERE id = ? LIMIT 1'
    );
    $check->execute([$type]);

    if (!$check->fetch()) {
        set_flash('The selected document type is not available.', 'error');
        redirect($request_url);
    }

    $pdo->beginTransaction();

    // Save the request.
    // This version assumes the column is document_type_id.
    $q = $pdo->prepare(
        'INSERT INTO document_requests
            (student_id, document_type_id, purpose, academic_period,
             student_remarks, status)
         VALUES (?, ?, ?, ?, ?, ?)'
    );

    $q->execute([
        current_user()['id'],
        $type,
        $purpose,
        $period !== '' ? $period : null,
        $remarks !== '' ? $remarks : null,
        'Pending'
    ]);

    $id = (int) $pdo->lastInsertId();

    // Record the initial status in the history table.
    $q = $pdo->prepare(
        'INSERT INTO request_status_history
            (request_id, old_status, new_status, changed_by, remarks)
         VALUES (?, ?, ?, ?, ?)'
    );

    $q->execute([
        $id,
        null,
        'Pending',
        current_user()['id'],
        'Request submitted by student'
    ]);

    $pdo->commit();

    set_flash('Your document request has been submitted successfully.');
    redirect('/registrarconnect/student/details.php?id=' . $id);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Document request submission failed: ' . $e->getMessage());

    set_flash(
        'Unable to submit your request. Please try again or contact the Registrar.',
        'error'
    );
    redirect($request_url);
}
