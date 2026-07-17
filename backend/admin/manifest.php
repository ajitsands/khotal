<?php
require_once __DIR__ . '/../config/db_connection.php';

header('Content-Type: application/manifest+json');
header('Cache-Control: no-cache');

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$startUrl = '/backend/admin/verify_member.php' . ($token ? '?token=' . urlencode($token) : '');

$memberName = 'K Wallet';
if ($token) {
    try {
        $decoded = UrlEncryptor::decryptUrlToken($token);
        if ($decoded && isset($decoded['member_id'])) {
            $stmt = $pdo->prepare("SELECT first_name, last_name FROM members WHERE id = ?");
            $stmt->execute([$decoded['member_id']]);
            $m = $stmt->fetch();
            if ($m) {
                $memberName = $m['first_name'] . "'s K Wallet";
            }
        }
    } catch (Exception $e) { /* ignore */ }
}

$manifest = [
    "name"             => $memberName,
    "short_name"       => "K Wallet",
    "description"      => "K Hotel Loyalty Wallet — your digital membership card and points tracker.",
    "start_url"        => $startUrl,
    "scope"            => "/",
    "display"          => "standalone",
    "orientation"      => "portrait",
    "background_color" => "#0b0f19",
    "theme_color"      => "#d97706",
    "lang"             => "en",
    "icons"            => [
        [
            "src"     => "/backend/admin/pwa-icon-192.png",
            "sizes"   => "192x192",
            "type"    => "image/png",
            "purpose" => "any maskable"
        ],
        [
            "src"     => "/backend/admin/pwa-icon-512.png",
            "sizes"   => "512x512",
            "type"    => "image/png",
            "purpose" => "any maskable"
        ]
    ]
];

echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
