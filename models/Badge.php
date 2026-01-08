<?php
// models/Badge.php
require_once __DIR__ . '/../core/Model.php';

class Badge extends Model {
    protected string $table = 'badges';
    
    /**
     * Attribuer un badge à un utilisateur (si pas déjà obtenu)
     */
    public function awardBadge(int $userId, string $badgeType): bool {
        // Vérifier si le badge existe déjà
        if ($this->hasBadge($userId, $badgeType)) {
            return false; // Badge déjà obtenu
        }
        
        $this->create([
            'user_id' => $userId,
            'badge_type' => $badgeType
        ]);
        
        return true;
    }
    
    /**
     * Vérifier si un utilisateur a déjà un badge
     */
    public function hasBadge(int $userId, string $badgeType): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM {$this->table}
            WHERE user_id = :user_id AND badge_type = :badge_type
        ");
        
        $stmt->execute(['user_id' => $userId, 'badge_type' => $badgeType]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Obtenir tous les badges d'un utilisateur
     */
    public function getUserBadges(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE user_id = :user_id
            ORDER BY earned_at DESC
        ");
        
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtenir le nombre total de badges d'un utilisateur
     */
    public function getUserBadgeCount(int $userId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM {$this->table}
            WHERE user_id = :user_id
        ");
        
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();
        
        return (int) $result['count'];
    }
}