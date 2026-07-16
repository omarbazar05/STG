<?php

namespace App\Controllers;

require_once __DIR__ . '/../services/PricingService.php';
require_once __DIR__ . '/../services/NotificationService.php';
require_once __DIR__ . '/../services/AuditLogger.php';
require_once __DIR__ . '/../helpers/validator.php';
require_once __DIR__ . '/../helpers/response.php';

use App\Services\PricingService;
use App\Services\NotificationService;
use App\Services\AuditLogger;

class QuoteController
{
    /**
     * GET /pricing — retourne tous les plans (pour la page publique)
     */
    public function plans(): void
    {
        $plans = PricingService::getAllPlans();
        jsonResponse(['plans' => $plans]);
    }

    /**
     * POST /api/quote/calculate — calcule le prix sans sauvegarder
     * Utilisé par le calculateur de devis en temps réel
     */
    public function calculate(): void
    {
        $body     = json_decode(file_get_contents('php://input'), true);
        $planKey  = sanitize($body['plan_key'] ?? '');
        $months   = (int) ($body['months'] ?? 12);

        if (empty($planKey)) {
            errorResponse('Plan tarifaire requis.', 422);
            return;
        }

        if ($months < 1 || $months > 60) {
            errorResponse('Durée invalide (1-60 mois).', 422);
            return;
        }

        $result = PricingService::calculatePrice($planKey, $months);

        if (!$result) {
            errorResponse('Plan tarifaire introuvable.', 404);
            return;
        }

        jsonResponse(['quote' => $result]);
    }

    /**
     * POST /api/quote — soumettre une demande de devis
     */
    public function store(): void
    {
        $body = json_decode(file_get_contents('php://input'), true);

        // Validation
        $companyName  = sanitize($body['company_name']  ?? '');
        $contactEmail = sanitize($body['contact_email'] ?? '');
        $phone        = sanitize($body['phone']         ?? '');
        $planKey      = sanitize($body['plan_key']      ?? '');
        $months       = (int) ($body['months']          ?? 12);
        $needs        = sanitize($body['needs_description'] ?? '');

        if (empty($companyName) || empty($contactEmail) || empty($planKey)) {
            errorResponse('Champs requis : company_name, contact_email, plan_key.', 422);
            return;
        }

        if (!validateEmail($contactEmail)) {
            errorResponse('Email invalide.', 422);
            return;
        }

        // Calculer le devis
        $quote = PricingService::calculatePrice($planKey, $months);
        if (!$quote) {
            errorResponse('Plan tarifaire introuvable.', 404);
            return;
        }

        // Enregistrer la demande en BDD
        $pdo  = \Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO quote_requests
             (company_name, contact_email, phone, needs_description, status)
             VALUES (:company_name, :contact_email, :phone, :needs_description, 'pending')"
        );
        $stmt->execute([
            'company_name'      => $companyName,
            'contact_email'     => $contactEmail,
            'phone'             => $phone ?: null,
            'needs_description' => $needs ?: null,
        ]);

        $quoteId = (int) $pdo->lastInsertId();

        // Notifier les admins
        NotificationService::onNewQuoteRequest($quoteId, $companyName);

        // Audit log (acteur = 0 car public, pas connecté)
        AuditLogger::log(0, 'client', 'quote_request', 'quote_requests', $quoteId, [
            'company' => $companyName,
            'plan'    => $planKey,
            'months'  => $months,
            'total'   => $quote['total'],
        ]);

        jsonResponse([
            'message'  => 'Demande de devis enregistrée. Nous vous contacterons sous 24h.',
            'quote_id' => $quoteId,
            'quote'    => $quote,
        ], 201);
    }
}