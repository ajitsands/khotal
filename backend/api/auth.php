<?php
require_once __DIR__ . '/../config/db_connection.php';

// Enable CORS for development
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'login':
        // Retrieve and decode JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        $emailOrMobile = isset($input['login_identifier']) ? trim($input['login_identifier']) : '';
        $password = isset($input['password']) ? $input['password'] : '';

        if (empty($emailOrMobile) || empty($password)) {
            sendJSONResponse(false, null, "Email/Mobile and password are required.", 400);
        }

        try {
            // Find user by email or mobile
            $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ? OR mobile = ?");
            $stmt->execute([$emailOrMobile, $emailOrMobile]);
            $member = $stmt->fetch();

            if (!$member || !password_verify($password, $member['password_hash'])) {
                sendJSONResponse(false, null, "Invalid credentials.", 401);
            }

            if ($member['status'] !== 'Active') {
                sendJSONResponse(false, null, "Account is " . strtolower($member['status']) . ". Please contact the front desk.", 403);
            }

            // Create a simple session token (for demonstration we use an encrypted token containing membership number and ID)
            $tokenParams = [
                'member_id' => $member['id'],
                'membership_number' => $member['membership_number'],
                'expiry' => time() + (86400 * 30) // 30 days session
            ];
            // Encrypt token using our URL encryption tool!
            $sessionToken = UrlEncryptor::encryptUrl('', $tokenParams);
            $sessionToken = parse_url($sessionToken, PHP_URL_QUERY); // Extract "token=..."
            $sessionToken = str_replace('token=', '', $sessionToken);

            // Clean sensitive data before returning user info
            unset($member['password_hash']);

            sendJSONResponse(true, [
                'token' => $sessionToken,
                'member' => $member
            ], "Login successful.");

        } catch (PDOException $e) {
            sendJSONResponse(false, null, "Database error during login: " . $e->getMessage(), 500);
        }
        break;

    case 'profile':
        // Check Authorization header
        $headers = getallheaders();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

        if (empty($authHeader) || strpos($authHeader, 'Bearer ') !== 0) {
            sendJSONResponse(false, null, "Unauthorized: Access token missing or invalid format.", 401);
        }

        $token = substr($authHeader, 7);
        $decoded = UrlEncryptor::decryptUrlToken($token);

        if (!$decoded || !isset($decoded['member_id']) || $decoded['expiry'] < time()) {
            sendJSONResponse(false, null, "Unauthorized: Token expired or invalid.", 401);
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
            $stmt->execute([$decoded['member_id']]);
            $member = $stmt->fetch();

            if (!$member) {
                sendJSONResponse(false, null, "Member not found.", 404);
            }

            unset($member['password_hash']);
            sendJSONResponse(true, ['member' => $member], "Profile retrieved.");
        } catch (PDOException $e) {
            sendJSONResponse(false, null, "Database error: " . $e->getMessage(), 500);
        }
        break;

    default:
        sendJSONResponse(false, null, "Invalid auth action.", 404);
        break;
}
?>
