-- CREATE DATABASE IF NOT EXISTS privilagecard;
-- USE privilagecard;

-- 1. Users / Members Table
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    membership_number VARCHAR(20) UNIQUE NOT NULL, -- Format: KP-XXXX (K Plus) or KR-XXXX (K Reward)
    title VARCHAR(10),
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    id_number VARCHAR(30) NOT NULL, -- CPR or Passport Number
    nationality VARCHAR(50) NOT NULL,
    address TEXT,
    mobile VARCHAR(20) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    company_name VARCHAR(100),
    position VARCHAR(50),
    membership_type ENUM('K Plus', 'K Reward') NOT NULL,
    card_type ENUM('Silver', 'Gold', 'Brown', 'Booker') NOT NULL,
    status ENUM('Pending Approval', 'Active', 'Inactive', 'Expired') DEFAULT 'Active',
    expiry_date DATE,
    password_hash VARCHAR(255) NOT NULL, -- For Mobile App login
    approved_by VARCHAR(50), -- For Gold cards requiring GM approval
    approved_at TIMESTAMP NULL,
    gold_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Staff Incentives Table (for K Plus cards sold by staff)
CREATE TABLE IF NOT EXISTS staff_incentives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(30) NOT NULL,
    staff_name VARCHAR(100) NOT NULL,
    department VARCHAR(50) NOT NULL,
    member_id INT NOT NULL, -- The K Plus member registered
    spending_id INT NULL, -- The spending record if incentive is from spending
    incentive_amount DECIMAL(10, 3) DEFAULT 5.000, -- 5.000 BHD for Silver
    status ENUM('Pending', 'Approved', 'Paid') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    paid_at TIMESTAMP NULL,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
);

-- 3. Staff Directory Table (to track authorized staff members and incentive percentages)
CREATE TABLE IF NOT EXISTS staff_directory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(30) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    department VARCHAR(50) NOT NULL,
    incentive_pct DECIMAL(5, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 4. Vouchers Table (vouchers issued to members)
CREATE TABLE IF NOT EXISTS vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    voucher_number VARCHAR(30) UNIQUE NOT NULL,
    voucher_type ENUM(
        'K Plus Room', 'K Plus Brunch', 'K Plus Thai Massage',
        'K Reward Meal', 'K Reward Fitness', 'K Reward Gift', 'K Reward Free Night'
    ) NOT NULL,
    description VARCHAR(255) NOT NULL,
    receipt_number VARCHAR(50), -- For K Plus Silver
    receipt_amount DECIMAL(10, 3), -- 55.000 BHD for K Plus Silver
    status ENUM('Active', 'Used', 'Expired') DEFAULT 'Active',
    issued_date DATE NOT NULL,
    valid_until DATE NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
);

-- 5. Points Ledger Table (for K Reward bookers)
CREATE TABLE IF NOT EXISTS points_ledger (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    booking_reference VARCHAR(50) NOT NULL, -- Opera booking / invoice reference
    points_earned INT DEFAULT 0,
    points_redeemed INT DEFAULT 0,
    transaction_type ENUM('Earned', 'Redeemed', 'Adjusted') NOT NULL,
    source ENUM('Room Booking', 'Event Booking', 'Restaurant Booking', 'Manual Adjustment', 'Voucher Redemption') NOT NULL,
    description VARCHAR(255),
    transaction_date DATE NOT NULL,
    expiry_date DATE, -- K Reward points expire in 1 year
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
);

-- 6. Spending Records Table (for tracking and upgrading members to K Plus Gold)
CREATE TABLE IF NOT EXISTS spending_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    amount DECIMAL(10, 3) NOT NULL,
    source_dept VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
);

-- 7. Redemption Requests Table (for K Reward point redemptions)
CREATE TABLE IF NOT EXISTS redemption_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    award_title VARCHAR(100) NOT NULL, -- e.g. "Dinner for two at KOLORS Restaurant"
    points_cost INT NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    approved_by VARCHAR(50),
    approved_at TIMESTAMP NULL,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
);

-- Indexing for performance
CREATE INDEX idx_members_number ON members(membership_number);
CREATE INDEX idx_members_email ON members(email);
CREATE INDEX idx_vouchers_member ON vouchers(member_id);
CREATE INDEX idx_points_member ON points_ledger(member_id);
CREATE INDEX idx_spending_member ON spending_records(member_id);

-- Foreign key constraints added after table creation to avoid order issues
ALTER TABLE staff_incentives ADD CONSTRAINT fk_staff_incentives_spending FOREIGN KEY (spending_id) REFERENCES spending_records(id) ON DELETE CASCADE;

-- Insert dummy admin user into members (with admin credentials, password: 'password123')
-- Admin can use the same members table or we can login as admin using a config-defined user.
-- Let's add a couple of dummy members for demonstration.
INSERT INTO members (membership_number, title, first_name, last_name, id_number, nationality, mobile, email, company_name, position, membership_type, card_type, status, expiry_date, password_hash)
VALUES 
('KP-1001', 'Mr.', 'John', 'Doe', '900101234', 'British', '+97333333333', 'john.doe@example.com', 'AeroCorp', 'Director', 'K Plus', 'Silver', 'Active', DATE_ADD(CURRENT_DATE, INTERVAL 1 YEAR), '$2y$10$Y1s4574K5m6Q1tF9W2lDfeVjH2P9dC.Z1L2aJtW8nO7u2C9k5V6t.'), -- password123
('KR-2001', 'Mrs.', 'Sarah', 'Smith', '881212987', 'Bahraini', '+97339999999', 'sarah.smith@example.com', 'Gulf Air', 'Corporate Booker', 'K Reward', 'Booker', 'Active', DATE_ADD(CURRENT_DATE, INTERVAL 1 YEAR), '$2y$10$Y1s4574K5m6Q1tF9W2lDfeVjH2P9dC.Z1L2aJtW8nO7u2C9k5V6t.'); -- password123

-- Add initial points to Sarah Smith
INSERT INTO points_ledger (member_id, booking_reference, points_earned, points_redeemed, transaction_type, source, description, transaction_date, expiry_date)
VALUES 
(2, 'OP-998877', 50, 0, 'Earned', 'Room Booking', 'Booked 5 Junior Suite nights', CURRENT_DATE, DATE_ADD(CURRENT_DATE, INTERVAL 1 YEAR)),
(2, 'OP-998878', 15, 0, 'Earned', 'Restaurant Booking', 'F&B Bill 600.000 BHD at KOLORS', CURRENT_DATE, DATE_ADD(CURRENT_DATE, INTERVAL 1 YEAR));
