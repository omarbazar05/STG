<?php $pageTitle = "Blog"; ?>

<?php
// MOCK — à remplacer plus tard par : $posts = BlogPost::getAllPublished();
$posts = [
    [
        'id'           => 1,
        'title'        => 'Les 5 meilleures pratiques SOC en 2026',
        'slug'         => 'les-5-meilleures-pratiques-soc-2026',
        'excerpt'      => 'Découvrez comment les centres opérationnels de sécurité modernes font face aux menaces avancées et protègent leurs clients.',
        'published_at' => '2026-06-15',
        'author'       => 'Ahmed Bennani',
    ],
    [
        'id'           => 2,
        'title'        => 'Comprendre les incidents de sécurité : niveaux de sévérité',
        'slug'         => 'comprendre-incidents-securite-severite',
        'excerpt'      => 'Low, medium, high, critical : que signifient ces niveaux et comment notre équipe réagit-elle à chacun d\'eux ?',
        'published_at' => '2026-05-28',
        'author'       => 'Sara El Idrissi',
    ],
    [
        'id'           => 3,
        'title'        => 'SIEM et SOAR : quelle différence pour votre entreprise ?',
        'slug'         => 'siem-soar-difference-entreprise',
        'excerpt'      => 'Ces deux outils sont au cœur de notre plateforme SOC. On vous explique leur rôle et leur complémentarité.',
        'published_at' => '2026-05-10',
        'author'       => 'Ahmed Bennani',
    ],
];
?>

<section class="blog-header">
    <h1>Blog</h1>
    <p>Actualités, conseils et ressources en cybersécurité.</p>
</section>

<section class="blog-list">
    <?php if (empty($posts)): ?>
        <p class="blog-empty">Aucun article publié pour le moment.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <article class="blog-card">

                <div class="blog-card-meta">
                    <span class="blog-date">
                        <?= date('d/m/Y', strtotime($post['published_at'])) ?>
                    </span>
                    <span class="blog-author">
                        Par <?= htmlspecialchars($post['author']) ?>
                    </span>
                </div>

                <h2 class="blog-card-title">
                    <a href="/blog/<?= htmlspecialchars($post['slug']) ?>">
                        <?= htmlspecialchars($post['title']) ?>
                    </a>
                </h2>

                <p class="blog-card-excerpt">
                    <?= htmlspecialchars($post['excerpt']) ?>
                </p>

                <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" class="btn-secondary">
                    Lire la suite →
                </a>

            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>