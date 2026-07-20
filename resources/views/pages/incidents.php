<?php $pageTitle = "Incidents"; ?>

<div id="incidents-loading">
    <p>Chargement de vos incidents...</p>
</div>

<div id="incidents-content" style="display:none;">
    <h1>Incidents</h1>
    <table class="incidents-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Sévérité</th>
                <th>Description</th>
                <th>Statut</th>
                <th>Détecté le</th>
            </tr>
        </thead>
        <tbody id="incidents-tbody">
            <!-- Rempli dynamiquement par JS -->
        </tbody>
    </table>
    <p id="incidents-empty" style="display:none;">Aucun incident pour le moment.</p>
</div>

<script>
(async function () {
    const token = sessionStorage.getItem('jwt_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {
        const response = await fetch('/api/incidents', {
            headers: { 'Authorization': 'Bearer ' + token },
        });

        if (response.status === 401) {
            sessionStorage.clear();
            window.location.href = '/login';
            return;
        }

        const data = await response.json();
        const tbody = document.getElementById('incidents-tbody');

        if (data.incidents.length === 0) {
            document.getElementById('incidents-empty').style.display = 'block';
        } else {
            data.incidents.forEach(function (incident) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${incident.id}</td>
                    <td><span class="badge badge--${incident.severity}">${incident.severity}</span></td>
                    <td>${escapeHtml(incident.description)}</td>
                    <td>${incident.status}</td>
                    <td>${new Date(incident.detected_at).toLocaleDateString('fr-FR')}</td>
                `;
                tbody.appendChild(row);
            });
        }

        document.getElementById('incidents-loading').style.display = 'none';
        document.getElementById('incidents-content').style.display = 'block';

    } catch (err) {
        document.getElementById('incidents-loading').textContent = 'Erreur de chargement.';
    }

    // Protection XSS côté JS — équivalent de htmlspecialchars() en PHP
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
})();
</script>