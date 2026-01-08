<?php
// services/BadgeSystem.php
require_once __DIR__ . '/../models/Badge.php';
require_once __DIR__ . '/../models/Consumption.php';
require_once __DIR__ . '/../models/UserProfile.php';
require_once __DIR__ . '/../config/constants.php';

class BadgeSystem {
    private Badge $badgeModel;
    private Consumption $consumptionModel;
    private UserProfile $profileModel;
    
    public function __construct() {
        $this->badgeModel = new Badge();
        $this->consumptionModel = new Consumption();
        $this->profileModel = new UserProfile();
    }
    
    /**
     * Vérifier et attribuer automatiquement les nouveaux badges
     */
    public function checkNewBadges(int $userId): array {
        $newBadges = [];
        
        // 1. Badge ECO WARRIOR : 7 jours consécutifs sous le quota
        if ($this->check7DaysUnderQuota($userId)) {
            if ($this->badgeModel->awardBadge($userId, BADGE_ECO_WARRIOR)) {
                $newBadges[] = '🌊 Eco Warrior';
            }
        }
        
        // 2. Badge WATER SAVER : Réduction ≥20% par rapport à la semaine dernière
        if ($this->checkReduction20Percent($userId)) {
            if ($this->badgeModel->awardBadge($userId, BADGE_WATER_SAVER)) {
                $newBadges[] = '💧 Water Saver';
            }
        }
        
        return $newBadges;
    }
    
    /**
     * Vérifier si l'utilisateur est resté 7 jours sous quota
     */
    private function check7DaysUnderQuota(int $userId): bool {
        $quotaTotal = $this->profileModel->calculateTotalQuota($userId);
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT date, SUM(volume) as daily_total
            FROM consumptions
            WHERE user_id = :user_id
            AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY date
            HAVING daily_total <= :quota
        ");
        
        $stmt->execute(['user_id' => $userId, 'quota' => $quotaTotal]);
        $daysUnderQuota = $stmt->fetchAll();
        
        return count($daysUnderQuota) >= 7;
    }
    
    /**
     * Vérifier si réduction ≥20% par rapport à semaine précédente
     */
    private function checkReduction20Percent(int $userId): bool {
        $db = Database::getInstance()->getConnection();
        
        // Consommation cette semaine
        $stmt = $db->prepare("
            SELECT SUM(volume) as total
            FROM consumptions
            WHERE user_id = :user_id
            AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ");
        $stmt->execute(['user_id' => $userId]);
        $thisWeek = $stmt->fetch()['total'] ?? 0;
        
        // Consommation semaine dernière
        $stmt = $db->prepare("
            SELECT SUM(volume) as total
            FROM consumptions
            WHERE user_id = :user_id
            AND date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
            AND date < DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ");
        $stmt->execute(['user_id' => $userId]);
        $lastWeek = $stmt->fetch()['total'] ?? 0;
        
        if ($lastWeek == 0) return false;
        
        $reduction = (($lastWeek - $thisWeek) / $lastWeek) * 100;
        return $reduction >= 20;
    }
}