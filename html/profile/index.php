<?php

session_start();

require_once dirname(__DIR__) . '/includes/functions.php';

$page_name = 'Profile | Face IT';
$isLoggedIn = isset($_SESSION['user_id']);

if (!$isLoggedIn) {
    header('Location: /login/');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$pdo = connectDatabase();

$myGroups = [];
$myPosts = [];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$statement = $pdo->prepare(
    'SELECT
        user_id,
        first_name,
        last_name,
        user_name,
        email
    FROM users
    WHERE user_id = :user_id
    LIMIT 1'
);

$statement->execute([
    'user_id' => $userId
]);

$user = $statement->fetch();

if (!$user) {
    http_response_code(404);
    exit('The user could not be found.');
}

$statement = $pdo->prepare(
    'SELECT
        posts.post_id,
        posts.content,
        posts.created_at,
        discussions.discussion_id,
        discussions.title,
        groups.group_id,
        groups.group_name
    FROM posts
    INNER JOIN discussions
        ON discussions.discussion_id = posts.discussion_id
    INNER JOIN groups
        ON groups.group_id = discussions.group_id
    WHERE posts.created_by = :user_id
    ORDER BY posts.created_at DESC'
);

$statement->execute([
    'user_id' => $userId
]);

$myPosts = $statement->fetchAll();

$statement = $pdo->prepare(
    'SELECT
        groups.group_id,
        groups.group_name,
        group_members.group_role
    FROM groups
    INNER JOIN group_members
        ON group_members.group_id = groups.group_id
    WHERE group_members.user_id = :user_id
    ORDER BY groups.group_name'
);

$statement->execute([
    'user_id' => $userId
]);

$myGroups = $statement->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Knewave&family=PT+Sans+Caption:wght@400;700&family=Poetsen+One&display=swap" rel="stylesheet">

<link rel="stylesheet" href="/style.css?v=3">
    <title>
        <?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8') ?>
    </title>

</head>
<body>
    <?php require dirname(__DIR__) . '/includes/navigation.php'; ?>

    <main class="profile-page">
        <a class="backLink" href="/" onclick="history.back(); return false;">Back</a>
        <h1>My profile</h1>

        <section class="profile-information">
            <h2>This is your profile, <?= htmlspecialchars($user['user_name'], ENT_QUOTES, 'UTF-8') ?>!</h2>
            <p><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
        </section>

        <section>
            <h2>My groups</h2>
             <?php if (empty($myGroups)): ?>
                <p>You are not a member of any groups yet.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($myGroups as $group): ?>
                        <li class="group-list-item">
                            <a group-link href="/groups/view/?id=<?= (int) $group['group_id'] ?>">
                                <?= htmlspecialchars($group['group_name'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

<section class="latest-discussions">
    <h2>My posts</h2>

    <?php if (empty($myPosts)): ?>
        <p>You have not written any posts yet.</p>
    <?php else: ?>
        <div class="discussion-list">
            <?php foreach ($myPosts as $post): ?>
                <article class="discussion-card">
                    <a class="group" href="/groups/view/?id=<?= (int) $post['group_id'] ?>"><?= htmlspecialchars($post['group_name'], ENT_QUOTES, 'UTF-8') ?></a>

                    <a class="message" href="/discussions/view/?id=<?= (int) $post['discussion_id'] ?>">
                        <h3 class="subject"><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></h3>

                        <p class="content"><?= nl2br(htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8')) ?></p>
                    </a>

                    <p class="author">
                        Written by
                        <span class="user-name" data-user-id="<?= $userId ?>" tabindex="0"><?= htmlspecialchars($user['user_name'], ENT_QUOTES, 'UTF-8') ?></span>
                    </p>

                    <time class="discussion-time" datetime="<?= htmlspecialchars($post['created_at'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($post['created_at'], ENT_QUOTES, 'UTF-8') ?></time>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

        <section>
            <h2>Groups I administer</h2>

            <?php $hasAdminGroups = false; ?>
                <ul>
                    <?php foreach ($myGroups as $group): ?>
                        <?php if ($group['group_role'] === 'admin'): ?>
                            <?php $hasAdminGroups = true; ?>

                            <li class="group-list-item">
                                <a href="/groups/view/?id=<?= (int) $group['group_id'] ?>">
                                    <?= htmlspecialchars($group['group_name'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>

                <?php if (!$hasAdminGroups): ?>
                    <p>You do not administer any groups yet.</p>
                    <a class="primary-btn" href="/groups/#create-group">Create a group</a>
                <?php endif; ?>

        </section>

        <section>
            <h2>Settings</h2>
                    <form action="/logout/" method="post">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="secondary-btn">Log out</button>
                    </form>
        </section>
    </main>

    <footer>
        <p>Contact</p>
    </footer>
</body>
</html>