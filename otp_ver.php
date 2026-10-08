<?php
session_start();
$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);
$reset_email = $_SESSION['reset_email'] ?? '';
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Enter OTP</title>
<link rel="stylesheet" href="./CSS/changepass.css">
</head>
<body>
<div class="changepass-container">
    <?php if (!empty(trim($message))): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (!empty(trim($error))): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="forpass-container">
        <div class="logo"><img src="./logo.png" alt="logo"/></div>
        <p>Enter the code sent to: <strong><?php echo htmlspecialchars($reset_email); ?></strong></p>
        <form action="otp_verify.php" method="post">
            <label for="otp">Reset code</label>
            <input type="text" id="otp" name="otp" maxlength="6" required>
            <button type="submit" name="verify_otp">Verify code</button>
        </form>
        <p><a href="forgot-password.php">← Back</a></p>
    </div>
</div>
</body>
</html>