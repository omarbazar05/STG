<?php $pageTitle = "Rapports"; ?>

<div id="reports-loading">
    <p>Chargement des rapports...</p>
</div>

<div id="reports-content" style="display:none;">
    <h1>Rapports</h1>

    <div class="report-section">
        <h2>Par sévérité</h2>
        <ul id="severity-list"></ul>
    </div>

    <div class="report-section">
        <h2>Par statut</h2>
        <ul id="status-list"></ul>
    </div>
</div>

<script>
(async function () {
    const token = sessionStorage.getItem('jwt_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {
        const response = await fetch('/api/reports', {
            headers: { 'Authorization': 'Bearer ' + token },
        });

        if (response.status === 401) {
            sessionStorage.clear();
            window.location.href = '/login';
            return;
        }

        if (response.status === 403) {
            document.getElementById('reports-loading').textContent =
                "Vous n'avez pas accès à cette page.";
            return;
        }

        const data = await response.json();

        const severityList = document.getElementById('severity-list');
        data.stats.by_severity.forEach(function (row) {
            const li = document.createElement('li');
            li.textContent = `${row.severity} : ${row.total}`;
            severityList.appendChild(li);
        });

        const statusList = document.getElementById('status-list');
        data.stats.by_status.forEach(function (row) {
            const li = document.createElement('li');
            li.textContent = `${row.status} : ${row.total}`;
            statusList.appendChild(li);
        });

        document.getElementById('reports-loading').style.display = 'none';
        document.getElementById('reports-content').style.display = 'block';

    } catch (err) {
        document.getElementById('reports-loading').textContent = 'Erreur de chargement.';
    }
})();
</script>