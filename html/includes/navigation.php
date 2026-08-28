 <?php if ($isLoggedIn): ?>
        <img src="face_it.webp" class="hero-logo" alt="Face IT">

                <nav class="main-navigation">
                    <a href="/">Home</a>
                    <a href="/groups/">My groups</a>
                    <a href="/profile/">Profile</a>
                    <a href="#">Reset password </a>

                    <form action="/logout/" method="post">
                        <button class="secondary-btn" type="submit">Log out</button>
                    </form>
                </nav>
            <?php endif; ?>
