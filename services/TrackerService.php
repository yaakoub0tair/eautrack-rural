<?php
require_once __DIR__ . '/../models/UserProfile.php';
require_once __DIR__ . '/../models/Consumption.php';
require_once __DIR__ . '/AlertService.php';
require_once __DIR__ . '/BadgeSystem.php';

class TrackerService {
    private UserProfile $profileModel;
    private Consumption $consumptionModel;
    private AlertService $alertService;
    private BadgeSystem $badgeSystem;
    
    public function __construct() {
        $this->profileModel = new UserProfile();
        $this->consumptionModel = new Consumption();
        $this->alertService = new AlertService();
        $this->badgeSystem = new BadgeSystem();
    }
    
    /**
     * Ajouter une consommation et déclencher les vérifications
     */
    public function addConsumption(array $data): array {
        try {
            // 1. Enregistrer la consommation
            $consumptionId = $this->consumptionModel->addConsumption($data);
            
            // 2. Vérifier les quotas et générer alertes si nécessaire
            $this->alertService->checkQuotaExceeded($data['user_id'], $consumptionId);
            
            // 3. Vérifier les badges
            $this->badgeSystem->checkNewBadges($data['user_id']);
            
            return [
                'success' => true,
                'consumption_id' => $consumptionId,
                'message' => 'Consommation enregistrée avec succès'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Synchroniser les données offline
     */
    public function syncOfflineData(array $consumptions): array {
        $synced = [];
        $errors = [];
        
        foreach ($consumptions as $consumption) {
            try {
                $result = $this->addConsumption($consumption);
                if ($result['success']) {
                    $synced[] = $result['consumption_id'];
                } else {
                    $errors[] = $result['error'];
                }
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
        
        return [
            'synced_count' => count($synced),
            'error_count' => count($errors),
            'synced_ids' => $synced,
            'errors' => $errors
        ];
    }
}