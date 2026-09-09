<?php

session_start();

require_once dirname(__DIR__) . '/includes/functions.php';

$isLoggedIn = isset($_SESSION['user_id']);

if (!$isLoggedIn) {
    header('Location: /login/');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$groupError = $_SESSION['group_error'] ?? '';
$groupSuccess = $_SESSION['group_success'] ?? '';

unset(
    $_SESSION['group_error'],
    $_SESSION['group_success']
);

$myGroups = [];
$pendingGroups = [];
$availableGroups = [];
$pdo = connectDatabase();
$userId = (int) $_SESSION['user_id'];

$statement = $pdo->prepare(
    'SELECT
        groups.group_id,
        groups.group_name,
        group_members.group_role AS member_role,
        group_members.joined_at
    FROM groups
    INNER JOIN group_members
        ON group_members.group_id = groups.group_id
    WHERE group_members.user_id = :user_id
    ORDER BY group_members.joined_at DESC'
);

$statement->execute([
    'user_id' => $userId
]);

$myGroups = $statement->fetchAll();

$statement = $pdo->prepare(
    'SELECT
        groups.group_id,
        groups.group_name,
        group_applications.application_id,
        group_applications.created_at
    FROM groups
    INNER JOIN group_applications
        ON group_applications.group_id = groups.group_id
    WHERE group_applications.user_id = :user_id
      AND group_applications.application_status = :status
    ORDER BY group_applications.created_at DESC'
);

$statement->execute([
    'user_id' => $userId,
    'status' => 'pending'
]);

$pendingGroups = $statement->fetchAll();

$statement = $pdo->prepare(
    'SELECT
        groups.group_id,
        groups.group_name
    FROM groups
    WHERE NOT EXISTS (
        SELECT 1
        FROM group_members
        WHERE group_members.group_id = groups.group_id
          AND group_members.user_id = :member_user_id
    )
    AND NOT EXISTS (
        SELECT 1
        FROM group_applications
        WHERE group_applications.group_id = groups.group_id
          AND group_applications.user_id = :application_user_id
          AND group_applications.application_status = :status
    )
    ORDER BY groups.group_name'
);

$statement->execute([
    'member_user_id' => $userId,
    'application_user_id' => $userId,
    'status' => 'pending'
]);

$availableGroups = $statement->fetchAll();

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


    <title>Groups | Face IT</title>
</head>
<body>
<?php require dirname(__DIR__) . '/includes/navigation.php'; ?>


<main class="groups-page">
    <a class="backLink" href="/" onclick="history.back(); return false;">Back</a>
    <?php if ($groupError !== ''): ?>
    <div class="form-errors" role="alert">
        <p><?= htmlspecialchars($groupError, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
<?php endif; ?>

<?php if ($groupSuccess !== ''): ?>
    <div class="success-message" role="status">
        <p><?= htmlspecialchars($groupSuccess, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
<?php endif; ?>
<div class="groups-columns">
        <div class="left-column">
            <section class="my-groups">
                <h1>My groups</h1>
                <?php if (empty($myGroups)): ?>
                    <p>You are not a member of any groups yet.</p>
                <?php else: ?>
                    <div class="groups-list">
                        <?php foreach ($myGroups as $group): ?>
                            <div class="group-list-item<?= $group['member_role'] === 'admin' ? ' admin-group' : '' ?>">

                                    <a class="group-link" href="/groups/view/?id=<?= (int) $group['group_id'] ?>">
                                        <span><?= htmlspecialchars($group['group_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </a>

                                        <?php if ($group['member_role'] === 'admin'): ?>
                                            <img class="admin-icon" src="/assets/icons/admin-icon.svg" alt="Administrator" title="You are an administrator for this group">
                                        <?php endif; ?>
                                        
                                        <?php if ($group['member_role'] === 'member'): ?>
                                            <form action="/groups/actions/" method="post">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="group_id" value="<?= (int) $group['group_id'] ?>">
                                                <input type="hidden" name="action" value="leave_group">

                                                <button class="group-button" type="submit" aria-label="Leave <?= htmlspecialchars($group['group_name'], ENT_QUOTES, 'UTF-8') ?>" title="Leave group"><img src="/assets/icons/remove.svg" alt="Leave group"></button>
                                            </form>
                                        <?php endif; ?>

                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                    <form id="create-group" class="create-group-form form-field" action="/groups/create/" method="post">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                        <label for="group-name">Create a new group</label>
                        <input id="group-name" name="group_name" type="text" maxlength="150" placeholder="Enter a group name" required>

                        <button class="secondary-btn" type="submit">Create</button>
                    </form>
            </section>

            <section class="pending-groups">
                <h2>Waiting for approval</h2>
                  <?php if (empty($pendingGroups)): ?>
                        <p>See a group you’d like to join? Hit the + and it’ll appear here while a group admin reviews your request.</p>
                    <?php else: ?>
                        <div class="groups-list">
                           <?php foreach ($pendingGroups as $group): ?>
                                <div class="group-list-item">

                                    <span><?= htmlspecialchars($group['group_name'], ENT_QUOTES, 'UTF-8') ?></span>

                                 <form action="/groups/actions/" method="post">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="group_id" value="<?= (int) $group['group_id'] ?>">
                                    <input type="hidden" name="action" value="cancel_application">

                                    <button class="group-button" type="submit" aria-label="Cancel application to <?= htmlspecialchars($group['group_name'], ENT_QUOTES, 'UTF-8') ?>" title="Cancel application"><img src="/assets/icons/undo.svg" alt="Cancel application"></button>
                                </form>

                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

            </section>
        </div>

        <section class="available-groups">
            <h1>Explore groups</h1>
               <?php if (empty($availableGroups)): ?>
                    <p>You’re already a member of every available group!</p>
                <?php else: ?>
                    <div class="groups-list">
                        <?php foreach ($availableGroups as $group): ?>
                            <div class="group-list-item">
                                <span> <?= htmlspecialchars( $group['group_name'], ENT_QUOTES, 'UTF-8' ) ?>
                                </span>
                                <form action="/groups/apply/" method="post">
                                    <input type="hidden" name="csrf_token"value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                                    <input type="hidden" name="group_id" value="<?= (int) $group['group_id'] ?>">

                                    <button class="apply-button" type="submit" aria-label="Apply to join <?= htmlspecialchars($group['group_name'],ENT_QUOTES, 'UTF-8') ?>"><img src="/assets/icons/add.svg" alt="Apply to join group"></button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

        </section>
  </div>
 </main>
</body>
</html>