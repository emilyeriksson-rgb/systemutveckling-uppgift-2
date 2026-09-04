<?php

$currentPath = parse_url(
    $_SERVER['REQUEST_URI'],
    PHP_URL_PATH
) ?? '/';
?>

<?php if ($isLoggedIn): ?>
    <header class="logged-in-header">
        <a href="/" aria-label="Face IT home">
            <img
                src="/face_it.webp"
                class="small-logo"
                alt="Face IT"
            >
        </a>

        <nav
            class="main-navigation"
            aria-label="Main navigation"
        >
            <a
                class="nav-item"
                href="/"
                aria-label="Home"
                <?= $currentPath === '/'
                    ? 'aria-current="page"'
                    : '' ?>
            >
                <img
                    class="nav-icon"
                    src="/assets/icons/home.svg"
                    alt=""
                >
                <span class="nav-label">Home</span>
            </a>

            <a
                class="nav-item"
                href="/groups/"
                aria-label="My groups"
                <?= str_starts_with($currentPath, '/groups')
                    ? 'aria-current="page"'
                    : '' ?>
            >
                <img
                    class="nav-icon"
                    src="/assets/icons/groups.svg"
                    alt=""
                >
                <span class="nav-label">My groups</span>
            </a>

            <a
                class="nav-item"
                href="/profile/"
                aria-label="Profile"
                <?= str_starts_with($currentPath, '/profile')
                    ? 'aria-current="page"'
                    : '' ?>
            >
                <img
                    class="nav-icon"
                    src="/assets/icons/settings.svg"
                    alt=""
                >
                <span class="nav-label">Profile</span>
            </a>

     
        </nav>
    </header>
<?php endif; ?>