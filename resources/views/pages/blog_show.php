<?php $pageTitle = isset($post['title']) ? $post['title'] : "Article"; ?>

<?php
// MOCK — à remplacer plus tard par : $post = BlogPost::findBySlug($slug);
if (!isset($post)) {
    $post = [
        'id'           => 1,
        'title'        => 'Les 5 meilleures pratiques SOC en 2026',
        'slug'         => 'les-5-meilleures-pratiques-soc-2026',
        'content'      => '
            <p>Les centres opérationnels de sécurité (SOC) jouent un rôle crucial dans la protection des entreprises modernes. Face à des menaces de plus en plus sophistiquées, voici les 5 pratiques essentielles à adopter.</p>

            <h2>1. Surveillance continue 24/7</h2>
            <p>Un SOC efficace ne dort jamais. La surveillance en temps réel permet de détecter les anomalies dès leur apparition, avant qu\'elles ne deviennent des incidents majeurs.</p>

            <h2>2. Triage rapide des alertes</h2>
            <p>Toutes les alertes ne sont pas critiques. Un bon SOC classe les incidents par sévérité (low, medium, high, critical) et priorise sa réponse en conséquence.</p>

            <h2>3. Playbooks de réponse aux incidents</h2>
            <p>Avoir des procédures documentées pour chaque type d\'incident réduit le temps de réponse et évite les erreurs sous pression.</p>

            <h2>4. Intégration SIEM + SOAR</h2>
            <p>Le SIEM collecte et corrèle les logs, le SOAR automatise les réponses répétitives. Ensemble, ils multiplient l\'efficacité de l\'équipe.</p>

            <h2>5. Amélioration continue</h2>
            <p>Chaque incident est une opportunité d\'apprendre. Les revues post-incident et les KPIs permettent d\'améliorer constamment les processus.</p>
        ',
        'published_at' => '2026-06-15',
        'author'       => 'Ahmed Bennani',
        'status'       => 'published',
    ];
}
?>

<?php if ($post['status'] !== 'published'): ?>
    <div class="alert alert-warning">
        Cet article n'est pas encore publié.
    </div>
<?php else: ?>

    <article class="blog-post">

        <header class="blog-post-header">
            <a href="/blog" class="btn-back">← Retour au blog</a>
            <h1><?= htmlspecialchars($post['title']) ?></h1>
            <div class="blog-post-meta">
                <span>Par <?= htmlspecialchars($post['author']) ?></span>
                <span>•</span>
                <span><?= date('d/m/Y', strtotime($post['published_at'])) ?></span>
            </div>
        </header>

        <div class="blog-post-content">
            <?= $post['content'] ?>
        </div>

        <footer class="blog-post-footer">
            <a href="/blog" class="btn-secondary">← Retour au blog</a>
        </footer>

    </article>

<?php endif; ?>