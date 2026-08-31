<?php

session_start();

require_once dirname(__DIR__) . '/includes/functions.php';

$page_name = 'Create account | Face IT';
$isLoggedIn = isset($_SESSION['user_id']);

if ($isLoggedIn) {
    header('Location: /');
    exit;
}

$errors = [];

$firstName = '';
$lastName = '';
$userName = '';
$email = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $userName = trim($_POST['user_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $passwordConfirmation = $_POST['password_confirmation'] ?? '';
    $submittedCsrfToken = $_POST['csrf_token'] ?? '';

    if (
        !is_string($submittedCsrfToken)
        || !hash_equals($_SESSION['csrf_token'], $submittedCsrfToken)
    ) {
        $errors[] = 'The form could not be verified. Please try again.';
    }

    if ($firstName === '') {
        $errors[] = 'First name is required.';
    }

    if ($lastName === '') {
        $errors[] = 'Last name is required.';
    }

    if ($userName === '') {
    $errors[] = 'User name is required.';
} elseif (strlen($userName) < 2) {
    $errors[] = 'The user name must contain at least 2 characters.';
} elseif (strlen($userName) > 50) {
    $errors[] = 'The user name may contain at most 50 characters.';
}

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'The password must contain at least 8 characters.';
    }

    if ($password !== $passwordConfirmation) {
        $errors[] = 'The passwords do not match.';
    }

    if (empty($errors)) {
        $pdo = connectDatabase();

        $statement = $pdo->prepare(
            'SELECT user_id
             FROM users
             WHERE email = :email
             LIMIT 1'
        );

        $statement->execute([
            'email' => $email
        ]);

        $existingUser = $statement->fetch();

        if ($existingUser) {
            $errors[] = 'An account with that email address already exists.';
        } else {
            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $statement = $pdo->prepare(
                'INSERT INTO users (
                    first_name,
                    last_name,
                    user_name,
                    email,
                    password_hash
                ) VALUES (
                    :first_name,
                    :last_name,
                    :user_name,
                    :email,
                    :password_hash
                )'
            );

            $statement->execute([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'user_name' => $userName,
                'email' => $email,
                'password_hash' => $passwordHash
            ]);

            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $pdo->lastInsertId();

            unset($_SESSION['csrf_token']);

            header('Location: /');
            exit;
        }
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

    <link rel="stylesheet" href="/style.css?v=3">

    <title>
        <?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8') ?>
    </title>
</head>

<body>
    <main class="register-page">
        <a href="/">Back to home</a>

        <h1>Create an account</h1>

        <?php if (!empty($errors)): ?>
            <div class="form-errors" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li>
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="/register/" method="post">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(
                    $_SESSION['csrf_token'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >

            <label for="first-name">First name</label>
            <input
                id="first-name"
                name="first_name"
                type="text"
                maxlength="100"
                autocomplete="given-name"
                value="<?= htmlspecialchars(
                    $firstName,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                required
            >

            <label for="last-name">Last name</label>
            <input
                id="last-name"
                name="last_name"
                type="text"
                maxlength="100"
                autocomplete="family-name"
                value="<?= htmlspecialchars(
                    $lastName,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                required
            >
            <label for="user-name">User name</label>

        <input
            id="user-name"
            name="user_name"
            type="text"
            minlength="2"
            maxlength="50"
            autocomplete="nickname"
            value="<?= htmlspecialchars(
                $userName,
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
            required
        >

            <label for="email">Email address</label>
            <input
                id="email"
                name="email"
                type="email"
                maxlength="255"
                autocomplete="email"
                value="<?= htmlspecialchars(
                    $email,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                required
            >

            <label for="password">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <label for="password-confirmation">
                Repeat password
            </label>
            <input
                id="password-confirmation"
                name="password_confirmation"
                type="password"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <button type="submit">Create account</button>
        </form>
    </main>

    <footer>
        <p>Contact</p>
    </footer>
</body>
</html>