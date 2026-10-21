
<?php
require_once __DIR__ . '/../includes/functions.php';

require_role('student');

// Load available document types.
try {
    $docs = db()->query(
        'SELECT id, name, description
         FROM document_types
         ORDER BY name ASC'
    )->fetchAll();

} catch (PDOException $e) {
    error_log('Load document types failed: ' . $e->getMessage());

    http_response_code(500);
    exit('Unable to load document types. Please try again later.');
}

// Validate the optional preselected document ID.
$type = filter_input(INPUT_GET, 'type', FILTER_VALIDATE_INT);
$type = ($type !== false && $type !== null && $type > 0)
    ? $type
    : 0;

// Only preselect a document that actually exists.
if ($type > 0) {
    $validTypeIds = array_map(
        'intval',
        array_column($docs, 'id')
    );

    if (!in_array($type, $validTypeIds, true)) {
        $type = 0;
    }
}

$page_title = 'Request Documents';

require __DIR__ . '/../includes/header.php';
?>

<div class="page-heading">
    <span class="eyebrow">DOCUMENT SERVICES</span>
    <h1>Request an Academic Document</h1>
    <p>
        Complete the form below. The Registrar's Office will review
        your submission.
    </p>
</div>

<section class="panel form-panel">
    <?php if (empty($docs)): ?>
        <p>No document types are currently available. Please contact
        the Registrar's Office for assistance.</p>
    <?php else: ?>
        <form
            method="post"
            action="/registrarconnect/student/submit_request.php"
        >
            <input
                type="hidden"
                name="csrf"
                value="<?= e(csrf_token()) ?>"
            >

            <label for="document_type_id">
                Document type <span class="required">*</span>
            </label>

            <select
                name="document_type_id"
                id="document_type_id"
                required
            >
                <option value="">Select a document</option>

                <?php foreach ($docs as $d): ?>
                    <option
                        value="<?= (int) $d['id'] ?>"
                        <?= $type === (int) $d['id'] ? 'selected' : '' ?>
                    >
                        <?= e($d['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="purpose">
                Purpose of request <span class="required">*</span>
            </label>

            <select name="purpose" id="purpose" required>
                <option value="">Select purpose</option>

                <?php foreach ([
                    'Employment',
                    'Scholarship application',
                    'School transfer',
                    'Personal copy',
                    'Further studies',
                    'Other'
                ] as $p): ?>
                    <option value="<?= e($p) ?>">
                        <?= e($p) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="academic_period">
                Academic year / term (if applicable)
            </label>

            <input
                type="text"
                name="academic_period"
                id="academic_period"
                maxlength="100"
                placeholder="e.g. AY 2025-2026, 1st Semester"
            >

            <label for="student_remarks">Additional details</label>

            <textarea
                name="student_remarks"
                id="student_remarks"
                rows="4"
                maxlength="1000"
                placeholder="Provide any information the registrar should know."
            ></textarea>

            <p class="form-note">
                Submitting a request does not mean the document has
                been approved or released. Verification, fees, and
                processing remain subject to Registrar's Office procedures.
            </p>

            <button type="submit" class="btn primary">
                Submit Request
            </button>

            <a
                class="btn outline"
                href="/registrarconnect/student/dashboard.php"
            >
                Cancel
            </a>
        </form>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
