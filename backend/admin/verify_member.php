<?php
// Prevent caching of the PWA document so card updates show immediately
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once __DIR__ . '/../config/db_connection.php';

// Handle AJAX POST confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm_checkin') {
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';
    if (empty($token)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Missing token.']);
        exit;
    }
    
    $decoded = UrlEncryptor::decryptUrlToken($token);
    if (!$decoded || !isset($decoded['member_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid or expired token.']);
        exit;
    }
    
    $memberId = $decoded['member_id'];
    try {
        $stmtUpdate = $pdo->prepare("UPDATE members SET last_verified_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmtUpdate->execute([$memberId]);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Check-in confirmed successfully!']);
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

// Handle AJAX POST redemption request from guest
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_redemption') {
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';
    $awardTitle = isset($_POST['award_title']) ? trim($_POST['award_title']) : '';
    
    if (empty($token) || empty($awardTitle)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        exit;
    }
    
    $decoded = UrlEncryptor::decryptUrlToken($token);
    if (!$decoded || !isset($decoded['member_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid or expired token.']);
        exit;
    }
    
    $memberId = $decoded['member_id'];
    
    // Define catalogue cost mapping matching points.php
    $awardsCatalogue = [
        'Lunch for two at KOLORS Restaurant' => 15,
        'Dinner for two at KOLORS Restaurant' => 20,
        'Lunch or Dinner for two at the K Lounge' => 35,
        'Friday Brunch for two at KOLORS Restaurant' => 50,
        '1 Month health club membership (single)' => 30,
        '1 Month health club membership (couple)' => 50,
        '3 Month health club membership (single)' => 100,
        '3 Month health club membership (couple)' => 150,
        '20.000 BHD gift voucher' => 20,
        '50.000 BHD gift voucher' => 50,
        '75.000 BHD gift voucher' => 75,
        '100.000 BHD gift voucher' => 100,
        'One night in a deluxe room' => 50,
        'One night in a Junior Suite' => 75,
        'One night in a Senior Suite' => 100,
        'One night in the Amiri Suite' => 150,
        'One night in the Royal Suite' => 250,
    ];
    
    if (!isset($awardsCatalogue[$awardTitle])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid award selected.']);
        exit;
    }
    
    $pointsCost = $awardsCatalogue[$awardTitle];
    
    try {
        // Calculate current points balance
        $stmtEarned = $pdo->prepare("SELECT SUM(points_earned) as total_earned FROM points_ledger WHERE member_id = ?");
        $stmtEarned->execute([$memberId]);
        $totalEarned = (int)$stmtEarned->fetch()['total_earned'];

        $stmtRedeemed = $pdo->prepare("SELECT SUM(points_redeemed) as total_redeemed FROM points_ledger WHERE member_id = ?");
        $stmtRedeemed->execute([$memberId]);
        $totalRedeemed = (int)$stmtRedeemed->fetch()['total_redeemed'];

        // Factor in pending cost
        $stmtPending = $pdo->prepare("SELECT SUM(points_cost) as pending_cost FROM redemption_requests WHERE member_id = ? AND status = 'Pending'");
        $stmtPending->execute([$memberId]);
        $pendingCost = (int)$stmtPending->fetch()['pending_cost'];

        $availablePoints = $totalEarned - $totalRedeemed - $pendingCost;

        if ($availablePoints < $pointsCost) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => "Insufficient points. You have {$availablePoints} points available, but this requires {$pointsCost} points."]);
            exit;
        }

        // Insert redemption request
        $stmtInsert = $pdo->prepare("INSERT INTO redemption_requests (member_id, award_title, points_cost, status) VALUES (?, ?, ?, 'Pending')");
        $stmtInsert->execute([$memberId, $awardTitle, $pointsCost]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => 'Redemption request submitted successfully! It is sent to the Front Desk for verification.'
        ]);
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

// Handle AJAX GET live status polling
if (isset($_GET['action']) && $_GET['action'] === 'get_live_status') {
    $token = isset($_GET['token']) ? trim($_GET['token']) : '';
    if (empty($token)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Missing token.']);
        exit;
    }
    
    $decoded = UrlEncryptor::decryptUrlToken($token);
    if (!$decoded || !isset($decoded['member_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid or expired token.']);
        exit;
    }
    
    $memberId = $decoded['member_id'];
    try {
        // Fetch currency from settings
        $currency = 'BHD';
        $stmtC = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'currency'");
        $cRow = $stmtC->fetch();
        if ($cRow) $currency = $cRow['setting_value'];

        // Get points balance
        $stmtPts = $pdo->prepare("SELECT 
            COALESCE(SUM(points_earned), 0) as total_earned,
            COALESCE(SUM(points_redeemed), 0) as total_redeemed
            FROM points_ledger WHERE member_id = ?");
        $stmtPts->execute([$memberId]);
        $pts = $stmtPts->fetch();
        $balance = (int)$pts['total_earned'] - (int)$pts['total_redeemed'];
        
        // Get latest spend
        $stmtSpend = $pdo->prepare("SELECT amount, source_dept, description, transaction_date, id FROM spending_records WHERE member_id = ? ORDER BY id DESC LIMIT 1");
        $stmtSpend->execute([$memberId]);
        $latestSpend = $stmtSpend->fetch();
        
        // Get latest points earned from this spend
        $earnedPoints = 0;
        if ($latestSpend) {
            $stmtEarned = $pdo->prepare("SELECT points_earned FROM points_ledger WHERE member_id = ? AND transaction_date = ? ORDER BY id DESC LIMIT 1");
            $stmtEarned->execute([$memberId, $latestSpend['transaction_date']]);
            $earnedRow = $stmtEarned->fetch();
            $earnedPoints = $earnedRow ? (int)$earnedRow['points_earned'] : 0;
        }
        
        // Fetch current member vouchers sorted by active first
        $stmtV = $pdo->prepare("SELECT * FROM vouchers WHERE member_id = ? ORDER BY CASE WHEN status = 'Active' THEN 0 ELSE 1 END, issued_date DESC");
        $stmtV->execute([$memberId]);
        $liveVouchersList = $stmtV->fetchAll();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'balance' => $balance,
            'currency' => $currency,
            'vouchers' => $liveVouchersList,
            'latest_spend' => $latestSpend ? [
                'id' => $latestSpend['id'],
                'amount' => (float)$latestSpend['amount'],
                'dept' => $latestSpend['source_dept'],
                'desc' => $latestSpend['description'],
                'date' => $latestSpend['transaction_date'],
                'points_earned' => $earnedPoints
            ] : null
        ]);
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$errorMsg = '';
$member = null;
$vouchers = [];

if (empty($token)) {
    $errorMsg = "Verification token is missing.";
} else {
    $decoded = UrlEncryptor::decryptUrlToken($token);
    
    if (!$decoded || !isset($decoded['member_id'])) {
        $errorMsg = "Invalid or tampered verification token.";
    } else {
        $memberId = $decoded['member_id'];
        
        try {
            // Fetch member
            $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
            $stmt->execute([$memberId]);
            $member = $stmt->fetch();
            
            if (!$member) {
                $errorMsg = "Member not found in database.";
            } else if ($member['status'] === 'Expired' || $member['status'] === 'Inactive') {
                $errorMsg = "This loyalty card is currently " . strtolower($member['status']) . ".";
            } else {
                // Fetch vouchers sorted by status = 'Active' first, then newest issued date
                $stmtV = $pdo->prepare("SELECT * FROM vouchers WHERE member_id = ? ORDER BY CASE WHEN status = 'Active' THEN 0 ELSE 1 END, issued_date DESC");
                $stmtV->execute([$memberId]);
                $vouchers = $stmtV->fetchAll();

                // Fetch dynamic redeemable vouchers catalogue
                $stmtS = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'redeemable_vouchers'");
                $stmtS->execute();
                $redeemableVouchersRow = $stmtS->fetch();
                $vouchersList = $redeemableVouchersRow ? json_decode($redeemableVouchersRow['setting_value'], true) : [];
            }
        } catch (PDOException $e) {
            $errorMsg = "Database error: " . $e->getMessage();
        }
    }
}

// Custom theme styling based on card type
$cardBg = '#718096';
$cardTitle = 'THE K PLUS SILVER';
$accentLine = '#a0aec0';
$textColor = '#ffffff';

if ($member) {
    switch ($member['card_type']) {
        case 'Gold':
            $cardBg = 'linear-gradient(135deg, #1e1b18 0%, #382f25 50%, #8a6f44 100%)';
            $cardTitle = 'THE K PLUS GOLD';
            $accentLine = '#fbbf24';
            break;
        case 'Brown':
            $cardBg = 'linear-gradient(135deg, #231912 0%, #412d1f 50%, #604430 100%)';
            $cardTitle = 'THE K PLUS BROWN';
            $accentLine = '#92613d';
            break;
        case 'Booker':
            $cardBg = 'linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%)';
            $cardTitle = 'THE K REWARD BOOKER';
            $accentLine = '#64748b';
            break;
        case 'Silver':
        default:
            $cardBg = 'linear-gradient(135deg, #2d3748 0%, #4a5568 50%, #718096 100%)';
            $cardTitle = 'THE K PLUS SILVER';
            $accentLine = '#a0aec0';
            break;
    }
}
$isCheckedIn = false;
if ($member) {
    $lastVerified = $member['last_verified_at'] ? strtotime($member['last_verified_at']) : 0;
    if ($lastVerified && (time() - $lastVerified <= 30 * 60)) {
        $isCheckedIn = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>K Hotel - Member Digital Pass</title>
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#d97706">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="K Wallet">
    <link rel="apple-touch-icon" href="/backend/admin/pwa-icon-192.png">
    <link rel="manifest" href="/backend/admin/manifest.php?token=<?php echo urlencode($token ?? ''); ?>">
    <!-- End PWA -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: #151f32;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --primary: #d97706;
            --accent-gold: #fbbf24;
            --border-color: #1e293b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .phone-container {
            width: 100%;
            max-width: 400px;
            background-color: #0c1322;
            border-radius: 30px;
            padding: 24px;
            border: 4px solid var(--border-color);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
        }

        .logo-circle {
            width: 50px;
            height: 50px;
            border-radius: 25px;
            background-color: rgba(217,119,6,0.1);
            border: 1px solid var(--accent-gold);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }

        .logo-text {
            color: var(--accent-gold);
            font-weight: 800;
            font-size: 24px;
        }

        .brand-title {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 2px;
            color: #fff;
        }

        /* Loyalty Card Display */
        .loyalty-card {
            background: <?php echo $cardBg; ?>;
            border-radius: 16px;
            padding: 20px;
            height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255,255,255,0.08);
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .hotel-name {
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: #fff;
        }

        .hotel-sub {
            font-size: 8px;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.7);
            margin-top: 2px;
        }

        .chip {
            width: 32px;
            height: 24px;
            border-radius: 4px;
            background-color: #ecc94b;
            opacity: 0.8;
            border: 1px solid #d69e2e;
        }

        .card-number {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 2px;
            font-family: monospace;
            color: #fff;
            margin: 15px 0;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .label {
            font-size: 7px;
            letter-spacing: 0.5px;
            color: rgba(255,255,255,0.7);
            margin-bottom: 2px;
        }

        .cardholder-name {
            font-size: 12px;
            font-weight: 700;
            color: #fff;
        }

        .expiry-date {
            font-size: 12px;
            font-weight: 700;
            color: #fff;
        }

        .tier-badge {
            font-size: 9px;
            font-weight: 800;
            color: <?php echo $accentLine; ?>;
            letter-spacing: 0.5px;
        }

        /* Details list */
        .section-box {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 11px;
            font-weight: 800;
            color: var(--accent-gold);
            letter-spacing: 1px;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--border-color);
            font-size: 13px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--text-muted);
        }

        .info-val {
            font-weight: 600;
            color: #fff;
        }

        /* Vouchers */
        .voucher-stub {
            background: linear-gradient(to right, #1a2538, #111a2e);
            border-radius: 10px;
            padding: 12px;
            border-left: 4px solid var(--accent-gold);
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .voucher-desc {
            font-size: 12px;
            font-weight: 600;
            color: #fff;
        }

        .voucher-expiry {
            font-size: 9px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .voucher-badge {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 700;
        }

        /* Errors styling */
        .error-container {
            text-align: center;
            padding: 40px 20px;
        }

        .error-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .error-title {
            font-size: 18px;
            font-weight: 700;
            color: #ef4444;
            margin-bottom: 8px;
        }

        .error-desc {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 20px;
        }
    </style>
</head>
<body>

    <div class="phone-container">
        <div class="header">
            <div class="logo-circle">
                <span class="logo-text">K</span>
            </div>
            <h2 class="brand-title">THE K HOTEL</h2>
        </div>

        <?php if ($errorMsg): ?>
            <div class="error-container">
                <div class="error-icon">⚠️</div>
                <h3 class="error-title">Verification Failed</h3>
                <p class="error-desc"><?php echo $errorMsg; ?></p>
            </div>
        <?php else: ?>
            <!-- Live Spend Toast Alert -->
            <div id="live-spend-alert" class="section-box" style="display: none; background: rgba(16, 185, 129, 0.1); border-color: #10b981; text-align: center; margin-bottom: 20px; animation: bounceIn 0.6s ease-out;">
                <div style="color: #10b981; font-size: 24px; margin-bottom: 8px;">🎉 Thank You!</div>
                <h4 style="color: #fff; margin-bottom: 4px;" id="alert-spend-title">Spend Logged!</h4>
                <p style="font-size: 13px; color: var(--text-main);" id="alert-spend-desc"></p>
                <p style="font-size: 12px; color: var(--accent-gold); font-weight: 700; margin-top: 8px;" id="alert-spend-points"></p>
            </div>

            <!-- Digital loyalty card mock -->
            <div class="loyalty-card">
                <div class="card-header">
                    <div>
                        <div class="hotel-name">THE K HOTEL</div>
                        <div class="hotel-sub">BAHRAIN</div>
                    </div>
                    <div class="chip"></div>
                </div>
                <div class="card-number">
                    <?php echo implode(' ', str_split($member['membership_number'], 4)); ?>
                </div>
                <div class="card-footer">
                    <div>
                        <div class="label">CARDHOLDER</div>
                        <div class="cardholder-name"><?php echo strtoupper($member['first_name'] . ' ' . $member['last_name']); ?></div>
                    </div>
                    <div style="text-align: right;">
                        <div class="label">EXPIRES</div>
                        <div class="expiry-date"><?php echo date('m/y', strtotime($member['expiry_date'])); ?></div>
                        <div class="tier-badge"><?php echo $cardTitle; ?></div>
                    </div>
                </div>
            </div>

            <!-- Wallet Balance Box -->
            <div class="section-box" style="background: linear-gradient(135deg, rgba(251,191,36,0.08) 0%, rgba(217,119,6,0.04) 100%); border-color: rgba(251,191,36,0.2); text-align: center; padding: 20px 15px; margin-bottom: 20px; position: relative;">
                <!-- Lock Status Icon top-right -->
                <div id="wallet-lock-icon" style="position: absolute; top: 12px; right: 14px; font-size: 13px; transition: all 0.4s ease;">
                    <?php if ($isCheckedIn): ?>
                        <i class="fa-solid fa-lock-open" style="color: #10b981;" title="Identity Verified"></i>
                    <?php else: ?>
                        <i class="fa-solid fa-lock" style="color: #ef4444;" title="Not Verified"></i>
                    <?php endif; ?>
                </div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1.5px; font-weight: 700;">Wallet Points Balance</div>
                <div style="font-size: 38px; font-weight: 800; color: var(--accent-gold); margin-top: 8px; font-family: 'Outfit', sans-serif;" id="live-points-card-val">0</div>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Earn points dynamically on every checkout</div>
            </div>

            <!-- Check-in Action Box -->
            <div class="section-box" style="text-align: center; border-color: rgba(217,119,6,0.3); background: rgba(217,119,6,0.02); padding: 20px 15px;" id="checkin-box">
                <div id="checkin-initial-state" style="<?php echo $isCheckedIn ? 'display: none;' : ''; ?>">
                    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 15px;">Please confirm your identity to enable spending and point recording at the desk.</p>
                    <button class="action-btn" onclick="confirmGuestCheckin()" style="background: var(--primary); color: #fff; border: none; padding: 12px 24px; border-radius: 25px; font-weight: 700; width: 100%; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: transform 0.15s ease;">
                        <span id="btn-spinner" style="display:none; width: 14px; height: 14px; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite; box-sizing: border-box;"></span>
                        <i class="fa-solid fa-circle-check" id="btn-icon"></i> Yes its Me
                    </button>
                </div>
                <div id="checkin-success-state" style="<?php echo $isCheckedIn ? 'display: block;' : 'display: none;'; ?>">
                    <div style="color: #10b981; font-size: 32px; margin-bottom: 8px;">✓</div>
                    <h4 style="color: #10b981; margin-bottom: 4px;">Check-in Confirmed</h4>
                    <p style="font-size: 12px; color: var(--text-muted);">Points entries are now unlocked for reception staff.</p>
                </div>
            </div>

            <!-- Install Wallet Button (Android) -->
            <div id="pwa-install-wrap" style="display:none; margin-bottom: 20px;">
                <button id="pwa-install-btn" onclick="triggerInstall()" style="width:100%; padding: 12px 20px; background: linear-gradient(135deg, #d97706, #fbbf24); color: #0b0f19; border: none; border-radius: 25px; font-size: 14px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 4px 20px rgba(251,191,36,0.25); transition: transform 0.15s ease;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                    <i class="fa-brands fa-android" style="font-size:16px;"></i>
                    <span>Install Wallet on This Device</span>
                </button>
            </div>

            <!-- iOS Install Instructions Button -->
            <div id="ios-install-wrap" style="display:none; margin-bottom: 20px;">
                <button onclick="document.getElementById('ios-modal').style.display='flex'" style="width:100%; padding: 12px 20px; background: linear-gradient(135deg, #d97706, #fbbf24); color: #0b0f19; border: none; border-radius: 25px; font-size: 14px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 4px 20px rgba(251,191,36,0.25); transition: transform 0.15s ease;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                    <i class="fa-brands fa-apple" style="font-size:16px;"></i>
                    <span>Install Wallet on This Device</span>
                </button>
            </div>

            <!-- Android Install Modal -->
            <div id="android-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); z-index:9999; align-items:flex-end; justify-content:center; padding: 0 10px 16px;">
                <div style="background:#111827; border-radius:22px; width:100%; max-width:420px; position:relative; box-shadow:0 -8px 50px rgba(0,0,0,0.6); overflow:hidden;">
                    <!-- Header -->
                    <div style="background:linear-gradient(135deg,#1a2035,#0f172a); padding:22px 20px 16px; text-align:center; border-bottom:1px solid rgba(255,255,255,0.06);">
                        <button onclick="document.getElementById('android-modal').style.display='none'" style="position:absolute;top:14px;right:16px;background:rgba(255,255,255,0.08);border:none;color:#9ca3af;font-size:16px;width:30px;height:30px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;">&times;</button>
                        <img src="/backend/admin/pwa-icon-192.png" style="width:56px;height:56px;border-radius:12px;margin-bottom:10px;box-shadow:0 4px 16px rgba(0,0,0,0.4);">
                        <div style="font-size:17px;font-weight:800;color:#f9fafb;">Add K Wallet to Home Screen</div>
                        <div style="font-size:11px;color:#6b7280;margin-top:3px;">Your personal loyalty card, one tap away</div>
                    </div>

                    <!-- Tab Switcher -->
                    <div style="display:flex; border-bottom:1px solid rgba(255,255,255,0.06);">
                        <button id="tab-https" onclick="switchTab('https')" style="flex:1;padding:12px;font-size:12px;font-weight:700;background:rgba(217,119,6,0.1);color:#fbbf24;border:none;border-bottom:2px solid #d97706;cursor:pointer;">⚡ Quick Install</button>
                        <button id="tab-manual" onclick="switchTab('manual')" style="flex:1;padding:12px;font-size:12px;font-weight:700;background:transparent;color:#6b7280;border:none;border-bottom:2px solid transparent;cursor:pointer;">📖 Manual Steps</button>
                    </div>

                    <!-- Tab: HTTPS Quick Install -->
                    <div id="panel-https" style="padding:20px;">
                        <div style="background:rgba(16,185,129,0.07);border:1px solid rgba(16,185,129,0.2);border-radius:12px;padding:14px;margin-bottom:16px;">
                            <div style="font-size:12px;font-weight:700;color:#10b981;margin-bottom:6px;"><i class="fa-solid fa-bolt"></i> One-Tap Install Available</div>
                            <div style="font-size:12px;color:#9ca3af;line-height:1.6;">Open your wallet using the <strong style="color:#f3f4f6;">secure HTTPS link</strong> — Chrome will show an <strong style="color:#f3f4f6;">"Install" button</strong> automatically at the bottom of the screen.</div>
                        </div>
                        <div style="font-size:11px;color:#6b7280;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.8px;font-weight:700;">Your Secure Wallet Link</div>
                        <div id="https-wallet-url" style="background:#1e293b;border:1px solid rgba(251,191,36,0.2);border-radius:10px;padding:12px;font-size:11px;color:#fbbf24;word-break:break-all;font-family:monospace;margin-bottom:14px;"></div>
                        <button onclick="openHttpsLink()" style="width:100%;padding:13px;background:linear-gradient(135deg,#d97706,#fbbf24);color:#0b0f19;border:none;border-radius:14px;font-size:14px;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
                            <i class="fa-solid fa-shield-halved"></i> Open Secure Link &amp; Install
                        </button>
                        <div style="font-size:10px;color:#4b5563;text-align:center;margin-top:10px;">This opens the same wallet page over HTTPS</div>
                    </div>

                    <!-- Tab: Manual Steps -->
                    <div id="panel-manual" style="padding:20px;display:none;">
                        <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:16px;">
                            <div style="display:flex;align-items:center;gap:14px;">
                                <div style="min-width:34px;height:34px;border-radius:50%;background:rgba(251,191,36,0.12);display:flex;align-items:center;justify-content:center;color:#fbbf24;font-weight:800;font-size:14px;">1</div>
                                <div style="font-size:13px;color:#d1d5db;">Tap <strong style="color:#fff;"><i class="fa-solid fa-ellipsis-vertical"></i> ⋮</strong> (three dots menu) in the top-right of Chrome</div>
                            </div>
                            <div style="display:flex;align-items:center;gap:14px;">
                                <div style="min-width:34px;height:34px;border-radius:50%;background:rgba(251,191,36,0.12);display:flex;align-items:center;justify-content:center;color:#fbbf24;font-weight:800;font-size:14px;">2</div>
                                <div style="font-size:13px;color:#d1d5db;">Tap <strong style="color:#fff;">Add to Home Screen</strong></div>
                            </div>
                            <div style="display:flex;align-items:center;gap:14px;">
                                <div style="min-width:34px;height:34px;border-radius:50%;background:rgba(251,191,36,0.12);display:flex;align-items:center;justify-content:center;color:#fbbf24;font-weight:800;font-size:14px;">3</div>
                                <div style="font-size:13px;color:#d1d5db;">Tap <strong style="color:#fff;">Add</strong> to save the K Wallet icon</div>
                            </div>
                        </div>
                        <button onclick="document.getElementById('android-modal').style.display='none'" style="width:100%;padding:13px;background:rgba(255,255,255,0.06);color:#d1d5db;border:1px solid rgba(255,255,255,0.1);border-radius:14px;font-size:13px;font-weight:600;cursor:pointer;">
                            Got it — I'll follow the steps
                        </button>
                    </div>
                </div>
            </div>

            <!-- Custom Reusable Confirm Modal (Client) -->
            <div id="pwa-confirm-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:10000; align-items:center; justify-content:center; padding: 20px;">
                <div style="background:#111827; border: 1px solid rgba(251,191,36,0.25); border-radius:18px; width:100%; max-width:350px; padding: 24px; text-align:center; box-shadow:0 8px 40px rgba(0,0,0,0.6);">
                    <div style="font-size:36px; color:#fbbf24; margin-bottom:12px;"><i class="fa-solid fa-circle-question"></i></div>
                    <div id="pwa-confirm-msg" style="font-size:13px; color:#f3f4f6; line-height:1.6; font-weight:700;">Are you sure?</div>
                    <div style="display:flex; justify-content:center; gap:10px; margin-top:20px;">
                        <button type="button" id="pwa-confirm-cancel-btn" style="flex:1; padding:10px; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); color:#d1d5db; border-radius:10px; font-size:12px; font-weight:700; cursor:pointer; border-radius: 20px;">Cancel</button>
                        <button type="button" id="pwa-confirm-ok-btn" style="flex:1; padding:10px; background:var(--primary); border:none; color:#fff; border-radius:10px; font-size:12px; font-weight:700; cursor:pointer; border-radius: 20px;">Confirm</button>
                    </div>
                </div>
            </div>

            <!-- Custom Reusable Alert Modal (Client) -->
            <div id="pwa-alert-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:10000; align-items:center; justify-content:center; padding: 20px;">
                <div style="background:#111827; border: 1px solid rgba(251,191,36,0.2); border-radius:18px; width:100%; max-width:350px; padding: 24px; text-align:center; box-shadow:0 8px 40px rgba(0,0,0,0.6);">
                    <div style="font-size:36px; color:#fbbf24; margin-bottom:12px;"><i class="fa-solid fa-circle-info"></i></div>
                    <div id="pwa-alert-msg" style="font-size:13px; color:#f3f4f6; line-height:1.6;">Message</div>
                    <div style="margin-top:20px;">
                        <button type="button" onclick="document.getElementById('pwa-alert-modal').style.display='none'" style="width:100%; padding:10px; background:var(--primary); border:none; color:#fff; border-radius:20px; font-size:12px; font-weight:700; cursor:pointer;">OK</button>
                    </div>
                </div>
            </div>

            <!-- iOS Install Modal -->
            <div id="ios-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:9999; align-items:flex-end; justify-content:center; padding: 0 12px 20px;">
                <div style="background:#1a2035; border-radius:20px; padding:28px 22px; width:100%; max-width:400px; position:relative; box-shadow:0 -4px 40px rgba(0,0,0,0.5);">
                    <button onclick="document.getElementById('ios-modal').style.display='none'" style="position:absolute;top:14px;right:16px;background:none;border:none;color:#9ca3af;font-size:20px;cursor:pointer;">&times;</button>
                    <div style="text-align:center; margin-bottom:18px;">
                        <img src="/backend/admin/pwa-icon-192.png" style="width:64px;height:64px;border-radius:14px;margin-bottom:10px;">
                        <div style="font-size:16px;font-weight:800;color:#f3f4f6;">Install K Wallet</div>
                        <div style="font-size:12px;color:#9ca3af;margin-top:4px;">Add to your Home Screen for quick access</div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:14px;">
                        <div style="display:flex;align-items:center;gap:14px;">
                            <div style="min-width:36px;height:36px;border-radius:50%;background:rgba(251,191,36,0.12);display:flex;align-items:center;justify-content:center;color:#fbbf24;font-weight:800;">1</div>
                            <div style="font-size:13px;color:#d1d5db;">Tap the <strong style="color:#fff;"><i class="fa-solid fa-arrow-up-from-bracket"></i> Share</strong> button at the bottom of Safari</div>
                        </div>
                        <div style="display:flex;align-items:center;gap:14px;">
                            <div style="min-width:36px;height:36px;border-radius:50%;background:rgba(251,191,36,0.12);display:flex;align-items:center;justify-content:center;color:#fbbf24;font-weight:800;">2</div>
                            <div style="font-size:13px;color:#d1d5db;">Scroll down and tap <strong style="color:#fff;">Add to Home Screen</strong></div>
                        </div>
                        <div style="display:flex;align-items:center;gap:14px;">
                            <div style="min-width:36px;height:36px;border-radius:50%;background:rgba(251,191,36,0.12);display:flex;align-items:center;justify-content:center;color:#fbbf24;font-weight:800;">3</div>
                            <div style="font-size:13px;color:#d1d5db;">Tap <strong style="color:#fff;">Add</strong> to install your K Wallet icon</div>
                        </div>
                    </div>
                    <div style="margin-top:20px;padding:12px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);border-radius:10px;font-size:11px;color:#10b981;text-align:center;">
                        <i class="fa-solid fa-shield-check"></i> Your wallet link is saved — opening the app will always show your card
                    </div>
                </div>
            </div>

            <!-- Profile Info Box -->
            <div class="section-box">
                <div class="section-title">Membership Info</div>
                <div class="info-row">
                    <span class="info-label">Member Name</span>
                    <span class="info-val"><?php echo $member['title'] . ' ' . $member['first_name'] . ' ' . $member['last_name']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nationality</span>
                    <span class="info-val"><?php echo $member['nationality']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Program Type</span>
                    <span class="info-val"><?php echo $member['membership_type'] . ' (' . $member['card_type'] . ')'; ?></span>
                </div>
                <div class="info-row" id="live-points-row">
                    <span class="info-label">Points Balance</span>
                    <span class="info-val" style="color: var(--accent-gold); font-weight: 700;" id="live-points-val">Loading...</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Card Status</span>
                    <span class="info-val" style="color: #10b981;"><?php echo $member['status']; ?></span>
                </div>
            </div>

            <!-- Vouchers Box -->
            <div class="section-box">
                <div class="section-title">Vouchers Wallet</div>
                <div id="vouchers-wallet-list">
                    <?php if (empty($vouchers)): ?>
                        <p style="font-size: 12px; color: var(--text-muted); text-align: center; padding: 10px 0;">No vouchers in wallet.</p>
                    <?php else: ?>
                        <?php foreach ($vouchers as $voucher): 
                            $isActive = $voucher['status'] === 'Active';
                            $isUsed = $voucher['status'] === 'Used';
                            $isExpired = $voucher['status'] === 'Expired';
                            
                            $opacity = $isActive ? '1' : '0.5';
                            $badgeStyle = 'background: var(--primary); color: #fff;';
                            if ($isUsed) {
                                $badgeStyle = 'background: #4b5563; color: #d1d5db;';
                            } else if ($isExpired) {
                                $badgeStyle = 'background: #dc2626; color: #ffffff;';
                            }
                        ?>
                            <div class="voucher-stub" style="opacity: <?php echo $opacity; ?>; display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); padding: 12px; border-radius: 10px; margin-bottom: 10px;">
                                <div>
                                    <div class="voucher-desc" style="font-size: 13px; font-weight: 700; color: #fff;"><?php echo htmlspecialchars($voucher['description']); ?></div>
                                    <div class="voucher-expiry" style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                                        <?php if ($isUsed): ?>
                                            Used: <?php echo $voucher['used_at']; ?>
                                        <?php else: ?>
                                            Exp: <?php echo $voucher['valid_until']; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="voucher-badge" style="padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; <?php echo $badgeStyle; ?>"><?php echo htmlspecialchars($voucher['status']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($member['membership_type'] === 'K Reward'): ?>
            <!-- Redeem Rewards Catalogue -->
            <div class="section-box" style="margin-top: 20px;">
                <div class="section-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <span>Redeem Rewards Store</span>
                    <span style="font-size: 11px; font-weight: normal; color: var(--text-muted);">Catalogue</span>
                </div>
                
                <!-- Tab Menu for Categories -->
                <div style="display: flex; gap: 6px; margin-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 8px; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <button type="button" class="cat-tab active" onclick="showRewardCategory('meals', this)" style="background: rgba(251,191,36,0.12); color: #fbbf24; border: 1px solid rgba(251,191,36,0.25); border-radius: 12px; padding: 6px 12px; font-size: 11px; font-weight: 700; cursor: pointer; white-space: nowrap; outline: none; transition: all 0.2s ease;">🍽️ Meals</button>
                    <button type="button" class="cat-tab" onclick="showRewardCategory('fitness', this)" style="background: transparent; color: var(--text-muted); border: 1px solid transparent; border-radius: 12px; padding: 6px 12px; font-size: 11px; font-weight: 700; cursor: pointer; white-space: nowrap; outline: none; transition: all 0.2s ease;">💪 Fitness</button>
                    <button type="button" class="cat-tab" onclick="showRewardCategory('gift', this)" style="background: transparent; color: var(--text-muted); border: 1px solid transparent; border-radius: 12px; padding: 6px 12px; font-size: 11px; font-weight: 700; cursor: pointer; white-space: nowrap; outline: none; transition: all 0.2s ease;">🎁 Gifts</button>
                    <button type="button" class="cat-tab" onclick="showRewardCategory('nights', this)" style="background: transparent; color: var(--text-muted); border: 1px solid transparent; border-radius: 12px; padding: 6px 12px; font-size: 11px; font-weight: 700; cursor: pointer; white-space: nowrap; outline: none; transition: all 0.2s ease;">🏨 Nights</button>
                </div>

                <?php
                $mealsVouchers = [];
                $fitnessVouchers = [];
                $giftVouchers = [];
                $nightsVouchers = [];

                if (is_array($vouchersList)) {
                    foreach ($vouchersList as $v) {
                        $cat = isset($v['category']) ? $v['category'] : '';
                        if ($cat === 'meals') {
                            $mealsVouchers[] = $v;
                        } else if ($cat === 'fitness') {
                            $fitnessVouchers[] = $v;
                        } else if ($cat === 'gift') {
                            $giftVouchers[] = $v;
                        } else if ($cat === 'nights') {
                            $nightsVouchers[] = $v;
                        }
                    }
                }
                ?>

                <!-- Meals Section -->
                <div class="reward-cat-panel" id="panel-meals" style="display: block;">
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php if (empty($mealsVouchers)): ?>
                            <div style="text-align: center; color: var(--text-muted); font-size: 12px; padding: 10px;">No awards available in this category.</div>
                        <?php else: ?>
                            <?php foreach ($mealsVouchers as $v): ?>
                                <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 12px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center;">
                                    <div style="flex: 1; padding-right: 10px;">
                                        <div style="font-size: 12.5px; font-weight: 700; color: #fff;"><?php echo htmlspecialchars($v['name']); ?></div>
                                        <div style="font-size: 10.5px; color: var(--accent-gold); font-weight: 800; margin-top: 3px;"><?php echo (int)$v['points']; ?> POINTS</div>
                                    </div>
                                    <button type="button" onclick="requestRedeem('<?php echo addslashes($v['name']); ?>', <?php echo (int)$v['points']; ?>)" style="background: var(--primary); border: none; color: #0b0f19; font-weight: 800; font-size: 11.5px; padding: 7px 16px; border-radius: 8px; cursor: pointer; transition: transform 0.15s ease;">Claim</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Fitness Section -->
                <div class="reward-cat-panel" id="panel-fitness" style="display: none;">
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php if (empty($fitnessVouchers)): ?>
                            <div style="text-align: center; color: var(--text-muted); font-size: 12px; padding: 10px;">No awards available in this category.</div>
                        <?php else: ?>
                            <?php foreach ($fitnessVouchers as $v): ?>
                                <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 12px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center;">
                                    <div style="flex: 1; padding-right: 10px;">
                                        <div style="font-size: 12.5px; font-weight: 700; color: #fff;"><?php echo htmlspecialchars($v['name']); ?></div>
                                        <div style="font-size: 10.5px; color: var(--accent-gold); font-weight: 800; margin-top: 3px;"><?php echo (int)$v['points']; ?> POINTS</div>
                                    </div>
                                    <button type="button" onclick="requestRedeem('<?php echo addslashes($v['name']); ?>', <?php echo (int)$v['points']; ?>)" style="background: var(--primary); border: none; color: #0b0f19; font-weight: 800; font-size: 11.5px; padding: 7px 16px; border-radius: 8px; cursor: pointer; transition: transform 0.15s ease;">Claim</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Gift Section -->
                <div class="reward-cat-panel" id="panel-gift" style="display: none;">
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php if (empty($giftVouchers)): ?>
                            <div style="text-align: center; color: var(--text-muted); font-size: 12px; padding: 10px;">No awards available in this category.</div>
                        <?php else: ?>
                            <?php foreach ($giftVouchers as $v): ?>
                                <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 12px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center;">
                                    <div style="flex: 1; padding-right: 10px;">
                                        <div style="font-size: 12.5px; font-weight: 700; color: #fff;"><?php echo htmlspecialchars($v['name']); ?></div>
                                        <div style="font-size: 10.5px; color: var(--accent-gold); font-weight: 800; margin-top: 3px;"><?php echo (int)$v['points']; ?> POINTS</div>
                                    </div>
                                    <button type="button" onclick="requestRedeem('<?php echo addslashes($v['name']); ?>', <?php echo (int)$v['points']; ?>)" style="background: var(--primary); border: none; color: #0b0f19; font-weight: 800; font-size: 11.5px; padding: 7px 16px; border-radius: 8px; cursor: pointer; transition: transform 0.15s ease;">Claim</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Nights Section -->
                <div class="reward-cat-panel" id="panel-nights" style="display: none;">
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php if (empty($nightsVouchers)): ?>
                            <div style="text-align: center; color: var(--text-muted); font-size: 12px; padding: 10px;">No awards available in this category.</div>
                        <?php else: ?>
                            <?php foreach ($nightsVouchers as $v): ?>
                                <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 12px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center;">
                                    <div style="flex: 1; padding-right: 10px;">
                                        <div style="font-size: 12.5px; font-weight: 700; color: #fff;"><?php echo htmlspecialchars($v['name']); ?></div>
                                        <div style="font-size: 10.5px; color: var(--accent-gold); font-weight: 800; margin-top: 3px;"><?php echo (int)$v['points']; ?> POINTS</div>
                                    </div>
                                    <button type="button" onclick="requestRedeem('<?php echo addslashes($v['name']); ?>', <?php echo (int)$v['points']; ?>)" style="background: var(--primary); border: none; color: #0b0f19; font-weight: 800; font-size: 11.5px; padding: 7px 16px; border-radius: 8px; cursor: pointer; transition: transform 0.15s ease;">Claim</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        function showPwaConfirm(msg, onConfirm) {
            $('#pwa-confirm-msg').html(msg);
            $('#pwa-confirm-modal').css('display', 'flex');
            
            $('#pwa-confirm-ok-btn').off('click').on('click', function() {
                $('#pwa-confirm-modal').css('display', 'none');
                if (typeof onConfirm === 'function') onConfirm();
            });
            
            $('#pwa-confirm-cancel-btn').off('click').on('click', function() {
                $('#pwa-confirm-modal').css('display', 'none');
            });
        }

        function showPwaAlert(msg) {
            $('#pwa-alert-msg').html(msg);
            $('#pwa-alert-modal').css('display', 'flex');
        }

        function confirmGuestCheckin() {
            // Show spinner
            $('#btn-icon').hide();
            $('#btn-spinner').show();
            
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: {
                    action: 'confirm_checkin',
                    token: '<?php echo htmlspecialchars($token); ?>'
                },
                success: function(response) {
                    $('#btn-spinner').hide();
                    $('#btn-icon').show();
                    
                    if (response.success) {
                        $('#checkin-initial-state').fadeOut(200, function() {
                            $('#checkin-success-state').fadeIn(200);
                            // Update lock icon to green open lock
                            $('#wallet-lock-icon').html('<i class="fa-solid fa-lock-open" style="color: #10b981;" title="Identity Verified"></i>');
                            // Auto-dismiss checkin box after 10 seconds
                            setTimeout(function() {
                                $('#checkin-box').fadeOut(600);
                            }, 10000);
                        });
                    } else {
                        showPwaAlert(response.message || "Verification failed");
                    }
                },
                error: function() {
                    $('#btn-spinner').hide();
                    $('#btn-icon').show();
                    showPwaAlert("Connection error. Please try again.");
                }
            });
        }

        let lastNotifiedSpendId = localStorage.getItem('last_spend_id_<?php echo $member ? $member['id'] : 0; ?>') || 0;
        
        function pollLiveStatus() {
            $.ajax({
                url: window.location.href,
                type: 'GET',
                data: {
                    action: 'get_live_status',
                    token: '<?php echo htmlspecialchars($token); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        // Update points balance text if exists
                        if ($('#live-points-val').length > 0) {
                            $('#live-points-val').text(response.balance + ' Pts');
                        }
                        if ($('#live-points-card-val').length > 0) {
                            $('#live-points-card-val').text(response.balance);
                        }

                        // Re-render Vouchers Wallet list dynamically
                        if (response.vouchers && $('#vouchers-wallet-list').length > 0) {
                            let vHtml = '';
                            if (response.vouchers.length === 0) {
                                vHtml = '<p style="font-size: 12px; color: var(--text-muted); text-align: center; padding: 10px 0;">No vouchers in wallet.</p>';
                            } else {
                                response.vouchers.forEach(v => {
                                    const isActive = v.status === 'Active';
                                    const isUsed = v.status === 'Used';
                                    const isExpired = v.status === 'Expired';
                                    
                                    const opacity = isActive ? '1' : '0.5';
                                    let badgeStyle = 'background: var(--primary); color: #fff;';
                                    if (isUsed) {
                                        badgeStyle = 'background: #4b5563; color: #d1d5db;';
                                    } else if (isExpired) {
                                        badgeStyle = 'background: #dc2626; color: #ffffff;';
                                    }
                                    
                                    const safeDesc = (v.description || '')
                                        .replace(/&/g, "&amp;")
                                        .replace(/</g, "&lt;")
                                        .replace(/>/g, "&gt;")
                                        .replace(/"/g, "&quot;")
                                        .replace(/'/g, "&#039;");
                                        
                                    const dateLabel = isUsed ? `Used: ${v.used_at || 'N/A'}` : `Exp: ${v.valid_until}`;
                                    
                                    vHtml += `
                                        <div class="voucher-stub" style="opacity: ${opacity}; display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); padding: 12px; border-radius: 10px; margin-bottom: 10px;">
                                            <div>
                                                <div class="voucher-desc" style="font-size: 13px; font-weight: 700; color: #fff;">${safeDesc}</div>
                                                <div class="voucher-expiry" style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                                                    ${dateLabel}
                                                </div>
                                            </div>
                                            <span class="voucher-badge" style="padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; ${badgeStyle}">${v.status.toUpperCase()}</span>
                                        </div>
                                    `;
                                });
                            }
                            $('#vouchers-wallet-list').html(vHtml);
                        }
                        
                        // Check if new spend record was logged
                        if (response.latest_spend) {
                            const spend = response.latest_spend;
                            
                            // Initialize lastNotifiedSpendId on first run if it's 0 to prevent notifying historical spending
                            if (lastNotifiedSpendId == 0) {
                                lastNotifiedSpendId = spend.id;
                                localStorage.setItem('last_spend_id_<?php echo $member ? $member['id'] : 0; ?>', spend.id);
                                return;
                            }
                            
                            if (spend.id > lastNotifiedSpendId) {
                                lastNotifiedSpendId = spend.id;
                                localStorage.setItem('last_spend_id_<?php echo $member ? $member['id'] : 0; ?>', spend.id);
                                
                                // Show dynamic spend alert
                                $('#alert-spend-desc').html(`You spent <strong>${spend.amount.toFixed(3)} ${response.currency}</strong> at <strong>${spend.dept}</strong>!`);
                                
                                if (spend.points_earned > 0) {
                                    $('#alert-spend-points').text(`+${spend.points_earned} Points added to your wallet!`);
                                    $('#alert-spend-points').show();
                                } else {
                                    $('#alert-spend-points').hide();
                                }
                                
                                $('#live-spend-alert').slideDown(300);
                                
                                // Auto hide alert after 8 seconds
                                setTimeout(function() {
                                    $('#live-spend-alert').slideUp(300);
                                }, 8000);
                            }
                        }
                    }
                }
            });
        }
        
        // PWA Install Logic
        let deferredPrompt = null;

        function isIOS() {
            return /iphone|ipad|ipod/i.test(navigator.userAgent);
        }
        function isAndroid() {
            return /android/i.test(navigator.userAgent);
        }
        function isInStandaloneMode() {
            return ('standalone' in window.navigator && window.navigator.standalone) ||
                   window.matchMedia('(display-mode: standalone)').matches;
        }

        // Capture native Android install prompt — auto-show immediately
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;

            if (isAndroid() && !isInStandaloneMode()) {
                // Auto-trigger the Chrome install bottom-sheet immediately
                e.prompt();
                e.userChoice.then(function(result) {
                    if (result.outcome === 'accepted') {
                        $('#pwa-install-wrap').fadeOut();
                    } else {
                        // User dismissed — keep button visible so they can try again
                        $('#pwa-install-wrap').show();
                    }
                    deferredPrompt = null;
                });
            }
        });

        function switchTab(tab) {
            if (tab === 'https') {
                document.getElementById('panel-https').style.display = 'block';
                document.getElementById('panel-manual').style.display = 'none';
                document.getElementById('tab-https').style.cssText += 'background:rgba(217,119,6,0.1);color:#fbbf24;border-bottom:2px solid #d97706;';
                document.getElementById('tab-manual').style.cssText += 'background:transparent;color:#6b7280;border-bottom:2px solid transparent;';
            } else {
                document.getElementById('panel-https').style.display = 'none';
                document.getElementById('panel-manual').style.display = 'block';
                document.getElementById('tab-manual').style.cssText += 'background:rgba(217,119,6,0.1);color:#fbbf24;border-bottom:2px solid #d97706;';
                document.getElementById('tab-https').style.cssText += 'background:transparent;color:#6b7280;border-bottom:2px solid transparent;';
            }
        }

        function openHttpsLink() {
            var url = document.getElementById('https-wallet-url').textContent.trim();
            if (url) window.open(url, '_blank');
        }

        function buildHttpsUrl() {
            // Swap current HTTP host for HTTPS via ngrok API (localhost:4040)
            // Then replace host in current URL
            var currentPath = window.location.pathname + window.location.search;
            $.getJSON('http://127.0.0.1:4040/api/tunnels', function(data) {
                var httpsUrl = null;
                if (data && data.tunnels) {
                    data.tunnels.forEach(function(t) {
                        if (t.proto === 'https') httpsUrl = t.public_url;
                    });
                }
                if (httpsUrl) {
                    document.getElementById('https-wallet-url').textContent = httpsUrl + currentPath;
                } else {
                    document.getElementById('https-wallet-url').textContent = 'HTTPS URL not found. Please ensure ngrok is running.';
                }
            }).fail(function() {
                document.getElementById('https-wallet-url').textContent = window.location.href.replace('http://', 'https://');
            });
        }

        function triggerInstall() {
            if (deferredPrompt) {
                // Native Chrome prompt available — use it
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function(result) {
                    if (result.outcome === 'accepted') {
                        $('#pwa-install-wrap').fadeOut();
                    }
                    deferredPrompt = null;
                });
            } else {
                // Populate the HTTPS URL then show the modal
                buildHttpsUrl();
                document.getElementById('android-modal').style.display = 'flex';
            }
        }

        // Register Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/backend/admin/sw.js').catch(function() {});
        }

        function showRewardCategory(category, btn) {
            $('.reward-cat-panel').hide();
            $('#panel-' + category).show();
            
            $('.cat-tab').removeClass('active').css({
                'background': 'transparent',
                'color': 'var(--text-muted)',
                'border-color': 'transparent'
            });
            $(btn).addClass('active').css({
                'background': 'rgba(251,191,36,0.12)',
                'color': '#fbbf24',
                'border-color': 'rgba(251,191,36,0.25)'
            });
        }

        function requestRedeem(awardTitle, pointsCost) {
            const currentBalance = parseInt($('#live-points-card-val').text() || '0');
            if (currentBalance < pointsCost) {
                showPwaAlert(`Insufficient points! This reward requires ${pointsCost} points, but you only have ${currentBalance} points.`);
                return;
            }

            showPwaConfirm(`Are you sure you want to request to redeem "${awardTitle}" for ${pointsCost} points?`, function() {
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: {
                        action: 'request_redemption',
                        token: '<?php echo htmlspecialchars($token); ?>',
                        award_title: awardTitle
                    },
                    success: function(response) {
                        if (response.success) {
                            showPwaAlert(response.message);
                            pollLiveStatus();
                        } else {
                            showPwaAlert(response.message || "Redemption request failed");
                        }
                    },
                    error: function() {
                        showPwaAlert("Connection error. Please try again.");
                    }
                });
            });
        }

        // Start polling every 4 seconds
        $(document).ready(function() {
            if (!isInStandaloneMode()) {
                if (isIOS()) {
                    $('#ios-install-wrap').show();
                } else if (isAndroid()) {
                    // Always show for Android — native prompt or manual guide
                    $('#pwa-install-wrap').show();
                }
            }

            <?php if ($member): ?>
                pollLiveStatus(); 
                setInterval(pollLiveStatus, 4000);
                <?php if ($isCheckedIn): ?>
                // Already verified on page load — auto-dismiss checkin box after 10s
                setTimeout(function() {
                    $('#checkin-box').fadeOut(600);
                }, 10000);
                <?php endif; ?>
            <?php endif; ?>
        });
    </script>
    
    <style>
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); opacity: 0.8; }
            70% { transform: scale(0.9); opacity: 0.9; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</body>
</html>
