
<?php
session_start();

// initialize variables to store email and error message
$email = '';
$error_message = '';

// handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // get email
    $email = trim($_POST['email'] ?? '');
    // get password
    $password = $_POST['password'] ?? '';
    
    // checks for empty fields
    if (!empty($email) && !empty($password)) {

        // connects to config and password hashing files for their functions
        require_once 'config.php';
        require_once 'hash_password.php';

        // connect to database
        $conn = getDBConnection();

        // SQL statement to fetch user by their email
        $sql = "SELECT id, name, email, password, role, position FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);

        // error handling for SQL statement 
        if ($stmt === false) {
            error_log("Failed: " . $conn->error);
            $error_message = "There's an error.";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            // see if user exists and verify password
            if ($result && $user = $result->fetch_assoc()) {
                if (verify_password_hash($password, $user['password'])) {

                    // sets session for login once password and user match and exist
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'] ?? '';
                    $_SESSION['user_position'] = $user['position'] ?? '';

                    session_regenerate_id(true);
                    error_log("login OK: session_id=" . session_id() . " session=" . json_encode($_SESSION));

                    $stmt->close();
                    $conn->close();


                    header('Location: index.php');
                    exit;
                } else {
                    $error_message = "Invalid password";
                }
            } else {
                $error_message = "User does not exist.";
            }

            $stmt->close();
        }

        $conn->close();
    } else {
        $error_message = "Please enter the correct email and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="./CSS/login.css">
</head>
<body>

    

    
    <div class="login-container">
        <div class="logo">
            <img src="./logo.png" alt="logo"/>
        </div>

        
        <?php if (!empty(trim($error_message ?? ''))): ?>
            <div class="error-msg">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="post">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Login</button>
            <a href="forgot-password.php">Forgot Password</a>
        </form>
    </div>
    <script src="./JS/login.js"></script>

    
</body>
</html>