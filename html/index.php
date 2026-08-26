<?php
$page_name = 'Face IT';
?>

<!DOCTYPE html>
<html lang="eng">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Knewave&family=PT+Sans+Caption:wght@400;700&family=Poetsen+One&display=swap" rel="stylesheet">

    <title>
        <?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8') ?>
    </title>

<link rel="stylesheet" href="/style.css?v=3">
</head>

<body>
    <main>
        <section class="black-back">
            <img src="face_it.webp" class="hero-logo" alt="Face IT">
            <p class="tagline"> — Talk tech. Share ideas. Solve together. Whether you’re writing your first line of code or shaping the future of IT, there’s always a place for you in the conversation.</p>
            <section class="two-buttons">
                <button type="submit" class="primary-btn">Log in</button>
                <button type="button" class="secondary-btn">Create a new user</button>    
            </section>
        </section>
    </main>
    
</body>
</html>