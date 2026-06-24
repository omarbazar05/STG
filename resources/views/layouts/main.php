<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - SOC Platform' : 'SOC Platform' ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <main>
        <?= $content ?? '' ?>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script src="/assets/js/app.js"></script>
</body>
</html>