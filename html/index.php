<?php
session_start();

$page_name = 'Face IT';
$isLoggedIn = isset($_SESSION['user_id']);

$loginError = $_SESSION['login_error'] ?? '';
$loginEmail = $_SESSION['login_email'] ?? '';


unset(
    $_SESSION['login_error'],
    $_SESSION['login_email']
);

if (!$isLoggedIn && empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/includes/functions.php';

$myGroups = [];

if ($isLoggedIn) {
    $pdo = connectDatabase();

    $statement = $pdo->prepare(
        'SELECT groups.group_id, groups.group_name
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
    <!-- Här kommer de senaste kommentarerna/diskussionerna användaren deltagit i att listas -->

    <!-- Här kommer andra diskusioner att visas. (visa grupp, titel, datum, antal kommentarer)
    Klickar man på en diskussion visas alla kommentarer i den diskussionen. --> 
    
<?php endif; ?>
    <?php endif; ?>

    </main>
    <footer>
        <p>Contact</p>

</footer>
    
</body>
</html>