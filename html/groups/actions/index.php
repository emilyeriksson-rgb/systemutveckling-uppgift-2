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

$groupId = filter_input(
    INPUT_POST,
    'group_id',
    FILTER_VALIDATE_INT
);

$action = $_POST['action'] ?? '';
$submittedCsrfToken = $_POST['csrf_token'] ?? '';

if (
    !$groupId
    || !is_string($submittedCsrfToken)
    || empty($_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $submittedCsrfToken)
) {
    http_response_code(400);
    exit('The request could not be verified.');
}

$pdo = connectDatabase();

if ($action === 'cancel_application') {
    $statement = $pdo->prepare(
        'DELETE FROM group_applications
         WHERE group_id = :group_id
           AND user_id = :user_id
           AND application_status = :status'
    );

    $statement->execute([
        'group_id' => $groupId,
        'user_id' => $userId,
        'status' => 'pending'
    ]);

    header('Location: /groups/');
    exit;
}

if ($action === 'leave_group') {
    $statement = $pdo->prepare(
        'DELETE FROM group_members
         WHERE group_id = :group_id
           AND user_id = :user_id
           AND group_role = :group_role'
    );

    $statement->execute([
        'group_id' => $groupId,
        'user_id' => $userId,
        'group_role' => 'member'
    ]);

    if ($statement->rowCount() === 1) {
        $statement = $pdo->prepare(
            'DELETE FROM group_applications
             WHERE group_id = :group_id
               AND user_id = :user_id'
        );

        $statement->execute([
            'group_id' => $groupId,
            'user_id' => $userId
        ]);
    }

    header('Location: /groups/');
    exit;
}

http_response_code(400);
exit('Unknown group action.');