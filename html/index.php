<?php
session_start();

require_once __DIR__ . '/includes/functions.php';


$page_name = 'Face IT';
$isLoggedIn = isset($_SESSION['user_id']);

$loginError = $_SESSION['login_error'] ?? '';
$loginEmail = $_SESSION['login_email'] ?? '';
$discussionError = $_SESSION['discussion_error'] ?? '';
$discussionSuccess = $_SESSION['discussion_success'] ?? '';

unset(
    $_SESSION['login_error'],
    $_SESSION['login_email'],
    $_SESSION['discussion_error'],
    $_SESSION['discussion_success']
);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$myGroups = [];
$latestDiscussions = [];

if ($isLoggedIn) {
    $pdo = connectDatabase();

    $statement = $pdo->prepare(
        'SELECT
            groups.group_id,
            groups.group_name
        FROM groups
        INNER JOIN group_members
            ON group_members.group_id = groups.group_id
        WHERE group_members.user_id = :user_id
        ORDER BY groups.group_name'
    );

    $statement->execute([
        'user_id' => $_SESSION['user_id']
    ]);

    $myGroups = $statement->fetchAll();

    $statement = $pdo->prepare(
        'SELECT
            discussions.discussion_id,
            discussions.title,
            discussions.created_at,
            groups.group_id,
            groups.group_name,
            users.user_id,
            users.user_name,
            posts.content
        FROM discussions
        INNER JOIN groups
            ON groups.group_id = discussions.group_id
        INNER JOIN group_members
            ON group_members.group_id = discussions.group_id
            AND group_members.user_id = :user_id
        INNER JOIN users
            ON users.user_id = discussions.created_by
        INNER JOIN posts
            ON posts.post_id = (
                SELECT MIN(first_post.post_id)
                FROM posts AS first_post
                WHERE first_post.discussion_id =
                    discussions.discussion_id
            )
        ORDER BY discussions.created_at DESC
        LIMIT 10'
    );

    $statement->execute([
        'user_id' => $_SESSION['user_id']
    ]);

    $latestDiscussions = $statement->fetchAll();

}
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
    <title>
        <?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8') ?>
    </title>
    
</head>

<body>
    <?php require __DIR__ . '/includes/navigation.php'; ?>
    <main>
        <?php if (!$isLoggedIn): ?>
            <div class="black-back">

    <img src="/face_it.webp" class="hero-logo" alt="Face IT">
    <p class="tagline"> — Talk tech. Share ideas. Solve together. Whether you’re writing your first line of code or shaping the future of IT, there’s always a place for you in the conversation.</p>
   
    <div class="two-buttons">
        <a href="/login/" class="primary-btn">Log in</a>
        <a href="/register/" class="secondary-btn">Create a new user</a>    
    </div>
</div>
    
    <?php else: ?>
        <section class="logged-in-content"><h1>Talk about it!</h1>

<h2>Create a new discussion</h2>

<?php if (empty($myGroups)): ?>
    <p>
        You need to join a group before you can create a discussion.
    </p>

    <a href="/groups/" class="primary-btn">
        Find groups
    </a>
<?php else: ?>
    <form
        class="create-discussion-form"
        action="/discussions/create/"
        method="post"
    >

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(
                $_SESSION['csrf_token'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >

            <div class="form-field subject-field">
    <label for="discussion-title">Subject</label>

    <input
        id="discussion-title"
        name="title"
        type="text"
        maxlength="200"
        placeholder="What do you want to discuss?"
        required >
</div>

        <div class="form-field group-field">
        <label for="group-id">Choose a group</label>
        <select id="group-id" name="group_id" required>
            <option value="">Select a group</option>
            <?php foreach ($myGroups as $group): ?>
                <option value="<?= (int) $group['group_id'] ?>">
                    <?= htmlspecialchars(
                        $group['group_name'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </option>
            <?php endforeach; ?>
        </select>
        </div>

        <div class="form-field content-field">
        <label for="discussion-content">First post</label>
        <textarea
            id="discussion-content"
            name="content"
            rows="5"
            placeholder="Write the first post of your discussion..."
            required
        ></textarea>
        </div>

        <button class="primary-btn" type="submit">
            Create discussion
        </button>
    </form>
    <?php if ($discussionError !== ''): ?>
    <div class="form-errors" role="alert">
        <p>
            <?= htmlspecialchars(
                $discussionError,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>
    </div>
<?php endif; ?>

<?php if ($discussionSuccess !== ''): ?>
    <div class="success-message" role="status">
        <p>
            <?= htmlspecialchars(
                $discussionSuccess,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>
    </div>
<?php endif; ?>

<section class="latest-discussions">
    <h2>Latest discussions</h2>

    <?php if (empty($latestDiscussions)): ?>
        <p>You don’t have any discussions yet.</p>
    <?php else: ?>
        <?php foreach ($latestDiscussions as $discussion): ?>
            <article class="discussion-card">
                <p class="author">
                    <span title="User ID: <?= (int) $discussion['user_id'] ?>">
                        <?= htmlspecialchars(
                            $discussion['user_name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>
                </p>
                <p class="group">
                    <a href="/groups/view/?id=<?= (int) $discussion['group_id'] ?>">
                        <?= htmlspecialchars(
                            $discussion['group_name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </a>
                </p>
                <h3 class="subject">
                    <a href="/discussions/view/?id=<?= (int) $discussion['discussion_id'] ?>">
                        <?= htmlspecialchars(
                            $discussion['title'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </a>
                </h3>


                <p class="content">
                    <?= nl2br(htmlspecialchars(
                        $discussion['content'],
                        ENT_QUOTES,
                        'UTF-8'
                    )) ?>
                </p>

            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
<?php endif; ?>

    <?php endif; ?>

    </main>
    <footer>
        <p>Contact</p>

</footer>
    
</body>
</html>