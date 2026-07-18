<?php
// PHP Script to execute SQL schema updates for user management on the server
require_once __DIR__ . '/db_connection.php';

try {
    echo "Creating admin_users table...<br>";
    
    // Create admin_users table
    $sql = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'counter') NOT NULL DEFAULT 'counter',
        status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Successfully created admin_users table!<br>";

    // Seed default admin and counter users
    $adminPasswordHash = password_hash('admin123', PASSWORD_BCRYPT);
    $counterPasswordHash = password_hash('counter123', PASSWORD_BCRYPT);
    
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password_hash, role, status) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE role=VALUES(role), status=VALUES(status)");
    
    $stmt->execute(['admin', $adminPasswordHash, 'admin', 'active']);
    $stmt->execute(['counter', $counterPasswordHash, 'counter', 'active']);
    
    echo "Successfully seeded default users: admin/admin123 and counter/counter123!<br>";
} catch (Exception $e) {
    echo "Failed to execute schema updates: " . $e->getMessage() . "<br>";
}
