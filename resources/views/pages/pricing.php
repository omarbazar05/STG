<?php $pageTitle = "Tarifs"; ?>

<?php
// MOCK — à remplacer plus tard par : $plans = PricingService::getPlans();
$plans = [
    [
        'plan_key'    => 'starter',
        'title'       => 'Starter',
        'description' => 'Idéal pour les petites entreprises qui débutent en cybersécurité.',
        'price'       => 299.00,
        'features'    => [
            'Surveillance 8h/jour',
            'Jusqu\'à 5 incidents/mois',
            'Tableau de bord basique',
            'Support par email',
        ],
    ],
    [
        'plan_key'    => 'professional',
        'title'       => 'Professional',
        'description' => 'Pour les entreprises avec des besoins de sécurité avancés.',
        'price'       => 799.00,
        'features'    => [
            'Surveillance 24/7',
            'Incidents illimités',
            'Tableau de bord avancé',
            'Support prioritaire',
            'Rapports mensuels',
        ],
    ],
    [
        'plan_key'    => 'enterprise',
        'title'       => 'Enterprise',
        'description' => 'Solution complète pour les grandes organisations.',
        'price'       => 1999.00,
        'features'    => [
            'Surveillance 24/7 dédiée',
            'Incidents illimités',
            'Config SOC personnalisée',
            'Support téléphonique dédié',
            'Rapports hebdomadaires',
            'Gestionnaire de compte dédié',
        ],
    ],
];
?>

<section class="pricing-header">
    <h1>Nos offres</h1>
    <p>Choisissez le plan adapté à vos besoins de sécurité.</p>
</section>

<section class="pricing-grid">
    <?php foreach ($plans as $plan): ?>
        <div class="pricing-card <?= $plan['plan_key'] === 'professional' ? 'pricing-card--featured' : '' ?>">
            
            <div class="pricing-card-header">
                <h2><?= htmlspecialchars($plan['title']) ?></h2>
                <p><?= htmlspecialchars($plan['description']) ?></p>
            </div>

            <div class="pricing-card-price">
                <span class="price"><?= number_format($plan['price'], 2, ',', ' ') ?> €</span>
                <span class="period">/ mois</span>
            </div>

            <ul class="pricing-features">
                <?php foreach ($plan['features'] as $feature): ?>
                    <li>✓ <?= htmlspecialchars($feature) ?></li>
                <?php endforeach; ?>
            </ul>

            <a href="/contact" class="btn-primary">
                Choisir <?= htmlspecialchars($plan['title']) ?>
            </a>

        </div>
    <?php endforeach; ?>
</section>