<?php
// PHP Script to execute database schema updates for Campaign Outbound Link click tracking
require_once __DIR__ . '/db_connection.php';

try {
    echo "Creating campaign_links table...<br>";
    
    // Create campaign_links table
    $sql = "CREATE TABLE IF NOT EXISTS campaign_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        token VARCHAR(255) UNIQUE NOT NULL,
        target_url TEXT NOT NULL,
        member_id INT NULL,
        staff_id VARCHAR(50) NULL,
        discount_rate VARCHAR(50) NULL,
        promo_code VARCHAR(100) NULL,
        click_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL
    )";
    $pdo->exec($sql);
    echo "Successfully created campaign_links table and connected foreign keys!<br>";
} catch (Exception $e) {
    echo "Failed to execute database schema updates: " . $e->getMessage() . "<br>";
}
