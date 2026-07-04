<?php $pageTitle = "Stages"; ?>

<?php
// MOCK — à remplacer plus tard par : $internships = Internship::getAllOpen();
$internships = [
    [
        'id'           => 1,
        'title'        => 'Stage Analyste SOC Junior',
        'description'  => 'Rejoignez notre équipe SOC et participez à la surveillance active des infrastructures de nos clients. Vous serez formé aux outils SIEM et SOAR.',
        'requirements' => 'Étudiant en cybersécurité ou informatique (Bac+3 minimum). Connaissances en réseaux et systèmes. Curiosité et rigueur.',
        'status'       => 'open',
        'created_at'   => '2026-06-01',
    ],
    [
        'id'           => 2,
        'title'        => 'Stage Développeur PHP Backend',
        'description'  => 'Participez au développement de notre plateforme SOC interne. Vous travaillerez sur l\'API REST, la gestion des incidents et l\'optimisation des performances.',
        'requirements' => 'Étudiant en développement web (Bac+3 minimum). Maîtrise de PHP, MySQL. Connaissance des bonnes pratiques de sécurité web.',
        'status'       => 'open',
        'created_at'   => '2026-06-10',
    ],
    [
        'id'           => 3,
        'title'        => 'Stage Threat Intelligence',
        'description'  => 'Analysez les menaces émergentes et rédigez des rapports de veille pour nos clients. Vous utiliserez des outils de Threat Intelligence professionnels.',
        'requirements' => 'Étudiant en cybersécurité (Bac+4/5). Anglais courant indispensable. Capacité d\'analyse et de synthèse.',
        'status'       => 'closed',
        'created_at'   => '2026-05-15',
    ],
];

// On filtre pour n'afficher que les stages ouverts
$openInternships = array_filter($internships, function($internship) {
    return $internship['status'] === 'open';
});
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
                    <a href="/apply?internship_id=<?= (int) $internship['id'] ?>" 
                       class="btn-primary">
                        Postuler
                    </a>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>