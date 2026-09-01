<?php

session_start();

$page_name = 'Log in | Face IT';

if (isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Knewave&family=PT+Sans+Caption:wght@400;700&family=Poetsen+One&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/style.css?v=4">

    <title>
        <?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8') ?>
    </title>
</head>

<body>
    <main class="login-page black-back">
        <header class="small-header">
            <a class="backLink" href="/">Back</a>

            <img
                src="/face_it.webp"
                class="small-logo"
                alt="Face IT"
            >
        </header>

        <h1>Log in</h1>

        <form action="/login/" method="post">
            <label for="email">Email address</label>

            <input
                id="email"
                name="email"
                type="email"
                placeholder="name@example.com"
                autocomplete="email"
                required
            >

            <label for="password">Password</label>

            <input
                id="password"
                name="password"
                type="password"
                placeholder="Enter your password"
                autocomplete="current-password"
                required
            >

            <button type="submit" class="primary-btn">
                Log in
            </button>
        </form>

        <p>
            Don’t have an account?
            <a class="secondary-btn" href="/register/">Create one</a>
        </p>
    </main>

    <footer>
        <p>Contact</p>
    </footer>
</body>
</html>