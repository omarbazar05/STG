<?php $pageTitle = "Dashboard"; ?>

<div id="dashboard-loading">
    <p>Chargement de votre tableau de bord...</p>
</div>

<div id="dashboard-content" style="display:none;">
    <h1>Tableau de bord</h1>
    <p>Bienvenue, <span id="user-role-display"></span> #<span id="user-id-display"></span></p>
</div>

<script>
(async function () {
    const token = sessionStorage.getItem('jwt_token');

    // Pas de token du tout → jamais connecté → retour login direct
    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {
        const response = await fetch('/api/dashboard-data', {
            headers: {
                'Authorization': 'Bearer ' + token,
            },
        });

        if (response.status === 401) {
            // Token expiré ou invalide → reconnexion nécessaire
            sessionStorage.clear();
            window.location.href = '/login';
            return;
        }

        const data = await response.json();

        document.getElementById('user-role-display').textContent = data.role;
        document.getElementById('user-id-display').textContent = data.user_id;

        document.getElementById('dashboard-loading').style.display = 'none';
        document.getElementById('dashboard-content').style.display = 'block';

    } catch (err) {
        document.getElementById('dashboard-loading').textContent = 
            'Erreur de chargement. Réessayez.';
    }
})();
</script>