<?php
// Public Redirector and Click Tracker API for encrypted campaign links
require_once __DIR__ . '/../config/db_connection.php';

$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token)) {
    displayError("Invalid Access", "A secure token is required to access this resource.");
}

try {
    // 1. Look up the campaign link
    $stmt = $pdo->prepare("SELECT * FROM campaign_links WHERE token = ?");
    $stmt->execute([$token]);
    $campaign = $stmt->fetch();

    if (!$campaign) {
        displayError("Link Expired or Invalid", "The link you clicked has expired or is no longer registered in our system.");
    }

    // 2. Increment click count
    $stmtUpdate = $pdo->prepare("UPDATE campaign_links SET click_count = click_count + 1 WHERE token = ?");
    $stmtUpdate->execute([$token]);

    // 3. Construct target URL with dynamic secure token appended
    $targetUrl = $campaign['target_url'];
    $separator = (strpos($targetUrl, '?') === false) ? '?' : '&';
    $redirectUrl = $targetUrl . $separator . 'token=' . urlencode($token);

    // 4. Redirect browser
    header("Location: " . $redirectUrl);
    exit;
} catch (Exception $e) {
    displayError("System Error", "An unexpected database error occurred: " . $e->getMessage());
}

// Visual error display fallback helper
function displayError($title, $message) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                background: #0b0f19;
                color: #f3f4f6;
                font-family: 'Outfit', sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
            }
            .error-card {
                max-width: 440px;
                width: 100%;
                background: rgba(22, 28, 45, 0.5);
                border: 1px solid rgba(239, 68, 68, 0.2);
                border-radius: 16px;
                padding: 40px 30px;
                text-align: center;
                box-shadow: 0 10px 30px rgba(0,0,0,0.5);
                backdrop-filter: blur(10px);
            }
            .error-icon {
                font-size: 50px;
                color: #ef4444;
                margin-bottom: 20px;
            }
            h1 {
                font-size: 22px;
                margin-bottom: 12px;
                font-weight: 700;
                color: #fff;
            }
            p {
                font-size: 14px;
                color: #9ca3af;
                line-height: 1.6;
                margin-bottom: 25px;
            }
            .btn {
                display: inline-block;
                padding: 10px 24px;
                background: #ef4444;
                color: #fff;
                text-decoration: none;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                transition: background 0.3s;
            }
            .btn:hover {
                background: #dc2626;
            }
        </style>
    </head>
    <body>
        <div class="error-card">
            <div class="error-icon">⚠️</div>
            <h1><?php echo htmlspecialchars($title); ?></h1>
            <p><?php echo htmlspecialchars($message); ?></p>
            <a href="https://thekhotel.com" class="btn">Return to Homepage</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}
