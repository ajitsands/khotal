<?php
require_once __DIR__ . '/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    // Load settings from Database
    $timezone = 'Asia/Bahrain';
    $currency = 'BHD';
    $pointsRules = [];

    try {
        $stmtSettings = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $dbSettings = $stmtSettings->fetchAll();
        foreach ($dbSettings as $sett) {
            if ($sett['setting_key'] === 'timezone') $timezone = $sett['setting_value'];
            if ($sett['setting_key'] === 'currency') $currency = $sett['setting_value'];
            if ($sett['setting_key'] === 'fb_points_rules') $pointsRules = json_decode($sett['setting_value'], true);
        }
    } catch (PDOException $e) {
        // If settings table doesn't exist yet, fallback silently
    }

    // Set dynamic timezone
    date_default_timezone_set($timezone);

    // Define Currency constant
    define('CURRENCY', $currency);

    // Make points rules accessible globally
    $GLOBALS['fb_points_rules'] = $pointsRules;
} catch (PDOException $e) {
    // Return standard JSON database error response
    sendJSONResponse(false, null, "Database Connection Failed: " . $e->getMessage(), 500);
}
?>
