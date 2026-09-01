<?php

session_start();

require_once dirname(__DIR__) . '/includes/functions.php';

$page_name = 'Log in | Face IT';

$error = '';
$email = '';

if (isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL)
        || $password === ''
    ) {
        $error = 'Invalid email address or password.';
    } else {
        $pdo = connectDatabase();

        $statement = $pdo->prepare(
            'SELECT user_id, password_hash
             FROM users
             WHERE email = :email
             LIMIT 1'
        );

        $statement->execute([
            'email' => $email
        ]);

        $user = $statement->fetch();

        if (
            $user
            && password_verify(
                $password,
                $user['password_hash']
            )
        ) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $user['user_id'];

            header('Location: /');
            exit;
        }

        $error = 'Invalid email address or password.';
    }
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
                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
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

            <?php if ($error !== ''): ?>
                <div class="form-errors" role="alert">
                    <p>
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
            <?php endif; ?>

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