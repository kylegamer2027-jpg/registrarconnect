
<div class="table-wrap">

    <table>
        <thead>
            <tr>
                <th>REQUEST ID</th>
                <th>STUDENT</th>
                <th>DOCUMENT TYPE</th>
                <th>DATE REQUESTED</th>
                <th>STATUS</th>
                <th>ACTION</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($requests as $r): ?>
                <tr>
                    <!-- Request ID -->
                    <td>
                        <?= e(create_request_id($r['id'])) ?>
                    </td>

                    <!-- Student Information -->
                    <td>
                        <?= e($r['student_name']) ?>

                        <small class="table-sub">
                            <?= e($r['student_no'] ?? '') ?>
                        </small>
                    </td>

                    <!-- Document Type -->
                    <td>
                        <?= e($r['document_name']) ?>
                    </td>

                    <!-- Date Requested -->
                    <td>
                        <?= e(
                            date(
                                'M d, Y',
                                strtotime($r['created_at'])
                            )
                        ) ?>
                    </td>

                    <!-- Request Status -->
                    <td>
                        <span class="status <?= e(status_class($r['status'])) ?>">
                            <?= e($r['status']) ?>
                        </span>
                    </td>

                    <!-- Action -->
                    <td>
                        <a
                            href="/registrarconnect/registrar/request_details.php?id=<?= (int) $r['id'] ?>"
                        >
                            Review
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Empty State -->
    <?php if (!$requests): ?>
        <div class="empty-state">
            No requests have been submitted.
        </div>
    <?php endif; ?>

</div>
