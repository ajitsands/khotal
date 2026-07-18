<?php
// backend/admin/logout.php
require_once __DIR__ . '/../config/session_helper.php';
session_unset();
session_destroy();
header('Location: login.php');
exit;
