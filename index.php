<?php
session_start();
$role = $_SESSION['user_role'] ?? '';
$is_admin = in_array(strtolower($role), ['admin'], true);
require_once __DIR__ . '/authorize.php';
require_login();
require_once __DIR__ . '/navbar.php';
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title> 
    <link rel="stylesheet" href="./CSS/index.css">
</head>
<body>

    <?php load_topnav(); ?>

    <?php load_sidebar($is_admin); ?>

    <div class="main" id="main">
        <h2>Hello 
            <?php
 
                $user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest';
                echo htmlspecialchars($user_name);
            ?>
            !
    </h2>

        
        


    </div>

    


    
    <script src="./JS/index.js"></script>
</body>
</html>