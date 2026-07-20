<?php $pageTitle = "Configuration SOC"; ?>

<div id="soc-loading">
    <p>Chargement de votre configuration...</p>
</div>

<div id="soc-content" style="display:none;">
    <h1>Configuration SOC</h1>
    <div id="soc-details"></div>
</div>

<script>
(async function () {
    const token = sessionStorage.getItem('jwt_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {
        const response = await fetch('/api/soc-config', {
            headers: { 'Authorization': 'Bearer ' + token },
        });

        if (response.status === 401) {
            sessionStorage.clear();
            window.location.href = '/login';
            return;
        }

        if (response.status === 403) {
            document.getElementById('soc-loading').textContent = 
                "Vous n'avez pas accès à cette page.";
            return;
        }

        const data = await response.json();
        const details = document.getElementById('soc-details');

        if (data.role === 'client') {
            if (!data.config) {
                details.innerHTML = '<p>Aucune configuration SOC définie pour le moment.</p>';
            } else {
                details.innerHTML = `
                    <p><strong>SIEM :</strong> ${escapeHtml(data.config.siem || '-')}</p>
                    <p><strong>SOAR :</strong> ${escapeHtml(data.config.soar || '-')}</p>
                    <p><strong>Threat Intelligence :</strong> ${escapeHtml(data.config.ti || '-')}</p>
                `;
            }
        } else if (data.role === 'admin') {
            details.innerHTML = `<p>${data.configs.length} configuration(s) client trouvée(s).</p>`;
        }

        document.getElementById('soc-loading').style.display = 'none';
        document.getElementById('soc-content').style.display = 'block';

    } catch (err) {
        document.getElementById('soc-loading').textContent = 'Erreur de chargement.';
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
})();
</script>