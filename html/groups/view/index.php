<?php

session_start();

require_once dirname(__DIR__, 2) . '/includes/functions.php';

$isLoggedIn = isset($_SESSION['user_id']);

if (!$isLoggedIn) {
    header('Location: /login/');
    exit;
}

$approvalError = $_SESSION['approval_error'] ?? '';
$approvalSuccess = $_SESSION['approval_success'] ?? '';

unset(
    $_SESSION['approval_error'],
    $_SESSION['approval_success']
);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$userId = (int) $_SESSION['user_id'];

$groupId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$groupId) {
    http_response_code(400);
    exit('Invalid group ID.');
}

$pdo = connectDatabase();

$statement = $pdo->prepare(
    'SELECT
        groups.group_id,
        groups.group_name,
        groups.created_at,
        group_members.group_role AS current_user_role
    FROM groups
    INNER JOIN group_members
        ON group_members.group_id = groups.group_id
        AND group_members.user_id = :user_id
    WHERE groups.group_id = :group_id
    LIMIT 1'
);

$statement->execute([
    'user_id' => $userId,
    'group_id' => $groupId
]);

$group = $statement->fetch();

if (!$group) {
    http_response_code(404);

    exit(
        'The group does not exist or you are not a member.'
    );
}

$statement = $pdo->prepare(
    'SELECT
        discussions.discussion_id,
        discussions.title,
        discussions.created_at,
        users.user_id,
        users.user_name,
        posts.content AS first_post
    FROM discussions
    INNER JOIN users
        ON users.user_id = discussions.created_by
    INNER JOIN posts
        ON posts.post_id = (
            SELECT MIN(first_post.post_id)
            FROM posts AS first_post
            WHERE first_post.discussion_id =
                discussions.discussion_id
        )
    WHERE discussions.group_id = :group_id
    ORDER BY discussions.created_at DESC'
);

$statement->execute([
    'group_id' => $groupId
]);

$discussions = $statement->fetchAll();

$statement = $pdo->prepare(
    'SELECT
        users.user_id,
        users.user_name,
        group_members.group_role
    FROM group_members
    INNER JOIN users
        ON users.user_id = group_members.user_id
    WHERE group_members.group_id = :group_id
    ORDER BY
        CASE
            WHEN group_members.group_role = :admin_role
            THEN 0
            ELSE 1
        END,
        users.user_name'
);

$statement->execute([
    'group_id' => $groupId,
    'admin_role' => 'admin'
]);

$groupUsers = $statement->fetchAll();

$pendingApplications = [];

if ($group['current_user_role'] === 'admin') {
    $statement = $pdo->prepare(
        'SELECT
            group_applications.application_id,
            group_applications.created_at,
            users.user_id AS applicant_id,
            users.user_name,
            users.first_name,
            users.last_name
        FROM group_applications
        INNER JOIN users
            ON users.user_id = group_applications.user_id
        WHERE group_applications.group_id = :group_id
          AND group_applications.application_status = :status
        ORDER BY group_applications.created_at ASC'
    );

    $statement->execute([
        'group_id' => $groupId,
        'status' => 'pending'
    ]);

    $pendingApplications = $statement->fetchAll();
}

$administrators = [];
$members = [];

foreach ($groupUsers as $groupUser) {
    if ($groupUser['group_role'] === 'admin') {
        $administrators[] = $groupUser;
    } else {
        $members[] = $groupUser;
    }
}

$page_name = $group['group_name'] . ' | Face IT';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Knewave&family=PT+Sans+Caption:wght@400;700&family=Poetsen+One&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="/style.css?v=4">

    <title><?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8') ?></title>
</head>

<body>
    <?php require dirname(__DIR__, 2) . '/includes/navigation.php'; ?>

    <main class="group-page">
        <a class="backLink" href="/groups/">Back</a>

        <header class="group-header">
            <h1><?= htmlspecialchars($group['group_name'], ENT_QUOTES, 'UTF-8') ?></h1>

           <div class="group-administrators opacity-line">
                <span class="administrators-label">Administrators:</span>

                <div class="administrators-list">
                    <?php foreach ($administrators as $administrator): ?>
                        <span class="administrator">
                            <img class="admin-icon" src="/assets/icons/admin-icon.svg" alt="">
                            <span class="user-name" data-user-id="<?= (int) $administrator['user_id'] ?>" tabindex="0"><?= htmlspecialchars($administrator['user_name'], ENT_QUOTES, 'UTF-8') ?></span>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </header>

        <div class="group-page-columns">
            <section class="group-discussions">
                <h2>Discussions</h2>

                <?php if (empty($discussions)): ?>
                    <p>No discussions have been started in this group yet.</p>
                <?php else: ?>
                    <div class="discussion-list">
                        <?php foreach ($discussions as $discussion): ?>
                            <article class="group-discussion-card message">
                                <a class="group-discussion-link" href="/discussions/view/?id=<?= (int) $discussion['discussion_id'] ?>">
                                    <h3><?= htmlspecialchars($discussion['title'], ENT_QUOTES, 'UTF-8') ?></h3>

                                    <p><?= nl2br(htmlspecialchars($discussion['first_post'], ENT_QUOTES, 'UTF-8')) ?></p>

                                    <span class="discussion-author opacity-line">
                                        Started by
                                        <span class="user-name" data-user-id="<?= (int) $discussion['user_id'] ?>" tabindex="0"><?= htmlspecialchars($discussion['user_name'], ENT_QUOTES, 'UTF-8') ?></span>

                                        <time datetime="<?= htmlspecialchars($discussion['created_at'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($discussion['created_at'], ENT_QUOTES, 'UTF-8') ?></time>
                                    </span>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <aside class="group-members">
                <?php if ($group['current_user_role'] === 'admin'): ?>
                <?php if ($approvalError !== ''): ?>
        <div class="form-errors" role="alert">
            <p><?= htmlspecialchars($approvalError, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    <?php endif; ?>

    <?php if ($approvalSuccess !== ''): ?>
        <div class="success-message" role="status">
            <p><?= htmlspecialchars($approvalSuccess, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    <?php endif; ?>
        <h2>Applications</h2>
        <?php if (empty($pendingApplications)): ?>
            <p>There are no applications waiting for approval.</p>
                <?php else: ?>
                    <div class="applications-list">
                        <?php foreach ($pendingApplications as $application): ?>
                            <div class="application-item">
                                <div class="applicant">
                                    <span class="user-name" data-user-id="<?= (int) $application['applicant_id'] ?>" tabindex="0"><?= htmlspecialchars($application['user_name']. ',', ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="applicant-name"><?= htmlspecialchars($application['first_name'] . ' ' . $application['last_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>

                                <form action="/groups/applications/" method="post">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="group_id" value="<?= (int) $group['group_id'] ?>">
                                    <input type="hidden" name="application_id" value="<?= (int) $application['application_id'] ?>">
                                    <button class="approve-button" type="submit">Approve</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
</section>
                <?php endif; ?>
                <h2>Members</h2>

                <?php if (empty($members)): ?>
                    <p>This group does not have any members.</p>
                <?php else: ?>
                    <ul class="member-list">
                        <?php foreach ($members as $member): ?>
                            <li class="member-item<?= $member['group_role'] === 'admin' ? ' admin-member' : '' ?>">
                                <span class="user-name" data-user-id="<?= (int) $member['user_id'] ?>" tabindex="0"><?= htmlspecialchars($member['user_name'], ENT_QUOTES, 'UTF-8') ?></span>

                                <?php if ($member['group_role'] === 'admin'): ?>
                                    <img class="admin-icon" src="/assets/icons/admin-icon.svg" alt="Administrator" title="Group administrator">
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </aside>
        </div>
    </main>

    <footer>
        <p>Contact</p>
    </footer>
</body>
</html>