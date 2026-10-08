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
    <title>customers</title> 
    <link rel="stylesheet" href="./CSS/orders-customers.css">
</head>
<body>

    <?php load_topnav(); ?>


    <?php load_sidebar($is_admin); ?>

    <div class="main" id="main">
        <h1>Overall Sales</h1>
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
                        <option value="date_asc">Order Date (Ascending)</option>
                        <option value="date_desc">Order Date (Descending)</option>
                    </select>
                </form>
            </div>
            
        </div>
        
        <?php
            require_once 'config.php';
            $conn = getDBConnection();


            $sort = isset($_GET['sort']) ? $_GET['sort'] : '';

            //default sort
            $orderBy = "";

            switch ($sort) {
                case 'date_asc':
                    $orderBy = "ORDER BY order_date ASC";
                    break;
                case 'date_desc':
                    $orderBy = "ORDER BY order_date DESC";
                    break;
                case 'id_asc':
                    $orderBy = "ORDER BY id ASC";
                    break;
                case 'id_desc':
                    $orderBy = "ORDER BY id DESC";
                    break;
            }

            //get number of orders per customer

            $sql0 = "SELECT order_date, customer_id, id FROM orders $orderBy";
            $result0 = $conn->query($sql0);

            

            // Display table
            echo '
            <div class="customers-table-container" id="table-container">
                <table class="customers-table">
                <tr>
                    <th>Order Date</th>
                    <th>Order ID</th>
                    <th>Customer ID</th>
                </tr>
            </div>';


            while ($row = $result0->fetch_assoc()) {
                // Now fetch customer name
                
                

                $id = $row['id'];
                $date = $row['order_date'];
                $customerId = $row['customer_id'];

                echo '
                <tr>
                    <td>' . htmlspecialchars($date) . '</td>
                    <td><a href="./orderdetailtemplate.php?id=' . $id . '">' . str_pad($id, 10, '0', STR_PAD_LEFT) . '</a></td>
                    <td><a href="./customerdetailtemplate.php?id=' . $customerId . '">' . str_pad($customerId, 5, '0', STR_PAD_LEFT) . '</a></td>
                    
                </tr>
                ';
            }
            

            echo '</table>';





            

            $conn->close();
        ?>

        

    </div>
    <div class="buttons" id="buttons">
        <a href="./sales-overall-chart.php">Chart</a>
        <button id="generate-pdf">Generate PDF</button>

    </div>
    
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="./JS/sales.js"></script>
</body>
</html>