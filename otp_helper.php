<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function generateOTP(): string {
    return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
}

function sendOTP(string $email, string $otp): bool {

    $smtpUser = $_ENV['SMTP_USER'] ?? getenv('SMTP_USER') ?? '';
    $smtpPass = $_ENV['SMTP_PASS'] ?? getenv('SMTP_PASS') ?? '';
    $smtpHost = $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?? 'smtp.gmail.com';
    $smtpPort = (int) ($_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?? 587);
    $fromName = $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME') ?? 'System';
    $replyTo  = $_ENV['SMTP_REPLYTO'] ?? getenv('SMTP_REPLYTO') ?? '';

    if ($smtpUser === '' || $smtpPass === '') {
        error_log('sendOTP: SMTP credentials missing (SMTP_USER/SMTP_PASS).');
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtpPort;

        
        $mail->setFrom($smtpUser, $fromName);
        if (!empty($replyTo)) {
            $mail->addReplyTo($replyTo, 'Support');
        }

        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Code';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width:600px; margin:0 auto;'>
                <h2 style='color:#1BA32E;'>Password Reset Request</h2>
                <p>Your password reset code is:</p>
                <div style='background:#f4f8f6; padding:20px; text-align:center; border-radius:5px; margin:20px 0;'>
                    <span style='font-size:32px; font-weight:bold; color:#1BA32E; letter-spacing:3px;'>" . htmlspecialchars($otp) . "</span>
                </div>
                <p>This code will expire in 10 minutes.</p>
                <p style='color:#666; font-size:14px;'>If you didn't request this, please ignore this email.</p>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('sendOTP failed: ' . $mail->ErrorInfo);
        return false;
    }
}


function storeOTP(int $user_id, string $email, string $otp): bool {
    $conn = getDBConnection();


    $delete_sql = "DELETE FROM otp_codes WHERE user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    if (! $delete_stmt) {
        error_log('storeOTP: prepare delete failed');
        return false;
    }
    $delete_stmt->bind_param('i', $user_id);
    $delete_stmt->execute();
    $delete_stmt->close();

    $expires_at = date('Y-m-d H:i:s', time() + 600); 
    $insert_sql = "INSERT INTO otp_codes (user_id, email, otp_code, expires_at, is_used) VALUES (?, ?, ?, ?, FALSE)";
    $insert_stmt = $conn->prepare($insert_sql);
    if (! $insert_stmt) {
        error_log('storeOTP: prepare insert failed');
        return false;
    }
    $insert_stmt->bind_param('isss', $user_id, $email, $otp, $expires_at);
    $ok = $insert_stmt->execute();
    $insert_stmt->close();

    return (bool) $ok;
}

function verifyOTP(int $user_id, string $otp): bool {
    $conn = getDBConnection();

    $sql = "SELECT id, expires_at FROM otp_codes WHERE user_id = ? AND otp_code = ? AND is_used = FALSE";
    $stmt = $conn->prepare($sql);
    if (! $stmt) {
        error_log('verifyOTP: prepare failed');
        return false;
    }
    $stmt->bind_param('is', $user_id, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        if (strtotime($row['expires_at']) > time()) {
            $otp_id = (int) $row['id'];
            $update_sql = "UPDATE otp_codes SET is_used = TRUE WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            if ($update_stmt) {
                $update_stmt->bind_param('i', $otp_id);
                $update_stmt->execute();
                $update_stmt->close();
            }
            $stmt->close();
            return true;
        }
    }

    $stmt->close();
    return false;
}

/**
 * High level helper: verify reset OTP (DB first, fallback to session if needed),
 * and apply stored hashed password (expects $_SESSION['reset_new_hash'] present).
 * Returns ['ok'=>bool, 'message'|'error'=>string].
 */
function verify_reset_otp(string $otp_submitted): array {
    $user_id = $_SESSION['reset_user_id'] ?? null;
    $new_hash = $_SESSION['reset_new_hash'] ?? null;
    if (! $user_id || ! $new_hash) {
        return ['ok' => false, 'error' => 'Session expired. Please start over.'];
    }

    if (verifyOTP((int)$user_id, $otp_submitted)) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if (! $stmt) {
            return ['ok' => false, 'error' => 'Server error. Try again later.'];
        }
        $stmt->bind_param('si', $new_hash, $user_id);
        $executed = $stmt->execute();
        $stmt->close();

        if ($executed) {
            unset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['reset_new_hash'], $_SESSION['reset_user_id'], $_SESSION['reset_email']);
            return ['ok' => true, 'message' => 'Password reset successfully. Please login.'];
        }
        return ['ok' => false, 'error' => 'Failed to update password.'];
    }

    return ['ok' => false, 'error' => 'Invalid or expired OTP.'];
}
?>
