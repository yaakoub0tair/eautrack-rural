<?php
require_once __DIR__ . '/../core/Model.php';

class UserProfile extends Model {
    protected string $table = 'user_profiles';
    
    /**
     * Calculer le quota total journalier d'un profil
     */
    public function calculateTotalQuota(int $profileId): float {
        $profile = $this->findById($profileId);
        if (!$profile) return 0;
        
        return $profile['quota_jour'] * $profile['nb_personnes'];
    }
    
    /**
     * Obtenir le score de gaspillage (0-100)
     * Plus le score est bas, mieux c'est
     */
    public function getWasteScore(int $profileId): int {
        $sql = "
            SELECT 
                SUM(c.volume) as total_consumed,
                (SELECT quota_jour * nb_personnes FROM user_profiles WHERE id = :profile_id) as quota_total
            FROM consumptions c
            WHERE c.user_id = :profile_id 
            AND c.date = CURDATE()
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['profile_id' => $profileId]);
        $data = $stmt->fetch();
        
        if (!$data || $data['total_consumed'] == 0) return 0;
        
        $ratio = ($data['total_consumed'] / $data['quota_total']) * 100;
        return min(100, (int) $ratio);
    }
    
    /**
     * Valider les données avant insertion/update
     */
    public function validate(array $data): array {
        $errors = [];
        
        if (empty($data['nom'])) {
            $errors[] = "Le nom est obligatoire";
        }
        
        if (!isset($data['nb_personnes']) || $data['nb_personnes'] < 1) {
            $errors[] = "Le nombre de personnes doit être ≥ 1";
        }
        
        if (!isset($data['quota_jour']) || $data['quota_jour'] < QUOTA_MIN || $data['quota_jour'] > QUOTA_MAX) {
            $errors[] = "Le quota doit être entre " . QUOTA_MIN . "L et " . QUOTA_MAX . "L";
        }
        
        return $errors;
    }
}