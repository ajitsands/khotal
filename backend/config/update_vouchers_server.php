<?php
// PHP Script to execute SQL setting seed for redeemable vouchers on the server
require_once __DIR__ . '/db_connection.php';

try {
    echo "Updating redeemable vouchers settings...<br>";
    
    $vouchersJson = '[{"id":"v1","name":"Lunch for two at KOLORS Restaurant","points":15,"category":"meals","description":"Lunch Buffet for two at KOLORS Restaurant"},{"id":"v2","name":"Dinner for two at KOLORS Restaurant","points":20,"category":"meals","description":"Dinner Buffet for two at KOLORS Restaurant"},{"id":"v3","name":"Lunch or Dinner for two at the K Lounge","points":35,"category":"meals","description":"Lunch or Dinner menu for two at the K Lounge"},{"id":"v4","name":"Friday Brunch for two at KOLORS Restaurant","points":50,"category":"meals","description":"Friday Brunch Buffet for two at KOLORS Restaurant"},{"id":"v5","name":"1 Month health club membership (single)","points":30,"category":"fitness","description":"1 Month health club fitness membership (single)"},{"id":"v6","name":"1 Month health club membership (couple)","points":50,"category":"fitness","description":"1 Month health club fitness membership (couple)"},{"id":"v7","name":"3 Month health club membership (single)","points":100,"category":"fitness","description":"3 Month health club fitness membership (single)"},{"id":"v8","name":"3 Month health club membership (couple)","points":150,"category":"fitness","description":"3 Month health club fitness membership (couple)"},{"id":"v9","name":"20.000 BHD gift voucher","points":20,"category":"gift","description":"20.000 BHD gift certificate"},{"id":"v10","name":"50.000 BHD gift voucher","points":50,"category":"gift","description":"50.000 BHD gift certificate"},{"id":"v11","name":"75.000 BHD gift voucher","points":75,"category":"gift","description":"75.000 BHD gift certificate"},{"id":"v12","name":"100.000 BHD gift voucher","points":100,"category":"gift","description":"100.000 BHD gift certificate"},{"id":"v13","name":"One night in a deluxe room","points":50,"category":"nights","description":"1 Night stay in a deluxe room for two"},{"id":"v14","name":"One night in a Junior Suite","points":75,"category":"nights","description":"1 Night stay in a Junior Suite for two"},{"id":"v15","name":"One night in a Senior Suite","points":100,"category":"nights","description":"1 Night stay in a Senior Suite for two"},{"id":"v16","name":"One night in the Amiri Suite","points":150,"category":"nights","description":"1 Night stay in the Amiri Suite for two"},{"id":"v17","name":"One night in the Royal Suite","points":250,"category":"nights","description":"1 Night stay in the Royal Suite for two"}]';
    
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $stmt->execute(['redeemable_vouchers', $vouchersJson]);
    $stmt->execute(['hotel_name', 'The K Hotel']);
    $stmt->execute(['hotel_sub', 'BAHRAIN']);
    $stmt->execute(['hotel_logo', '']);
    
    echo "Successfully updated redeemable vouchers, hotel_name, hotel_sub, and hotel_logo settings in the database!<br>";
} catch (Exception $e) {
    echo "Failed to update: " . $e->getMessage() . "<br>";
}
