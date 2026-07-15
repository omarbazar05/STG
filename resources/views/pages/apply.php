<?php $pageTitle = "Postuler"; ?>

<?php
require_once __DIR__ . '/../../../app/models/Internship.php';
require_once __DIR__ . '/../../../app/models/Application.php';

// Récupération de l'internship_id — priorité au controller (URL /apply/{id}),
// fallback sur $_GET pour tests isolés
if (!isset($internshipId)) {
    $internshipId = isset($_GET['internship_id']) ? (int) $_GET['internship_id'] : 0;
}

// Vérification que le stage existe
$internship = Internship::findById($internshipId);

// Variables pour les messages
$success = false;
$errors = [];

// Traitement du formulaire quand il est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Récupération et nettoyage des données
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // 2. Validation
    if (empty($name)) {
        $errors[] = "Le nom est obligatoire.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email est invalide.";
    }
    if (!isset($_FILES['cv']) || $_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Le CV est obligatoire.";
    }

    // 3. Vérification doublon
    if (empty($errors) && Application::alreadyApplied($internshipId, $email)) {
        $errors[] = "Vous avez déjà postulé pour ce stage avec cet email.";
    }

    // 4. Upload du CV si pas d'erreurs
    if (empty($errors)) {
        $file     = $_FILES['cv'];
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed  = ['pdf'];

        if (!in_array($ext, $allowed)) {
            $errors[] = "Seuls les fichiers PDF sont acceptés.";
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = "Le CV ne doit pas dépasser 2 Mo.";
        } else {
            $filename  = uniqid('cv_') . '.' . $ext;
            $uploadDir = __DIR__ . '/../../../../storage/uploads/cv/';
            $uploadPath = $uploadDir . $filename;

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $applicationId = Application::create([
                    'internship_id' => $internshipId,
                    'name'          => $name,
                    'email'         => $email,
                    'cv_path'       => 'storage/uploads/cv/' . $filename,
                ]);

                if ($applicationId) {
                    $success = true;
                } else {
                    $errors[] = "Erreur lors de l'enregistrement. Réessayez.";
                }
            } else {
                $errors[] = "Erreur lors de l'upload du fichier.";
            }
        }
    }
}
?>

<?php if (!$internship): ?>
    <div class="alert alert-error">
        <h2>Stage introuvable</h2>
        <p>Ce stage n'existe pas ou n'est plus disponible.</p>
        <a href="/internships" class="btn-secondary">← Retour aux stages</a>
    </div>

<?php elseif ($internship['status'] !== 'open'): ?>
    <div class="alert alert-warning">
        <h2>Stage fermé</h2>
        <p>Ce stage n'accepte plus de candidatures.</p>
        <a href="/internships" class="btn-secondary">← Retour aux stages</a>
    </div>

<?php elseif ($success): ?>
    <div class="alert alert-success">
        <h2>Candidature envoyée ! ✓</h2>
        <p>Merci <strong><?= htmlspecialchars($_POST['name']) ?></strong>, 
           votre candidature pour <strong><?= htmlspecialchars($internship['title']) ?></strong> 
           a bien été reçue.</p>
        <p>Nous vous contacterons à l'adresse 
           <strong><?= htmlspecialchars($_POST['email']) ?></strong>.</p>
        <a href="/internships" class="btn-secondary">← Retour aux stages</a>
    </div>

<?php else: ?>

    <div class="apply-container">

        <a href="/internships" class="btn-back">← Retour aux stages</a>

        <h1>Postuler : <?= htmlspecialchars($internship['title']) ?></h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" 
              action="/apply/<?= (int) $internshipId ?>" 
              enctype="multipart/form-data" 
              class="apply-form">

            <div class="form-group">
                <label for="name">Nom complet *</label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                       placeholder="Votre nom complet"
                       required>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="votre@email.com"
                       required>
            </div>

            <div class="form-group">
                <label for="cv">CV (PDF uniquement, max 2 Mo) *</label>
                <input type="file" 
                       id="cv" 
                       name="cv" 
                       accept=".pdf"
                       required>
            </div>

            <button type="submit" class="btn-primary">
                Envoyer ma candidature
            </button>

        </form>

    </div>

<?php endif; ?>