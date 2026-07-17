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
('departments', '["F&B", "Front Office", "Spa"]'),
('gold_upgrade_threshold', '500.000'),
('hotel_name', 'The K Hotel'),
('hotel_sub', 'BAHRAIN')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- 3b. Seed default redeemable vouchers in settings
INSERT INTO settings (setting_key, setting_value) VALUES 
('redeemable_vouchers', '[{"id":"v1","name":"Lunch for two at KOLORS Restaurant","points":15,"category":"meals","description":"Lunch Buffet for two at KOLORS Restaurant"},{"id":"v2","name":"Dinner for two at KOLORS Restaurant","points":20,"category":"meals","description":"Dinner Buffet for two at KOLORS Restaurant"},{"id":"v3","name":"Lunch or Dinner for two at the K Lounge","points":35,"category":"meals","description":"Lunch or Dinner menu for two at the K Lounge"},{"id":"v4","name":"Friday Brunch for two at KOLORS Restaurant","points":50,"category":"meals","description":"Friday Brunch Buffet for two at KOLORS Restaurant"},{"id":"v5","name":"1 Month health club membership (single)","points":30,"category":"fitness","description":"1 Month health club fitness membership (single)"},{"id":"v6","name":"1 Month health club membership (couple)","points":50,"category":"fitness","description":"1 Month health club fitness membership (couple)"},{"id":"v7","name":"3 Month health club membership (single)","points":100,"category":"fitness","description":"3 Month health club fitness membership (single)"},{"id":"v8","name":"3 Month health club membership (couple)","points":150,"category":"fitness","description":"3 Month health club fitness membership (couple)"},{"id":"v9","name":"20.000 BHD gift voucher","points":20,"category":"gift","description":"20.000 BHD gift certificate"},{"id":"v10","name":"50.000 BHD gift voucher","points":50,"category":"gift","description":"50.000 BHD gift certificate"},{"id":"v11","name":"75.000 BHD gift voucher","points":75,"category":"gift","description":"75.000 BHD gift certificate"},{"id":"v12","name":"100.000 BHD gift voucher","points":100,"category":"gift","description":"100.000 BHD gift certificate"},{"id":"v13","name":"One night in a deluxe room","points":50,"category":"nights","description":"1 Night stay in a deluxe room for two"},{"id":"v14","name":"One night in a Junior Suite","points":75,"category":"nights","description":"1 Night stay in a Junior Suite for two"},{"id":"v15","name":"One night in a Senior Suite","points":100,"category":"nights","description":"1 Night stay in a Senior Suite for two"},{"id":"v16","name":"One night in the Amiri Suite","points":150,"category":"nights","description":"1 Night stay in the Amiri Suite for two"},{"id":"v17","name":"One night in the Royal Suite","points":250,"category":"nights","description":"1 Night stay in the Royal Suite for two"}]')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- 4. Alter source_dept from ENUM to VARCHAR in spending_records
ALTER TABLE spending_records MODIFY COLUMN source_dept VARCHAR(50) NOT NULL;

-- 5. Create staff_directory table
CREATE TABLE IF NOT EXISTS staff_directory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(30) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    department VARCHAR(50) NOT NULL,
    incentive_pct DECIMAL(5, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 6. Add spending_id column and foreign key constraint to staff_incentives table
ALTER TABLE staff_incentives ADD COLUMN spending_id INT NULL AFTER member_id;
ALTER TABLE staff_incentives ADD CONSTRAINT fk_staff_incentives_spending FOREIGN KEY (spending_id) REFERENCES spending_records(id) ON DELETE CASCADE;
