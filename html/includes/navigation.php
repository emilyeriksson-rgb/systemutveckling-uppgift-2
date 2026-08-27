 <?php if ($isLoggedIn): ?>
        <img src="face_it.webp" class="hero-logo" alt="Face IT">

                <nav class="main-navigation">
                    <a href="/">Home</a>
                    <a href="/groups/">My groups</a>
                    <a href="/profile/">Profile</a>

                    <form action="/logout/" method="post">
                        <button type="submit">Log out</button>
                    </form>
                </nav>
            <?php endif; ?>