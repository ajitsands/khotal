-- Update database schema to add Settings and Verification flags
-- USE privilagecard;

-- 1. Add verification timestamp to members
ALTER TABLE members ADD COLUMN last_verified_at TIMESTAMP NULL AFTER expiry_date;

-- 2. Create Settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 3. Seed default settings
INSERT INTO settings (setting_key, setting_value) VALUES 
('timezone', 'Asia/Bahrain'),
('currency', 'BHD'),
('fb_points_rules', '[{"threshold": 50.000, "points": 10}, {"threshold": 100.000, "points": 20}]'),
('departments', '["F&B", "Front Office", "Spa"]')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- 4. Alter source_dept from ENUM to VARCHAR in spending_records
ALTER TABLE spending_records MODIFY COLUMN source_dept VARCHAR(50) NOT NULL;
