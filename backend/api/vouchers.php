<?php
require_once __DIR__ . '/../config/db_connection.php';

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, OPTIONS");

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

switch ($action) {
    case 'my_vouchers':
        try {
            // Fetch vouchers for the logged-in member, sorted with Active first
            $stmt = $pdo->prepare("SELECT * FROM vouchers WHERE member_id = ? ORDER BY CASE WHEN status = 'Active' THEN 0 ELSE 1 END, issued_date DESC");
            $stmt->execute([$memberId]);
            $vouchers = $stmt->fetchAll();

            sendJSONResponse(true, ['vouchers' => $vouchers], "Vouchers retrieved successfully.");
        } catch (PDOException $e) {
            sendJSONResponse(false, null, "Database error: " . $e->getMessage(), 500);
        }
        break;

    default:
        sendJSONResponse(false, null, "Invalid vouchers action.", 404);
        break;
}
?>
