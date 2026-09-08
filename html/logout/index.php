<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

$submittedCsrfToken = $_POST['csrf_token'] ?? '';

if (
    !is_string($submittedCsrfToken)
    || empty($_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $submittedCsrfToken)
) {
    http_response_code(403);
    exit('The logout request could not be verified.');
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $cookieParameters = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        [
            'expires' => time() - 42000,
            'path' => $cookieParameters['path'],
            'domain' => $cookieParameters['domain'],
            'secure' => $cookieParameters['secure'],
            'httponly' => $cookieParameters['httponly'],
            'samesite' => $cookieParameters['samesite'] ?? 'Lax'
        ]
    );
}

session_destroy();

header('Location: /');
exit;