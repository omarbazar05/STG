<aside class="sidebar">
    <ul class="sidebar-links">

        <?php if (($userRole ?? null) === 'admin'): ?>
            <li><a href="/dashboard">Vue d'ensemble</a></li>
            <li><a href="/admin/cms/home">Gérer CMS - Accueil</a></li>
            <li><a href="/admin/cms/pricing">Gérer CMS - Tarifs</a></li>
            <li><a href="/admin/cms/blog">Gérer Blog</a></li>
            <li><a href="/admin/claims">Réclamations</a></li>
            <li><a href="/admin/absences">Absences à valider</a></li>
            <li><a href="/admin/internships">Stages & Candidatures</a></li>

        <?php elseif (($userRole ?? null) === 'employee'): ?>
            <li><a href="/dashboard">Vue d'ensemble</a></li>
            <li><a href="/employe/taches">Mes tâches</a></li>
            <li><a href="/employe/absences">Mes absences</a></li>

        <?php elseif (($userRole ?? null) === 'client'): ?>
            <li><a href="/dashboard">Vue d'ensemble</a></li>
            <li><a href="/client/incidents">Mes incidents</a></li>
            <li><a href="/client/soc-config">Ma configuration SOC</a></li>
            <li><a href="/client/reclamations">Mes réclamations</a></li>

        <?php else: ?>
            <li><em>Rôle non reconnu</em></li>
        <?php endif; ?>

    </ul>
</aside>