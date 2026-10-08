<?php
session_start();
require_once 'config.php';
require_once 'otp_helper.php';
require_once 'hash_password.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot-password.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$npassword = $_POST['npassword'] ?? '';
$cpassword = $_POST['cpassword'] ?? '';

if ($email === '' || $npassword === '' || $cpassword === '') {
    $_SESSION['error'] = "Please fill in all fields.";
    header('Location: forgot-password.php');
    exit;
}

if ($npassword !== $cpassword) {
    $_SESSION['error'] = "Passwords do not match.";
    header('Location: forgot-password.php');
    exit;
}

// verify user exists
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if (! $result || $result->num_rows === 0) {
    $_SESSION['error'] = "Email address not found.";
    $stmt->close();
    $conn->close();
    header('Location: forgot-password.php');
    exit;
}
$user = $result->fetch_assoc();
$user_id = (int)$user['id'];
$stmt->close();

// generate OTP, store in DB, hash new password in session
$otp = generateOTP();
if (! storeOTP($user_id, $email, $otp)) {
    $_SESSION['error'] = "Failed to generate reset code. Try again.";
    $conn->close();
    header('Location: forgot-password.php');
    exit;
}

$_SESSION['reset_user_id'] = $user_id;
$_SESSION['reset_email'] = $email;
$_SESSION['reset_new_hash'] = hash_password($npassword);

// send email
$sent = sendOTP($email, $otp);
if ($sent) {
    $_SESSION['message'] = "A reset code has been sent to " . htmlspecialchars($email) . ".";
} else {

    $_SESSION['message'] = "Unable to send email; for testing your OTP is: " . htmlspecialchars($otp);
}

$conn->close();
header('Location: otp_ver.php');
exit;
?>