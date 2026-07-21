<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - SOC Platform' : 'SOC Platform' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <main>
        <?php if (!empty($isLoggedIn)): ?>
            <div class="app-layout">
                <?php include __DIR__ . '/../partials/sidebar.php'; ?>
                <div class="app-content">
                    <?= $content ?? '' ?>
                </div>
            </div>
        <?php else: ?>
            <?= $content ?? '' ?>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script src="/assets/js/app.js"></script>
</body>
</html>