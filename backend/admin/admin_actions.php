<?php
require_once __DIR__ . '/../config/db_connection.php';

// Access Control (In a real system, verify admin session. For now, allow requests for demo).
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'enrol_member':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJSONResponse(false, null, "Method not allowed.", 405);
        }

        // Retrieve inputs
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $firstName = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
        $lastName = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
        $idNumber = isset($_POST['id_number']) ? trim($_POST['id_number']) : '';
        $nationality = isset($_POST['nationality']) ? trim($_POST['nationality']) : '';
        $address = isset($_POST['address']) ? trim($_POST['address']) : '';
        $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $companyName = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
        $position = isset($_POST['position']) ? trim($_POST['position']) : '';
        
        $membershipType = isset($_POST['membership_type']) ? $_POST['membership_type'] : 'K Plus';
        $cardType = isset($_POST['card_type']) ? $_POST['card_type'] : 'Silver';
        $goldReason = isset($_POST['gold_reason']) ? trim($_POST['gold_reason']) : '';
        
        // Application Details
        $voucherTypeSelection = isset($_POST['voucher_type']) ? $_POST['voucher_type'] : 'N/A';
        $receiptNumber = isset($_POST['receipt_number']) ? trim($_POST['receipt_number']) : '';
        $receiptAmount = isset($_POST['receipt_amount']) ? (float)$_POST['receipt_amount'] : 0.000;
        
        // Employee details
        $staffId = isset($_POST['staff_id']) ? trim($_POST['staff_id']) : '';
        $staffName = isset($_POST['staff_name']) ? trim($_POST['staff_name']) : '';
        $staffDept = isset($_POST['staff_dept']) ? trim($_POST['staff_dept']) : '';

        // Validation
        if (empty($firstName) || empty($lastName) || empty($idNumber) || empty($nationality) || empty($mobile) || empty($email)) {
            sendJSONResponse(false, null, "Missing required guest fields.", 400);
        }

        // Generate a 16-digit membership card number (e.g., 4500123456789012)
        $firstGroup = '4500';
        if ($membershipType === 'K Plus') {
            if ($cardType === 'Gold') $firstGroup = '4510';
            else if ($cardType === 'Brown') $firstGroup = '4520';
            else $firstGroup = '4500'; // Silver
        } else {
            $firstGroup = '4600'; // Booker
        }
        $membershipNumber = $firstGroup . mt_rand(1000, 9999) . mt_rand(1000, 9999) . mt_rand(1000, 9999);

        // Default password is 'password123'
        $passwordHash = password_hash('password123', PASSWORD_DEFAULT);

        // Expiry Date (valid for 1 membership year)
        $expiryDate = date('Y-m-d', strtotime('+1 year'));

        // Status
        // Gold card requires GM approval. Set status based on card type.
        $status = ($cardType === 'Gold') ? 'Pending Approval' : 'Active';

        try {
            $pdo->beginTransaction();

            // Insert member
            $stmt = $pdo->prepare("INSERT INTO members (
                membership_number, title, first_name, last_name, id_number, nationality, address, mobile, email, 
                company_name, position, membership_type, card_type, status, expiry_date, password_hash, gold_reason
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $membershipNumber, $title, $firstName, $lastName, $idNumber, $nationality, $address, $mobile, $email,
                $companyName, $position, $membershipType, $cardType, $status, $expiryDate, $passwordHash, $goldReason
            ]);
            $memberId = $pdo->lastInsertId();

            // If K Plus (Silver or Brown) and voucher is selected, auto-issue the voucher
            if ($membershipType === 'K Plus' && $cardType !== 'Gold' && $voucherTypeSelection !== 'N/A') {
                $voucherNumber = 'VCH-' . mt_rand(100000, 999999);
                $voucherTypeMap = [
                    'Room' => 'K Plus Room',
                    'Brunch' => 'K Plus Brunch',
                    'Thai Massage' => 'K Plus Thai Massage'
                ];
                $vType = isset($voucherTypeMap[$voucherTypeSelection]) ? $voucherTypeMap[$voucherTypeSelection] : 'K Plus Room';
                
                $stmtV = $pdo->prepare("INSERT INTO vouchers (
                    member_id, voucher_number, voucher_type, description, receipt_number, receipt_amount, issued_date, valid_until
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmtV->execute([
                    $memberId,
                    $voucherNumber,
                    $vType,
                    "Complimentary 1-year member signup voucher for " . $voucherTypeSelection,
                    $receiptNumber,
                    $receiptAmount,
                    date('Y-m-d'),
                    $expiryDate
                ]);
            }

            // Record staff incentive if K Plus Silver is sold
            if ($membershipType === 'K Plus' && $cardType === 'Silver' && !empty($staffId)) {
                $stmtI = $pdo->prepare("INSERT INTO staff_incentives (
                    staff_id, staff_name, department, member_id, incentive_amount, status
                ) VALUES (?, ?, ?, ?, 5.000, 'Pending')");
                
                $stmtI->execute([$staffId, $staffName, $staffDept, $memberId]);
            }

            $pdo->commit();

            $msg = ($cardType === 'Gold') 
                ? "Enrolment saved. Member is pending GM approval as it is a Gold card." 
                : "Member enrolled successfully. Card Number: {$membershipNumber}. Default password: password123";

            sendJSONResponse(true, ['membership_number' => $membershipNumber], $msg);

        } catch (PDOException $e) {
            $pdo->rollBack();
            sendJSONResponse(false, null, "Database error during enrolment: " . $e->getMessage(), 500);
        }
        break;

    case 'edit_member':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJSONResponse(false, null, "Method not allowed.", 405);
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $firstName = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
        $lastName = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
        $idNumber = isset($_POST['id_number']) ? trim($_POST['id_number']) : '';
        $nationality = isset($_POST['nationality']) ? trim($_POST['nationality']) : '';
        $address = isset($_POST['address']) ? trim($_POST['address']) : '';
        $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $companyName = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
        $position = isset($_POST['position']) ? trim($_POST['position']) : '';
        
        $membershipType = isset($_POST['membership_type']) ? $_POST['membership_type'] : 'K Plus';
        $cardType = isset($_POST['card_type']) ? $_POST['card_type'] : 'Silver';
        $goldReason = isset($_POST['gold_reason']) ? trim($_POST['gold_reason']) : '';

        if ($id <= 0 || empty($firstName) || empty($lastName) || empty($idNumber) || empty($nationality) || empty($mobile) || empty($email)) {
            sendJSONResponse(false, null, "Missing required guest fields.", 400);
        }

        try {
            $stmt = $pdo->prepare("UPDATE members SET 
                title = ?, first_name = ?, last_name = ?, id_number = ?, nationality = ?, address = ?, mobile = ?, email = ?, 
                company_name = ?, position = ?, membership_type = ?, card_type = ?, gold_reason = ?
                WHERE id = ?");
            
            $stmt->execute([
                $title, $firstName, $lastName, $idNumber, $nationality, $address, $mobile, $email,
                $companyName, $position, $membershipType, $cardType, $goldReason, $id
            ]);

            sendJSONResponse(true, null, "Member details updated successfully.");
        } catch (PDOException $e) {
            sendJSONResponse(false, null, $e->getMessage(), 500);
        }
        break;

    case 'get_members':
        try {
            $stmt = $pdo->query("SELECT m.*, 
                (COALESCE((SELECT SUM(pl.points_earned) FROM points_ledger pl WHERE pl.member_id = m.id), 0) - 
                 COALESCE((SELECT SUM(pl.points_redeemed) FROM points_ledger pl WHERE pl.member_id = m.id), 0)) as points_balance 
                FROM members m 
                ORDER BY m.created_at DESC");
            $members = $stmt->fetchAll();
            
            // Evaluate dynamic verification status (valid for 30 minutes since last scan)
            $currentTime = time();
            $verifiedWindow = 30 * 60; 
            
            foreach ($members as &$member) {
                $lastVerified = $member['last_verified_at'] ? strtotime($member['last_verified_at']) : 0;
                $member['is_verified'] = ($lastVerified && ($currentTime - $lastVerified <= $verifiedWindow)) ? 1 : 0;
            }
            unset($member);
            
            sendJSONResponse(true, $members, "Members list retrieved.");
        } catch (PDOException $e) {
            sendJSONResponse(false, null, $e->getMessage(), 500);
        }
        break;

    case 'add_spending':
        $memberId = isset($_POST['member_id']) ? (int)$_POST['member_id'] : 0;
        $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
        $dept = isset($_POST['source_dept']) ? $_POST['source_dept'] : 'F&B';
        $desc = isset($_POST['description']) ? trim($_POST['description']) : '';

        if ($memberId <= 0 || $amount <= 0) {
            sendJSONResponse(false, null, "Invalid member or amount.", 400);
        }

        try {
            // Record spending
            $stmt = $pdo->prepare("INSERT INTO spending_records (member_id, amount, source_dept, description, transaction_date) VALUES (?, ?, ?, ?, CURRENT_DATE)");
            $stmt->execute([$memberId, $amount, $dept, $desc]);
            $spendingId = $pdo->lastInsertId();

            // Record staff incentives if any are provided
            $staffReferrals = isset($_POST['staff_referrals']) ? json_decode($_POST['staff_referrals'], true) : [];
            if (is_array($staffReferrals)) {
                foreach ($staffReferrals as $ref) {
                    $staffId = isset($ref['id']) ? trim($ref['id']) : '';
                    $staffName = isset($ref['name']) ? trim($ref['name']) : '';
                    $staffDept = isset($ref['dept']) ? trim($ref['dept']) : '';
                    $pct = isset($ref['pct']) ? (float)$ref['pct'] : 0.0;
                    
                    if (!empty($staffId) && !empty($staffName) && $pct > 0) {
                        $incAmount = $amount * ($pct / 100.0);
                        
                        $stmtI = $pdo->prepare("INSERT INTO staff_incentives (
                            staff_id, staff_name, department, member_id, spending_id, incentive_amount, status
                        ) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
                        $stmtI->execute([$staffId, $staffName, $staffDept, $memberId, $spendingId, $incAmount]);
                    }
                }
            }

            // If user is a K Reward Booker, add points!
            // Event booking: 70.000 BHD = 3 Points. Restaurant: 40.000 BHD = 1 Point.
            $stmtM = $pdo->prepare("SELECT membership_type, membership_number FROM members WHERE id = ?");
            $stmtM->execute([$memberId]);
            $member = $stmtM->fetch();

            if ($member) {
                $points = 0;
                $rules = isset($GLOBALS['fb_points_rules']) ? $GLOBALS['fb_points_rules'] : [];
                
                // Filter rules for the specific logged department/service
                $serviceRules = array_filter($rules, function($r) use ($dept) {
                    $srv = isset($r['service']) ? $r['service'] : 'F&B';
                    return $srv === $dept;
                });
                
                // Sort service rules by threshold descending
                usort($serviceRules, function($a, $b) {
                    return (float)$b['threshold'] <=> (float)$a['threshold'];
                });
                
                // Find highest threshold met and calculate points proportionally
                foreach ($serviceRules as $rule) {
                    $thresh = (float)$rule['threshold'];
                    if ($thresh > 0 && $amount >= $thresh) {
                        $points = floor($amount / $thresh) * (int)$rule['points'];
                        break;
                    }
                }
                
                if ($points > 0) {
                    $stmtP = $pdo->prepare("INSERT INTO points_ledger (member_id, booking_reference, points_earned, transaction_type, source, description, transaction_date, expiry_date) VALUES (?, ?, ?, 'Earned', ?, ?, CURRENT_DATE, DATE_ADD(CURRENT_DATE, INTERVAL 1 YEAR))");
                    $stmtP->execute([
                        $memberId, 
                        'SP-' . mt_rand(1000, 9999), 
                        $points, 
                        ($dept === 'F&B' ? 'Restaurant Booking' : 'Event Booking'), 
                        "Point earned from {$dept} spending of {$amount} BHD",
                    ]);
                }
            }

            sendJSONResponse(true, null, "Spending recorded successfully.");
        } catch (PDOException $e) {
            sendJSONResponse(false, null, $e->getMessage(), 500);
        }
        break;

    case 'get_pending_upgrades':
        try {
            // Fetch threshold from settings table (default to 500.000 if not configured)
            $stmtThresh = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'gold_upgrade_threshold'");
            $stmtThresh->execute();
            $thresholdRow = $stmtThresh->fetch();
            $threshold = $thresholdRow ? (float)$thresholdRow['setting_value'] : 500.000;

            // Recommend Gold upgrade for K Plus Silver/Brown card members who spent more than the threshold
            $sql = "SELECT m.id, m.membership_number, m.first_name, m.last_name, m.card_type, 
                           COALESCE(SUM(s.amount), 0) as total_spending 
                    FROM members m 
                    LEFT JOIN spending_records s ON m.id = s.member_id 
                    WHERE m.membership_type = 'K Plus' AND m.card_type IN ('Silver', 'Brown')
                    GROUP BY m.id
                    HAVING total_spending >= :threshold";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['threshold' => $threshold]);
            $recommendations = $stmt->fetchAll();
            
            // Also get list of members pending GM Approval
            $stmt2 = $pdo->query("SELECT * FROM members WHERE card_type = 'Gold' AND status = 'Pending Approval'");
            $pendingGMAproval = $stmt2->fetchAll();
 
            sendJSONResponse(true, [
                'recommendations' => $recommendations,
                'pending_gm' => $pendingGMAproval,
                'gold_upgrade_threshold' => $threshold
            ], "Upgrade details fetched.");
        } catch (PDOException $e) {
            sendJSONResponse(false, null, $e->getMessage(), 500);
        }
        break;

    case 'approve_gold':
        $memberId = isset($_POST['member_id']) ? (int)$_POST['member_id'] : 0;
        $actionType = isset($_POST['action_type']) ? $_POST['action_type'] : 'approve'; // approve or upgrade

        if ($memberId <= 0) {
            sendJSONResponse(false, null, "Invalid member ID.", 400);
        }

        try {
            if ($actionType === 'upgrade') {
                // Upgrade Silver/Brown to Gold
                $stmt = $pdo->prepare("UPDATE members SET card_type = 'Gold', status = 'Pending Approval', gold_reason = 'Recommended based on spending report' WHERE id = ?");
                $stmt->execute([$memberId]);
                sendJSONResponse(true, null, "Gold upgrade recommended. Sent to GM for final approval.");
            } else {
                // GM approves
                $stmt = $pdo->prepare("UPDATE members SET status = 'Active', approved_by = 'General Manager', approved_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$memberId]);
                sendJSONResponse(true, null, "Gold membership approved and activated by GM.");
            }
        } catch (PDOException $e) {
            sendJSONResponse(false, null, $e->getMessage(), 500);
        }
        break;

    case 'get_redemptions':
        try {
            $stmt = $pdo->query("SELECT r.*, m.membership_number, m.first_name, m.last_name 
                                 FROM redemption_requests r 
                                 JOIN members m ON r.member_id = m.id 
                                 ORDER BY r.created_at DESC");
            $redemptions = $stmt->fetchAll();
            sendJSONResponse(true, $redemptions, "Redemption list retrieved.");
        } catch (PDOException $e) {
            sendJSONResponse(false, null, $e->getMessage(), 500);
        }
        break;

    case 'approve_redemption':
        $requestId = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
        $status = isset($_POST['status']) ? $_POST['status'] : 'Approved'; // Approved or Rejected

        if ($requestId <= 0) {
            sendJSONResponse(false, null, "Invalid request ID.", 400);
        }

        try {
            $pdo->beginTransaction();

            // Fetch request details
            $stmt = $pdo->prepare("SELECT * FROM redemption_requests WHERE id = ?");
            $stmt->execute([$requestId]);
            $request = $stmt->fetch();

            if (!$request) {
                sendJSONResponse(false, null, "Redemption request not found.", 404);
            }

            if ($request['status'] !== 'Pending') {
                sendJSONResponse(false, null, "Request already processed.", 400);
            }

            if ($status === 'Rejected') {
                $stmtU = $pdo->prepare("UPDATE redemption_requests SET status = 'Rejected', approved_by = 'Admin', approved_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmtU->execute([$requestId]);
                $pdo->commit();
                sendJSONResponse(true, null, "Redemption request rejected.");
            }

            $memberId = $request['member_id'];
            $pointsCost = $request['points_cost'];
            $awardTitle = $request['award_title'];

            // 1. Add debit record to points ledger
            $stmtP = $pdo->prepare("INSERT INTO points_ledger (member_id, booking_reference, points_earned, points_redeemed, transaction_type, source, description, transaction_date) VALUES (?, ?, 0, ?, 'Redeemed', 'Voucher Redemption', ?, CURRENT_DATE)");
            $stmtP->execute([
                $memberId,
                'RED-' . mt_rand(1000, 9999),
                $pointsCost,
                "Redeemed points for award: " . $awardTitle
            ]);

            // 2. Map award title to voucher type
            $vType = 'K Reward Meal';
            if (strpos($awardTitle, 'health club') !== false) {
                $vType = 'K Reward Fitness';
            } else if (strpos($awardTitle, 'gift voucher') !== false) {
                $vType = 'K Reward Gift';
            } else if (strpos($awardTitle, 'night') !== false) {
                $vType = 'K Reward Free Night';
            }

            // 3. Issue Voucher
            $voucherNumber = 'VCH-' . mt_rand(100000, 999999);
            $expiryDate = date('Y-m-d', strtotime('+1 year'));

            $stmtV = $pdo->prepare("INSERT INTO vouchers (
                member_id, voucher_number, voucher_type, description, status, issued_date, valid_until
            ) VALUES (?, ?, ?, ?, 'Active', CURRENT_DATE, ?)");
            
            $stmtV->execute([
                $memberId,
                $voucherNumber,
                $vType,
                "Points reward: " . $awardTitle,
                $expiryDate
            ]);

            // 4. Update request status
            $stmtU = $pdo->prepare("UPDATE redemption_requests SET status = 'Approved', approved_by = 'Admin', approved_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmtU->execute([$requestId]);

            $pdo->commit();
            sendJSONResponse(true, ['voucher_number' => $voucherNumber], "Redemption request approved. Voucher {$voucherNumber} generated.");

        } catch (PDOException $e) {
            $pdo->rollBack();
            sendJSONResponse(false, null, "Error processing redemption: " . $e->getMessage(), 500);
        }
        break;

    case 'get_reports':
        try {
            // Count total members by type and card
            $stmtCounts = $pdo->query("SELECT card_type, COUNT(*) as count FROM members GROUP BY card_type");
            $counts = $stmtCounts->fetchAll();

            // Total active vouchers distributed
            $stmtVouchers = $pdo->query("SELECT voucher_type, COUNT(*) as count FROM vouchers WHERE status = 'Active' GROUP BY voucher_type");
            $vouchers = $stmtVouchers->fetchAll();

            // Staff incentives list
            $stmtInc = $pdo->query("SELECT s.*, m.first_name, m.last_name, m.membership_number 
                                    FROM staff_incentives s 
                                    JOIN members m ON s.member_id = m.id 
                                    ORDER BY s.created_at DESC");
            $incentives = $stmtInc->fetchAll();

            sendJSONResponse(true, [
                'counts' => $counts,
                'vouchers' => $vouchers,
                'incentives' => $incentives
            ], "Report details loaded.");
        } catch (PDOException $e) {
            sendJSONResponse(false, null, $e->getMessage(), 500);
        }
        break;

    case 'get_settings':
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
            $rows = $stmt->fetchAll();
            $settings = [];
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            sendJSONResponse(true, $settings, "Settings loaded.");
        } catch (PDOException $e) {
            sendJSONResponse(false, null, $e->getMessage(), 500);
        }
        break;

    case 'save_settings':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJSONResponse(false, null, "Method not allowed.", 405);
        }

        $timezone = isset($_POST['timezone']) ? trim($_POST['timezone']) : 'Asia/Bahrain';
        $currency = isset($_POST['currency']) ? trim($_POST['currency']) : 'BHD';
        $rulesJson = isset($_POST['fb_points_rules']) ? trim($_POST['fb_points_rules']) : '[]';
        $deptsJson = isset($_POST['departments']) ? trim($_POST['departments']) : '[]';
        $goldThreshold = isset($_POST['gold_upgrade_threshold']) ? (float)$_POST['gold_upgrade_threshold'] : 500.000;

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute(['timezone', $timezone]);
            $stmt->execute(['currency', $currency]);
            $stmt->execute(['fb_points_rules', $rulesJson]);
            $stmt->execute(['departments', $deptsJson]);
            $stmt->execute(['gold_upgrade_threshold', $goldThreshold]);

            $pdo->commit();
            sendJSONResponse(true, null, "Settings saved successfully.");
        } catch (PDOException $e) {
            $pdo->rollBack();
            sendJSONResponse(false, null, $e->getMessage(), 500);
        }
        break;

    case 'redeem_member_points':
        $memberId = isset($_POST['member_id']) ? (int)$_POST['member_id'] : 0;
        $points = isset($_POST['points']) ? (int)$_POST['points'] : 0;
        $desc = isset($_POST['description']) ? trim($_POST['description']) : '';

        if ($memberId <= 0 || $points <= 0 || empty($desc)) {
            sendJSONResponse(false, null, "All fields are required.", 400);
        }

        try {
            // Verify balance first
            $stmtBal = $pdo->prepare("SELECT 
                (SELECT COALESCE(SUM(pl.points_earned), 0) FROM points_ledger pl WHERE pl.member_id = ?) as earned,
                (SELECT COALESCE(SUM(pl.points_redeemed), 0) FROM points_ledger pl WHERE pl.member_id = ?) as redeemed");
            $stmtBal->execute([$memberId, $memberId]);
            $balRow = $stmtBal->fetch();
            $currentBal = (int)$balRow['earned'] - (int)$balRow['redeemed'];

            if ($points > $currentBal) {
                sendJSONResponse(false, null, "Insufficient points balance (Available: {$currentBal} Pts).", 400);
            }

            // Debit points
            $stmtDebit = $pdo->prepare("INSERT INTO points_ledger (member_id, booking_reference, points_redeemed, transaction_type, source, description, transaction_date) VALUES (?, ?, ?, 'Redeemed', 'Manual Adjustment', ?, CURRENT_DATE)");
            $stmtDebit->execute([
                $memberId,
                'RD-' . mt_rand(1000, 9999),
                $points,
                $desc
            ]);

            sendJSONResponse(true, null, "Redeemed {$points} points successfully.");
        } catch (PDOException $e) {
            sendJSONResponse(false, null, $e->getMessage(), 500);
        }
        break;

    case 'get_staff_members':
        try {
            $stmt = $pdo->query("SELECT * FROM staff_directory ORDER BY name ASC");
            $staff = $stmt->fetchAll();
            sendJSONResponse(true, $staff, "Staff directory loaded.");
        } catch (PDOException $e) {
            sendJSONResponse(false, null, $e->getMessage(), 500);
        }
        break;

    case 'add_staff_member':
        $staffId = isset($_POST['staff_id']) ? trim($_POST['staff_id']) : '';
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $dept = isset($_POST['department']) ? trim($_POST['department']) : '';
        $pct = isset($_POST['incentive_pct']) ? (float)$_POST['incentive_pct'] : 0.00;

        if (empty($staffId) || empty($name) || empty($dept)) {
            sendJSONResponse(false, null, "All fields are required.", 400);
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO staff_directory (staff_id, name, department, incentive_pct) VALUES (?, ?, ?, ?)");
            $stmt->execute([$staffId, $name, $dept, $pct]);
            sendJSONResponse(true, null, "Staff member added successfully.");
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                sendJSONResponse(false, null, "Staff ID already exists.", 400);
            } else {
                sendJSONResponse(false, null, $e->getMessage(), 500);
            }
        }
        break;

    case 'edit_staff_member':
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $staffId = isset($_POST['staff_id']) ? trim($_POST['staff_id']) : '';
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $dept = isset($_POST['department']) ? trim($_POST['department']) : '';
        $pct = isset($_POST['incentive_pct']) ? (float)$_POST['incentive_pct'] : 0.00;

        if ($id <= 0 || empty($staffId) || empty($name) || empty($dept)) {
            sendJSONResponse(false, null, "All fields are required.", 400);
        }

        try {
            $stmt = $pdo->prepare("UPDATE staff_directory SET staff_id = ?, name = ?, department = ?, incentive_pct = ? WHERE id = ?");
            $stmt->execute([$staffId, $name, $dept, $pct, $id]);
            sendJSONResponse(true, null, "Staff member updated successfully.");
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                sendJSONResponse(false, null, "Staff ID already exists.", 400);
            } else {
                sendJSONResponse(false, null, $e->getMessage(), 500);
            }
        }
        break;

    case 'delete_staff_member':
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            sendJSONResponse(false, null, "Invalid staff ID.", 400);
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM staff_directory WHERE id = ?");
            $stmt->execute([$id]);
            sendJSONResponse(true, null, "Staff member removed.");
        } catch (PDOException $e) {
            sendJSONResponse(false, null, $e->getMessage(), 500);
        }
        break;

    case 'get_member_pass_url':
        $memberId = isset($_POST['member_id']) ? (int)$_POST['member_id'] : 0;
        if ($memberId <= 0) {
            sendJSONResponse(false, null, "Invalid member ID.", 400);
        }

        try {
            // Fetch mobile number
            $stmtMobile = $pdo->prepare("SELECT mobile FROM members WHERE id = ?");
            $stmtMobile->execute([$memberId]);
            $memberInfo = $stmtMobile->fetch();
            $mobile = $memberInfo ? $memberInfo['mobile'] : '';

            // Generate encrypted URL
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
            $host = $_SERVER['HTTP_HOST'];
            // Target: verify_member.php on the same host/port
            $baseUrl = "{$protocol}://{$host}/backend/admin/verify_member.php";
            
            $securedUrl = UrlEncryptor::encryptUrl($baseUrl, ['member_id' => $memberId]);

            sendJSONResponse(true, [
                'secured_url' => $securedUrl,
                'mobile' => $mobile
            ], "Member pass verification link generated successfully.");
        } catch (PDOException $e) {
            sendJSONResponse(false, null, $e->getMessage(), 500);
        }
        break;

    case 'get_member_spending_details':
        $memberId = isset($_GET['member_id']) ? (int)$_GET['member_id'] : 0;
        if ($memberId <= 0) {
            sendJSONResponse(false, null, "Invalid member ID.", 400);
        }

        try {
            // Fetch spending logs
            $stmtSpend = $pdo->prepare("SELECT * FROM spending_records WHERE member_id = ? ORDER BY transaction_date DESC, id DESC");
            $stmtSpend->execute([$memberId]);
            $spending = $stmtSpend->fetchAll();

            // Fetch points logs
            $stmtPoints = $pdo->prepare("SELECT * FROM points_ledger WHERE member_id = ? ORDER BY transaction_date DESC, id DESC");
            $stmtPoints->execute([$memberId]);
            $points = $stmtPoints->fetchAll();

            // Fetch total metrics
            $stmtTotals = $pdo->prepare("SELECT 
                (SELECT COALESCE(SUM(amount), 0) FROM spending_records WHERE member_id = ?) as total_spent,
                (SELECT COALESCE(SUM(points_earned), 0) FROM points_ledger WHERE member_id = ?) as total_earned,
                (SELECT COALESCE(SUM(points_redeemed), 0) FROM points_ledger WHERE member_id = ?) as total_redeemed");
            $stmtTotals->execute([$memberId, $memberId, $memberId]);
            $totals = $stmtTotals->fetch();

            sendJSONResponse(true, [
                'spending' => $spending,
                'points' => $points,
                'totals' => $totals
            ], "Member spending details retrieved.");

        } catch (PDOException $e) {
            sendJSONResponse(false, null, $e->getMessage(), 500);
        }
        break;

    case 'generate_encrypted_link':
        // Action to encrypt outbound urls
        $url = isset($_POST['target_url']) ? $_POST['target_url'] : '';
        $paramsStr = isset($_POST['params']) ? $_POST['params'] : '';
        
        if (empty($url)) {
            sendJSONResponse(false, null, "URL is required.", 400);
        }

        // Parse key-value params (format: param1=val1&param2=val2)
        parse_str($paramsStr, $paramsArray);
        
        $securedUrl = UrlEncryptor::encryptUrl($url, $paramsArray);

        sendJSONResponse(true, [
            'secured_url' => $securedUrl
        ], "URL encrypted successfully.");
        break;

    case 'decrypt_url_token':
        $token = isset($_POST['token']) ? trim($_POST['token']) : '';
        if (empty($token)) {
            sendJSONResponse(false, null, "Token parameter is required.", 400);
        }

        $params = UrlEncryptor::decryptUrlToken($token);
        if ($params === false) {
            sendJSONResponse(false, null, "Failed to decrypt token. Tampering detected or expired key.", 400);
        }

        sendJSONResponse(true, $params, "Token decrypted successfully.");
        break;

    default:
        sendJSONResponse(false, null, "Action not found.", 404);
        break;
}
?>
