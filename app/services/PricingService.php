<?php

namespace App\Services;

class PricingService
{
    /**
     * Récupère tous les plans tarifaires depuis cms_pricing
     */
    public static function getAllPlans(): array
    {
        $pdo  = \Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM cms_pricing ORDER BY price ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Récupère un plan par sa clé (ex: 'starter', 'pro', 'enterprise')
     */
    public static function getPlanByKey(string $planKey): array|false
    {
        $pdo  = \Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM cms_pricing WHERE plan_key = :plan_key LIMIT 1");
        $stmt->execute(['plan_key' => $planKey]);
        return $stmt->fetch();
    }

    /**
     * Calcule le prix total selon le plan et la durée (en mois)
     */
    public static function calculatePrice(string $planKey, int $months = 12): array|false
    {
        $plan = self::getPlanByKey($planKey);

        if (!$plan) {
            return false;
        }

        $pricePerMonth = (float) $plan['price'];
        $total         = $pricePerMonth * $months;

        // Remise selon la durée
        $discount = match (true) {
            $months >= 24 => 0.20, // 20% de remise pour 2 ans
            $months >= 12 => 0.10, // 10% de remise pour 1 an
            default       => 0.00, // Pas de remise pour moins d'1 an
        };

        $totalAfterDiscount = $total * (1 - $discount);

        return [
            'plan_key'       => $plan['plan_key'],
            'plan_title'     => $plan['title'],
            'price_per_month'=> $pricePerMonth,
            'months'         => $months,
            'subtotal'       => round($total, 2),
            'discount_rate'  => $discount * 100 . '%',
            'discount_amount'=> round($total * $discount, 2),
            'total'          => round($totalAfterDiscount, 2),
            'features'       => json_decode($plan['features'] ?? '[]', true),
        ];
    }

    /**
     * Insère un nouveau plan tarifaire (utilisé par l'admin CMS)
     */
    public static function createPlan(array $data, int $adminId): int
    {
        $pdo  = \Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO cms_pricing (plan_key, title, description, price, features, updated_by)
             VALUES (:plan_key, :title, :description, :price, :features, :updated_by)"
        );
        $stmt->execute([
            'plan_key'    => $data['plan_key'],
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'price'       => $data['price'],
            'features'    => isset($data['features']) ? json_encode($data['features']) : null,
            'updated_by'  => $adminId,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Met à jour un plan tarifaire existant
     */
    public static function updatePlan(string $planKey, array $data, int $adminId): bool
    {
        $pdo  = \Database::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE cms_pricing
             SET title = :title, description = :description,
                 price = :price, features = :features, updated_by = :updated_by
             WHERE plan_key = :plan_key"
        );
        $stmt->execute([
            'plan_key'    => $planKey,
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'price'       => $data['price'],
            'features'    => isset($data['features']) ? json_encode($data['features']) : null,
            'updated_by'  => $adminId,
        ]);
        return $stmt->rowCount() > 0;
    }
}