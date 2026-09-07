<?php

session_start();

require_once dirname(__DIR__, 2) . '/includes/functions.php';

$isLoggedIn = isset($_SESSION['user_id']);

if (!$isLoggedIn) {
    header('Location: /login/');
    exit;
}

$userId = (int) $_SESSION['user_id'];

$discussionId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$discussionId) {
    http_response_code(400);
    exit('Invalid discussion ID.');
}

$pdo = connectDatabase();

/*
 * Hämta diskussionen.
 *
 * INNER JOIN med group_members gör att diskussionen bara hittas
 * om den inloggade användaren är medlem i gruppen.
 */
$statement = $pdo->prepare(
    'SELECT
        discussions.discussion_id,
        discussions.title,
        discussions.created_at,
        groups.group_id,
        groups.group_name,
        users.user_id AS creator_id,
        users.user_name AS creator_name
    FROM discussions
    INNER JOIN groups
        ON groups.group_id = discussions.group_id
    INNER JOIN group_members
        ON group_members.group_id = discussions.group_id
        AND group_members.user_id = :user_id
    INNER JOIN users
        ON users.user_id = discussions.created_by
    WHERE discussions.discussion_id = :discussion_id
    LIMIT 1'
);

$statement->execute([
    'user_id' => $userId,
    'discussion_id' => $discussionId
]);

$discussion = $statement->fetch();

if (!$discussion) {
    http_response_code(404);

    exit(
        'The discussion does not exist or you do not have access to it.'
    );
}

$statement = $pdo->prepare(
    'SELECT
        posts.post_id,
        posts.content,
        posts.created_at,
        users.user_id,
        users.user_name
    FROM posts
    INNER JOIN users
        ON users.user_id = posts.created_by
    WHERE posts.discussion_id = :discussion_id
    ORDER BY posts.created_at ASC, posts.post_id ASC'
);

$statement->execute([
    'discussion_id' => $discussionId
]);

$posts = $statement->fetchAll();

$page_name = $discussion['title'] . ' | Face IT';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >
    <link
        href="https://fonts.googleapis.com/css2?family=Knewave&family=PT+Sans+Caption:wght@400;700&family=Poetsen+One&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="/style.css?v=4">

    <title>
        <?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8') ?>
    </title>
</head>

<body>
    <?php
    require dirname(__DIR__, 2) . '/includes/navigation.php';
    ?>

    <main class="discussion-page">
        <a class="backLink" href="/">
            Back
        </a>

        <header class="discussion-header">
            <a
                class="group"
                href="/groups/view/?id=<?= (int) $discussion['group_id'] ?>"
            >
                <?= htmlspecialchars(
                    $discussion['group_name'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </a>

            <h1>
                <?= htmlspecialchars(
                    $discussion['title'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </h1>

            <p class="opacity-line">
                Started by

                <span
                    class="user-name"
                    data-user-id="<?= (int) $discussion['creator_id'] ?>"
                    tabindex="0"
                >
                    <?= htmlspecialchars(
                        $discussion['creator_name'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </span>
            </p>
        </header>

        <section class="discussion-posts">
            <?php if (empty($posts)): ?>
                <p>This discussion does not have any posts.</p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <article class="post-card">
                        <p class="author">
                            <span
                                class="user-name"
                                data-user-id="<?= (int) $post['user_id'] ?>"
                                tabindex="0"
                            >
                                <?= htmlspecialchars(
                                    $post['user_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </span>
                        </p>

                        <p class="message">
                            <?= nl2br(htmlspecialchars(
                                $post['content'],
                                ENT_QUOTES,
                                'UTF-8'
                            )) ?>
                        </p>

                        <time class="opacity-line" datetime="<?= htmlspecialchars(
                            $post['created_at'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>">
                            <?= htmlspecialchars(
                                $post['created_at'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </time>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>Contact</p>
    </footer>
</body>
</html>