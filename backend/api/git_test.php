<?php
// backend/api/git_test.php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: text/plain');

echo "=== Git Webhook Diagnostic Test ===\n";
echo "Current server user: " . (function_exists('get_current_user') ? get_current_user() : 'Unknown') . "\n";
echo "Configured REPO_DIR: " . (defined('REPO_DIR') ? REPO_DIR : 'Not Defined') . "\n";

$repoDir = defined('REPO_DIR') ? REPO_DIR : '';

if (empty($repoDir) || !is_dir($repoDir)) {
    echo "ERROR: REPO_DIR does not exist or is not a directory on this server!\n";
    exit;
}

echo "\n--- 1. Testing 'git status' ---\n";
$outputStatus = [];
$returnStatus = 0;
exec("cd " . escapeshellarg($repoDir) . " && git status 2>&1", $outputStatus, $returnStatus);
echo "Exit code: " . $returnStatus . "\n";
echo "Output:\n" . implode("\n", $outputStatus) . "\n";

echo "\n--- 2. Testing 'git pull origin main' ---\n";
$outputPull = [];
$returnPull = 0;
exec("cd " . escapeshellarg($repoDir) . " && git pull origin main 2>&1", $outputPull, $returnPull);
echo "Exit code: " . $returnPull . "\n";
echo "Output:\n" . implode("\n", $outputPull) . "\n";
?>
