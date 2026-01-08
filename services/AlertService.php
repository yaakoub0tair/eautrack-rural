<?php
// services/AlertService.php
require_once __DIR__ . '/../models/Alert.php';
require_once __DIR__ . '/../models/Consumption.php';
require_once __DIR__ . '/../models/UserProfile.php';
require_once __DIR__ . '/../config/constants.php';

class AlertService {
    private Alert $alertModel;
    private Consumption $consumptionModel;
    private UserProfile $profileModel;
    
    public function __construct() {
        $this->alertModel = new Alert();
        $this->consumptionModel = new Consumption();
        $this->profileModel = new UserProfile();
    }
    
    /**
     * Vérifier si le quota est dépassé et générer les alertes appropriées
     */
    public function checkQuotaExceeded(int $userId, int $consumptionId): void {
        // 1. Récupérer le quota total
        $quotaTotal = $this->profileModel->calculateTotalQuota($userId);
        
        // 2. Récupérer la consommation totale du jour
        $dailyTotal = $this->consumptionModel->getDailyTotal($userId);
        
        // 3. Calculer le ratio
        $ratio = $dailyTotal / $quotaTotal;
        
        // 4. Générer les alertes selon les seuils
        if ($ratio >= 1.0 && !$this->alertModel->alertExistsToday($userId, ALERT_LEVEL_CRITICAL)) {
            $this->alertModel->createAlert(
                $userId,
                $consumptionId,
                ALERT_LEVEL_CRITICAL,
                "⚠️ ALERTE CRITIQUE : Vous avez dépassé votre quota journalier ! ({$dailyTotal}L / {$quotaTotal}L)"
            );
        } elseif ($ratio >= 0.8 && !$this->alertModel->alertExistsToday($userId, ALERT_LEVEL_WARNING)) {
            $this->alertModel->createAlert(
                $userId,
                $consumptionId,
                ALERT_LEVEL_WARNING,
                "⚡ ATTENTION : Vous avez atteint 80% de votre quota ({$dailyTotal}L / {$quotaTotal}L)"
            );
        } elseif ($ratio >= 0.5 && !$this->alertModel->alertExistsToday($userId, ALERT_LEVEL_INFO)) {
            $this->alertModel->createAlert(
                $userId,
                $consumptionId,
                ALERT_LEVEL_INFO,
                "ℹ️ INFO : Vous avez atteint 50% de votre quota ({$dailyTotal}L / {$quotaTotal}L)"
            );
        }
    }
}