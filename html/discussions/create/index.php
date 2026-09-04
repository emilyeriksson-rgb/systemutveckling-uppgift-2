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

$groupId = filter_var(
    $_POST['group_id'] ?? null,
    FILTER_VALIDATE_INT
);

$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$submittedCsrfToken = $_POST['csrf_token'] ?? '';

if (
    !is_string($submittedCsrfToken)
    || empty($_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $submittedCsrfToken)
) {
    $_SESSION['discussion_error'] =
        'The form could not be verified. Please try again.';

    header('Location: /');
    exit;
}

if (!$groupId || $title === '' || $content === '') {
    $_SESSION['discussion_error'] =
        'Choose a group and fill in all fields.';

    header('Location: /');
    exit;
}

if (strlen($title) > 200) {
    $_SESSION['discussion_error'] =
        'The subject may contain at most 200 characters.';

    header('Location: /');
    exit;
}

$pdo = connectDatabase();

$statement = $pdo->prepare(
    'SELECT 1
     FROM group_members
     WHERE group_id = :group_id
       AND user_id = :user_id
     LIMIT 1'
);

$statement->execute([
    'group_id' => $groupId,
    'user_id' => $userId
]);

$isMember = $statement->fetchColumn();

if (!$isMember) {
    $_SESSION['discussion_error'] =
        'You are not a member of the selected group.';

    header('Location: /');
    exit;
}

try {
    $pdo->beginTransaction();

    $statement = $pdo->prepare(
        'INSERT INTO discussions (
            group_id,
            created_by,
            title
        ) VALUES (
            :group_id,
            :created_by,
            :title
        )'
    );

    $statement->execute([
        'group_id' => $groupId,
        'created_by' => $userId,
        'title' => $title
    ]);

    $discussionId = (int) $pdo->lastInsertId();

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

    $pdo->commit();

    unset($_SESSION['csrf_token']);

    $_SESSION['discussion_success'] =
        'Your discussion was created.';

    header('Location: /');
    exit;
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($exception->getMessage());

    $_SESSION['discussion_error'] =
        'The discussion could not be created. Please try again.';

    header('Location: /');
    exit;
}