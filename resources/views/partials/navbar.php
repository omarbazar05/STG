<nav class="navbar">
    <div class="navbar-brand">
        <a href="/">SOC Platform</a>
    </div>

    <ul class="navbar-links">
        <li><a href="/">Accueil</a></li>
        <li><a href="/pricing">Tarifs</a></li>
        <li><a href="/blog">Blog</a></li>
        <li><a href="/internships">Stages</a></li>

        <?php if (!empty($isLoggedIn)): ?>
            <li><a href="/dashboard">Dashboard</a></li>
            <li><a href="/logout">Déconnexion</a></li>
        <?php else: ?>
            <li><a href="/login">Connexion</a></li>
        <?php endif; ?>
    </ul>
</nav>