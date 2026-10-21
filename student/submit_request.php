
<?php
require_once __DIR__ . '/../includes/functions.php';

require_role('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/registrarconnect/student/request.php');
}

verify_csrf();

$type = (int) ($_POST['document_type_id'] ?? 0);
$purpose = trim($_POST['purpose'] ?? '');
$period = trim($_POST['academic_period'] ?? '');
$remarks = trim($_POST['student_remarks'] ?? '');

if ($type <= 0 || $purpose === '') {
    set_flash('Please select a valid document type and purpose.', 'error');
    redirect('/registrarconnect/student/request.php');
}

// Verify that the selected document type exists.
$check = db()->prepare(
    'SELECT id FROM document_types WHERE id = ?'
);
$check->execute([$type]);

if (!$check->fetch(PDO::FETCH_ASSOC)) {
    set_flash('Please select a valid document type and purpose.', 'error');
    redirect('/registrarconnect/student/request.php');
}

try {
    db()->beginTransaction();

    // Create the document request.
    $q = db()->prepare(
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

    $id = (int) db()->lastInsertId();

    // Record the initial request status.
    $q = db()->prepare(
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

    db()->commit();

    set_flash('Your document request has been submitted successfully.');
    redirect('/registrarconnect/student/details.php?id=' . $id);

} catch (Throwable $e) {
    if (db()->inTransaction()) {
        db()->rollBack();
    }

    error_log('Document request submission failed: ' . $e->getMessage());

    set_flash('Unable to submit your request. Please try again or contact the Registrar.', 'error');
    redirect('/registrarconnect/student/request.php');
}
?>
