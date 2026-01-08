<?php
//congig/constants.php
// Seuils d'alertes
define('ALERT_THRESHOLD_LOW', 0.5);     // 50% du quota
define('ALERT_THRESHOLD_MEDIUM', 0.8);  // 80% du quota
define('ALERT_THRESHOLD_HIGH', 1.0);    // 100% du quota

// Niveaux d'alertes
define('ALERT_LEVEL_INFO', 1);
define('ALERT_LEVEL_WARNING', 2);
define('ALERT_LEVEL_CRITICAL', 3);

// Quotas recommandés (litres/personne/jour)
define('QUOTA_MIN', 50);
define('QUOTA_RECOMMENDED', 150);
define('QUOTA_MAX', 500);

// Types de badges
define('BADGE_ECO_WARRIOR', 'eco_warrior');      // 7 jours sous quota
define('BADGE_WATER_SAVER', 'water_saver');      // Réduction ≥20%
define('BADGE_WEEK_CHAMPION', 'week_champion');  // Meilleure semaine
define('BADGE_MONTH_HERO', 'month_hero');        // Meilleur mois