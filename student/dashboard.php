
<?php

require_once __DIR__ . '/../includes/functions.php';

require_role('student');

$uid = (int) current_user()['id'];

/*
|--------------------------------------------------------------------------
| Student Request Statistics
|--------------------------------------------------------------------------
*/

$query = db()->prepare("
    SELECT
        COUNT(*) AS total,
        COALESCE(SUM(status IN ('Pending', 'Under Review')), 0) AS pending,
        COALESCE(SUM(status = 'Processing'), 0) AS processing,
        COALESCE(SUM(status IN ('Ready for Release', 'Completed')), 0) AS completed
    FROM document_requests
    WHERE student_id = ?
");

$query->execute([$uid]);
$counts = $query->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Available Academic Documents
|--------------------------------------------------------------------------
*/

$query = db()->query("
    SELECT
        id,
        name,
        description,
        icon_color,
        icon_symbol
    FROM document_types
    WHERE is_active = 1
    ORDER BY sort_order, name
");

$docs = $query->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Recent Student Requests
|--------------------------------------------------------------------------
*/

$query = db()->prepare("
    SELECT
        r.*,
        COALESCE(d.name, 'Unknown document') AS document_name
    FROM document_requests AS r
    LEFT JOIN document_types AS d
        ON d.id = r.document_id
    WHERE r.student_id = ?
    ORDER BY r.created_at DESC
    LIMIT 5
");

$query->execute([$uid]);
$requests = $query->fetchAll(PDO::FETCH_ASSOC);

$selected = $requests[0] ?? null;

/*
|--------------------------------------------------------------------------
| Page Setup
|--------------------------------------------------------------------------
*/

$page_title = 'Student Dashboard';

require __DIR__ . '/../includes/header.php';

?>

<!-- Welcome Section -->
<div class="welcome-row">
    <div>
        <span class="eyebrow">STUDENT DASHBOARD</span>

        <h1>
            Welcome back,
            <?= e(explode(' ', trim(current_user()['full_name']))[0]) ?>!
        </h1>

        <p>
            Request your academic documents and track their progress online.
        </p>
    </div>

    <a
        class="btn primary"
        href="/registrarconnect/student/request.php"
    >
        ＋ Request a Document
    </a>
</div>

<!-- Request Statistics -->
<div class="stats-grid">

    <div class="stat-card">
        <span class="stat-icon blue">▤</span>
        <div>
            <small>Total Requests</small>
            <p>All time</p>
        </div>
        <strong><?= (int) ($counts['total'] ?? 0) ?></strong>
    </div>

    <div class="stat-card">
        <span class="stat-icon amber">◷</span>
        <div>
            <small>Pending Requests</small>
            <p>Awaiting review</p>
        </div>
        <strong><?= (int) ($counts['pending'] ?? 0) ?></strong>
    </div>

    <div class="stat-card">
        <span class="stat-icon red">✣</span>
        <div>
            <small>Processing Requests</small>
            <p>Currently in progress</p>
        </div>
        <strong><?= (int) ($counts['processing'] ?? 0) ?></strong>
    </div>

    <div class="stat-card">
        <span class="stat-icon green">✓</span>
        <div>
            <small>Completed Requests</small>
            <p>Ready or released</p>
        </div>
        <strong><?= (int) ($counts['completed'] ?? 0) ?></strong>
    </div>

</div>

<!-- Available Academic Documents -->
<div class="section-heading">
    <div>
        <h2>Available Academic Documents</h2>
        <p>Select a document to begin your online request.</p>
    </div>

    <a href="/registrarconnect/student/request.php">
        View all ›
    </a>
</div>

<div class="document-grid">

    <?php foreach ($docs as $d): ?>
        <article class="document-card">

            <span class="doc-icon <?= e($d['icon_color'] ?? 'blue') ?>">
                <?= e($d['icon_symbol'] ?? '▤') ?>
            </span>

            <h3><?= e($d['name']) ?></h3>

            <p><?= e($d['description'] ?? '') ?></p>

            <a href="/registrarconnect/student/request.php?type=<?= (int) $d['id'] ?>">
                Request now ›
            </a>

        </article>
    <?php endforeach; ?>

    <?php if (!$docs): ?>
        <div class="empty-state">
            No academic documents are currently available.
        </div>
    <?php endif; ?>

</div>

<!-- Recent Requests and Tracking -->
<div class="lower-grid">

    <section class="panel">

        <div class="panel-heading">
            <div>
                <h2>Recent Requests</h2>
                <p>Your latest document transactions.</p>
            </div>

            <a href="/registrarconnect/student/requests.php">
                View all ›
            </a>
        </div>

        <?php if (!$requests): ?>

            <div class="empty-state">
                You haven't submitted any requests yet.

                <p>
                    <a href="/registrarconnect/student/request.php">
                        Submit your first request
                    </a>
                </p>
            </div>

        <?php else: ?>

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
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td>
                                    <?= e(create_request_id((int) $r['id'])) ?>
                                </td>

                                <td><?= e($r['document_name']) ?></td>

                                <td>
                                    <?= e(date('M d, Y', strtotime($r['created_at']))) ?>
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
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

    </section>

    <!-- Request Tracking -->
    <aside class="panel tracking-panel">

        <div class="tracking-title">
            <small>REQUEST TRACKING</small>

            <?php if ($selected): ?>
                <span class="status <?= e(status_class($selected['status'])) ?>">
                    <?= e($selected['status']) ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if ($selected): ?>

            <h3><?= e(create_request_id((int) $selected['id'])) ?></h3>

            <div class="tracking-doc">
                <span class="doc-icon red">▤</span>

                <div>
                    <b><?= e($selected['document_name']) ?></b>

                    <small>
                        Requested on
                        <?= e(date('M d, Y', strtotime($selected['created_at']))) ?>
                    </small>
                </div>
            </div>

            <?php
            $steps = [
                'Pending',
                'Under Review',
                'Processing',
                'Ready for Release',
                'Completed',
            ];

            $current = array_search($selected['status'], $steps, true);
            ?>

            <?php if ($selected['status'] === 'Rejected'): ?>

                <p class="alert error">
                    This request was rejected. Review the registrar's remarks
                    in the request details.
                </p>

            <?php else: ?>

                <ol class="timeline">

                    <?php foreach ($steps as $i => $step): ?>
                        <?php
                        $isCompleted = (
                            $current !== false && $i < $current
                        );

                        $isCurrent = (
                            $current !== false && $i === $current
                        );

                        $stepClass = $isCompleted
                            ? 'done'
                            : ($isCurrent ? 'current' : '');
                        ?>

                        <li class="<?= e($stepClass) ?>">
                            <span>
                                <?= $isCompleted ? '✓' : $i + 1 ?>
                            </span>

                            <div>
                                <b><?= e($step) ?></b>

                                <small>
                                    <?php if ($isCompleted): ?>
                                        Completed
                                    <?php elseif ($isCurrent): ?>
                                        Current stage
                                    <?php else: ?>
                                        Upcoming
                                    <?php endif; ?>
                                </small>
                            </div>
                        </li>
                    <?php endforeach; ?>

                </ol>

            <?php endif; ?>

            <a
                class="btn outline full"
                href="/registrarconnect/student/details.php?id=<?= (int) $selected['id'] ?>"
            >
                View tracking details
            </a>

        <?php else: ?>

            <div class="empty-state">
                Your request timeline will appear here after you submit a request.
            </div>

        <?php endif; ?>

    </aside>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
