<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>K Hotel Loyalty Program - Admin Control Center</title>
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
            <i class="fa-solid fa-hotel"></i>
            <div class="brand-text">
                <h1>The K Hotel</h1>
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
            <li class="menu-item" data-view="settings">
                <a href="#"><i class="fa-solid fa-gear"></i> Settings</a>
            </li>
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
            <div style="display: flex; gap: 10px;">
                <button class="btn btn-secondary" id="theme-toggle-btn" onclick="toggleTheme()"><i class="fa-solid fa-moon"></i> Dark Mode</button>
                <button class="btn btn-secondary" onclick="loadAllData()"><i class="fa-solid fa-arrows-rotate"></i> Refresh</button>
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
            <div class="form-section">
                <div class="section-title"><i class="fa-solid fa-user-shield"></i> Outbound URL Encryption Engine</div>
                <p style="color: var(--text-muted); margin-bottom: 20px; font-size: 14px;">
                    Ensure outbound email/SMS promotional campaigns send fully-encrypted secure tokens rather than raw, editable URL queries. 
                    This prevents users from tampering with user IDs, discount ratios, or promotion identifiers.
                </p>
                
                <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label>Target Offer / Promotion URL</label>
                        <input type="text" id="encrypt-target-url" value="https://thekhotel.com/promos/special_brunch.php" placeholder="Enter base promo page url">
                    </div>
                    <div class="form-group">
                        <label>Query Parameters (Format: key1=val1&key2=val2)</label>
                        <input type="text" id="encrypt-params" value="member_id=12&discount_rate=20&promo_code=BRUNCH2026&staff_ref=FO102" placeholder="e.g. member_id=45&discount=15">
                    </div>
                </div>
                <div style="margin-top: 10px;">
                    <button class="btn" onclick="generateSecureLink()"><i class="fa-solid fa-lock"></i> Encrypt Link & Generate Token</button>
                </div>
                
                <div class="encrypted-result-box" id="encryption-result">
                    <h4 style="color: var(--accent-gold); margin-bottom: 8px;"><i class="fa-solid fa-check-circle"></i> Securely Encrypted URL:</h4>
                    <p id="encrypted-url-text" style="font-family: monospace; font-size: 13px; color: var(--text-main);"></p>
                    <div style="margin-top: 15px; display: flex; gap: 10px;">
                        <button class="btn btn-secondary" onclick="copyEncryptedUrl()"><i class="fa-solid fa-copy"></i> Copy Link</button>
                        <button class="btn btn-secondary" onclick="testDecryptToken()"><i class="fa-solid fa-key"></i> Test Decrypt Token</button>
                    </div>
                </div>

                <div class="encrypted-result-box" id="decryption-result" style="border-color: var(--success);">
                    <h4 style="color: var(--success); margin-bottom: 8px;"><i class="fa-solid fa-unlock"></i> Decrypted Payload Result:</h4>
                    <pre id="decrypted-json-text" style="font-family: monospace; font-size: 13px; color: var(--text-main);"></pre>
                </div>
            </div>
        </div>

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
                    <label>Points to Redeem *</label>
                    <input type="number" step="1" name="points" id="redeem-points-input" required placeholder="Enter points amount">
                </div>
                <div class="form-group" style="margin-bottom:20px;">
                    <label>Redemption Description / Reference *</label>
                    <input type="text" name="description" required placeholder="e.g. Free Buffet Meal, 1 Night Stay">
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn btn-secondary" onclick="$('#redeemPointsModal').css('display', 'none')">Cancel</button>
                    <button type="submit" class="btn">Redeem Points</button>
                </div>
            </form>
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
    <!-- Toast Notification -->
    <div class="toast" id="adminToast">
        <i class="fa-solid fa-circle-check" style="color: var(--success); font-size: 20px;"></i>
        <span id="toast-message">Operation successful!</span>
    </div>

    <!-- AJAX Scripts -->
    <script>
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
                settings: { title: 'System Settings', subtitle: 'Configure timezone, currency, and points calculations' }
            };

            $('#view-title').text(titles[view].title);
            $('#view-subtitle').text(titles[view].subtitle);
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
                error: function() {
                    showToast("Error recording spending", true);
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
                                    <td>${member.expiry_date}</td>
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

                        recommend.forEach(rec => {
                            actionItemsHtml += `
                                <div style="display:flex; justify-content:space-between; align-items:center; background:var(--card-bg); border:1px solid var(--border-color); padding:16px; border-radius:8px; margin-bottom:10px; border-left:4px solid var(--accent-gold);">
                                    <div>
                                        <h5 style="color:var(--text-main); font-size:14px;"><i class="fa-solid fa-circle-exclamation"></i> SPENDING MILESTONE REACHED: Gold Upgrade Recommended</h5>
                                        <p style="font-size:12px; color:var(--text-muted); margin-top:4px;">Guest: <strong>${rec.first_name} ${rec.last_name} (${rec.membership_number})</strong> | Tier: ${rec.card_type} | Total spent: <strong>${rec.total_spending} BHD</strong> (Threshold >= 500.000 BHD)</p>
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

            // Load settings
            $.ajax({
                url: 'admin_actions.php?action=get_settings',
                type: 'GET',
                cache: false,
                success: function(response) {
                    if (response.success) {
                        const settings = response.data;
                        $('#settings-timezone').val(settings.timezone || 'Asia/Bahrain');
                        $('#settings-currency').val(settings.currency || 'BHD');
                        
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
                    }
                }
            });
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
            const message = `Dear Valued Guest, here is your dynamic secure digital loyalty pass for The K Hotel: ${currentPassUrl}`;
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
                        
                        // Show modal
                        $('#memberDetailsModal').css('display', 'flex');
                    }
                }
            });
        }

        function switchModalTab(tab) {
            if (tab === 'spend') {
                $('#modal-tab-spend').css('border-bottom-color', 'var(--primary)');
                $('#modal-tab-points').css('border-bottom-color', 'transparent');
                $('#modal-content-spend').show();
                $('#modal-content-points').hide();
            } else {
                $('#modal-tab-spend').css('border-bottom-color', 'transparent');
                $('#modal-tab-points').css('border-bottom-color', 'var(--primary)');
                $('#modal-content-spend').hide();
                $('#modal-content-points').show();
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
            $.ajax({
                url: 'admin_actions.php?action=approve_redemption',
                type: 'POST',
                data: { request_id: id, status: status },
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
        }

        // Encryption tester API trigger
        function generateSecureLink() {
            const url = $('#encrypt-target-url').val();
            const params = $('#encrypt-params').val();

            $.ajax({
                url: 'admin_actions.php?action=generate_encrypted_link',
                type: 'POST',
                data: { target_url: url, params: params },
                success: function(response) {
                    if (response.success) {
                        $('#encrypted-url-text').text(response.data.secured_url);
                        $('#encryption-result').fadeIn();
                        $('#decryption-result').hide();
                    }
                }
            });
        }

        function copyEncryptedUrl() {
            const text = $('#encrypted-url-text').text();
            navigator.clipboard.writeText(text);
            showToast("Copied to clipboard!");
        }

        function testDecryptToken() {
            const url = $('#encrypted-url-text').text();
            const urlObj = new URL(url);
            const token = urlObj.searchParams.get('token');

            $.ajax({
                url: 'admin_actions.php?action=decrypt_url_token',
                type: 'POST',
                data: { token: token },
                success: function(response) {
                    if (response.success) {
                        $('#decrypted-json-text').text(JSON.stringify(response.data, null, 4));
                        $('#decryption-result').fadeIn();
                    } else {
                        showToast(response.message, true);
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || "Tampered token detected!", true);
                }
            });
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

            $.ajax({
                url: 'admin_actions.php?action=save_settings',
                type: 'POST',
                data: {
                    timezone: $('#settings-timezone').val(),
                    currency: $('#settings-currency').val(),
                    fb_points_rules: JSON.stringify(rules),
                    departments: JSON.stringify(globalDepartments)
                },
                success: function(response) {
                    if (response.success) {
                        showToast("System settings updated successfully!");
                        loadAllData();
                    } else {
                        showToast(response.message, true);
                    }
                },
                error: function() {
                    showToast("Error saving system settings.", true);
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
            $('.currency-label').text(currency);
        }

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
                                    <td>${member.expiry_date}</td>
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
            $('#redeem-points-input').attr('max', balance);
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
                        globalStaffDirectory.forEach(st => {
                            enrolSelectHtml += `<option value="${st.staff_id}">${st.name} (${st.staff_id}) - ${st.department}</option>`;
                        });
                        $('#enrol-staff-select').html(enrolSelectHtml);
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
            if (!confirm("Are you sure you want to remove this staff member from the directory?")) return;
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
    </script>
</body>
</html>
