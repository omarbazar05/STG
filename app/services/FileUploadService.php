<?php

namespace App\Services;

class FileUploadService
{
    // Types MIME autorisés par catégorie
    private const ALLOWED_TYPES = [
        'cv' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        'documents' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png',
        ],
        'cms' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ],
    ];

    // Tailles max en octets par catégorie
    private const MAX_SIZES = [
        'cv'        => 5 * 1024 * 1024,  // 5 MB
        'documents' => 10 * 1024 * 1024, // 10 MB
        'cms'       => 8 * 1024 * 1024,  // 8 MB
    ];

    // Extensions interdites (sécurité absolue)
    private const FORBIDDEN_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'phtml',
        'exe', 'sh', 'bat', 'cmd', 'py',
        'js', 'html', 'htm', 'xml',
    ];

    // Dossiers de destination
    private const UPLOAD_DIRS = [
        'cv'        => __DIR__ . '/../../storage/uploads/cv/',
        'documents' => __DIR__ . '/../../storage/uploads/documents/',
        'cms'       => __DIR__ . '/../../storage/uploads/cms/',
    ];

    /**
     * Upload principal — valide et déplace le fichier
     *
     * @param array  $file     $_FILES['nom_du_champ']
     * @param string $category 'cv' | 'documents' | 'cms'
     * @return array{success: bool, path: ?string, error: ?string}
     */
    public static function upload(array $file, string $category): array
    {
        // 1. Vérifier que la catégorie est valide
        if (!array_key_exists($category, self::ALLOWED_TYPES)) {
            return self::error("Catégorie d'upload invalide.");
        }

        // 2. Vérifier qu'il n'y a pas d'erreur d'upload PHP
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return self::error(self::uploadErrorMessage($file['error']));
        }

        // 3. Vérifier la taille
        if ($file['size'] > self::MAX_SIZES[$category]) {
            $maxMB = self::MAX_SIZES[$category] / 1024 / 1024;
            return self::error("Fichier trop volumineux (max {$maxMB} MB).");
        }

        // 4. Vérifier l'extension (première couche de protection)
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($extension, self::FORBIDDEN_EXTENSIONS)) {
            return self::error("Extension de fichier interdite.");
        }

        // 5. Vérifier le type MIME RÉEL (deuxième couche — plus fiable que l'extension)
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, self::ALLOWED_TYPES[$category])) {
            return self::error("Type de fichier non autorisé ({$mimeType}).");
        }

        // 6. Vérifier que le dossier de destination existe et est accessible en écriture
        $destDir = self::UPLOAD_DIRS[$category];
        if (!is_dir($destDir) || !is_writable($destDir)) {
            return self::error("Dossier de destination inaccessible.");
        }

        // 7. Générer un nom de fichier unique et sécurisé (jamais le nom original)
        $uniqueName = bin2hex(random_bytes(16)) . '_' . time() . '.' . $extension;
        $destPath   = $destDir . $uniqueName;

        // 8. Déplacer le fichier depuis le dossier temporaire PHP
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return self::error("Échec du déplacement du fichier.");
        }

        // 9. Retourner le chemin relatif (pour stockage en BDD)
        $relativePath = "storage/uploads/{$category}/{$uniqueName}";

        return [
            'success' => true,
            'path'    => $relativePath,
            'error'   => null,
        ];
    }

    /**
     * Supprimer un fichier uploadé (ex: lors d'une suppression en BDD)
     */
    public static function delete(string $relativePath): bool
    {
        $fullPath = __DIR__ . '/../../' . $relativePath;

        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    /**
     * Formater les messages d'erreur PHP d'upload
     */
    private static function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE   => "Fichier dépasse la limite du serveur (php.ini).",
            UPLOAD_ERR_FORM_SIZE  => "Fichier dépasse la limite du formulaire.",
            UPLOAD_ERR_PARTIAL    => "Fichier partiellement uploadé.",
            UPLOAD_ERR_NO_FILE    => "Aucun fichier envoyé.",
            UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant.",
            UPLOAD_ERR_CANT_WRITE => "Impossible d'écrire sur le disque.",
            default               => "Erreur d'upload inconnue (code {$code}).",
        };
    }

    /**
     * Helper pour retourner une erreur formatée
     */
    private static function error(string $message): array
    {
        return ['success' => false, 'path' => null, 'error' => $message];
    }
}