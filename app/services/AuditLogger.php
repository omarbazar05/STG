<?php

namespace App\Services;

class AuditLogger
{
    /**
     * Enregistre une action sensible dans audit_logs
     *
     * @param int    $actorId    ID de l'utilisateur qui fait l'action
     * @param string $actorType  'admin' | 'employee' | 'client'
     * @param string $action     Ex: 'login_success', 'cms_edit', 'claim_created'...
     * @param string $entityType Ex: 'admins', 'claims', 'incidents'...
     * @param int    $entityId   ID de l'entité concernée
     * @param array  $payload    Données supplémentaires (avant/après modification...)
     */
    public static function log(
        int    $actorId,
        string $actorType,
        string $action,
        string $entityType = '',
        int    $entityId = 0,
        array  $payload = []
    ): void {
        try {
            $pdo = \Database::getConnection();

            $stmt = $pdo->prepare(
                "INSERT INTO audit_logs
                 (actor_id, actor_type, action, entity_type, entity_id, payload, ip_address)
                 VALUES
                 (:actor_id, :actor_type, :action, :entity_type, :entity_id, :payload, :ip_address)"
            );

            $stmt->execute([
                'actor_id'    => $actorId,
                'actor_type'  => $actorType,
                'action'      => $action,
                'entity_type' => $entityType ?: null,
                'entity_id'   => $entityId ?: null,
                'payload'     => !empty($payload) ? json_encode($payload) : null,
                'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\PDOException $e) {
            // On log l'erreur sans planter l'application
            error_log("[AuditLogger] Erreur : " . $e->getMessage());
        }
    }

    /**
     * Actions prédéfinies — appelées depuis les controllers
     */
    public static function loginSuccess(int $actorId, string $actorType): void
    {
        self::log($actorId, $actorType, 'login_success', $actorType . 's', $actorId);
    }

    public static function loginFailed(string $email, string $actorType): void
    {
        self::log(0, $actorType, 'login_failed', '', 0, ['email' => $email]);
    }

    public static function logout(int $actorId, string $actorType): void
    {
        self::log($actorId, $actorType, 'logout', $actorType . 's', $actorId);
    }

    public static function cmsEdit(int $adminId, string $table, int $entityId, array $changes = []): void
    {
        self::log($adminId, 'admin', 'cms_edit', $table, $entityId, $changes);
    }

    public static function userCreated(int $adminId, string $userType, int $newUserId): void
    {
        self::log($adminId, 'admin', 'user_create', $userType . 's', $newUserId);
    }

    public static function fileUploaded(int $actorId, string $actorType, string $filePath): void
    {
        self::log($actorId, $actorType, 'file_upload', '', 0, ['path' => $filePath]);
    }

    public static function incidentUpdated(int $actorId, string $actorType, int $incidentId, array $changes = []): void
    {
        self::log($actorId, $actorType, 'incident_update', 'incidents', $incidentId, $changes);
    }

    public static function claimUpdated(int $actorId, string $actorType, int $claimId, array $changes = []): void
    {
        self::log($actorId, $actorType, 'claim_update', 'claims', $claimId, $changes);
    }
}