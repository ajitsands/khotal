<?php
require_once __DIR__ . '/../config/session_helper.php';
if (!isset($_SESSION['admin_user_id'])) {
    header('Location: login.php');
    exit;
}

$userRole = $_SESSION['admin_role'] ?? 'counter';
$currentUsername = $_SESSION['admin_username'] ?? 'User';

require_once __DIR__ . '/../config/db_connection.php';

// Fetch settings
$hotelName = 'The K Hotel';
$hotelSub = 'BAHRAIN';
$hotelLogo = '';

try {
    $stmtS = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $rows = $stmtS->fetchAll();
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    if (isset($settings['hotel_name'])) {
        $hotelName = $settings['hotel_name'];
    }
    if (isset($settings['hotel_sub'])) {
        $hotelSub = $settings['hotel_sub'];
    }
    if (isset($settings['hotel_logo'])) {
        $hotelLogo = $settings['hotel_logo'];
    }
} catch (PDOException $e) {
    // Fallback defaults
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hotelName); ?> Loyalty Program - Admin Control Center</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <!-- DataTables CSS & JS CDN -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        :root {
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --primary: #d97706; /* Elegant Dark Gold */
            --primary-hover: #b45309;
            --accent-gold: #b45309; /* Darker gold for text visibility in light mode */
            --success: #10b981;
            --danger: #ef4444;
            --border-color: #e2e8f0;
            --sidebar-width: 260px;
            
            /* Sidebar remains dark for high contrast visual structure */
            --sidebar-bg: #0f172a;
            --sidebar-text: #94a3b8;
            --sidebar-header: #ffffff;
            
            --input-bg: #ffffff;
            --input-text: #0f172a;
            --table-header-bg: #f1f5f9;
        }

        [data-theme="dark"] {
            --bg-color: #0b0f19;
            --card-bg: #151f32;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --accent-gold: #fbbf24;
            --border-color: #1e293b;
            
            --sidebar-bg: #0e1626;
            --sidebar-text: #9ca3af;
            --sidebar-header: #ffffff;
            
            --input-bg: #0e1626;
            --input-text: #ffffff;
            --table-header-bg: #0e1626;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            scrollbar-width: thin;
            scrollbar-color: var(--primary) var(--bg-color);
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Styling (Now Horizontal Top Menu) */
        .sidebar {
            width: 100%;
            height: 70px;
            background-color: var(--sidebar-bg);
            border-bottom: 1px solid var(--border-color);
            border-right: none;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            top: 0;
            left: 0;
            padding: 0 40px;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 0;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: none;
        }

        .sidebar-brand i {
            color: var(--accent-gold);
            font-size: 24px;
        }

        .brand-text h1 {
            font-size: 18px;
            font-weight: 700;
            color: var(--sidebar-header);
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .brand-text span {
            font-size: 9px;
            color: var(--primary);
            text-transform: uppercase;
            font-weight: 600;
            display: block;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            display: flex;
            flex-direction: row;
            gap: 8px;
            align-items: center;
        }

        .menu-item a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .menu-item a:hover, .menu-item.active a {
            background-color: rgba(217, 119, 6, 0.15);
            color: var(--accent-gold);
        }

        .menu-item.active a i {
            color: var(--accent-gold);
        }

        /* Main Content Panel */
        .main-content {
            margin-left: 0;
            flex: 1;
            padding: 110px 40px 40px 40px; /* Top padding accommodates fixed horizontal menu */
            min-height: 100vh;
            background-image: radial-gradient(circle at top right, rgba(217, 119, 6, 0.05), transparent 400px);
        }

        .header-panel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header-title h2 {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-main);
        }

        .header-title p {
            color: var(--text-muted);
            font-size: 14px;
            margin-top: 4px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
        }

        .stat-details h3 {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-details .number {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-main);
            margin-top: 8px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background-color: rgba(217, 119, 6, 0.1);
            color: var(--accent-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        /* Tab Content Views */
        .view-pane {
            display: none;
            animation: fadeIn 0.4s ease forwards;
        }

        .view-pane.active-pane {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Forms Layout */
        .form-section {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--accent-gold);
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full-width {
            grid-column: span 3;
        }

        label {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input, select, textarea {
            background-color: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--input-text);
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--primary);
        }

        .btn {
            background-color: var(--primary);
            color: #fff;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn:hover {
            background-color: var(--primary-hover);
        }

        .btn-secondary {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-main);
        }

        .btn-secondary:hover {
            background-color: var(--border-color);
        }

        .btn-danger {
            background-color: var(--danger);
        }
        .btn-danger:hover {
            background-color: #d32f2f;
        }

        /* Tables & Lists */
        .table-container {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            background-color: var(--table-header-bg);
            color: var(--text-muted);
            padding: 16px 20px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
        }

        td {
            padding: 16px 20px;
            font-size: 14px;
            color: var(--text-main);
            border-bottom: 1px solid var(--border-color);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: rgba(217, 119, 6, 0.03);
        }

        /* Badge status */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-active {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .badge-pending {
            background-color: rgba(217, 119, 6, 0.1);
            color: var(--primary);
        }

        .badge-expired {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            width: 500px;
            max-width: 90%;
            animation: modalSlide 0.3s ease;
        }

        @keyframes modalSlide {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: var(--card-bg);
            border-left: 4px solid var(--primary);
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.5);
            z-index: 2000;
            display: none;
            align-items: center;
            gap: 12px;
        }

        /* Output text encryption UI */
        .encrypted-result-box {
            background-color: var(--input-bg);
            border: 1px dashed var(--primary);
            padding: 16px;
            border-radius: 8px;
            margin-top: 20px;
            word-break: break-all;
            display: none;
        }

        /* DataTables Custom Theme Styling */
        .dataTables_wrapper {
            margin-top: 15px;
            font-family: 'Outfit', sans-serif;
        }
        .dataTables_wrapper .dataTables_length, 
        .dataTables_wrapper .dataTables_filter {
            color: var(--text-main) !important;
            margin-bottom: 15px;
        }
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            background-color: var(--card-bg) !important;
            color: var(--text-main) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 6px !important;
            padding: 6px 12px !important;
            font-size: 13px !important;
            outline: none;
        }
        .dataTables_wrapper .dataTables_filter input {
            width: 250px;
            margin-left: 10px;
        }
        .dataTables_wrapper .dataTables_info {
            color: var(--text-muted) !important;
            font-size: 12px;
            padding-top: 12px;
        }
        .dataTables_wrapper .dataTables_paginate {
            padding-top: 12px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            background: var(--bg-color) !important;
            color: var(--text-muted) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 4px !important;
            padding: 5px 10px !important;
            margin: 0 2px !important;
            font-size: 12px;
            transition: all 0.2s ease;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--primary) !important;
            color: white !important;
            border-color: var(--primary) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: var(--primary) !important;
            color: white !important;
            border-color: var(--primary) !important;
            font-weight: bold;
        }
        table.dataTable {
            border-collapse: collapse !important;
            margin-top: 15px !important;
            width: 100% !important;
            border: 1px solid var(--border-color) !important;
        }
        table.dataTable thead th {
            background-color: var(--table-header-bg) !important;
            color: var(--text-main) !important;
            font-weight: 600 !important;
            border-bottom: 2px solid var(--border-color) !important;
            padding: 12px 10px !important;
        }
        table.dataTable tbody td {
            padding: 12px 10px !important;
            border-bottom: 1px solid var(--border-color) !important;
            color: var(--text-main) !important;
        }
        table.dataTable.no-footer {
            border-bottom: 1px solid var(--border-color) !important;
        }
    </style>
</head>
<body>

    <!-- Sidebar Menu -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <?php if (!empty($hotelLogo)): ?>
                <img src="<?php echo htmlspecialchars($hotelLogo); ?>" style="max-height: 40px; max-width: 40px; border-radius: 6px; object-fit: contain;">
            <?php else: ?>
                <i class="fa-solid fa-hotel"></i>
            <?php endif; ?>
            <div class="brand-text">
                <h1><?php echo htmlspecialchars($hotelName); ?></h1>
                <span>Loyalty Admin Portal</span>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item active" data-view="dashboard">
                <a href="#"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            </li>
            <li class="menu-item" data-view="enrolment">
                <a href="#"><i class="fa-solid fa-user-plus"></i> New Enrolment</a>
            </li>
            <li class="menu-item" data-view="members">
                <a href="#"><i class="fa-solid fa-users"></i> Member Directory</a>
            </li>
            <li class="menu-item" data-view="redemptions">
                <a href="#"><i class="fa-solid fa-ticket"></i> Redemptions</a>
            </li>
            <li class="menu-item" data-view="incentives">
                <a href="#"><i class="fa-solid fa-wallet"></i> Staff Incentives</a>
            </li>
            <li class="menu-item" data-view="security">
                <a href="#"><i class="fa-solid fa-shield-halved"></i> Link Encryption</a>
            </li>
            <?php if ($userRole === 'admin'): ?>
            <li class="menu-item" data-view="users">
                <a href="#"><i class="fa-solid fa-users-gear"></i> User Management</a>
            </li>
            <li class="menu-item" data-view="settings">
                <a href="#"><i class="fa-solid fa-gear"></i> Settings</a>
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Main Content Panel -->
    <div class="main-content">
        
        <!-- Header Panel -->
        <div class="header-panel">
            <div class="header-title">
                <h2 id="view-title">Dashboard Overview</h2>
                <p id="view-subtitle">Real-time statistics of loyalty programs</p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <span style="color: var(--text-muted); font-size: 13px; font-weight: 500; margin-right: 10px;"><i class="fa-solid fa-circle-user"></i> Hello, <strong><?php echo htmlspecialchars($currentUsername); ?></strong> (<span style="text-transform: capitalize;"><?php echo htmlspecialchars($userRole); ?></span>)</span>
                <button class="btn btn-secondary" onclick="openChangePasswordModal()"><i class="fa-solid fa-key"></i> Change Password</button>
                <button class="btn btn-secondary" id="theme-toggle-btn" onclick="toggleTheme()"><i class="fa-solid fa-moon"></i> Dark Mode</button>
                <button class="btn btn-secondary" onclick="loadAllData()"><i class="fa-solid fa-arrows-rotate"></i> Refresh</button>
                <a href="logout.php" class="btn btn-secondary" style="border-color: var(--danger); color: var(--danger); text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 5px;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>

        <!-- Dashboard View Pane -->
        <div id="view-dashboard" class="view-pane active-pane">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-details">
                        <h3>K Plus Members</h3>
                        <div class="number" id="stat-kplus-count">0</div>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-id-card"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-details">
                        <h3>K Reward Bookers</h3>
                        <div class="number" id="stat-kreward-count">0</div>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-award"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-details">
                        <h3>Pending Redemptions</h3>
                        <div class="number" id="stat-redemptions-count">0</div>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-gift"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-details">
                        <h3>Staff Incentives (BHD)</h3>
                        <div class="number" id="stat-incentives-count">0.000</div>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-coins"></i></div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-title"><i class="fa-solid fa-bell"></i> Action Items Required</div>
                <div id="dashboard-actions-list">
                    <p style="color: var(--text-muted)">Loading outstanding items...</p>
                </div>
            </div>
        </div>

        <!-- Enrolment Form View Pane -->
        <div id="view-enrolment" class="view-pane">
            <form id="enrolmentForm">
                <input type="hidden" name="id" id="enrol-member-edit-id">
                <!-- Page 5: Guest Details -->
                <div class="form-section">
                    <div class="section-title" id="enrol-form-title"><i class="fa-solid fa-user"></i> Guest Details</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Title</label>
                            <select name="title">
                                <option value="Mr.">Mr.</option>
                                <option value="Mrs.">Mrs.</option>
                                <option value="Ms.">Ms.</option>
                                <option value="Dr.">Dr.</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>First Name *</label>
                            <input type="text" name="first_name" required placeholder="Guest first name">
                        </div>
                        <div class="form-group">
                            <label>Last Name *</label>
                            <input type="text" name="last_name" required placeholder="Guest last name">
                        </div>
                        <div class="form-group">
                            <label>ID Number (CPR or Passport) *</label>
                            <input type="text" name="id_number" required placeholder="Civil ID or Passport">
                        </div>
                        <div class="form-group">
                            <label>Nationality *</label>
                            <input type="text" name="nationality" required placeholder="Guest nationality">
                        </div>
                        <div class="form-group">
                            <label>Mobile *</label>
                            <input type="text" name="mobile" required placeholder="Mobile phone number">
                        </div>
                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" required placeholder="example@email.com">
                        </div>
                        <div class="form-group">
                            <label>Company Name</label>
                            <input type="text" name="company_name" placeholder="If business / booker">
                        </div>
                        <div class="form-group">
                            <label>Position</label>
                            <input type="text" name="position" placeholder="Job Title">
                        </div>
                        <div class="form-group full-width">
                            <label>Home/Mailing Address</label>
                            <textarea name="address" rows="3" placeholder="Full postal address"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Page 5: Application Details -->
                <div class="form-section">
                    <div class="section-title"><i class="fa-solid fa-file-contract"></i> Application Details</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Membership Program</label>
                            <select name="membership_type" id="enroll-type-select">
                                <option value="K Plus">The K Plus (Regular Guest)</option>
                                <option value="K Reward">The K Reward (Booker)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Card Tier</label>
                            <select name="card_type" id="enroll-tier-select">
                                <option value="Silver">Silver (55.000 BHD)</option>
                                <option value="Gold">Gold (VIP - GM Approval Required)</option>
                                <option value="Brown">Brown (Walk-in / Promo)</option>
                            </select>
                        </div>
                        <div class="form-group" id="voucher-select-group">
                            <label>Voucher Choice (K Plus Silver/Brown)</label>
                            <select name="voucher_type">
                                <option value="N/A">None (N/A)</option>
                                <option value="Room">1 Night Deluxe Room Stay for 2</option>
                                <option value="Brunch">Brunch Buffet for 2</option>
                                <option value="Thai Massage">Thai Massage for 2</option>
                            </select>
                        </div>
                        <div class="form-group" id="receipt-num-group">
                            <label>Receipt Number (For Silver Card)</label>
                            <input type="text" name="receipt_number" placeholder="Opera Payment Receipt">
                        </div>
                        <div class="form-group" id="receipt-amt-group">
                            <label>Receipt Amount (BHD)</label>
                            <input type="number" step="0.001" name="receipt_amount" value="55.000" placeholder="55.000">
                        </div>
                        <div class="form-group full-width" id="gold-reason-group" style="display:none;">
                            <label>Gold Membership Upgrade Reason</label>
                            <input type="text" name="gold_reason" placeholder="State reason for GM approval recommendation">
                        </div>
                    </div>
                </div>

                <!-- Page 5: Employee Details -->
                <div class="form-section">
                    <div class="section-title"><i class="fa-solid fa-id-badge"></i> Enrolled By (Employee Details)</div>
                    <div class="form-grid">
                        <div class="form-group" style="grid-column: span 3;">
                            <label>Select Referrer Staff Member</label>
                            <select id="enrol-staff-select" onchange="onEnrolStaffChange(this.value)">
                                <option value="">-- Choose Registered Staff Member --</option>
                            </select>
                        </div>
                        <input type="hidden" name="staff_id" id="enrol-staff-id">
                        <input type="hidden" name="staff_name" id="enrol-staff-name">
                        <input type="hidden" name="staff_dept" id="enrol-staff-dept">
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button type="button" class="btn btn-secondary" style="display: none;" id="enrol-cancel-btn" onclick="cancelMemberEdit()">Cancel Edit</button>
                        <button type="submit" class="btn" id="enrol-submit-btn"><i class="fa-solid fa-save"></i> Save Loyalty Enrolment</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Member Directory View Pane -->
        <div id="view-members" class="view-pane">
            <div class="form-section">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                     <div class="section-title" style="margin-bottom:0; border-bottom:none; padding-bottom:0;"><i class="fa-solid fa-address-book"></i> Privileged Members</div>
                </div>
                <!-- Card Number Prefix Legend -->
                <div style="margin-bottom: 16px; padding: 8px 14px; background: rgba(251,191,36,0.05); border: 1px solid rgba(251,191,36,0.15); border-radius: 8px; font-size: 11px; color: var(--text-muted); display: flex; flex-wrap: wrap; gap: 6px 20px; align-items: center;">
                    <span style="color: var(--accent-gold); font-weight: 700; margin-right: 4px;"><i class="fa-solid fa-circle-info"></i> Card Prefixes:</span>
                    <span><strong style="color: var(--text-main);">4500</strong> → K Plus Silver</span>
                    <span><strong style="color: #fbbf24;">4510</strong> → K Plus Gold</span>
                    <span><strong style="color: #92613d;">4520</strong> → K Plus Brown</span>
                    <span><strong style="color: #64748b;">4600</strong> → K Reward Booker</span>
                </div>
                <div class="table-container">
                    <table id="membersTable" class="display" style="width: 100%;">
                        <thead>
                            <tr>
                                 <th>Card Number</th>
                                 <th>Name</th>
                                 <th>Mobile</th>
                                 <th>Nationality</th>
                                 <th>Program</th>
                                 <th>Points</th>
                                 <th>Card Tier</th>
                                 <th>Status</th>
                                 <th>Expiry Date</th>
                                 <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="member-table-body">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Redemptions View Pane -->
        <div id="view-redemptions" class="view-pane">
            <div class="form-section">
                <div class="section-title"><i class="fa-solid fa-clipboard-list"></i> Booker Point Redemptions</div>
                <p style="color: var(--text-muted); margin-bottom: 20px; font-size: 14px;">Review and approve/reject point redemption claims requested from the mobile app by bookers.</p>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Req ID</th>
                                <th>Card No</th>
                                <th>Member Name</th>
                                <th>Award Claimed</th>
                                <th>Points Cost</th>
                                <th>Submitted Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="redemptions-table-body">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Staff Incentives View Pane -->
        <div id="view-incentives" class="view-pane">
            <div class="form-section">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                    <div class="section-title" style="margin-bottom:0; border-bottom:none; padding-bottom:0;"><i class="fa-solid fa-file-invoice-dollar"></i> Staff Enrolment Incentives</div>
                    <button class="btn" onclick="openStaffDirectoryModal()"><i class="fa-solid fa-users-gear"></i> Manage Staff Directory</button>
                </div>
                <p style="color: var(--text-muted); margin-bottom: 20px; font-size: 14px;">Tracks staff commissions (Silver registration commissions & dynamic spend split incentives). Click "Manage Staff Directory" to add and manage eligible hotel employees.</p>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Staff Name</th>
                                <th>Department</th>
                                <th>Member Registered</th>
                                <th>Card Sold</th>
                                <th>Incentive</th>
                                <th>Date Sold</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="incentives-table-body">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Link Encryption View Pane -->
        <div id="view-security" class="view-pane">
            <div style="display: grid; grid-template-columns: 1fr; gap: 25px;">
                <!-- Link Generator Card -->
                <div class="form-section">
                    <div class="section-title"><i class="fa-solid fa-user-shield"></i> Outbound URL Encryption & Click Tracker</div>
                    <p style="color: var(--text-muted); margin-bottom: 20px; font-size: 14px;">
                        Create secured promotion campaign links. Outbound URLs are routed through our tracking redirector to log customer hits, and contain fully-encrypted URL tokens to prevent tampering with member IDs or rates.
                    </p>
                    
                    <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Target Offer / Promotion URL *</label>
                            <input type="text" id="encrypt-target-url" value="https://thekhotel.com/promos/special_brunch.php" placeholder="Enter base promo page url" required>
                        </div>
                        <div class="form-group">
                            <label>Select Customer (Member)</label>
                            <select id="encrypt-member-select" style="width:100%;">
                                <option value="">-- No Customer Link / General Campaign --</option>
                                <!-- Populated dynamically from loaded members -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Select Staff Referrer</label>
                            <select id="encrypt-staff-select" style="width:100%;">
                                <option value="">-- No Staff Referrer --</option>
                                <!-- Populated dynamically from staff directory -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Discount Rate / Value (e.g. 20% or 10 BHD)</label>
                            <input type="text" id="encrypt-discount-rate" placeholder="e.g. 25% or 15 BHD">
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label>Promo Code</label>
                            <input type="text" id="encrypt-promo-code" placeholder="e.g. SUMMER2026">
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <button class="btn" onclick="generateSecureLink()"><i class="fa-solid fa-link"></i> Generate Secure Campaign Link</button>
                    </div>
                    
                    <div class="encrypted-result-box" id="encryption-result">
                        <h4 style="color: var(--accent-gold); margin-bottom: 8px;"><i class="fa-solid fa-check-circle"></i> Securely Tracked URL:</h4>
                        <p id="encrypted-url-text" style="font-family: monospace; font-size: 13px; color: var(--text-main); word-break: break-all; user-select: all; padding: 10px; background: rgba(0,0,0,0.2); border-radius: 6px;"></p>
                        <div style="margin-top: 15px; display: flex; gap: 10px;">
                            <button class="btn btn-secondary" onclick="copyEncryptedUrl()"><i class="fa-solid fa-copy"></i> Copy Link</button>
                        </div>
                    </div>
                </div>

                <!-- Campaigns & Click Tracker Stats -->
                <div class="form-section">
                    <div class="section-title"><i class="fa-solid fa-chart-bar"></i> Active Secure Campaigns & Hits Tracker</div>
                    <div class="table-responsive">
                        <table id="campaignsTable" class="display" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Created At</th>
                                    <th>Target URL</th>
                                    <th>Customer / Member</th>
                                    <th>Staff Referrer</th>
                                    <th>Rate / Promo Code</th>
                                    <th>Total Hits (Clicks)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="campaigns-table-body">
                                <!-- Populated dynamically via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($userRole === 'admin'): ?>
        <!-- Settings View Pane -->
        <div id="view-settings" class="view-pane">
            <form id="settingsForm">
                <!-- Timezone & Currency settings -->
                <div class="form-section">
                    <div class="section-title"><i class="fa-solid fa-earth-americas"></i> Regional Settings</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>System Timezone</label>
                            <select name="timezone" id="settings-timezone" required>
                                <option value="Asia/Bahrain">Asia/Bahrain (GMT+3)</option>
                                <option value="Asia/Dubai">Asia/Dubai (GMT+4)</option>
                                <option value="Europe/London">Europe/London (GMT+0 / BST)</option>
                                <option value="UTC">UTC (GMT+0)</option>
                                <option value="Asia/Kolkata">Asia/Kolkata (GMT+5:30)</option>
                                <option value="America/New_York">America/New_York (EST/EDT)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Default Currency Symbol</label>
                            <input type="text" name="currency" id="settings-currency" required placeholder="e.g. BHD, USD, EUR">
                        </div>
                        <div class="form-group">
                            <label>Gold Card Upgrade Threshold (<span class="currency-label">BHD</span>)</label>
                            <input type="number" step="0.001" name="gold_upgrade_threshold" id="settings-gold-threshold" required placeholder="500.000">
                        </div>
                        <div class="form-group">
                            <label>Hotel Brand Name</label>
                            <input type="text" name="hotel_name" id="settings-hotel-name" required placeholder="e.g. The K Hotel">
                        </div>
                        <div class="form-group">
                            <label>Hotel Location / Sub-label</label>
                            <input type="text" name="hotel_sub" id="settings-hotel-sub" required placeholder="e.g. BAHRAIN">
                        </div>
                        <div class="form-group">
                            <label>Hotel Logo Image</label>
                            <input type="file" id="settings-hotel-logo-file" accept="image/*" style="width: 100%;">
                            <input type="hidden" name="hotel_logo" id="settings-hotel-logo-value">
                            <div style="margin-top: 10px; display: flex; align-items: center; gap: 10px;">
                                <img id="settings-logo-preview" src="" style="max-height: 50px; border-radius: 8px; border: 1px solid var(--border-color); display: none;">
                                <button type="button" class="btn btn-secondary" id="clear-logo-btn" style="padding: 4px 8px; font-size: 11px; border-color: var(--danger); color: var(--danger); background: rgba(239,68,68,0.05); display: none;">Remove Logo</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Departments Settings -->
                <div class="form-section">
                    <div class="section-title"><i class="fa-solid fa-building"></i> Hotel Departments</div>
                    <p style="color: var(--text-muted); margin-bottom: 15px; font-size: 14px;">
                        Manage the list of hotel departments available for staff directory registration and tracking guest spending.
                    </p>
                    
                    <div style="display: flex; gap: 10px; margin-bottom: 15px; max-width: 500px;">
                        <input type="text" id="new-dept-input" placeholder="e.g. Spa, Housekeeping, Kitchen" style="flex: 1; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 4px; background: var(--bg-color); color: var(--text-main);">
                        <button type="button" class="btn" style="padding: 10px 16px;" onclick="addDepartmentFromInput()"><i class="fa-solid fa-plus"></i> Add Department</button>
                    </div>
                    
                    <div id="departments-list-container" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 15px;">
                        <!-- Departments badges will be populated here dynamically -->
                    </div>
                </div>

                <!-- Redeemable Vouchers Settings -->
                <div class="form-section">
                    <div class="section-title"><i class="fa-solid fa-ticket"></i> Redeemable Vouchers Settings</div>
                    <p style="color: var(--text-muted); margin-bottom: 15px; font-size: 14px;">
                        Manage the list of vouchers that members can redeem their points for. Configure the Points Cost, Category, and Description for each.
                    </p>
                    
                    <div id="vouchers-settings-container">
                        <!-- Voucher rows will be loaded dynamically -->
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <button type="button" class="btn btn-secondary" onclick="addVoucherRow()"><i class="fa-solid fa-plus"></i> Add Voucher Option</button>
                    </div>
                </div>

                <!-- Point Rules settings -->
                <div class="form-section">
                    <div class="section-title"><i class="fa-solid fa-star-half-stroke"></i> Service-Specific Point Incentives</div>
                    <p style="color: var(--text-muted); margin-bottom: 20px; font-size: 14px;">
                        Configure spending thresholds and points dynamically for each service (F&B, Front Office, etc.).
                    </p>
                    
                    <div id="points-rules-container">
                        <!-- Rules rows will be loaded here dynamically -->
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <button type="button" class="btn btn-secondary" onclick="addPointsRuleRow()"><i class="fa-solid fa-plus"></i> Add Point Rule</button>
                    </div>
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="btn"><i class="fa-solid fa-save"></i> Save System Settings</button>
                </div>
            </form>
        </div>

        <!-- User Management View Pane (Admin Only) -->
        <div id="view-users" class="view-pane">
            <div style="display: grid; grid-template-columns: 1fr 1.8fr; gap: 25px;">
                <!-- Add User Form -->
                <div>
                    <div class="form-section">
                        <div class="section-title"><i class="fa-solid fa-user-plus"></i> Create User Account</div>
                        <form id="addUserForm">
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label>Username *</label>
                                <input type="text" name="username" placeholder="e.g. counter_agent1" required class="form-control" style="width:100%;">
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label>Initial Password *</label>
                                <input type="password" name="password" placeholder="Enter password" required autocomplete="new-password" class="form-control" style="width:100%;">
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label>Access Role *</label>
                                <select name="role" required style="width:100%;">
                                    <option value="counter" selected>Counter User (Restricted settings)</option>
                                    <option value="admin">Admin User (Full access)</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label>Account Status *</label>
                                <select name="status" required style="width:100%;">
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div style="margin-top: 20px;">
                                <button type="submit" class="btn" style="width: 100%;"><i class="fa-solid fa-save"></i> Create User</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Users list Table -->
                <div>
                    <div class="form-section">
                        <div class="section-title"><i class="fa-solid fa-users"></i> System Accounts</div>
                        <div class="table-responsive">
                            <table id="usersTable" class="display" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="users-table-body">
                                    <!-- Loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Add Spending Modal -->
    <div id="spendingModal" class="modal">
        <div class="modal-content">
            <h3 style="color: var(--accent-gold); margin-bottom: 20px;"><i class="fa-solid fa-calculator"></i> Record Guest Spending</h3>
            <form id="spendingForm">
                <input type="hidden" name="member_id" id="spending-member-id">
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Guest Name</label>
                    <input type="text" id="spending-guest-name" readonly style="background-color: var(--border-color);">
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Spend Amount (BHD) *</label>
                    <input type="number" step="0.001" name="amount" required placeholder="0.000 BHD">
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Source Department</label>
                    <select name="source_dept">
                        <option value="F&B">Food & Beverage (F&B)</option>
                        <option value="Front Office">Front Office (Rooms / Events)</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Description / Booking Reference</label>
                    <input type="text" name="description" placeholder="e.g. Invoice #20412, Restaurant Bill">
                </div>
                
                <!-- Dynamic Staff Referrals Checklist for this Spend -->
                <div style="border-top:1px solid var(--border-color); padding-top:15px; margin-top:15px; margin-bottom:20px;">
                    <label style="font-weight:700; font-size:12px; color:var(--accent-gold); text-transform:uppercase; display:block; margin-bottom:10px;">Select Referrer Staff & Incentive Split</label>
                    
                    <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 6px; padding: 10px; background: var(--bg-color);" id="spend-staff-checklist-container">
                        <p style="color: var(--text-muted); font-size: 12px; text-align: center; margin: 10px 0;">No staff registered under this department.</p>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn btn-secondary" onclick="$('#spendingModal').css('display', 'none')">Cancel</button>
                    <button type="submit" class="btn">Record Spend</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Member QR Pass Modal -->
    <div id="qrPassModal" class="modal">
        <div class="modal-content" style="text-align: center;">
            <h3 style="color: var(--accent-gold); margin-bottom: 15px;"><i class="fa-solid fa-qrcode"></i> Digital Member Pass QR</h3>
            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 20px;">Scan this QR code with your mobile camera to open the secure encrypted Member Pass in your browser.</p>
            
            <div id="qr-pass-container" style="background: #fff; padding: 15px; display: inline-block; border-radius: 12px; margin-bottom: 20px;">
                <!-- QR Code Image will be loaded here -->
            </div>
            
            <div style="word-break: break-all; font-size: 11px; margin-bottom: 20px; color: var(--text-muted);">
                <strong>Encrypted Target URL:</strong>
                <p id="qr-pass-url-text" style="margin-top: 5px; font-family: monospace;"></p>
            </div>
            
            <div style="margin-top: 15px; display: flex; gap: 10px; justify-content: center;">
                <button class="btn btn-secondary" style="border-color: #25d366; color: #25d366; background: rgba(37,211,102,0.05);" id="send-whatsapp-btn" onclick="sendPassWhatsApp()"><i class="fa-brands fa-whatsapp"></i> Send WhatsApp</button>
                <button class="btn" onclick="$('#qrPassModal').css('display', 'none')">Close</button>
            </div>
        </div>
    </div>

    </div>

    <!-- Member Details & Spending History Modal -->
    <div id="memberDetailsModal" class="modal">
        <div class="modal-content" style="width: 900px; max-width: 95%;">
            <h3 style="color: var(--accent-gold); margin-bottom: 10px;"><i class="fa-solid fa-user-tag"></i> Member Activity History</h3>
            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 20px;" id="details-modal-subtitle"></p>
            
            <!-- Summary Stats -->
            <div style="display:flex; gap: 15px; margin-bottom: 20px;">
                <div style="flex:1; background: var(--bg-color); border:1px solid var(--border-color); padding: 12px; border-radius: 8px;">
                    <span style="font-size: 10px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Total Spent</span>
                    <h4 style="font-size: 18px; color: var(--text-main); margin-top: 4px;" id="details-total-spent">0.000 BHD</h4>
                </div>
                <div style="flex:1; background: var(--bg-color); border:1px solid var(--border-color); padding: 12px; border-radius: 8px;" id="details-points-container">
                    <span style="font-size: 10px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Points (Available / Earned)</span>
                    <h4 style="font-size: 18px; color: var(--text-main); margin-top: 4px;" id="details-points-val">0 / 0 Pts</h4>
                </div>
            </div>

            <!-- Tabs inside modal -->
            <div style="display:flex; border-bottom:1px solid var(--border-color); margin-bottom: 15px; gap: 5px;">
                <button class="btn btn-secondary" id="modal-tab-spend" onclick="switchModalTab('spend')" style="border-bottom: 2px solid var(--primary); border-radius:0; padding:8px 16px;">Spend Logs</button>
                <button class="btn btn-secondary" id="modal-tab-points" onclick="switchModalTab('points')" style="border-bottom: 2px solid transparent; border-radius:0; padding:8px 16px;">Points Ledger</button>
                <button class="btn btn-secondary" id="modal-tab-vouchers" onclick="switchModalTab('vouchers')" style="border-bottom: 2px solid transparent; border-radius:0; padding:8px 16px;">Vouchers Wallet</button>
            </div>

            <!-- Spending List Content -->
            <div id="modal-content-spend" style="max-height: 250px; overflow-y: auto;">
                <div class="table-container" style="margin-top:0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Dept</th>
                                <th>Description / Ref</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody id="modal-spend-table-body">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Points List Content -->
            <div id="modal-content-points" style="max-height: 250px; overflow-y: auto; display: none;">
                <div class="table-container" style="margin-top:0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Source</th>
                                <th>Description</th>
                                <th>Points Change</th>
                            </tr>
                        </thead>
                        <tbody id="modal-points-table-body">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Vouchers Wallet Content -->
            <div id="modal-content-vouchers" style="max-height: 250px; overflow-y: auto; display: none;">
                <div class="table-container" style="margin-top:0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Voucher No</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Validity / Expiry</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="modal-vouchers-table-body">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:20px;">
                <button class="btn" onclick="$('#memberDetailsModal').css('display', 'none')">Close</button>
            </div>
        </div>
    </div>

    <!-- Admin Points Redemption Modal -->
    <div id="redeemPointsModal" class="modal">
        <div class="modal-content" style="width: 450px; max-width: 90%;">
            <h3 style="color: var(--accent-gold); margin-bottom: 20px;"><i class="fa-solid fa-gift"></i> Redeem Member Points</h3>
            <form id="redeemPointsForm">
                <input type="hidden" name="member_id" id="redeem-member-id">
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Guest Name</label>
                    <input type="text" id="redeem-guest-name" readonly style="background-color: var(--border-color);">
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Available Balance</label>
                    <input type="text" id="redeem-available-balance" readonly style="background-color: var(--border-color); font-weight: 700; color: var(--accent-gold);">
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Select Voucher to Redeem *</label>
                    <select name="description" id="redeem-voucher-select" onchange="onRedeemVoucherSelect(this.value)" required>
                        <option value="">-- Choose Voucher --</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Points Cost</label>
                    <input type="number" name="points" id="redeem-points-input" readonly style="background-color: var(--border-color); font-weight: 700; color: var(--accent-gold);" required placeholder="0">
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Validity / Expiry *</label>
                    <select name="validity_option" id="redeem-validity-select" onchange="onRedeemValidityChange(this.value)" required>
                        <option value="1 Month">1 Month</option>
                        <option value="2 Month">2 Month</option>
                        <option value="3 Month">3 Month</option>
                        <option value="6 Month">6 Month</option>
                        <option value="1 Year" selected>1 Year</option>
                        <option value="Custom Date">Custom Expiry Date</option>
                    </select>
                </div>
                <div class="form-group" id="redeem-custom-date-group" style="margin-bottom:20px; display:none;">
                    <label>Custom Expiry Date *</label>
                    <input type="date" name="custom_date" id="redeem-custom-date-input">
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn btn-secondary" onclick="$('#redeemPointsModal').css('display', 'none')">Cancel</button>
                    <button type="submit" class="btn">Redeem Points</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Approve Redemption Request Modal -->
    <div id="approveRedemptionModal" class="modal">
        <div class="modal-content" style="width: 450px; max-width: 90%;">
            <h3 style="color: var(--accent-gold); margin-bottom: 20px;"><i class="fa-solid fa-clipboard-check"></i> Approve Points Redemption</h3>
            <form id="approveRedemptionForm">
                <input type="hidden" name="request_id" id="approve-request-id">
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Guest Name</label>
                    <input type="text" id="approve-guest-name" readonly style="background-color: var(--border-color);">
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Award Claimed</label>
                    <input type="text" id="approve-award-title" readonly style="background-color: var(--border-color); font-weight: 700;">
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Points Cost</label>
                    <input type="text" id="approve-points-cost" readonly style="background-color: var(--border-color); font-weight: 700; color: var(--accent-gold);">
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Validity / Expiry *</label>
                    <select name="validity_option" id="approve-validity-select" onchange="onApproveValidityChange(this.value)" required>
                        <option value="1 Month">1 Month</option>
                        <option value="2 Month">2 Month</option>
                        <option value="3 Month">3 Month</option>
                        <option value="6 Month">6 Month</option>
                        <option value="1 Year" selected>1 Year</option>
                        <option value="Custom Date">Custom Expiry Date</option>
                    </select>
                </div>
                <div class="form-group" id="approve-custom-date-group" style="margin-bottom:20px; display:none;">
                    <label>Custom Expiry Date *</label>
                    <input type="date" name="custom_date" id="approve-custom-date-input">
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn btn-secondary" onclick="$('#approveRedemptionModal').css('display', 'none')">Cancel</button>
                    <button type="submit" class="btn" style="background:var(--success);">Confirm Approval</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Custom Reusable Confirmation Modal -->
    <div id="customConfirmModal" class="modal" style="z-index: 10000;">
        <div class="modal-content" style="width: 420px; max-width: 90%; text-align: center; padding: 30px 24px; border-radius: 16px; border: 1px solid rgba(251,191,36,0.25);">
            <div style="font-size: 44px; color: var(--accent-gold); margin-bottom: 16px;">
                <i class="fa-solid fa-circle-question"></i>
            </div>
            <h4 style="color: var(--text-main); margin-bottom: 15px; font-weight: 700; font-size: 16px; line-height:1.5;" id="custom-confirm-message">Are you sure?</h4>
            <div style="display: flex; justify-content: center; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" id="custom-confirm-cancel-btn" style="min-width: 110px;">Cancel</button>
                <button type="button" class="btn" id="custom-confirm-ok-btn" style="min-width: 110px; background: var(--primary); color: #fff;">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Manage Staff Directory Modal -->
    <div id="staffDirectoryModal" class="modal">
        <div class="modal-content" style="width: 950px; max-width: 95%;">
            <h3 style="color: var(--accent-gold); margin-bottom: 20px;"><i class="fa-solid fa-users-gear"></i> Staff Directory Management</h3>
            
            <div style="display: grid; grid-template-columns: 1.2fr 2fr; gap: 25px;">
                <!-- Left column: Add Staff Form -->
                <div>
                    <h4 style="color: var(--text-main); margin-bottom: 12px; font-size:14px; border-bottom: 1px solid var(--border-color); padding-bottom:6px;" id="staff-form-title"><i class="fa-solid fa-plus-circle"></i> Add Staff</h4>
                    <form id="addStaffForm">
                        <input type="hidden" name="id" id="staff-edit-id">
                        <div class="form-group" style="margin-bottom:12px;">
                            <label style="font-size:11px;">Staff ID *</label>
                            <input type="text" name="staff_id" id="staff-form-staff-id" required placeholder="e.g. ST1005" style="padding: 8px 10px; font-size:12px; width:100%;">
                        </div>
                        <div class="form-group" style="margin-bottom:12px;">
                            <label style="font-size:11px;">Staff Name *</label>
                            <input type="text" name="name" id="staff-form-name" required placeholder="Full Name" style="padding: 8px 10px; font-size:12px; width:100%;">
                        </div>
                        <div class="form-group" style="margin-bottom:12px;">
                            <label style="font-size:11px;">Department *</label>
                            <select name="department" id="add-staff-department-select" required style="padding: 8px 10px; font-size:12px; width:100%;">
                                <option value="">-- Choose Department --</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:15px;">
                            <label style="font-size:11px;">Default Incentive % *</label>
                            <input type="number" step="0.1" name="incentive_pct" id="staff-form-pct" required placeholder="e.g. 5.0" min="0" max="100" style="padding: 8px 10px; font-size:12px; width:100%;">
                        </div>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <button type="submit" class="btn" style="width:100%; font-size:12px; padding: 10px 12px;" id="staff-form-submit-btn"><i class="fa-solid fa-plus"></i> Add to Directory</button>
                            <button type="button" class="btn btn-secondary" style="width:100%; font-size:12px; padding: 8px 12px; display:none;" id="staff-form-cancel-btn" onclick="cancelStaffEdit()">Cancel Edit</button>
                        </div>
                    </form>
                </div>
                
                <!-- Right column: Staff Directory List -->
                <div>
                    <h4 style="color: var(--text-main); margin-bottom: 12px; font-size:14px; border-bottom: 1px solid var(--border-color); padding-bottom:6px;"><i class="fa-solid fa-list"></i> Active Directory</h4>
                    <div style="max-height: 320px; overflow-y: auto; border: 1px solid var(--border-color); border-radius:6px; padding: 8px;">
                        <table style="font-size:12px; width: 100%;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Dept</th>
                                    <th>Default Inc %</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="staff-directory-list-body">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div style="display:flex; justify-content:flex-end; margin-top:20px; border-top:1px solid var(--border-color); padding-top:15px;">
                <button class="btn btn-secondary" onclick="$('#staffDirectoryModal').css('display', 'none')">Close</button>
            </div>
        </div>
    </div>

    <!-- Admin Reset Password Modal (Admin Only) -->
    <?php if ($userRole === 'admin'): ?>
    <div id="adminResetPasswordModal" class="modal" style="z-index: 10001;">
        <div class="modal-content" style="width: 400px; max-width: 90%;">
            <h3 style="color: var(--accent-gold); margin-bottom: 20px;"><i class="fa-solid fa-key"></i> Reset User Password</h3>
            <form id="adminResetPasswordForm">
                <input type="hidden" name="id" id="admin-reset-user-id">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Username</label>
                    <input type="text" id="admin-reset-username" readonly style="width:100%; opacity:0.6;">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>New Password *</label>
                    <input type="password" name="new_password" required placeholder="Enter new password" autocomplete="new-password" style="width:100%;">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-secondary" onclick="$('#adminResetPasswordModal').css('display', 'none')">Cancel</button>
                    <button type="submit" class="btn" style="background: var(--success);">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Change Self Password Modal (All Users) -->
    <div id="changePasswordModal" class="modal" style="z-index: 10001;">
        <div class="modal-content" style="width: 420px; max-width: 90%;">
            <h3 style="color: var(--accent-gold); margin-bottom: 20px;"><i class="fa-solid fa-key"></i> Change Your Password</h3>
            <form id="changePasswordForm">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Current Password *</label>
                    <input type="password" name="current_password" required placeholder="Enter current password" autocomplete="current-password" style="width:100%;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>New Password *</label>
                    <input type="password" name="new_password" id="new-self-password" required placeholder="Enter new password" autocomplete="new-password" style="width:100%;">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Confirm New Password *</label>
                    <input type="password" name="confirm_password" required placeholder="Confirm new password" autocomplete="new-password" style="width:100%;">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-secondary" onclick="$('#changePasswordModal').css('display', 'none')">Cancel</button>
                    <button type="submit" class="btn" style="background: var(--success);">Update Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="adminToast">
        <i class="fa-solid fa-circle-check" style="color: var(--success); font-size: 20px;"></i>
        <span id="toast-message">Operation successful!</span>
    </div>

    <!-- AJAX Scripts -->
    <script>
        const globalHotelName = <?php echo json_encode($hotelName); ?>;
        let currentCurrency = 'BHD';

        // Tab routing
        $('.sidebar-menu .menu-item').on('click', function(e) {
            e.preventDefault();
            $('.sidebar-menu .menu-item').removeClass('active');
            $(this).addClass('active');
            
            const view = $(this).data('view');
            $('.view-pane').removeClass('active-pane');
            $(`#view-${view}`).addClass('active-pane');

            // Update titles
            const titles = {
                dashboard: { title: 'Dashboard Overview', subtitle: 'Real-time statistics of loyalty programs' },
                enrolment: { title: 'New Guest Enrolment', subtitle: 'SOP Control Form page 5 equivalent' },
                members: { title: 'Privileged Members', subtitle: 'Detailed directory of guest profiles and spend history' },
                redemptions: { title: 'Redemption Claims', subtitle: 'Verify and process K Reward claims' },
                incentives: { title: 'Staff Incentives Dashboard', subtitle: 'Manage commissions for Silver card enrolments' },
                security: { title: 'Outbound URL Protection', subtitle: 'Secure campaign links with cryptographic tokens' },
                users: { title: 'User Management', subtitle: 'Create, status toggle and password reset system accounts' },
                settings: { title: 'System Settings', subtitle: 'Configure timezone, currency, and points calculations' }
            };

            $('#view-title').text(titles[view].title);
            $('#view-subtitle').text(titles[view].subtitle);

            if (view === 'security') {
                loadCampaignLinks();
            }
        });

        // Enrolment form select handlers
        $('#enroll-type-select').on('change', function() {
            const val = $(this).val();
            if (val === 'K Reward') {
                $('#enroll-tier-select').html('<option value="Booker">Corporate / Booker</option>');
                $('#voucher-select-group').hide();
                $('#receipt-num-group').hide();
                $('#receipt-amt-group').hide();
                $('#gold-reason-group').hide();
            } else {
                $('#enroll-tier-select').html(
                    '<option value="Silver">Silver (55.000 BHD)</option>' +
                    '<option value="Gold">Gold (VIP - GM Approval Required)</option>' +
                    '<option value="Brown">Brown (Walk-in / Promo)</option>'
                );
                $('#voucher-select-group').show();
                $('#receipt-num-group').show();
                $('#receipt-amt-group').show();
            }
        });

        $('#enroll-tier-select').on('change', function() {
            const val = $(this).val();
            if (val === 'Gold') {
                $('#gold-reason-group').show();
                $('#receipt-num-group').hide();
                $('#receipt-amt-group').hide();
                $('#voucher-select-group').hide();
            } else if (val === 'Silver') {
                $('#gold-reason-group').hide();
                $('#receipt-num-group').show();
                $('#receipt-amt-group').show();
                $('[name="receipt_amount"]').val('55.000');
                $('#voucher-select-group').show();
            } else if (val === 'Brown') {
                $('#gold-reason-group').hide();
                $('#receipt-num-group').hide();
                $('#receipt-amt-group').hide();
                $('#voucher-select-group').show();
            }
        });

        // Toast Trigger
        function showToast(message, isError = false) {
            const toast = $('#adminToast');
            const icon = toast.find('i');
            $('#toast-message').text(message);
            
            if (isError) {
                icon.removeClass('fa-circle-check').addClass('fa-circle-xmark').css('color', 'var(--danger)');
                toast.css('border-left-color', 'var(--danger)');
            } else {
                icon.removeClass('fa-circle-xmark').addClass('fa-circle-check').css('color', 'var(--success)');
                toast.css('border-left-color', 'var(--primary)');
            }

            toast.css('display', 'flex').fadeIn(200).delay(3000).fadeOut(200);
        }

        // Form Submission
        $('#enrolmentForm').on('submit', function(e) {
            e.preventDefault();
            const editId = $('#enrol-member-edit-id').val();
            const actionUrl = editId ? 'admin_actions.php?action=edit_member' : 'admin_actions.php?action=enrol_member';
            
            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        showToast(response.message);
                        cancelMemberEdit();
                        loadAllData();
                    } else {
                        showToast(response.message, true);
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || "Error submitting enrolment", true);
                }
            });
        });

        // Add spending form submit
        $('#spendingForm').on('submit', function(e) {
            e.preventDefault();
            
            const staffList = [];
            $('.spend-staff-check-row').each(function() {
                const checkbox = $(this).find('.staff-checkbox');
                if (checkbox.is(':checked')) {
                    const id = checkbox.data('id');
                    const name = checkbox.data('name');
                    const dept = checkbox.data('dept');
                    const pct = parseFloat($(this).find('.staff-pct-input').val());
                    if (id && name && dept && !isNaN(pct)) {
                        staffList.push({ id, name, dept, pct });
                    }
                }
            });
            
            const formData = {
                member_id: $('#spending-member-id').val(),
                amount: $('#spendingForm input[name="amount"]').val(),
                source_dept: $('#spendingForm select[name="source_dept"]').val(),
                description: $('#spendingForm input[name="description"]').val(),
                staff_referrals: JSON.stringify(staffList)
            };
            
            $.ajax({
                url: 'admin_actions.php?action=add_spending',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showToast("Spending logged and points/incentives updated!");
                        $('#spendingModal').css('display', 'none');
                        $('#spendingForm')[0].reset();
                        $('#spend-staff-checklist-container').empty();
                        loadAllData();
                    } else {
                        showToast(response.message, true);
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || "Error recording spending", true);
                }
            });
        });

        // Change listener for Source Department in spending modal to filter referral lists
        $('#spendingForm select[name="source_dept"]').on('change', function() {
            renderSpendStaffChecklist($(this).val());
        });

        // Load DB Data
        let membersMap = {};

        function loadAllData() {
            // Get dashboard summaries & members
            $.ajax({
                url: 'admin_actions.php?action=get_members',
                type: 'GET',
                cache: false,
                success: function(response) {
                    if (response.success) {
                        const members = response.data;
                        membersMap = {};
                        let kplus = 0;
                        let kreward = 0;
                        let tableHtml = '';

                        members.forEach(member => {
                            membersMap[member.id] = member;
                            if (member.membership_type === 'K Plus') kplus++;
                            else kreward++;

                            let badgeClass = 'badge-active';
                            if (member.status === 'Pending Approval') badgeClass = 'badge-pending';
                            if (member.status === 'Expired') badgeClass = 'badge-expired';

                            const spendBtn = member.is_verified === 1
                                ? `<button class="btn btn-secondary" style="padding: 6px 12px; font-size:12px; background:rgba(16,185,129,0.1); color:#10b981;" onclick="openSpendingModal(${member.id}, '${member.first_name} ${member.last_name}')"><i class="fa-solid fa-plus"></i> Spend</button>`
                                : `<button class="btn btn-secondary" style="padding: 6px 12px; font-size:12px; opacity:0.4; cursor:not-allowed;" disabled title="Verify card via QR/WhatsApp first"><i class="fa-solid fa-lock"></i> Spend</button>`;

                            const redeemBtn = `<button class="btn btn-secondary" style="padding: 6px 12px; font-size:12px; border-color:var(--primary); color:var(--primary); background:rgba(217,119,6,0.05);" onclick="openRedeemModal(${member.id}, '${member.first_name} ${member.last_name}', ${member.points_balance})"><i class="fa-solid fa-gift"></i> Redeem</button>`;

                            tableHtml += `
                                <tr>
                                    <td><strong style="color:var(--accent-gold);">${formatCardNumber(member.membership_number)}</strong></td>
                                    <td>${member.title} ${member.first_name} ${member.last_name}</td>
                                    <td>${member.mobile}</td>
                                    <td>${member.nationality}</td>
                                    <td>${member.membership_type}</td>
                                    <td><strong style="color:var(--accent-gold);">${member.points_balance}</strong> Pts</td>
                                    <td><span class="badge ${member.card_type === 'Gold' ? 'badge-pending' : 'badge-active'}">${member.card_type}</span></td>
                                    <td><span class="badge ${badgeClass}">${member.status}</span></td>
                                    <td>${formatDateToDMY(member.expiry_date)}</td>
                                    <td>
                                        ${spendBtn}
                                        ${redeemBtn}
                                        <button class="btn btn-secondary" style="padding: 6px 12px; font-size:12px;" onclick="showMemberPassQR(${member.id}, '${member.membership_number}')"><i class="fa-solid fa-qrcode"></i> QR Pass</button>
                                        <button class="btn btn-secondary" style="padding: 6px 12px; font-size:12px;" onclick="viewMemberDetails(${member.id}, '${member.first_name} ${member.last_name}', '${member.membership_number}', '${member.membership_type}')"><i class="fa-solid fa-list"></i> Details</button>
                                        <button class="btn btn-secondary" style="padding: 6px 12px; font-size:12px; color:var(--text-main); border-color:var(--border-color); background:var(--bg-color);" onclick="startMemberEdit(${member.id})"><i class="fa-solid fa-pencil"></i> Edit</button>
                                        ${member.card_type !== 'Gold' && member.membership_type === 'K Plus' ? 
                                            `<button class="btn" style="padding: 6px 12px; font-size:12px;" onclick="recommendGold(${member.id})"><i class="fa-solid fa-arrow-up"></i> Upgrade</button>` : ''
                                        }
                                    </td>
                                </tr>
                            `;
                        });

                        $('#stat-kplus-count').text(kplus);
                        $('#stat-kreward-count').text(kreward);
                        
                        if ($.fn.DataTable.isDataTable('#membersTable')) {
                            $('#membersTable').DataTable().destroy();
                        }
                        $('#member-table-body').html(tableHtml);
                        $('#membersTable').DataTable({
                            "pageLength": 10,
                            "ordering": true,
                            "stateSave": true,
                            "language": {
                                "search": "Filter Members:"
                            }
                        });

                        let memberSelectHtml = '<option value="">-- No Customer Link / General Campaign --</option>';
                        members.forEach(member => {
                            memberSelectHtml += `<option value="${member.id}">${member.first_name} ${member.last_name} (${member.membership_number})</option>`;
                        });
                        $('#encrypt-member-select').html(memberSelectHtml);
                    }
                }
            });

            // Get upgrades & action items
            $.ajax({
                url: 'admin_actions.php?action=get_pending_upgrades',
                type: 'GET',
                cache: false,
                success: function(response) {
                    if (response.success) {
                        const gmPending = response.data.pending_gm;
                        const recommend = response.data.recommendations;
                        let actionItemsHtml = '';

                        if (gmPending.length === 0 && recommend.length === 0) {
                            actionItemsHtml = '<p style="color: var(--text-muted)">All loyalty programs operating smoothly. No actions required.</p>';
                        }

                        gmPending.forEach(member => {
                            actionItemsHtml += `
                                <div style="display:flex; justify-content:space-between; align-items:center; background:var(--card-bg); border:1px solid var(--border-color); padding:16px; border-radius:8px; margin-bottom:10px; border-left:4px solid var(--primary);">
                                    <div>
                                        <h5 style="color:var(--text-main); font-size:14px;"><i class="fa-solid fa-gavel"></i> GM APPROVAL REQUIRED: Gold Card Upgrade</h5>
                                        <p style="font-size:12px; color:var(--text-muted); margin-top:4px;">Guest: <strong>${member.first_name} ${member.last_name} (${member.membership_number})</strong> | Reason: ${member.gold_reason}</p>
                                    </div>
                                    <button class="btn" style="padding:8px 16px; font-size:12px;" onclick="approveGold(${member.id})">Approve & Activate</button>
                                </div>
                            `;
                        });

                        const thresholdVal = parseFloat(response.data.gold_upgrade_threshold || 500.000).toFixed(3);
                        recommend.forEach(rec => {
                            actionItemsHtml += `
                                <div style="display:flex; justify-content:space-between; align-items:center; background:var(--card-bg); border:1px solid var(--border-color); padding:16px; border-radius:8px; margin-bottom:10px; border-left:4px solid var(--accent-gold);">
                                    <div>
                                        <h5 style="color:var(--text-main); font-size:14px;"><i class="fa-solid fa-circle-exclamation"></i> SPENDING MILESTONE REACHED: Gold Upgrade Recommended</h5>
                                        <p style="font-size:12px; color:var(--text-muted); margin-top:4px;">Guest: <strong>${rec.first_name} ${rec.last_name} (${rec.membership_number})</strong> | Tier: ${rec.card_type} | Total spent: <strong>${parseFloat(rec.total_spending).toFixed(3)} ${currentCurrency}</strong> (Threshold >= ${thresholdVal} ${currentCurrency})</p>
                                    </div>
                                    <button class="btn" style="padding:8px 16px; font-size:12px; background:var(--primary);" onclick="recommendGold(${rec.id})">Flag for GM Approval</button>
                                </div>
                            `;
                        });

                        $('#dashboard-actions-list').html(actionItemsHtml);
                    }
                }
            });

            // Get redemptions
            $.ajax({
                url: 'admin_actions.php?action=get_redemptions',
                type: 'GET',
                cache: false,
                success: function(response) {
                    if (response.success) {
                        const redemptions = response.data;
                        let html = '';
                        let pendingCount = 0;

                        redemptions.forEach(req => {
                            if (req.status === 'Pending') pendingCount++;
                            
                            html += `
                                <tr>
                                    <td>#${req.id}</td>
                                    <td><strong style="color:var(--accent-gold);">${req.membership_number}</strong></td>
                                    <td>${req.first_name} ${req.last_name}</td>
                                    <td>${req.award_title}</td>
                                    <td><strong style="color:var(--text-main);">${req.points_cost} Pts</strong></td>
                                    <td>${req.created_at}</td>
                                    <td><span class="badge ${req.status === 'Approved' ? 'badge-active' : (req.status === 'Pending' ? 'badge-pending' : 'badge-expired')}">${req.status}</span></td>
                                    <td>
                                        ${req.status === 'Pending' ? 
                                            `<button class="btn" style="padding:6px 12px; font-size:12px; background:var(--success);" onclick="processRedemption(${req.id}, 'Approved')"><i class="fa-solid fa-check"></i> Approve</button>
                                             <button class="btn btn-danger" style="padding:6px 12px; font-size:12px;" onclick="processRedemption(${req.id}, 'Rejected')"><i class="fa-solid fa-xmark"></i> Reject</button>` : 
                                            `<span style="color:var(--text-muted); font-size:12px;">Processed by ${req.approved_by}</span>`
                                        }
                                    </td>
                                </tr>
                            `;
                        });
                        $('#stat-redemptions-count').text(pendingCount);
                        $('#redemptions-table-body').html(html);
                    }
                }
            });

            // Get incentives & reports
            $.ajax({
                url: 'admin_actions.php?action=get_reports',
                type: 'GET',
                cache: false,
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        let incHtml = '';
                        let totalIncentives = 0.000;

                        data.incentives.forEach(inc => {
                            if (inc.status !== 'Paid') {
                                totalIncentives += parseFloat(inc.incentive_amount);
                            }

                            incHtml += `
                                <tr>
                                    <td><strong>${inc.staff_name}</strong> (ID: ${inc.staff_id})</td>
                                    <td>${inc.department}</td>
                                    <td>${inc.first_name} ${inc.last_name}</td>
                                    <td><strong style="color:var(--accent-gold);">${inc.membership_number}</strong> (Silver)</td>
                                    <td>${parseFloat(inc.incentive_amount).toFixed(3)} ${currentCurrency}</td>
                                    <td>${inc.created_at}</td>
                                    <td><span class="badge ${inc.status === 'Paid' ? 'badge-active' : 'badge-pending'}">${inc.status}</span></td>
                                </tr>
                            `;
                        });

                        $('#stat-incentives-count').text(totalIncentives.toFixed(3) + ' ' + currentCurrency);
                        $('#incentives-table-body').html(incHtml);
                    }
                }
            });

            // Load settings (Admin Only)
            if ($('#settings-timezone').length > 0) {
                $.ajax({
                    url: 'admin_actions.php?action=get_settings',
                    type: 'GET',
                    cache: false,
                    success: function(response) {
                        if (response.success) {
                        const settings = response.data;
                        $('#settings-timezone').val(settings.timezone || 'Asia/Bahrain');
                        $('#settings-currency').val(settings.currency || 'BHD');
                        $('#settings-gold-threshold').val(parseFloat(settings.gold_upgrade_threshold || 500.000).toFixed(3));
                        $('#settings-hotel-name').val(settings.hotel_name || 'The K Hotel');
                        $('#settings-hotel-sub').val(settings.hotel_sub || 'BAHRAIN');
                        $('#settings-hotel-logo-value').val(settings.hotel_logo || '');
                        if (settings.hotel_logo) {
                            $('#settings-logo-preview').attr('src', settings.hotel_logo).show();
                            $('#clear-logo-btn').show();
                        } else {
                            $('#settings-logo-preview').attr('src', '').hide();
                            $('#clear-logo-btn').hide();
                        }
                        
                        const currency = settings.currency || 'BHD';
                        updateGlobalCurrency(currency);
                        
                        // Populate points rules
                        $('#points-rules-container').empty();
                        let rules = [];
                        try {
                            rules = JSON.parse(settings.fb_points_rules || '[]');
                        } catch (e) {
                            rules = [];
                        }
                        
                        if (rules.length === 0) {
                            addPointsRuleRow('F&B', 50.000, 10);
                            addPointsRuleRow('Front Office', 70.000, 15);
                            rules = [
                                { service: 'F&B', threshold: 50.000, points: 10 },
                                { service: 'Front Office', threshold: 70.000, points: 15 }
                            ];
                        } else {
                            rules.forEach(r => {
                                const srv = r.service || 'F&B';
                                addPointsRuleRow(srv, r.threshold, r.points);
                            });
                        }

                        // Populate departments
                        try {
                            globalDepartments = JSON.parse(settings.departments || '[]');
                        } catch (e) {
                            globalDepartments = [];
                        }
                        if (globalDepartments.length === 0) {
                            globalDepartments = ['F&B', 'Front Office', 'Spa'];
                        }
                        renderDepartmentsList();

                        // Populate redeemable vouchers
                        $('#vouchers-settings-container').empty();
                        let settingsVouchers = [];
                        try {
                            settingsVouchers = JSON.parse(settings.redeemable_vouchers || '[]');
                        } catch (e) {
                            settingsVouchers = [];
                        }
                        
                        globalVouchers = settingsVouchers;
                        if (globalVouchers.length === 0) {
                            addVoucherRow('', '', '', 'meals', '');
                        } else {
                            globalVouchers.forEach(v => {
                                addVoucherRow(v.id || '', v.name || '', v.points || '', v.category || 'meals', v.description || '');
                            });
                        }
                        }
                    }
                });
            }

            // Load users list (Admin Only)
            if ($('#usersTable').length > 0) {
                loadUsersList();
            }

            loadCampaignLinks();
        }

        // Open spending modal
        function openSpendingModal(id, name) {
            $('#spending-member-id').val(id);
            $('#spending-guest-name').val(name);
            const firstDept = $('#spendingForm select[name="source_dept"]').val();
            renderSpendStaffChecklist(firstDept);
            $('#spendingModal').css('display', 'flex');
        }

        let currentPassUrl = '';
        let currentPassMobile = '';

        function showMemberPassQR(id, membership_number) {
            $.ajax({
                url: 'admin_actions.php?action=get_member_pass_url',
                type: 'POST',
                data: { member_id: id },
                success: function(response) {
                    if (response.success) {
                        const url = response.data.secured_url;
                        const mobile = response.data.mobile;
                        currentPassUrl = url;
                        currentPassMobile = mobile;

                        $('#qr-pass-url-text').text(url);
                        const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' + encodeURIComponent(url);
                        $('#qr-pass-container').html(`<img src="${qrUrl}" alt="QR Pass" style="display: block; width:160px; height:160px;" />`);

                        if (mobile) {
                            $('#send-whatsapp-btn').show();
                        } else {
                            $('#send-whatsapp-btn').hide();
                        }

                        $('#qrPassModal').css('display', 'flex');
                    } else {
                        showToast(response.message, true);
                    }
                },
                error: function() {
                    showToast("Error generating QR Pass URL", true);
                }
            });
        }

        function sendPassWhatsApp() {
            if (!currentPassUrl || !currentPassMobile) return;
            const message = `Dear Valued Guest, here is your dynamic secure digital loyalty pass for ${globalHotelName}: ${currentPassUrl}`;
            const cleanMobile = currentPassMobile.replace(/[^0-9]/g, '');
            const waUrl = `https://wa.me/${cleanMobile}?text=${encodeURIComponent(message)}`;
            window.open(waUrl, '_blank');
        }

        function viewMemberDetails(id, name, cardNum, program) {
            $('#details-modal-subtitle').text(`Guest: ${name} (${formatCardNumber(cardNum)}) | Program: ${program}`);
            
            $('#modal-tab-points').show();
            $('#details-points-container').show();

            $.ajax({
                url: 'admin_actions.php?action=get_member_spending_details',
                type: 'GET',
                data: { member_id: id },
                cache: false,
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        
                        // Set Totals
                        $('#details-total-spent').text(parseFloat(data.totals.total_spent).toFixed(3) + ' ' + currentCurrency);
                        
                        const pointsAvailable = parseInt(data.totals.total_earned) - parseInt(data.totals.total_redeemed);
                        $('#details-points-val').text(`${pointsAvailable} / ${data.totals.total_earned} Pts`);
                        
                        // Render Spend Table
                        let spendHtml = '';
                        if (data.spending.length === 0) {
                            spendHtml = '<tr><td colspan="4" style="text-align:center; color:var(--text-muted);">No spending history recorded.</td></tr>';
                        } else {
                            data.spending.forEach(row => {
                                spendHtml += `
                                    <tr>
                                        <td>${row.transaction_date}</td>
                                        <td><span class="badge badge-active" style="background-color:rgba(217,119,6,0.1); color:var(--primary);">${row.source_dept}</span></td>
                                        <td>${row.description || 'N/A'}</td>
                                        <td><strong>${parseFloat(row.amount).toFixed(3)} ${currentCurrency}</strong></td>
                                    </tr>
                                `;
                            });
                        }
                        $('#modal-spend-table-body').html(spendHtml);
                        
                        // Render Points Table
                        let pointsHtml = '';
                        if (data.points.length === 0) {
                            pointsHtml = '<tr><td colspan="4" style="text-align:center; color:var(--text-muted);">No points activities recorded.</td></tr>';
                        } else {
                            data.points.forEach(row => {
                                const isEarned = row.transaction_type === 'Earned';
                                pointsHtml += `
                                    <tr>
                                        <td>${row.transaction_date}</td>
                                        <td>${row.source}</td>
                                        <td>${row.description || 'N/A'}</td>
                                        <td><strong style="color:${isEarned ? 'var(--success)' : 'var(--danger)'}">${isEarned ? '+' + row.points_earned : '-' + row.points_redeemed} Pts</strong></td>
                                    </tr>
                                `;
                            });
                        }
                        $('#modal-points-table-body').html(pointsHtml);

                        // Render Vouchers Table
                        let vouchersHtml = '';
                        if (!data.vouchers || data.vouchers.length === 0) {
                            vouchersHtml = '<tr><td colspan="6" style="text-align:center; color:var(--text-muted);">No vouchers issued.</td></tr>';
                        } else {
                            data.vouchers.forEach(row => {
                                const isActive = row.status === 'Active';
                                const badgeClass = isActive ? 'badge-active' : (row.status === 'Used' ? 'badge-pending' : 'badge-expired');
                                const useBtn = isActive ? 
                                    `<button class="btn" style="padding:4px 8px; font-size:11px; background:var(--primary); color:#0b0f19; font-weight:700;" onclick="markVoucherAsUsed(${row.id}, ${id})"><i class="fa-solid fa-check-double"></i> Mark as Used</button>` :
                                    `<span style="color:var(--text-muted); font-size:11px;">N/A</span>`;
                                
                                vouchersHtml += `
                                    <tr>
                                        <td><strong style="color:var(--accent-gold); font-family:monospace;">${row.voucher_number}</strong></td>
                                        <td><span class="badge badge-active" style="background-color:rgba(251,191,36,0.1); color:var(--accent-gold); font-size:10px;">${row.voucher_type}</span></td>
                                        <td>${row.description || 'N/A'}</td>
                                        <td>${row.valid_until}</td>
                                        <td><span class="badge ${badgeClass}">${row.status.toUpperCase()}</span></td>
                                        <td>${useBtn}</td>
                                    </tr>
                                `;
                            });
                        }
                        $('#modal-vouchers-table-body').html(vouchersHtml);
                        
                        // Show modal
                        $('#memberDetailsModal').css('display', 'flex');
                    }
                }
            });
        }

        function switchModalTab(tab) {
            // Reset borders
            $('#modal-tab-spend').css('border-bottom-color', 'transparent');
            $('#modal-tab-points').css('border-bottom-color', 'transparent');
            $('#modal-tab-vouchers').css('border-bottom-color', 'transparent');
            
            // Hide panels
            $('#modal-content-spend').hide();
            $('#modal-content-points').hide();
            $('#modal-content-vouchers').hide();
            
            if (tab === 'spend') {
                $('#modal-tab-spend').css('border-bottom-color', 'var(--primary)');
                $('#modal-content-spend').show();
            } else if (tab === 'points') {
                $('#modal-tab-points').css('border-bottom-color', 'var(--primary)');
                $('#modal-content-points').show();
            } else if (tab === 'vouchers') {
                $('#modal-tab-vouchers').css('border-bottom-color', 'var(--primary)');
                $('#modal-content-vouchers').show();
            }
        }

        // Upgrade recommendation
        function recommendGold(id) {
            $.ajax({
                url: 'admin_actions.php?action=approve_gold',
                type: 'POST',
                data: { member_id: id, action_type: 'upgrade' },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message);
                        loadAllData();
                    }
                }
            });
        }

        // Approve gold card (GM action)
        function approveGold(id) {
            $.ajax({
                url: 'admin_actions.php?action=approve_gold',
                type: 'POST',
                data: { member_id: id, action_type: 'approve' },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message);
                        loadAllData();
                    }
                }
            });
        }

        // Process Redemption request
        function processRedemption(id, status) {
            if (status === 'Rejected') {
                showCustomConfirm("Are you sure you want to REJECT this redemption request?", function() {
                    $.ajax({
                        url: 'admin_actions.php?action=approve_redemption',
                        type: 'POST',
                        data: { request_id: id, status: 'Rejected' },
                        success: function(response) {
                            if (response.success) {
                                showToast(response.message);
                                loadAllData();
                            } else {
                                showToast(response.message, true);
                            }
                        },
                        error: function(xhr) {
                            showToast(xhr.responseJSON?.message || "Failed to process redemption", true);
                        }
                    });
                });
            } else if (status === 'Approved') {
                // Find redemption details from the table row
                const row = $(`#redemptions-table-body td:contains(#${id})`).closest('tr');
                const guestName = row.find('td:nth-child(3)').text();
                const awardClaimed = row.find('td:nth-child(4)').text();
                const pointsCost = row.find('td:nth-child(5)').text();
                
                $('#approve-request-id').val(id);
                $('#approve-guest-name').val(guestName);
                $('#approve-award-title').val(awardClaimed);
                $('#approve-points-cost').val(pointsCost);
                
                // Reset validity dropdown
                $('#approve-validity-select').val('1 Year');
                $('#approve-custom-date-group').hide();
                $('#approve-custom-date-input').val('').prop('required', false);
                
                $('#approveRedemptionModal').css('display', 'flex');
            }
        }

        // Outbound campaign tracker URL generator
        function generateSecureLink() {
            const url = $('#encrypt-target-url').val();
            const memberId = $('#encrypt-member-select').val();
            const staffId = $('#encrypt-staff-select').val();
            const discountRate = $('#encrypt-discount-rate').val();
            const promoCode = $('#encrypt-promo-code').val();

            if (!url) {
                showToast("Please enter a target destination URL.", true);
                return;
            }

            $.ajax({
                url: 'admin_actions.php?action=generate_encrypted_link',
                type: 'POST',
                data: { 
                    target_url: url, 
                    member_id: memberId,
                    staff_id: staffId,
                    discount_rate: discountRate,
                    promo_code: promoCode
                },
                success: function(response) {
                    if (response.success) {
                        const trackerUrl = window.location.origin + '/backend/api/click.php?token=' + response.data.token;
                        $('#encrypted-url-text').text(trackerUrl);
                        $('#encryption-result').fadeIn();
                        showToast("Campaign link generated successfully!");
                        
                        // Reset optional generator inputs
                        $('#encrypt-discount-rate').val('');
                        $('#encrypt-promo-code').val('');
                        $('#encrypt-member-select').val('');
                        $('#encrypt-staff-select').val('');
                        
                        loadCampaignLinks();
                    } else {
                        showToast(response.message, true);
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || "Error generating secure link.", true);
                }
            });
        }

        function copyTextToClipboard(text) {
            if (!text) return;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    showToast("Copied to clipboard!");
                }, function(err) {
                    fallbackCopyTextToClipboard(text);
                });
            } else {
                fallbackCopyTextToClipboard(text);
            }
        }

        function fallbackCopyTextToClipboard(text) {
            var textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                var successful = document.execCommand('copy');
                if (successful) {
                    showToast("Copied to clipboard!");
                } else {
                    showToast("Failed to copy link.", true);
                }
            } catch (err) {
                showToast("Failed to copy link.", true);
            }
            document.body.removeChild(textArea);
        }

        function copyEncryptedUrl() {
            const text = $('#encrypted-url-text').text();
            copyTextToClipboard(text);
        }

        let campaignsTable = null;

        function loadCampaignLinks() {
            if ($.fn.DataTable.isDataTable('#campaignsTable')) {
                $('#campaignsTable').DataTable().ajax.reload(null, false);
            } else {
                campaignsTable = $('#campaignsTable').DataTable({
                    ajax: {
                        url: 'admin_actions.php?action=get_campaign_links',
                        dataSrc: 'data'
                    },
                    columns: [
                        { 
                            data: 'id', 
                            render: function(data) { 
                                return `<strong>${data}</strong>`; 
                            } 
                        },
                        { 
                            data: 'created_at', 
                            render: function(data) { 
                                return `<small>${data}</small>`; 
                            } 
                        },
                        { 
                            data: 'target_url', 
                            render: function(data) {
                                return `<a href="${data}" target="_blank" style="color:var(--accent-gold); text-decoration:none; font-size:12px; word-break:break-all;">${data}</a>`;
                            }
                        },
                        { 
                            data: null, 
                            render: function(row) {
                                return row.member_id 
                                    ? `<strong>${row.first_name} ${row.last_name}</strong><br><small style="color:var(--accent-gold);">${row.membership_number}</small>`
                                    : `<span style="color:var(--text-muted);">General Campaign</span>`;
                            }
                        },
                        { 
                            data: null, 
                            render: function(row) {
                                return row.staff_name 
                                    ? `<strong>${row.staff_name}</strong><br><small style="color:var(--text-muted);">${row.staff_id}</small>`
                                    : `<span style="color:var(--text-muted);">None</span>`;
                            }
                        },
                        { 
                            data: null, 
                            render: function(row) {
                                return `Rate: <strong>${row.discount_rate || 'N/A'}</strong><br>Code: <strong>${row.promo_code || 'N/A'}</strong>`;
                            }
                        },
                        { 
                            data: 'click_count', 
                            render: function(data) {
                                return `<strong style="font-size:16px; color: var(--text-main);">${data}</strong> hits`;
                            }
                        },
                        { 
                            data: null, 
                            orderable: false, 
                            render: function(row) {
                                const trackerUrl = window.location.origin + '/backend/api/click.php?token=' + row.token;
                                return `<button class="btn btn-secondary" style="padding:4px 8px; font-size:11px;" onclick="copySpecificUrl('${trackerUrl}')"><i class="fa-solid fa-copy"></i> Copy Link</button>`;
                            }
                        }
                    ],
                    pageLength: 5,
                    ordering: true,
                    order: [[0, "desc"]]
                });
            }
        }

        function copySpecificUrl(url) {
            copyTextToClipboard(url);
        }

        // Settings Form Submission
        $('#settingsForm').on('submit', function(e) {
            e.preventDefault();
            
            const rules = [];
            $('.points-rule-row').each(function() {
                const service = $(this).find('.rule-service').val();
                const threshold = $(this).find('.rule-threshold').val();
                const points = $(this).find('.rule-points').val();
                if (service && threshold && points) {
                    rules.push({
                        service: service,
                        threshold: parseFloat(threshold),
                        points: parseInt(points)
                    });
                }
            });

            const vouchers = [];
            $('.voucher-rule-row').each(function() {
                const name = $(this).find('.voucher-name').val();
                const points = $(this).find('.voucher-points').val();
                const category = $(this).find('.voucher-category').val();
                const description = $(this).find('.voucher-desc').val();
                if (name && points && category) {
                    vouchers.push({
                        id: 'vch-' + Math.floor(Math.random() * 1000000),
                        name: name,
                        points: parseInt(points),
                        category: category,
                        description: description
                    });
                }
            });

            const formData = new FormData();
            formData.append('timezone', $('#settings-timezone').val());
            formData.append('currency', $('#settings-currency').val());
            formData.append('gold_upgrade_threshold', $('#settings-gold-threshold').val());
            formData.append('fb_points_rules', JSON.stringify(rules));
            formData.append('departments', JSON.stringify(globalDepartments));
            formData.append('redeemable_vouchers', JSON.stringify(vouchers));
            formData.append('hotel_name', $('#settings-hotel-name').val());
            formData.append('hotel_sub', $('#settings-hotel-sub').val());
            formData.append('hotel_logo', $('#settings-hotel-logo-value').val());
            
            const fileInput = $('#settings-hotel-logo-file')[0];
            if (fileInput.files.length > 0) {
                formData.append('logo_file', fileInput.files[0]);
            }

            $.ajax({
                url: 'admin_actions.php?action=save_settings',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showToast("System settings updated successfully!");
                        $('#settings-hotel-logo-file').val('');
                        loadAllData();
                    } else {
                        showToast(response.message, true);
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || "Error saving system settings.", true);
                }
            });
        });

        function addPointsRuleRow(service = 'F&B', threshold = '', points = '') {
            const rowId = 'points-rule-row-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
            
            let optHtml = '';
            globalDepartments.forEach(dept => {
                const selected = dept === service ? 'selected' : '';
                optHtml += `<option value="${dept}" ${selected}>${dept}</option>`;
            });

            const rowHtml = `
                <div class="form-grid points-rule-row" id="${rowId}" style="grid-template-columns: 1.2fr 1fr 1fr auto; align-items: flex-end; margin-bottom: 12px; gap: 15px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Service / Department</label>
                        <select class="rule-service" data-selected="${service}" required style="width: 100%; padding: 8px 10px; font-size:12px; border: 1px solid var(--border-color); border-radius: 4px; background: var(--bg-color); color: var(--text-main);">
                            ${optHtml}
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Spending Threshold (<span class="currency-symbol">BHD</span>)</label>
                        <input type="number" step="0.001" class="rule-threshold" required value="${threshold}" placeholder="0.000">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Points Awarded</label>
                        <input type="number" step="1" class="rule-points" required value="${points}" placeholder="Points">
                    </div>
                    <div style="padding-bottom: 2px;">
                        <button type="button" class="btn btn-secondary" style="color:var(--danger); border-color:var(--danger); background:rgba(239,68,68,0.05); padding: 8px 12px;" onclick="$('#${rowId}').remove()"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </div>
            `;
            $('#points-rules-container').append(rowHtml);
            updateRulesCurrencyLabel();
        }

        function updateRulesCurrencyLabel() {
            const currency = $('#settings-currency').val() || 'BHD';
            $('.currency-symbol').text(currency);
        }

        $('#settings-currency').on('input', updateRulesCurrencyLabel);
        
        function updateGlobalCurrency(currency) {
            currentCurrency = currency;
            $('.currency-label').text(currency);
        }

        let globalVouchers = [];

        function addVoucherRow(id = '', name = '', points = '', category = 'meals', description = '') {
            const rowId = 'voucher-rule-row-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
            
            const cats = [
                { val: 'meals', text: '🍽️ Meals' },
                { val: 'fitness', text: '💪 Fitness' },
                { val: 'gift', text: '🎁 Gifts' },
                { val: 'nights', text: '🏨 Nights' }
            ];
            
            let catOptions = '';
            cats.forEach(c => {
                const selected = c.val === category ? 'selected' : '';
                catOptions += `<option value="${c.val}" ${selected}>${c.text}</option>`;
            });

            const rowHtml = `
                <div class="form-grid voucher-rule-row" id="${rowId}" style="grid-template-columns: 1.5fr 1fr 1.2fr 2fr auto; align-items: flex-end; margin-bottom: 12px; gap: 15px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Voucher Name</label>
                        <input type="text" class="voucher-name" required value="${name}" placeholder="e.g. Dinner for two">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Points Cost</label>
                        <input type="number" step="1" class="voucher-points" required value="${points}" placeholder="Points">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Category</label>
                        <select class="voucher-category" required style="width: 100%; padding: 8px 10px; font-size:12px; border: 1px solid var(--border-color); border-radius: 4px; background: var(--bg-color); color: var(--text-main);">
                            ${catOptions}
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Voucher Description</label>
                        <input type="text" class="voucher-desc" required value="${description}" placeholder="e.g. Dinner Buffet for two at KOLORS">
                    </div>
                    <div style="padding-bottom: 2px;">
                        <button type="button" class="btn btn-secondary" style="color:var(--danger); border-color:var(--danger); background:rgba(239,68,68,0.05); padding: 8px 12px;" onclick="$('#${rowId}').remove()"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </div>
            `;
            $('#vouchers-settings-container').append(rowHtml);
        }

        function populateRedemptionVouchersDropdown(availablePoints) {
            const select = $('#redeem-voucher-select');
            select.empty();
            select.append('<option value="">-- Choose Voucher --</option>');
            
            let count = 0;
            globalVouchers.forEach(v => {
                if (v.points <= availablePoints) {
                    select.append(`<option value="${v.name}" data-points="${v.points}">${v.name} (${v.points} Pts)</option>`);
                    count++;
                }
            });
            
            if (count === 0) {
                select.append('<option value="" disabled style="color:var(--danger);">No affordable vouchers (insufficient points)</option>');
            }
            
            $('#redeem-points-input').val('0');
        }
        
        function onRedeemVoucherSelect(val) {
            const selectedOpt = $('#redeem-voucher-select option:selected');
            const points = selectedOpt.data('points') || 0;
            $('#redeem-points-input').val(points);
        }

        function onRedeemValidityChange(val) {
            if (val === 'Custom Date') {
                $('#redeem-custom-date-group').show();
                $('#redeem-custom-date-input').prop('required', true);
            } else {
                $('#redeem-custom-date-group').hide();
                $('#redeem-custom-date-input').prop('required', false);
            }
        }

        function onApproveValidityChange(val) {
            if (val === 'Custom Date') {
                $('#approve-custom-date-group').show();
                $('#approve-custom-date-input').prop('required', true);
            } else {
                $('#approve-custom-date-group').hide();
                $('#approve-custom-date-input').prop('required', false);
            }
        }

        function showCustomConfirm(message, onConfirm) {
            $('#custom-confirm-message').text(message);
            $('#customConfirmModal').css('display', 'flex');
            
            // Unbind previous clicks
            $('#custom-confirm-ok-btn').off('click').on('click', function() {
                $('#customConfirmModal').css('display', 'none');
                if (typeof onConfirm === 'function') onConfirm();
            });
            
            $('#custom-confirm-cancel-btn').off('click').on('click', function() {
                $('#customConfirmModal').css('display', 'none');
            });
        }

        function markVoucherAsUsed(voucherId, memberId) {
            showCustomConfirm("Are you sure you want to mark this voucher as USED? This action cannot be undone.", function() {
                $.ajax({
                    url: 'admin_actions.php?action=use_voucher',
                    type: 'POST',
                    data: { voucher_id: voucherId },
                    success: function(response) {
                        if (response.success) {
                            showToast(response.message);
                            
                            // Extract subtitles to refresh modal dynamically
                            const subtitleText = $('#details-modal-subtitle').text();
                            const match = subtitleText.match(/Guest:\s*([^\(]+)\(([^)]+)\)\s*\|\s*Program:\s*(.*)/);
                            const name = match ? match[1].trim() : '';
                            const cardNum = match ? match[2].trim() : '';
                            const program = match ? match[3].trim() : '';
                            
                            viewMemberDetails(memberId, name, cardNum, program);
                            loadAllData();
                        } else {
                            showToast(response.message, true);
                        }
                    },
                    error: function() {
                        showToast("Error updating voucher status", true);
                    }
                });
            });
        }

        // Approve Redemption Submit
        $('#approveRedemptionForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: 'admin_actions.php?action=approve_redemption',
                type: 'POST',
                data: $(this).serialize() + '&status=Approved',
                success: function(response) {
                    if (response.success) {
                        showToast(response.message);
                        $('#approveRedemptionModal').css('display', 'none');
                        loadAllData();
                    } else {
                        showToast(response.message, true);
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || "Failed to approve redemption", true);
                }
            });
        });

        let globalDepartments = [];

        function renderDepartmentsList() {
            let html = '';
            globalDepartments.forEach((dept, index) => {
                html += `
                    <div class="dept-badge" style="display: inline-flex; align-items: center; background: var(--bg-color); border: 1px solid var(--border-color); padding: 6px 12px; border-radius: 20px; font-size: 13px; color: var(--text-main); font-weight: 500;">
                        <span>${dept}</span>
                        <button type="button" style="background: none; border: none; color: var(--danger); margin-left: 8px; cursor: pointer; font-size: 14px; padding: 0; line-height: 1;" onclick="removeDepartment(${index})"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                `;
            });
            if (globalDepartments.length === 0) {
                html = '<p style="color: var(--text-muted); font-size: 13px;">No departments configured.</p>';
            }
            $('#departments-list-container').html(html);

            // Re-populate dropdowns
            let srvHtml = '';
            let deptHtml = '<option value="">-- Choose Department --</option>';
            globalDepartments.forEach(srv => {
                srvHtml += `<option value="${srv}">${srv}</option>`;
                deptHtml += `<option value="${srv}">${srv}</option>`;
            });
            $('#spendingForm select[name="source_dept"]').html(srvHtml);
            $('#add-staff-department-select').html(deptHtml);

            // Re-populate dropdowns in existing point rules rows without losing selection
            $('.points-rule-row').each(function() {
                const $select = $(this).find('.rule-service');
                if ($select.length) {
                    const currentVal = $select.val() || $select.attr('data-selected');
                    let optHtml = '';
                    globalDepartments.forEach(dept => {
                        const selected = dept === currentVal ? 'selected' : '';
                        optHtml += `<option value="${dept}" ${selected}>${dept}</option>`;
                    });
                    $select.html(optHtml);
                }
            });
        }

        function addDepartmentFromInput() {
            const val = $('#new-dept-input').val().trim();
            if (val === '') return;
            if (globalDepartments.includes(val)) {
                showToast("Department already exists!", true);
                return;
            }
            globalDepartments.push(val);
            $('#new-dept-input').val('');
            renderDepartmentsList();
        }

        function removeDepartment(index) {
            globalDepartments.splice(index, 1);
            renderDepartmentsList();
        }

        function refreshMembersOnly() {
            const activePane = $('.view-pane.active-pane').attr('id');
            if (activePane !== 'view-members') return;

            // Prevent background refresh if user is searching or modal is active
            if ($.fn.DataTable.isDataTable('#membersTable')) {
                if ($('#membersTable_filter input').is(':focus') || $('.modal:visible').length > 0) {
                    return;
                }
            }

            $.ajax({
                url: 'admin_actions.php?action=get_members',
                type: 'GET',
                cache: false,
                success: function(response) {
                    if (response.success) {
                        const members = response.data;
                        let kplus = 0;
                        let kreward = 0;
                        let tableHtml = '';

                        members.forEach(member => {
                            // Update membersMap local cache
                            membersMap[member.id] = member;

                            if (member.membership_type === 'K Plus') kplus++;
                            else kreward++;

                            let badgeClass = 'badge-active';
                            if (member.status === 'Pending Approval') badgeClass = 'badge-pending';
                            if (member.status === 'Expired') badgeClass = 'badge-expired';

                            const spendBtn = member.is_verified === 1
                                ? `<button class="btn btn-secondary" style="padding: 6px 12px; font-size:12px; background:rgba(16,185,129,0.1); color:#10b981;" onclick="openSpendingModal(${member.id}, '${member.first_name} ${member.last_name}')"><i class="fa-solid fa-plus"></i> Spend</button>`
                                : `<button class="btn btn-secondary" style="padding: 6px 12px; font-size:12px; opacity:0.4; cursor:not-allowed;" disabled title="Verify card via QR/WhatsApp first"><i class="fa-solid fa-lock"></i> Spend</button>`;

                            const redeemBtn = `<button class="btn btn-secondary" style="padding: 6px 12px; font-size:12px; border-color:var(--primary); color:var(--primary); background:rgba(217,119,6,0.05);" onclick="openRedeemModal(${member.id}, '${member.first_name} ${member.last_name}', ${member.points_balance})"><i class="fa-solid fa-gift"></i> Redeem</button>`;

                            tableHtml += `
                                <tr>
                                    <td><strong style="color:var(--accent-gold);">${formatCardNumber(member.membership_number)}</strong></td>
                                    <td>${member.title} ${member.first_name} ${member.last_name}</td>
                                    <td>${member.mobile}</td>
                                    <td>${member.nationality}</td>
                                    <td>${member.membership_type}</td>
                                    <td><strong style="color:var(--accent-gold);">${member.points_balance}</strong> Pts</td>
                                    <td><span class="badge ${member.card_type === 'Gold' ? 'badge-pending' : 'badge-active'}">${member.card_type}</span></td>
                                    <td><span class="badge ${badgeClass}">${member.status}</span></td>
                                    <td>${formatDateToDMY(member.expiry_date)}</td>
                                    <td>
                                        ${spendBtn}
                                        ${redeemBtn}
                                        <button class="btn btn-secondary" style="padding: 6px 12px; font-size:12px;" onclick="showMemberPassQR(${member.id}, '${member.membership_number}')"><i class="fa-solid fa-qrcode"></i> QR Pass</button>
                                        <button class="btn btn-secondary" style="padding: 6px 12px; font-size:12px;" onclick="viewMemberDetails(${member.id}, '${member.first_name} ${member.last_name}', '${member.membership_number}', '${member.membership_type}')"><i class="fa-solid fa-list"></i> Details</button>
                                        <button class="btn btn-secondary" style="padding: 6px 12px; font-size:12px; color:var(--text-main); border-color:var(--border-color); background:var(--bg-color);" onclick="startMemberEdit(${member.id})"><i class="fa-solid fa-pencil"></i> Edit</button>
                                        ${member.card_type !== 'Gold' && member.membership_type === 'K Plus' ? 
                                            `<button class="btn" style="padding: 6px 12px; font-size:12px;" onclick="recommendGold(${member.id})"><i class="fa-solid fa-arrow-up"></i> Upgrade</button>` : ''
                                        }
                                    </td>
                                </tr>
                            `;
                        });

                        $('#stat-kplus-count').text(kplus);
                        $('#stat-kreward-count').text(kreward);
                        
                        if ($.fn.DataTable.isDataTable('#membersTable')) {
                            $('#membersTable').DataTable().destroy();
                        }
                        $('#member-table-body').html(tableHtml);
                        $('#membersTable').DataTable({
                            "pageLength": 10,
                            "ordering": true,
                            "stateSave": true,
                            "language": {
                                "search": "Filter Members:"
                            }
                        });
                    }
                }
            });
        }

        function openRedeemModal(id, name, balance) {
            $('#redeem-member-id').val(id);
            $('#redeem-guest-name').val(name);
            $('#redeem-available-balance').val(balance + ' Pts');
            
            // Populate vouchers dropdown based on available points
            populateRedemptionVouchersDropdown(balance);
            
            // Reset validity fields
            $('#redeem-validity-select').val('1 Year');
            $('#redeem-custom-date-group').hide();
            $('#redeem-custom-date-input').val('').prop('required', false);
            
            $('#redeemPointsModal').css('display', 'flex');
        }

        $('#redeemPointsForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: 'admin_actions.php?action=redeem_member_points',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        showToast("Points redeemed successfully!");
                        $('#redeemPointsModal').css('display', 'none');
                        $('#redeemPointsForm')[0].reset();
                        loadAllData();
                    } else {
                        showToast(response.message, true);
                    }
                },
                error: function() {
                    showToast("Error processing redemption", true);
                }
            });
        });

        // Search filter handled by DataTables automatically

        $(document).ready(function() {
            // Apply saved theme or default light
            const currentTheme = localStorage.getItem('admin-theme') || 'light';
            if (currentTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                $('#theme-toggle-btn').html('<i class="fa-solid fa-sun"></i> Light Mode');
            } else {
                document.documentElement.removeAttribute('data-theme');
                $('#theme-toggle-btn').html('<i class="fa-solid fa-moon"></i> Dark Mode');
            }
            loadAllData();
            loadStaffDirectory();
            // Clear logo handler
            $('#clear-logo-btn').on('click', function() {
                $('#settings-hotel-logo-value').val('');
                $('#settings-hotel-logo-file').val('');
                $('#settings-logo-preview').attr('src', '').hide();
                $(this).hide();
            });

            // Logo file select preview handler
            $('#settings-hotel-logo-file').on('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#settings-logo-preview').attr('src', e.target.result).show();
                        $('#clear-logo-btn').show();
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Poll for verified guest status updates every 8 seconds
            setInterval(refreshMembersOnly, 8000);
        });

        let globalStaffDirectory = [];

        function loadStaffDirectory() {
            $.ajax({
                url: 'admin_actions.php?action=get_staff_members',
                type: 'GET',
                cache: false,
                success: function(response) {
                    if (response.success) {
                        globalStaffDirectory = response.data;
                        
                        // Populate staff list in Manage Modal
                        let tableHtml = '';
                        globalStaffDirectory.forEach(st => {
                            tableHtml += `
                                <tr>
                                    <td><strong>${st.staff_id}</strong></td>
                                    <td>${st.name}</td>
                                    <td>${st.department}</td>
                                    <td><strong style="color: var(--accent-gold);">${parseFloat(st.incentive_pct || 0).toFixed(1)}%</strong></td>
                                    <td>
                                        <div style="display:flex; gap:5px;">
                                            <button class="btn btn-secondary" style="padding: 4px 8px; color: var(--text-main); border-color: var(--border-color); background: var(--bg-color);" onclick="startStaffEdit(${st.id}, '${st.staff_id}', '${st.name.replace(/'/g, "\\'")}', '${st.department}', ${st.incentive_pct})"><i class="fa-solid fa-pencil"></i></button>
                                            <button class="btn btn-secondary" style="padding: 4px 8px; color: var(--danger); border-color: var(--danger); background:rgba(239,68,68,0.05);" onclick="deleteStaffMember(${st.id})"><i class="fa-solid fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                        $('#staff-directory-list-body').html(tableHtml);

                        // Populate referral dropdown in New Enrolment
                        let enrolSelectHtml = '<option value="">-- Choose Registered Staff Referrer --</option>';
                        let encryptStaffHtml = '<option value="">-- No Staff Referrer --</option>';
                        globalStaffDirectory.forEach(st => {
                            enrolSelectHtml += `<option value="${st.staff_id}">${st.name} (${st.staff_id}) - ${st.department}</option>`;
                            encryptStaffHtml += `<option value="${st.staff_id}">${st.name} (${st.staff_id}) - ${st.department}</option>`;
                        });
                        $('#enrol-staff-select').html(enrolSelectHtml);
                        $('#encrypt-staff-select').html(encryptStaffHtml);
                    }
                }
            });
        }

        function openStaffDirectoryModal() {
            loadStaffDirectory();
            $('#staffDirectoryModal').css('display', 'flex');
        }

        // Add / Edit staff member form submit
        $('#addStaffForm').on('submit', function(e) {
            e.preventDefault();
            const editId = $('#staff-edit-id').val();
            const actionUrl = editId ? 'admin_actions.php?action=edit_staff_member' : 'admin_actions.php?action=add_staff_member';
            
            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        showToast(editId ? "Staff member updated successfully!" : "Staff member added successfully!");
                        cancelStaffEdit();
                        loadStaffDirectory();
                    } else {
                        showToast(response.message, true);
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || "Error adding staff member", true);
                }
            });
        });

        // Delete staff member
        function deleteStaffMember(id) {
            showCustomConfirm("Are you sure you want to remove this staff member from the directory?", function() {
                $.ajax({
                    url: 'admin_actions.php?action=delete_staff_member',
                    type: 'POST',
                    data: { id: id },
                    success: function(response) {
                        if (response.success) {
                            showToast("Staff member removed.");
                            loadStaffDirectory();
                        } else {
                            showToast(response.message, true);
                        }
                    }
                });
            });
        }

        function onEnrolStaffChange(staffId) {
            const member = globalStaffDirectory.find(st => st.staff_id === staffId);
            if (member) {
                $('#enrol-staff-id').val(member.staff_id);
                $('#enrol-staff-name').val(member.name);
                $('#enrol-staff-dept').val(member.department);
            } else {
                $('#enrol-staff-id').val('');
                $('#enrol-staff-name').val('');
                $('#enrol-staff-dept').val('');
            }
        }

        function renderSpendStaffChecklist(dept) {
            const container = $('#spend-staff-checklist-container');
            container.empty();
            
            const filteredStaff = globalStaffDirectory.filter(st => st.department === dept);
            if (filteredStaff.length === 0) {
                container.html(`<p style="color: var(--text-muted); font-size: 12px; text-align: center; margin: 10px 0;">No staff registered under department: ${dept}</p>`);
                return;
            }
            
            let html = '';
            filteredStaff.forEach(st => {
                const rowId = 'spend-staff-check-' + st.staff_id;
                html += `
                    <div class="spend-staff-check-row" style="display: grid; grid-template-columns: auto 1fr 1fr 1.2fr; align-items: center; padding: 8px; border-bottom: 1px solid var(--border-color); gap: 15px;">
                        <div>
                            <input type="checkbox" class="staff-checkbox" data-id="${st.staff_id}" data-name="${st.name}" data-dept="${st.department}" id="${rowId}" style="width: 18px; height: 18px; cursor: pointer;" onchange="toggleSpendStaffPctInput('${rowId}', this.checked)">
                        </div>
                        <div>
                            <label for="${rowId}" style="cursor: pointer; margin-bottom: 0; font-size:13px; font-weight:600; color:var(--text-main);">${st.name}</label>
                        </div>
                        <div style="font-size:12px; color:var(--text-muted);">
                            Code: <strong>${st.staff_id}</strong>
                        </div>
                        <div>
                            <div style="display:flex; align-items:center; gap:5px; justify-content:flex-end;">
                                <input type="number" step="0.1" class="staff-pct-input" value="${parseFloat(st.incentive_pct || 0).toFixed(1)}" disabled min="0" max="100" style="padding: 4px 8px; font-size:12px; width: 70px; text-align: right; background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-main);">
                                <span style="font-size:12px; color:var(--text-muted);">%</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            container.html(html);
        }

        function toggleSpendStaffPctInput(rowId, isChecked) {
            const row = $('#' + rowId).closest('.spend-staff-check-row');
            row.find('.staff-pct-input').prop('disabled', !isChecked);
        }

        function startStaffEdit(id, staffId, name, department, pct) {
            $('#staff-edit-id').val(id);
            $('#staff-form-staff-id').val(staffId);
            $('#staff-form-name').val(name);
            $('#add-staff-department-select').val(department);
            $('#staff-form-pct').val(parseFloat(pct || 0).toFixed(1));
            
            $('#staff-form-title').html('<i class="fa-solid fa-pencil"></i> Edit Staff: ' + name);
            $('#staff-form-submit-btn').html('<i class="fa-solid fa-save"></i> Update Staff Member');
            $('#staff-form-cancel-btn').show();
        }

        function cancelStaffEdit() {
            $('#staff-edit-id').val('');
            $('#addStaffForm')[0].reset();
            
            $('#staff-form-title').html('<i class="fa-solid fa-plus-circle"></i> Add Staff');
            $('#staff-form-submit-btn').html('<i class="fa-solid fa-plus"></i> Add to Directory');
            $('#staff-form-cancel-btn').hide();
        }

        function formatCardNumber(num) {
            if (!num) return '';
            let cleaned = num.replace(/[\s-]/g, '');
            if (cleaned.length === 16) {
                return cleaned.match(/.{1,4}/g).join(' ');
            }
            return num;
        }

        function formatDateToDMY(dateStr) {
            if (!dateStr) return 'N/A';
            const parts = dateStr.split('-');
            if (parts.length !== 3) return dateStr;
            
            const year = parts[0];
            const monthIdx = parseInt(parts[1], 10) - 1;
            const day = parts[2];
            
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const monthName = months[monthIdx] || parts[1];
            
            return `${day}-${monthName}-${year}`;
        }

        function startMemberEdit(memberId) {
            const member = membersMap[memberId];
            if (!member) { showToast('Member data not found. Please refresh.', true); return; }
            
            $('#enrol-member-edit-id').val(member.id);
            $('#enrolmentForm select[name="title"]').val(member.title);
            $('#enrolmentForm input[name="first_name"]').val(member.first_name);
            $('#enrolmentForm input[name="last_name"]').val(member.last_name);
            $('#enrolmentForm input[name="id_number"]').val(member.id_number);
            $('#enrolmentForm input[name="nationality"]').val(member.nationality);
            $('#enrolmentForm textarea[name="address"]').val(member.address);
            $('#enrolmentForm input[name="mobile"]').val(member.mobile);
            $('#enrolmentForm input[name="email"]').val(member.email);
            $('#enrolmentForm input[name="company_name"]').val(member.company_name);
            $('#enrolmentForm input[name="position"]').val(member.position);
            
            $('#enroll-type-select').val(member.membership_type).trigger('change');
            $('#enroll-tier-select').val(member.card_type).trigger('change');
            
            $('#enrolmentForm input[name="gold_reason"]').val(member.gold_reason || '');
            
            $('#enrol-form-title').html('<i class="fa-solid fa-pencil"></i> Edit Member: ' + member.first_name + ' ' + member.last_name + ' (' + formatCardNumber(member.membership_number) + ')');
            $('#enrol-submit-btn').html('<i class="fa-solid fa-save"></i> Update Member Profile');
            $('#enrol-cancel-btn').show();
            
            $('.sidebar-menu li').removeClass('active');
            $('.sidebar-menu li[data-target="view-enrolment"]').addClass('active');
            $('.view-pane').removeClass('active-pane');
            $('#view-enrolment').addClass('active-pane');
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function cancelMemberEdit() {
            $('#enrol-member-edit-id').val('');
            $('#enrolmentForm')[0].reset();
            
            $('#enroll-type-select').trigger('change');
            
            $('#enrol-form-title').html('<i class="fa-solid fa-user"></i> Guest Details');
            $('#enrol-submit-btn').html('<i class="fa-solid fa-save"></i> Save Loyalty Enrolment');
            $('#enrol-cancel-btn').hide();
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            if (currentTheme === 'dark') {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('admin-theme', 'light');
                $('#theme-toggle-btn').html('<i class="fa-solid fa-moon"></i> Dark Mode');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('admin-theme', 'dark');
                $('#theme-toggle-btn').html('<i class="fa-solid fa-sun"></i> Light Mode');
            }
        }

        // Load system users list (Admin Only)
        function loadUsersList() {
            $.ajax({
                url: 'admin_actions.php?action=get_users',
                type: 'GET',
                cache: false,
                success: function(response) {
                    if (response.success) {
                        let html = '';
                        const currentUsername = <?php echo json_encode($currentUsername); ?>;
                        response.data.forEach(user => {
                            const isSelf = user.username === currentUsername;
                            const statusBadge = user.status === 'active' 
                                ? `<span class="badge badge-active">Active</span>`
                                : `<span class="badge badge-expired">Inactive</span>`;
                                
                            const toggleBtn = isSelf 
                                ? `<button class="btn btn-secondary" style="padding: 4px 8px; font-size: 11px; opacity: 0.5;" disabled>Toggle Status</button>`
                                : `<button class="btn btn-secondary" style="padding: 4px 8px; font-size: 11px;" onclick="toggleUserStatus(${user.id}, '${user.status}')">Toggle Status</button>`;
                                
                            const resetBtn = `<button class="btn btn-secondary" style="padding: 4px 8px; font-size: 11px;" onclick="adminResetUserPassword(${user.id}, '${user.username}')"><i class="fa-solid fa-key"></i> Reset Pass</button>`;

                            html += `
                                <tr>
                                    <td><strong>${user.id}</strong></td>
                                    <td>${user.username}</td>
                                    <td><span style="text-transform: capitalize;">${user.role}</span></td>
                                    <td>${statusBadge}</td>
                                    <td>${user.created_at}</td>
                                    <td>
                                        <div style="display:flex; gap:5px;">
                                            ${toggleBtn}
                                            ${resetBtn}
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                        
                        if ($.fn.DataTable.isDataTable('#usersTable')) {
                            $('#usersTable').DataTable().destroy();
                        }
                        $('#users-table-body').html(html);
                        $('#usersTable').DataTable({
                            "pageLength": 10,
                            "ordering": true,
                            "destroy": true
                        });
                    }
                }
            });
        }

        // Add User Account (Admin Only)
        $('#addUserForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'admin_actions.php?action=add_user',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        showToast("User account created successfully!");
                        $('#addUserForm')[0].reset();
                        loadUsersList();
                    } else {
                        showToast(response.message, true);
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || "Error creating user account.", true);
                }
            });
        });

        // Toggle User Status (Admin Only)
        function toggleUserStatus(userId, currentStatus) {
            showCustomConfirm("Are you sure you want to change this user's active/inactive status?", function() {
                $.ajax({
                    url: 'admin_actions.php?action=toggle_user_status',
                    type: 'POST',
                    data: { id: userId, current_status: currentStatus },
                    success: function(response) {
                        if (response.success) {
                            showToast(response.message);
                            loadUsersList();
                        } else {
                            showToast(response.message, true);
                        }
                    },
                    error: function(xhr) {
                        showToast(xhr.responseJSON?.message || "Error updating user status.", true);
                    }
                });
            });
        }

        // Admin reset user's password (Admin Only)
        function adminResetUserPassword(userId, username) {
            $('#admin-reset-user-id').val(userId);
            $('#admin-reset-username').val(username);
            $('#adminResetPasswordForm')[0].reset();
            $('#adminResetPasswordModal').css('display', 'flex');
        }

        $('#adminResetPasswordForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'admin_actions.php?action=admin_reset_password',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        showToast("User password has been reset successfully.");
                        $('#adminResetPasswordModal').css('display', 'none');
                    } else {
                        showToast(response.message, true);
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || "Error resetting user password.", true);
                }
            });
        });

        // Change own password
        function openChangePasswordModal() {
            $('#changePasswordForm')[0].reset();
            $('#changePasswordModal').css('display', 'flex');
        }

        $('#changePasswordForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'admin_actions.php?action=change_self_password',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        showToast("Your password was updated successfully!");
                        $('#changePasswordModal').css('display', 'none');
                    } else {
                        showToast(response.message, true);
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || "Error changing password.", true);
                }
            });
        });
    </script>
</body>
</html>
