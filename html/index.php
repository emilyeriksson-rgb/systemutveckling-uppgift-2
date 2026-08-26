<?php
$page_name = 'Face IT';
?>

<!DOCTYPE html>
<html lang="eng">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8') ?>
    </title>

<link rel="stylesheet" href="/style.css?v=3">
</head>

<body>
    <main>
        <h1><?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8') ?></h1>

        <p>Face-it is the only .</p>

        <img src="snack.webp" class="hero-image" alt="Snacka it på webben">
    </main>
</body>
</html>