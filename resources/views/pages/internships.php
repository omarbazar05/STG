<?php $pageTitle = "Stages"; ?>

<?php
if (!isset($openInternships)) {
    require_once __DIR__ . '/../../../app/models/Internship.php';
    // MOCK / fallback — utilisé seulement si le controller n'a pas fourni $openInternships
    $openInternships = Internship::getAllOpen();
}
?>

<section class="internships-header">
    <h1>Offres de stage</h1>
    <p>Rejoignez notre équipe et développez vos compétences en cybersécurité.</p>
</section>

<section class="internships-list">
    <?php if (empty($openInternships)): ?>
        <p class="internships-empty">
            Aucune offre de stage disponible pour le moment. 
            Revenez bientôt !
        </p>
    <?php else: ?>
        <?php foreach ($openInternships as $internship): ?>
            <div class="internship-card">

                <div class="internship-card-header">
                    <h2><?= htmlspecialchars($internship['title']) ?></h2>
                    <span class="internship-badge internship-badge--open">
                        Ouvert
                    </span>
                </div>

                <p class="internship-description">
                    <?= htmlspecialchars($internship['description']) ?>
                </p>

                <div class="internship-requirements">
                    <h3>Profil recherché</h3>
                    <p><?= htmlspecialchars($internship['requirements']) ?></p>
                </div>

                <div class="internship-footer">
                    <span class="internship-date">
                        Publié le <?= date('d/m/Y', strtotime($internship['created_at'])) ?>
                    </span>
                    <a href="/apply/<?= (int) $internship['id'] ?>" 
                       class="btn-primary">
                        Postuler
                    </a>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>