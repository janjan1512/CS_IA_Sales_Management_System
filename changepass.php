<?php
session_start();
require_once 'config.php';
require_once 'otp_helper.php'; 

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';


unset($_SESSION['message'], $_SESSION['error']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="./CSS/changepass.css">
</head>
<body>
    <div class="changepass-container">
        
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        <div class="forpass-container">
            <div class="logo">
                <img src="./logo.png" alt="logo"/>
            </div>

            
            <?php if (!empty(trim($error ?? ''))): ?>
                <div class="error-msg">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form action="otp_req.php" method="post">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>

                <label for="password">New Password</label>
                <input type="password" id="password" name="npassword" required>

                <label for="cpassword">Enter Again</label>
                <input type="password" id="cpassword" name="cpassword" required>

                <button type="submit" name="request_reset">Set Password</button>
                <p><a href="login.php">Back to Login</a></p>
            </form>
        </div>
    </div>
    
    <script src="./JS/change-pass.js"></script>
</body>
</html>
