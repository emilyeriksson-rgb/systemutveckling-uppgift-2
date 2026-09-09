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

$groupId = filter_var(
    $_POST['group_id'] ?? null,
    FILTER_VALIDATE_INT
);

$submittedCsrfToken = $_POST['csrf_token'] ?? '';

if (
    !is_string($submittedCsrfToken)
    || empty($_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $submittedCsrfToken)
) {
    $_SESSION['application_error'] =
        'The form could not be verified. Please try again.';

    header('Location: /groups/');
    exit;
}

if (!$groupId) {
    $_SESSION['application_error'] =
        'The selected group is not valid.';

    header('Location: /groups/');
    exit;
}

$pdo = connectDatabase();

$statement = $pdo->prepare(
    'SELECT group_id
     FROM groups
     WHERE group_id = :group_id
     LIMIT 1'
);

$statement->execute([
    'group_id' => $groupId
]);

$groupExists = $statement->fetchColumn();

if (!$groupExists) {
    $_SESSION['application_error'] =
        'The selected group does not exist.';

    header('Location: /groups/');
    exit;
}
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

$isAlreadyMember = $statement->fetchColumn();

if ($isAlreadyMember) {
    $_SESSION['application_error'] =
        'You are already a member of this group.';

    header('Location: /groups/');
    exit;
}
$statement = $pdo->prepare(
    'SELECT
        application_id,
        application_status
     FROM group_applications
     WHERE group_id = :group_id
       AND user_id = :user_id
     LIMIT 1'
);

$statement->execute([
    'group_id' => $groupId,
    'user_id' => $userId
]);

$existingApplication = $statement->fetch();

if (
    $existingApplication
    && $existingApplication['application_status'] === 'pending'
) {
    $_SESSION['application_error'] =
        'Your application is already waiting for approval.';

    header('Location: /groups/');
    exit;
}

try {
    if (
        $existingApplication
        && $existingApplication['application_status'] === 'rejected'
    ) {
        $statement = $pdo->prepare(
            'UPDATE group_applications
             SET application_status = :status,
                 created_at = CURRENT_TIMESTAMP,
                 handled_at = NULL,
                 handled_by = NULL
             WHERE application_id = :application_id'
        );

        $statement->execute([
            'status' => 'pending',
            'application_id' =>
                $existingApplication['application_id']
        ]);
    } else {
        $statement = $pdo->prepare(
            'INSERT INTO group_applications (
                group_id,
                user_id,
                application_status
            ) VALUES (
                :group_id,
                :user_id,
                :status
            )'
        );

        $statement->execute([
            'group_id' => $groupId,
            'user_id' => $userId,
            'status' => 'pending'
        ]);
    }

    unset($_SESSION['csrf_token']);

    $_SESSION['application_success'] =
        'Application sent! The group is now waiting for approval.';

    header('Location: /groups/');
    exit;
} catch (Throwable $exception) {
    error_log($exception->getMessage());

    $_SESSION['application_error'] =
        'The application could not be sent. Please try again.';

    header('Location: /groups/');
    exit;
}