<?php
session_start();

$page_name = 'Face IT';
$isLoggedIn = isset($_SESSION['user_id']); ?>

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
    <header>
    <?php require __DIR__ . '/includes/navigation.php'; ?>
</header>
    <main>
        <div class="black-back">
        <?php if (!$isLoggedIn): ?>

    <img src="face_it.webp" class="hero-logo" alt="Face IT">
    <p class="tagline"> — Talk tech. Share ideas. Solve together. Whether you’re writing your first line of code or shaping the future of IT, there’s always a place for you in the conversation.</p>
    <div class="two-buttons">
        <a href="/login/" class="primary-btn">Log in</a>
        <a href="/register/" class="secondary-btn">Create a new user</a>    
    </div>
    <?php endif; ?>

            </div>
    </main>
    <footer>
        <p>Contact</p>

</footer>
    
</body>
</html>