<?php
session_start();
require_once 'config.php';
require_once 'otp_helper.php';
require_once 'hash_password.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot-password.php');
    exit;
}

$otp_submitted = trim($_POST['otp'] ?? '');
$result = verify_reset_otp($otp_submitted);

if (! $result['ok']) {
    $_SESSION['error'] = $result['error'];
    header('Location: otp_ver.php');
    exit;
}

$_SESSION['message'] = $result['message'];
header('Location: login.php');
exit;
?>