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

$adminId = (int) $_SESSION['user_id'];

$groupId = filter_var(
    $_POST['group_id'] ?? null,
    FILTER_VALIDATE_INT
);

$applicationId = filter_var(
    $_POST['application_id'] ?? null,
    FILTER_VALIDATE_INT
);

$submittedCsrfToken = $_POST['csrf_token'] ?? '';

if (!$groupId || !$applicationId) {
    header('Location: /groups/');
    exit;
}

$groupUrl = '/groups/view/?id=' . $groupId;

if (
    !is_string($submittedCsrfToken)
    || empty($_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $submittedCsrfToken)
) {
    $_SESSION['approval_error'] =
        'The request could not be verified. Please try again.';

    header('Location: ' . $groupUrl);
    exit;
}

$pdo = connectDatabase();

try {
    $pdo->beginTransaction();

    /*
     * Hämta ansökan och kontrollera samtidigt att den
     * inloggade användaren är admin i rätt grupp.
     */
    $statement = $pdo->prepare(
        'SELECT group_applications.user_id
         FROM group_applications
         INNER JOIN group_members
            ON group_members.group_id =
                group_applications.group_id
         WHERE group_applications.application_id =
                :application_id
           AND group_applications.group_id = :group_id
           AND group_applications.application_status =
                :application_status
           AND group_members.user_id = :admin_id
           AND group_members.group_role = :admin_role
         LIMIT 1
         FOR UPDATE'
    );

    $statement->execute([
        'application_id' => $applicationId,
        'group_id' => $groupId,
        'application_status' => 'pending',
        'admin_id' => $adminId,
        'admin_role' => 'admin'
    ]);

    $applicantId = $statement->fetchColumn();

    if (!$applicantId) {
        $pdo->rollBack();

        $_SESSION['approval_error'] =
            'The application was not found or you do not have permission to approve it.';

        header('Location: ' . $groupUrl);
        exit;
    }

    /*
     * Lägg den sökande användaren i gruppen som medlem.
     */
    $statement = $pdo->prepare(
        'INSERT IGNORE INTO group_members (
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
        'user_id' => $applicantId,
        'group_role' => 'member'
    ]);

    /*
     * Markera ansökan som godkänd.
     */
    $statement = $pdo->prepare(
        'UPDATE group_applications
         SET application_status = :approved_status,
             handled_at = CURRENT_TIMESTAMP,
             handled_by = :handled_by
         WHERE application_id = :application_id
           AND application_status = :pending_status'
    );

    $statement->execute([
        'approved_status' => 'approved',
        'handled_by' => $adminId,
        'application_id' => $applicationId,
        'pending_status' => 'pending'
    ]);

    $pdo->commit();

    unset($_SESSION['csrf_token']);

    $_SESSION['approval_success'] =
        'The application was approved.';

    header('Location: ' . $groupUrl);
    exit;
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($exception->getMessage());

    $_SESSION['approval_error'] =
        'The application could not be approved. Please try again.';

    header('Location: ' . $groupUrl);
    exit;
}