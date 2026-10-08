
<?php
session_start();

$role = $_SESSION['user_role'] ?? '';
$is_admin = in_array(strtolower($role), ['admin'], true);
require_once __DIR__ . '/authorize.php';
require_login();

require_once __DIR__ . '/navbar.php';
require_once __DIR__ . '/validate.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmployeeWelcomeEmail(string $email, string $name, string $tempPassword, string $loginUrl, string $changeUrl): bool
{
    $smtpUser = $_ENV['SMTP_USER'] ?? getenv('SMTP_USER') ?? '';
    $smtpPass = $_ENV['SMTP_PASS'] ?? getenv('SMTP_PASS') ?? '';
    $smtpHost = $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?? 'smtp.gmail.com';
    $smtpPort = (int) ($_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?? 587);
    $fromName = $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME') ?? 'System';
    $replyTo  = $_ENV['SMTP_REPLYTO'] ?? getenv('SMTP_REPLYTO') ?? '';

    if ($smtpUser === '' || $smtpPass === '') {
       
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
        if ($replyTo !== '') {
            $mail->addReplyTo($replyTo, 'Support');
        }

        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to the team';

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safePassword = htmlspecialchars($tempPassword, ENT_QUOTES, 'UTF-8');
        $safeLoginUrl = htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8');
        $safeChangeUrl = htmlspecialchars($changeUrl, ENT_QUOTES, 'UTF-8');

        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width:600px; margin:0 auto;'>
                <h2 style='color:#1BA32E;'>Welcome {$safeName}!</h2>
                <p>Your account has been created. Use the temporary password below to sign in, then change it right away.</p>
                <div style='background:#f4f8f6; padding:18px; border-radius:8px; text-align:center; margin:20px 0;'>
                    <span style='font-size:28px; font-weight:bold; letter-spacing:2px;'>{$safePassword}</span>
                </div>
                <p>
                    <a href='{$safeLoginUrl}' style='display:inline-block; padding:12px 20px; background:#1BA32E; color:#fff; text-decoration:none; border-radius:6px; font-weight:bold;'>Log In</a>
                </p>
                <p>
                    After logging in, change your password:<br><br>
                    <a href='{$safeChangeUrl}' style='display:inline-block; padding:12px 20px; background:#1BA32E; color:#fff; text-decoration:none; border-radius:6px; font-weight:bold;'>Change Password</a>
                </p>
                <p style='font-size:13px; color:#777;'>If the buttons do not work, copy these links into your browser:<br>{$safeLoginUrl}<br>{$safeChangeUrl}</p>
            </div>
        ";
        $mail->AltBody = "Welcome {$name}!\n\nTemporary password: {$tempPassword}\nLog in: {$loginUrl}\nChange password: {$changeUrl}";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('sendEmployeeWelcomeEmail failed: ' . $mail->ErrorInfo);
        return false;
    }
}

$errors = [];
$form = [
    'name'     => '',
    'position' => '',
    'phone'    => '',
    'email'    => '',
    'temppass' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_emp'])) {
    $form['name']    = sanitize_input($_POST['name'] ?? '');
    $form['phone']   = sanitize_input($_POST['phone'] ?? '');
    $form['email']   = sanitize_input($_POST['email'] ?? '');
    $form['position']= sanitize_input($_POST['position'] ?? '');
    $form['temppass']= trim($_POST['temppass'] ?? '');

    $errors[] = validate_name($form['name']);
    if ($form['position'] === '') {
        $errors[] = 'Position is required.';
    }

    $errors[] = validate_phone($form['phone']);
    $errors[] = validate_email($form['email']);
    $errors = array_values(array_filter($errors));

    if ($form['temppass'] === '') {
        $errors[] = 'Temporary password is required.';
    }


    if ($errors === []) {
        require_once 'config.php';
        $conn = getDBConnection();
        require_once __DIR__ . '/hash_password.php';

        $sql = 'INSERT INTO users (name, position, phone, email, password, role) VALUES (?, ?, ?, ?, ?, ?)';
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $hashedTempPass = hash_password($form['temppass']);
            $roleValue = 'employee';
            $stmt->bind_param(
                'ssssss',
                $form['name'],
                $form['position'],
                $form['phone'],
                $form['email'],
                $hashedTempPass,
                $roleValue
            );
            
            if ($stmt->execute()) {
                $newEmployeeId = $conn->insert_id;
                $baseUrl = $_ENV['APP_URL'] ?? getenv('APP_URL');
                if (!$baseUrl) {
                    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
                    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    $baseUrl = $scheme . $host;
                }
                $baseUrl = rtrim($baseUrl, '/');
                $loginUrl  = $baseUrl . '/login.php';
                $changeUrl = $baseUrl . '/changepass.php';
                $mailSent = sendEmployeeWelcomeEmail(
                    $form['email'],
                    $form['name'],
                    $form['temppass'],
                    $loginUrl,
                    $changeUrl
                );
                if (! $mailSent) {
                    $_SESSION['flash_warning'] = 'Employee created, but the welcome email could not be sent.';
                }
                $stmt->close();
                $conn->close();
                header("Location: employeedetailtemplate.php?id={$newEmployeeId}");
                exit;
            }
            $stmt->close();
            $errors[] = 'Employee creation failed.';
        } else {
            $errors[] = 'Failed to prepare employee insert statement.';
        }
        $conn->close();
    }
}
?>
               


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>newcust</title> 
    <link rel="stylesheet" href="./CSS/neworder-cust-user.css">
</head>
<body>

    <?php load_topnav(); ?>

    <?php load_sidebar($is_admin); ?>

    <div class="main" id="main">
        <h1>Add New Employee</h1>
        <div class="container-main">
            <?php if ($errors): ?>
                <div class="form-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="add-employee.php">
                <div class="input">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($form['name']); ?>" required>
                </div>

                <div class="input">
                    <label for="position">Position:</label>
                    <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($form['position']); ?>" required>
                </div>

                <div class="input">
                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($form['phone']); ?>" required>
                </div>

                <div class="input">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($form['email']); ?>" required>
                </div>

                <div class="input">
                    <label for="temppass">Temporary Password:</label>
                    <input type="password" id="temppass" name="temppass" value="<?php echo htmlspecialchars($form['temppass']); ?>" required>
                </div>


                <div class="input">
                    <button type="submit" name="add_emp">Add</button>
                </div>
            </form>

                
                

            
        </div>
        
            


        
        
        
        

    </div>
    
    <script src="./JS/orders-customers.js"></script>
</body>
</html>
