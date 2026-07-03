<?php

namespace App\Services;

use App\Models\Notification;

require_once __DIR__ . '/../models/Notification.php';

class NotificationService
{
    /**
     * Notifie tous les admins
     */
    public static function notifyAdmins(string $type, string $message, string $link = null): void
    {
        $pdo  = \Database::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM admins");
        $stmt->execute();
        $admins = $stmt->fetchAll();

        foreach ($admins as $admin) {
            Notification::create($admin['id'], 'admin', $type, $message, $link);
        }
    }

    /**
     * Notifie un employé spécifique
     */
    public static function notifyEmployee(int $employeeId, string $type, string $message, string $link = null): void
    {
        Notification::create($employeeId, 'employee', $type, $message, $link);
    }

    /**
     * Notifie un client spécifique
     */
    public static function notifyClient(int $clientId, string $type, string $message, string $link = null): void
    {
        Notification::create($clientId, 'client', $type, $message, $link);
    }

    // =============================================
    // Notifications métier — appelées depuis les controllers
    // =============================================

    /**
     * Client crée une réclamation → notifie les admins
     */
    public static function onNewClaim(int $claimId, string $clientCompany): void
    {
        self::notifyAdmins(
            'new_claim',
            "Nouvelle réclamation de {$clientCompany}.",
            "/admin/claims/{$claimId}"
        );
    }

    /**
     * Admin répond à une réclamation → notifie le client
     */
    public static function onClaimUpdated(int $clientId, int $claimId, string $status): void
    {
        self::notifyClient(
            $clientId,
            'claim_updated',
            "Votre réclamation #{$claimId} a été mise à jour : {$status}.",
            "/client/claims/{$claimId}"
        );
    }

    /**
     * Employé déclare une absence → notifie les admins
     */
    public static function onNewAbsence(int $absenceId, string $employeeName): void
    {
        self::notifyAdmins(
            'new_absence',
            "Nouvelle demande d'absence de {$employeeName}.",
            "/admin/absences/{$absenceId}"
        );
    }

    /**
     * Admin approuve/refuse une absence → notifie l'employé
     */
    public static function onAbsenceDecision(int $employeeId, int $absenceId, string $decision): void
    {
        self::notifyEmployee(
            $employeeId,
            'absence_decision',
            "Votre demande d'absence #{$absenceId} a été {$decision}.",
            "/employe/absences/{$absenceId}"
        );
    }

    /**
     * Document prêt → notifie le client
     */
    public static function onDocumentReady(int $clientId, int $documentId, string $documentType): void
    {
        self::notifyClient(
            $clientId,
            'document_ready',
            "Votre document '{$documentType}' est prêt à télécharger.",
            "/client/documents/{$documentId}"
        );
    }

    /**
     * Incident assigné → notifie l'employé
     */
    public static function onIncidentAssigned(int $employeeId, int $incidentId, string $severity): void
    {
        self::notifyEmployee(
            $employeeId,
            'incident_assigned',
            "Nouvel incident #{$incidentId} assigné (sévérité : {$severity}).",
            "/employe/incidents/{$incidentId}"
        );
    }

    /**
     * Nouveau devis soumis → notifie les admins
     */
    public static function onNewQuoteRequest(int $quoteId, string $companyName): void
    {
        self::notifyAdmins(
            'new_quote',
            "Nouveau devis demandé par {$companyName}.",
            "/admin/quotes/{$quoteId}"
        );
    }
}