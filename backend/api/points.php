<?php
require_once __DIR__ . '/../config/db_connection.php';

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Authenticate request
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (empty($authHeader) || strpos($authHeader, 'Bearer ') !== 0) {
    sendJSONResponse(false, null, "Unauthorized: Access token missing.", 401);
}

$token = substr($authHeader, 7);
$decoded = UrlEncryptor::decryptUrlToken($token);

if (!$decoded || !isset($decoded['member_id']) || $decoded['expiry'] < time()) {
    sendJSONResponse(false, null, "Unauthorized: Token expired or invalid.", 401);
}

$memberId = $decoded['member_id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Awards Point Cost Mapping
$awardsCatalogue = [
    // Meals
    'Lunch for two at KOLORS Restaurant' => 15,
    'Dinner for two at KOLORS Restaurant' => 20,
    'Lunch or Dinner for two at the K Lounge' => 35,
    'Friday Brunch for two at KOLORS Restaurant' => 50,
    // Fitness
    '1 Month health club membership (single)' => 30,
    '1 Month health club membership (couple)' => 50,
    '3 Month health club membership (single)' => 100,
    '3 Month health club membership (couple)' => 150,
    // Shopping
    '20.000 BHD gift voucher' => 20,
    '50.000 BHD gift voucher' => 50,
    '75.000 BHD gift voucher' => 75,
    '100.000 BHD gift voucher' => 100,
    // Free Nights
    'One night in a deluxe room' => 50,
    'One night in a Junior Suite' => 75,
    'One night in a Senior Suite' => 100,
    'One night in the Amiri Suite' => 150,
    'One night in the Royal Suite' => 250,
];

switch ($action) {
    case 'balance':
        try {
            // Verify if user is K Reward member
            $stmtUser = $pdo->prepare("SELECT membership_type FROM members WHERE id = ?");
            $stmtUser->execute([$memberId]);
            $user = $stmtUser->fetch();
            
            if ($user['membership_type'] !== 'K Reward') {
                sendJSONResponse(true, [
                    'membership_type' => $user['membership_type'],
                    'current_balance' => 0,
                    'total_earned' => 0,
                    'total_redeemed' => 0
                ], "Non-points program.");
            }

            // Sum earned points
            $stmtEarned = $pdo->prepare("SELECT SUM(points_earned) as total_earned FROM points_ledger WHERE member_id = ?");
            $stmtEarned->execute([$memberId]);
            $earned = $stmtEarned->fetch();
            $totalEarned = (int)$earned['total_earned'];

            // Sum redeemed points (points used in approved or pending redemptions)
            // Wait, points are deducted only on approved redemptions, but let's query ledger points_redeemed.
            $stmtRedeemed = $pdo->prepare("SELECT SUM(points_redeemed) as total_redeemed FROM points_ledger WHERE member_id = ?");
            $stmtRedeemed->execute([$memberId]);
            $redeemed = $stmtRedeemed->fetch();
            $totalRedeemed = (int)$redeemed['total_redeemed'];

            $currentBalance = $totalEarned - $totalRedeemed;

            sendJSONResponse(true, [
                'membership_type' => 'K Reward',
                'current_balance' => $currentBalance,
                'total_earned' => $totalEarned,
                'total_redeemed' => $totalRedeemed
            ], "Points balance calculated.");
        } catch (PDOException $e) {
            sendJSONResponse(false, null, "Database error: " . $e->getMessage(), 500);
        }
        break;

    case 'history':
        try {
            // Get all point transactions
            $stmt = $pdo->prepare("SELECT * FROM points_ledger WHERE member_id = ? ORDER BY transaction_date DESC, id DESC");
            $stmt->execute([$memberId]);
            $ledger = $stmt->fetchAll();

            sendJSONResponse(true, ['ledger' => $ledger], "Points history retrieved.");
        } catch (PDOException $e) {
            sendJSONResponse(false, null, "Database error: " . $e->getMessage(), 500);
        }
        break;

    case 'redeem':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJSONResponse(false, null, "Request method must be POST.", 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $awardTitle = isset($input['award_title']) ? trim($input['award_title']) : '';

        if (empty($awardTitle) || !isset($awardsCatalogue[$awardTitle])) {
            sendJSONResponse(false, null, "Invalid award title specified.", 400);
        }

        $pointsCost = $awardsCatalogue[$awardTitle];

        try {
            // 1. Calculate current points balance
            $stmtEarned = $pdo->prepare("SELECT SUM(points_earned) as total_earned FROM points_ledger WHERE member_id = ?");
            $stmtEarned->execute([$memberId]);
            $totalEarned = (int)$stmtEarned->fetch()['total_earned'];

            $stmtRedeemed = $pdo->prepare("SELECT SUM(points_redeemed) as total_redeemed FROM points_ledger WHERE member_id = ?");
            $stmtRedeemed->execute([$memberId]);
            $totalRedeemed = (int)$stmtRedeemed->fetch()['total_redeemed'];

            // Also check pending redemptions, to avoid double-spending
            $stmtPending = $pdo->prepare("SELECT SUM(points_cost) as pending_cost FROM redemption_requests WHERE member_id = ? AND status = 'Pending'");
            $stmtPending->execute([$memberId]);
            $pendingCost = (int)$stmtPending->fetch()['pending_cost'];

            $availablePoints = $totalEarned - $totalRedeemed - $pendingCost;

            if ($availablePoints < $pointsCost) {
                sendJSONResponse(false, null, "Insufficient points. You have {$availablePoints} points available (excluding pending redemptions), but this award requires {$pointsCost} points.", 400);
            }

            // 2. Insert redemption request
            $stmtInsert = $pdo->prepare("INSERT INTO redemption_requests (member_id, award_title, points_cost, status) VALUES (?, ?, ?, 'Pending')");
            $stmtInsert->execute([$memberId, $awardTitle, $pointsCost]);
            $requestId = $pdo->lastInsertId();

            sendJSONResponse(true, [
                'request_id' => $requestId,
                'award_title' => $awardTitle,
                'points_cost' => $pointsCost,
                'status' => 'Pending'
            ], "Redemption request submitted successfully. It will be verified by the front desk.");

        } catch (PDOException $e) {
            sendJSONResponse(false, null, "Database error: " . $e->getMessage(), 500);
        }
        break;

    case 'catalogue':
        // Return awards catalogue
        sendJSONResponse(true, ['catalogue' => $awardsCatalogue], "Awards catalogue retrieved.");
        break;

    default:
        sendJSONResponse(false, null, "Invalid points action.", 404);
        break;
}
?>
