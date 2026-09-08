<?php

session_start();

require_once dirname(__DIR__, 2) . '/includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /groups/');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$groupName = trim($_POST['group_name'] ?? '');
$submittedCsrfToken = $_POST['csrf_token'] ?? '';

if (
    !is_string($submittedCsrfToken)
    || empty($_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $submittedCsrfToken)
) {
    $_SESSION['group_error'] =
        'The form could not be verified. Please try again.';

    header('Location: /groups/');
    exit;
}

if ($groupName === '') {
    $_SESSION['group_error'] =
        'Enter a name for the group.';

    header('Location: /groups/');
    exit;
}

if (strlen($groupName) < 2) {
    $_SESSION['group_error'] =
        'The group name must contain at least 2 characters.';

    header('Location: /groups/');
    exit;
}

if (strlen($groupName) > 150) {
    $_SESSION['group_error'] =
        'The group name may contain at most 150 characters.';

    header('Location: /groups/');
    exit;
}

$pdo = connectDatabase();

$statement = $pdo->prepare(
    'SELECT group_id
     FROM groups
     WHERE group_name = :group_name
     LIMIT 1'
);

$statement->execute([
    'group_name' => $groupName
]);

$existingGroup = $statement->fetchColumn();

if ($existingGroup) {
    $_SESSION['group_error'] =
        'A group with that name already exists.';

    header('Location: /groups/');
    exit;
}

try {

    $pdo->beginTransaction();

    $statement = $pdo->prepare(
        'INSERT INTO groups (
            group_name,
            created_by
        ) VALUES (
            :group_name,
            :created_by
        )'
    );

    $statement->execute([
        'group_name' => $groupName,
        'created_by' => $userId
    ]);

    $groupId = (int) $pdo->lastInsertId();

    $statement = $pdo->prepare(
        'INSERT INTO group_members (
            group_id,
            user_id,
            group_role
        ) VALUES (
            :group_id,
            :user_id,
            :group_role
        )'
    );

    $statement->execute([
        'group_id' => $groupId,
        'user_id' => $userId,
        'group_role' => 'admin'
    ]);

    $pdo->commit();

    unset($_SESSION['csrf_token']);

    $_SESSION['group_success'] =
        'Your new group is live!';

    header('Location: /groups/');
    exit;
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($exception->getMessage());

    $_SESSION['group_error'] =
        'The group could not be created. Please try again.';

    header('Location: /groups/');
    exit;
}