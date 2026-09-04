<?php

session_start();

$page_name = 'Profile | Face IT';
$isLoggedIn = isset($_SESSION['user_id']);

if (!$isLoggedIn) {
    header('Location: /');
    exit;
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

<link rel="stylesheet" href="/style.css?v=3">
    <title>
        <?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8') ?>
    </title>
    
</head>
<body>
    <?php require dirname(__DIR__) . '/includes/navigation.php'; ?>

    <main class="profile-page">
        <h1>My profile</h1>

        <section>
            <h2>Personal information</h2>

            <p>Name will appear here.</p>
            <p>Email address will appear here.</p>
        </section>

        <section>
            <h2>My groups</h2>

            <p>Your groups will appear here.</p>
        </section>

        <section>
            <h2>My discussions</h2>

            <p>Your discussions will appear here.</p>
        </section>

        <section>
            <h2>Groups I administer</h2>

            <p>Groups you administer will appear here.</p>
        </section>

        <section>
            <h2>Settings</h2>
                    <form action="/logout/" method="post">
                        <button type="submit" class="secondary-btn">Log out</button>
                    </form>
        </section>
    </main>

    <footer>
        <p>Contact</p>
    </footer>
</body>
</html>