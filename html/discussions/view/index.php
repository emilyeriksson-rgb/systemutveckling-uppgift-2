<?php

session_start();

require_once dirname(__DIR__, 2) . '/includes/functions.php';

$isLoggedIn = isset($_SESSION['user_id']);

if (!$isLoggedIn) {
    header('Location: /login/');
    exit;
}

$replyError = $_SESSION['reply_error'] ?? '';
$replySuccess = $_SESSION['reply_success'] ?? '';

unset(
    $_SESSION['reply_error'],
    $_SESSION['reply_success']
);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
     <a class="backLink" href="/" onclick="history.back(); return false;">Back</a>

        <header class="discussion-header">
      
<div>
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
            </div>
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

        <section class="reply-section">
    <?php if ($replyError !== ''): ?>
        <div class="form-errors" role="alert">
            <p>
                <?= htmlspecialchars(
                    $replyError,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if ($replySuccess !== ''): ?>
        <div class="success-message" role="status">
            <p>
                <?= htmlspecialchars(
                    $replySuccess,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>
        </div>
    <?php endif; ?>

    <form class="form-field" action="/discussions/reply/" method="post">
        <input type="hidden" name="csrf_token"
            value="<?= htmlspecialchars(
                $_SESSION['csrf_token'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >

        <input type="hidden" name="discussion_id" value="<?= (int) $discussion['discussion_id'] ?>"
        >

        <label for="reply-content">
            Reply
        </label>

        <textarea
            id="reply-content"
            name="content"
            rows="5"
            maxlength="5000"
            placeholder="Write your reply..."
            required
        ></textarea>

        <button class="primary-btn" type="submit">
            Post
        </button>
    </form>
</section>
    </main>

    <footer>
        <p>Contact</p>
    </footer>
</body>
</html>