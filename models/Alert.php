<?php
// models/Alert.php
require_once __DIR__ . '/../core/Model.php';

class Alert extends Model {
    protected string $table = 'alerts';
    
    /**
     * Créer une alerte
     */
    public function createAlert(int $userId, int $consumptionId, int $level, string $message): int {
        return $this->create([
            'user_id' => $userId,
            'consumption_id' => $consumptionId,
            'level' => $level,
            'message' => $message
        ]);
    }
    
    /**
     * Obtenir les alertes actives d'un utilisateur (aujourd'hui)
     */
    public function getActiveAlerts(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE user_id = :user_id 
            AND DATE(created_at) = CURDATE()
            ORDER BY level DESC, created_at DESC
        ");
        
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Vérifier si une alerte de même niveau existe déjà aujourd'hui
     */
    public function alertExistsToday(int $userId, int $level): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM {$this->table}
            WHERE user_id = :user_id 
            AND level = :level
            AND DATE(created_at) = CURDATE()
        ");
        
        $stmt->execute(['user_id' => $userId, 'level' => $level]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
}