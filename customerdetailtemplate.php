<?php
session_start();
$role = $_SESSION['user_role'] ?? '';
$is_admin = in_array(strtolower($role), ['admin'], true);
require_once __DIR__ . '/authorize.php';
require_login();
require_once __DIR__ . '/navbar.php';
require_once __DIR__ . '/validate.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>customers</title> 
    <link rel="stylesheet" href="./CSS/cdetailtemplate.css">
</head>
<body>

    <?php load_topnav(); ?>

    <?php load_sidebar($is_admin); ?>

    

    <div class="main" id="main">
        <h1>Customer Details</h1>
        <div class="container-main">
            <div class="left">
                

                    <?php
                   
                        $customerIdRaw = sanitize_input($_GET['id'] ?? '');
                        $customer_id = ctype_digit($customerIdRaw) ? (int) $customerIdRaw : 0;

              
                        require_once 'config.php';
                        $conn = getDBConnection();

   
                        $sql = "SELECT * FROM customers WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $customer_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        
    
                        if ($result->num_rows > 0) {

                            $row = $result->fetch_assoc();
                            $status = $row["license"];
                            if($status==1){
                                $status = "Active";

                            } else {
                                $status = "Inactive";
                            }
                            echo'<div class="item">
                            <h3>Name:</h3>
                            <p>'.$row["name"].'</p>
                        </div>
                        <div class="item">
                            <h3>ID: </h3>
                            <p>#'. str_pad($row["id"], 5, '0', STR_PAD_LEFT).'</p>
                        </div>
                        <div class="item">
                            <h3>Address: </h3>
                            <p>'.$row["address"].'</p>
                        </div>
                        <div class="item">
                            <h3>Phone: </h3>
                            <p>'.$row["phone"].'</p>
                        </div>
                        <div class="item">
                            <h3>Email: </h3>
                            <p>'.$row["email"].'</p>
                        </div>
                        <div class="item">
                            <h3>License: </h3>
                            <p>'.$status.'</p>
                        </div>';
                        } else {
                            echo "Customer not found.";
                        }


                    ?>

                

                
                
            </div>
            <div class="right">

                <h2>Order History</h2>
                <?php
                    $sql = "SELECT id, order_date FROM orders WHERE customer_id = ? ORDER BY order_date DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $customer_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                ?>
                <div class="card-container">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($order = $result->fetch_assoc()): ?>
                            <div class="card">
                                <a class="left" href="./orderdetailtemplate.php?id=<?php echo (int) $order['id']; ?>">
                                    <h2>#<?php echo str_pad($order['id'], 10, '0', STR_PAD_LEFT); ?></h2>
                                </a>
                                <p class="right"><?php echo htmlspecialchars($order['order_date']); ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No orders found.</p>
                    <?php endif; ?>
                </div>
                <?php
                    $stmt->close();
                    $conn->close();
                ?>
                <?php if ($is_admin && $customer_id): ?>
                    <div class="change-action">
                        <a class="change-btn" href="./custdetailreqchange.php?id=<?php echo (int) $customer_id; ?>">Change Details</a>
                    </div>
                <?php endif; ?>
                            
            </div>    

        </div>
        
            


        
        
        
        

    </div>
    
    <script src="./JS/orders-customers.js"></script>
</body>
</html>


                    
