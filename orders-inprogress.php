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
    <title>in progress</title> 
    <link rel="stylesheet" href="./CSS/orders-customers.css">
</head>
<body>

    <?php load_topnav(); ?>

    <?php load_sidebar($is_admin); ?>

    <div class="main" id="main">
        <h1>In Progress Orders</h1>
        <div class="uppersection">
            <div class="filter">
                <svg id = "filter-btn" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                </svg>
                <form id = "filter" method="GET" action="">
                    <label for="sort">Sort by:</label>
                    <select name="sort" id="sort" onchange="this.form.submit()">
                        <option value="">-- Select --</option>
                        <option value="id_asc">ID (Ascending)</option>
                        <option value="id_desc">ID (Descending)</option>
                        <option value="date_newest">Newest</option>
                        <option value="date_oldest">Oldest</option>
                    </select>
                </form>
            </div>
            <a href="create-order.php" class="btn">New Order</a>
        </div>
        <?php
            require_once 'config.php';
            $conn = getDBConnection();
            $sort = isset($_GET['sort']) ? $_GET['sort'] : '';

            //default sort
            $orderBy = "";

            switch ($sort) {
                case 'id_asc':
                    $orderBy = "ORDER BY id ASC";
                    break;
                case 'id_desc':
                    $orderBy = "ORDER BY id DESC";
                    break;
                
                case 'date_newest':
                    $orderBy = "ORDER BY order_date DESC";
                    break;
                case 'date_oldest':
                    $orderBy = "ORDER BY order_date ASC";
                    break;
            }


            $sql = "SELECT id, customer_id, order_date FROM orders WHERE status = 'in-progress' $orderBy";
            
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $idc = htmlspecialchars($row['customer_id']);
                    $id = htmlspecialchars($row['id']);
                    $date = htmlspecialchars($row['order_date']);
                    $sql2 = "SELECT name FROM customers WHERE id = $idc"; 
                    $res2 = $conn->query($sql2);
                    $custName = "Unknown";
                
                    if ($res2 && $res2->num_rows > 0) {
                        $row2 = $res2->fetch_assoc();
                        $custName = htmlspecialchars($row2['name']);
                    }

                    echo '
                    <div class="card">
                        <a class="left" href="./orderdetailtemplate.php?id='.$id.'"><h2>#'.str_pad($id, 10, '0', STR_PAD_LEFT).'</h2></a>
                        <p class="mid">'.$custName.'</p>
                        <h2 class="right">'.$date.'</h2>
                    </div>
                    ';
                }
            } else {
                echo "No customers found.";
            }

            $conn->close();
        ?>
        
        

    </div>
    
    <script src="./JS/orders-customers.js"></script>
</body>
</html>