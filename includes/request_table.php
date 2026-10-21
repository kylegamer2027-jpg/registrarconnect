
<div class="table-wrap">

    <?php if (!empty($requests)): ?>

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

                    <?php
                    $requestId = (int) ($r['id'] ?? 0);
                    $studentName = $r['student_name'] ?? 'Unknown Student';
                    $studentNo = $r['student_no'] ?? '';
                    $documentName = $r['document_name'] ?? 'Unknown Document';
                    $status = $r['status'] ?? 'Pending';

                    $createdAt = $r['created_at'] ?? '';
                    $timestamp = $createdAt !== ''
                        ? strtotime($createdAt)
                        : false;

                    $formattedDate = $timestamp !== false
                        ? date('M d, Y', $timestamp)
                        : 'Date unavailable';
                    ?>

                    <tr>
                        <td>
                            <?= e(create_request_id($requestId)) ?>
                        </td>

                        <td>
                            <?= e($studentName) ?>

                            <?php if ($studentNo !== ''): ?>
                                <small class="table-sub">
                                    <?= e($studentNo) ?>
                                </small>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= e($documentName) ?>
                        </td>

                        <td>
                            <?= e($formattedDate) ?>
                        </td>

                        <td>
                            <span class="status <?= e(status_class($status)) ?>">
                                <?= e($status) ?>
                            </span>
                        </td>

                        <td>
                            <a
                                href="/registrarconnect/registrar/request_details.php?id=<?= $requestId ?>"
                            >
                                Review
                            </a>
                        </td>
                    </tr>

                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>

        <div class="empty-state">
            <p>No requests have been submitted yet.</p>
            <small>Student document requests will appear here.</small>
        </div>

    <?php endif; ?>

</div>
