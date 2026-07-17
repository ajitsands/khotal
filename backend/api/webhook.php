<?php
// backend/api/webhook.php
require_once __DIR__ . '/../config/config.php';

// Check if secret is configured
if (!defined('WEBHOOK_SECRET') || WEBHOOK_SECRET === 'your_webhook_secret_here' || empty(WEBHOOK_SECRET)) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Webhook secret is not configured or is using default template value.');
}

// Read raw request payload
$payload = file_get_contents('php://input');

// Verify signature
if (!isset($_SERVER['HTTP_X_HUB_SIGNATURE_256'])) {
    header('HTTP/1.1 403 Forbidden - No Signature');
    exit('Signature header missing.');
}

list($algo, $hash) = explode('=', $_SERVER['HTTP_X_HUB_SIGNATURE_256'], 2);
if ($algo !== 'sha256') {
    header('HTTP/1.1 400 Bad Request');
    exit('Unsupported signature algorithm.');
}

$calculatedHash = hash_hmac('sha256', $payload, WEBHOOK_SECRET);
if (!hash_equals($calculatedHash, $hash)) {
    header('HTTP/1.1 403 Forbidden - Invalid Signature');
    exit('Signature verification failed.');
}

// Handle GitHub ping event
$event = isset($_SERVER['HTTP_X_GITHUB_EVENT']) ? $_SERVER['HTTP_X_GITHUB_EVENT'] : '';
if ($event === 'ping') {
    echo 'pong';
    exit();
}

// Check if directory exists
$repoDir = defined('REPO_DIR') ? REPO_DIR : '';
if (empty($repoDir) || !is_dir($repoDir)) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Repository directory not found or not configured.');
}

// Perform git pull
$output = [];
$returnVar = 0;

// Run git fetch and reset. We redirect stderr to stdout to capture everything.
exec("cd " . escapeshellarg($repoDir) . " && git fetch origin main 2>&1 && git reset --hard origin/main 2>&1", $output, $returnVar);

// Log result
$logMessage = "[" . date('Y-m-d H:i:s') . "] Sync Attempt:\n";
$logMessage .= "Return code: " . $returnVar . "\n";
$logMessage .= "Output:\n" . implode("\n", $output) . "\n";
$logMessage .= str_repeat("-", 40) . "\n";

// Write to a local log file (make sure directory is writable by webserver)
@file_put_contents(__DIR__ . '/../config/webhook_sync.log', $logMessage, FILE_APPEND);

if ($returnVar === 0) {
    echo "Synchronization successful.";
} else {
    header('HTTP/1.1 500 Internal Server Error');
    echo "Synchronization failed. Check webhook_sync.log for details.";
}
?>
