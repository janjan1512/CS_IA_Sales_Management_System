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
    <title>profile</title> 
    <link rel="stylesheet" href="./CSS/cdetailtemplate.css">
</head>
<body>

    <?php load_topnav(); ?>

    <?php load_sidebar($is_admin); ?>

    <div class="main" id="main">
        <h1>Account Details</h1>
        <div class="container-main">
            <div class="left">
                

                    <?php
               
                        $id = $_SESSION['user_id'] ?? null;

              
                        require_once 'config.php';
                        $conn = getDBConnection();

                       
                        $sql = "SELECT * FROM users WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        
                  
                        if ($result->num_rows > 0) {

                            $row = $result->fetch_assoc();
                            $email = $row["email"];
                            $name = $row["name"];
                            $id = $row["id"];
                            $position = $row["position"];

                            echo'<div class="item">
                            <h3>Name:</h3>
                            <p>'.$name.'</p>
                        </div>
                        <div class="item">
                            <h3>ID: </h3>
                            <p>#'. str_pad($id, 5, '0', STR_PAD_LEFT).'</p>
                        </div>
                        <div class="item">
                            <h3>Position: </h3>
                            <p>'.$position.'</p>
                        </div>
                        <div class="item">
                            <h3>Email: </h3>
                            <p>'.$email.'</p>
                        </div>

                        <div class="item" style="margin-top: 30px;">
                            <a href="./changepass.php" class="change-pass-btn" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; margin-right: 10px;">
                                Change Password
                            </a>
                            <a href="./logout.php" class="logout-btn" style="background-color: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                                Logout
                            </a>
                        </div>
                        
                        ';
                        } else {
                            echo "Customer not found.";
                        }


                    ?>

                

                
                
            </div>
        </div>
            


        
        
        
        

    </div>
    
    <script src="./JS/orders-customers.js"></script>
</body>
</html>


                    