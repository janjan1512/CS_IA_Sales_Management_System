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
    <title>pending req</title> 
    <link rel="stylesheet" href="./CSS/orders-customers.css">
</head>
<body>

    <?php load_topnav(); ?>


    <?php load_sidebar($is_admin); ?>

    <div class="main" id="main">
        <h1>Resolved Requests</h1>


        <?php
            require_once 'config.php';
            $conn = getDBConnection();

            $sql = "SELECT order_id, employee_id FROM requests WHERE req_status = 1"; 
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $order_id = htmlspecialchars($row['order_id']);
                    $employee_id = htmlspecialchars($row['employee_id']);


                    echo '
                    <div class="card">
                        <p class="left">#' . str_pad($order_id, 5, '0', STR_PAD_LEFT) . '</p>
                        <p class="mid">#' . str_pad($employee_id, 5, '0', STR_PAD_LEFT) . '</p>
                        <a href="./requestdetailtemplate.php?id=' . $order_id . '" class="right-1" >View Details</a>
                    </div>
                    ';
                }
            } else {
                echo "No employees found.";
            }

            $conn->close();
        ?>


    
    <script src="./JS/orders-customers.js"></script>
</body>
</html>
