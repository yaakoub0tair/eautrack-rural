<?php
require_once __DIR__ . '/../core/Model.php';

class Consumption extends Model {
    protected string $table = 'consumptions';
    
    /**
     * Ajouter une consommation avec validation
     */
    public function addConsumption(array $data): int {
        // Validation
        $errors = $this->validate($data);
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        // Ajout date/heure si non fourni
        if (empty($data['date'])) {
            $data['date'] = date('Y-m-d');
        }
        if (empty($data['time'])) {
            $data['time'] = date('H:i:s');
        }
        
        return $this->create($data);
    }
    
    /**
     * Obtenir le total consommé aujourd'hui pour un profil
     */
    public function getDailyTotal(int $userId, string $date = null): float {
        $date = $date ?? date('Y-m-d');
        
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(volume), 0) as total
            FROM {$this->table}
            WHERE user_id = :user_id AND date = :date
        ");
        
        $stmt->execute(['user_id' => $userId, 'date' => $date]);
        $result = $stmt->fetch();
        
        return (float) $result['total'];
    }
    
    /**
     * Obtenir la répartition par activité (pour graphiques)
     */
    public function getActivityBreakdown(int $userId, string $date = null): array {
        $date = $date ?? date('Y-m-d');
        
        $stmt = $this->db->prepare("
            SELECT 
                ar.name as activity,
                SUM(c.volume) as total_volume,
                COUNT(*) as count
            FROM {$this->table} c
            JOIN activity_references ar ON c.activity_id = ar.id
            WHERE c.user_id = :user_id AND c.date = :date
            GROUP BY c.activity_id, ar.name
            ORDER BY total_volume DESC
        ");
        
        $stmt->execute(['user_id' => $userId, 'date' => $date]);
        return $stmt->fetchAll();
    }
    
    /**
     * Détecter une fuite potentielle (consommation anormalement élevée)
     */
    public function detectLeak(int $userId): bool {
        $stmt = $this->db->prepare("
            SELECT volume 
            FROM {$this->table}
            WHERE user_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        
        $stmt->execute(['user_id' => $userId]);
        $last = $stmt->fetch();
        
        // Si dernière consommation > 200L, possible fuite
        return $last && $last['volume'] > 200;
    }
    
    /**
     * Validation des données
     */
    private function validate(array $data): array {
        $errors = [];
        
        if (empty($data['user_id'])) {
            $errors[] = "L'utilisateur est obligatoire";
        }
        
        if (empty($data['activity_id'])) {
            $errors[] = "L'activité est obligatoire";
        }
        
        if (!isset($data['volume']) || $data['volume'] <= 0 || $data['volume'] > 1000) {
            $errors[] = "Le volume doit être entre 1L et 1000L";
        }
        
        return $errors;
    }
}