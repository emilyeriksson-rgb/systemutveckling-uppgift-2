<?php

session_start();

require_once dirname(__DIR__, 2) . '/includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

$userId = (int) $_SESSION['user_id'];

$discussionId = filter_var(
    $_POST['discussion_id'] ?? null,
    FILTER_VALIDATE_INT
);

$content = trim($_POST['content'] ?? '');
$submittedCsrfToken = $_POST['csrf_token'] ?? '';

if (!$discussionId) {
    header('Location: /');
    exit;
}

$discussionUrl = '/discussions/view/?id=' . $discussionId;


if (
    !is_string($submittedCsrfToken)
    || empty($_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $submittedCsrfToken)
) {
    $_SESSION['reply_error'] =
        'The form could not be verified. Please try again.';

    header('Location: ' . $discussionUrl);
    exit;
}

if ($content === '') {
    $_SESSION['reply_error'] =
        'Write something before posting your reply.';

    header('Location: ' . $discussionUrl);
    exit;
}

if (strlen($content) > 5000) {
    $_SESSION['reply_error'] =
        'The reply may contain at most 5000 characters.';

    header('Location: ' . $discussionUrl);
    exit;
}

$pdo = connectDatabase();

$statement = $pdo->prepare(
    'SELECT 1
     FROM discussions
     INNER JOIN group_members
        ON group_members.group_id = discussions.group_id
     WHERE discussions.discussion_id = :discussion_id
       AND group_members.user_id = :user_id
     LIMIT 1'
);

$statement->execute([
    'discussion_id' => $discussionId,
    'user_id' => $userId
]);

$isMember = $statement->fetchColumn();

if (!$isMember) {
    $_SESSION['reply_error'] =
        'You do not have permission to reply to this discussion.';

    header('Location: ' . $discussionUrl);
    exit;
}

try {
    $statement = $pdo->prepare(
        'INSERT INTO posts (
            discussion_id,
            created_by,
            content
        ) VALUES (
            :discussion_id,
            :created_by,
            :content
        )'
    );

    $statement->execute([
        'discussion_id' => $discussionId,
        'created_by' => $userId,
        'content' => $content
    ]);

    unset($_SESSION['csrf_token']);

    $_SESSION['reply_success'] =
        'Your reply was posted.';

    header('Location: ' . $discussionUrl);
    exit;
} catch (Throwable $exception) {
    error_log($exception->getMessage());

    $_SESSION['reply_error'] =
        'The reply could not be posted. Please try again.';

    header('Location: ' . $discussionUrl);
    exit;
}