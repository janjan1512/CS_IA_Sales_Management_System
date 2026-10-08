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
    <link rel="stylesheet" href="./CSS/odetailtemplate copy.css">
</head>
<body>

    <?php load_topnav(); ?>

    <?php load_sidebar($is_admin); ?>

    <div class="main" id="main">
        <h1>Order Details</h1>
        <div class="container-main">
            <div class="left">
            <?php
                $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
                require_once 'config.php';
                $conn = getDBConnection();

                $orderRow = null;
                if ($id > 0) {
                    $stmt = $conn->prepare('SELECT * FROM orders WHERE id = ?');
                    $stmt->bind_param('i', $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result && $result->num_rows > 0) {
                        $orderRow = $result->fetch_assoc();
                    }
                    $stmt->close();
                }

                if ($orderRow) {
                    $customerId   = (int) $orderRow['customer_id'];
                    $status       = $orderRow['status'];
                    $product      = $orderRow['product_id'];
                    $amount       = $orderRow['amount'];
                    $orderDate    = $orderRow['order_date'];
                    $dueDate      = $orderRow['due_date'];
                    $finishDate   = $orderRow['finish_date'];
                    $price        = $orderRow['price'];
                    $paymentState = (int) $orderRow['payment_status'];
                    $link         = $orderRow['link'];

                    $address = 'Unknown';
                    $phone   = 'Unknown';
                    $email   = 'Unknown';
                    $customerStmt = $conn->prepare('SELECT address, phone, email FROM customers WHERE id = ?');
                    if ($customerStmt) {
                        $customerStmt->bind_param('i', $customerId);
                        $customerStmt->execute();
                        $customerResult = $customerStmt->get_result();
                        if ($customerResult && $customerResult->num_rows > 0) {
                            $customerDetails = $customerResult->fetch_assoc();
                            $address = $customerDetails['address'] ?? $address;
                            $phone   = $customerDetails['phone'] ?? $phone;
                            $email   = $customerDetails['email'] ?? $email;
                        }
                        $customerStmt->close();
                    }

                    if ($status === 'in-progress') {
                        $status = 'In Progress';
                    } elseif ($status === 'pending') {
                        $status = 'Pending';
                    } else {
                        $status = 'Completed';
                    }

                    $paymentLabel = $paymentState === 1 ? 'Paid' : 'Unpaid';

                    echo '
                        <div class="item">
                            <h3>ID: </h3>
                            <p>#' . str_pad($orderRow['id'], 10, '0', STR_PAD_LEFT) . '</p>
                        </div>
                        <div class="item">
                            <h3>Customer ID: </h3>
                            <p>#' . str_pad($customerId, 5, '0', STR_PAD_LEFT) . '</p>
                        </div>
                        <div class="item">
                            <h3>Item: </h3>
                            <p>' . htmlspecialchars($product) . '</p>
                        </div>
                        <div class="item">
                            <h3>Quantity: </h3>
                            <p>' . htmlspecialchars($amount) . '</p>
                        </div>
                        <div class="item">
                            <h3>Total: </h3>
                            <p>Rp. ' . number_format((float) $price, 2, '.', ',') . '</p>
                        </div>
                        <div class="item">
                            <h3>Address: </h3>
                            <p>' . htmlspecialchars($address) . '</p>
                        </div>
                        <div class="item">
                            <h3>Phone: </h3>
                            <p>' . htmlspecialchars($phone) . '</p>
                        </div>
                        <div class="item">
                            <h3>Email: </h3>
                            <p>' . htmlspecialchars($email) . '</p>
                        </div>
                        <div class="item">
                            <h3>Order Status: </h3>
                            <p>*' . $status . '*</p>
                        </div>
                        <div class="item">
                            <h3>Order Date: </h3>
                            <p>' . htmlspecialchars($orderDate) . '</p>
                        </div>
                        <div class="item">
                            <h3>Due Date: </h3>
                            <p>' . htmlspecialchars($dueDate) . '</p>
                        </div>
                        <div class="item">
                            <h3>Finish Date: </h3>
                            <p>' . htmlspecialchars($finishDate ?? '-') . '</p>
                        </div>
                        <div class="item">
                            <h3>Downpayment Status: </h3>
                            <p>*' . $paymentLabel . '*</p>
                        </div>';
                } elseif ($id <= 0) {
                    echo '<p class="state-message">Order ID is required.</p>';
                } else {
                    echo '<p class="state-message">Order not found.</p>';
                }
            ?>
                <br>
                <br>
                <br>
                
                
            </div>
            <div class="right">

                <?php
                    if ($orderRow) {
                        $safeId  = (int) $orderRow['id'];
                        $docLink = $orderRow['link'] ?? '';
                        echo '<div class="right-bot">
                                <h2>Important Documents</h2>';
                        if ($docLink) {
                            echo '<a href="' . htmlspecialchars($docLink) . '" target="_blank" rel="noopener">Link to Purchase Order Document</a>';
                        } else {
                            echo '<p>No documents linked.</p>';
                        }
                        echo '<a class="req-btn" href="./orderdetailreqchange.php?id=' . $safeId . '">Change</a>
                            </div>';
                    } elseif ($id <= 0) {
                        echo '<p class="state-message">Select an order to see its documents.</p>';
                    } else {
                        echo '<p class="state-message">No documents available.</p>';
                    }

                    $conn->close();
                ?>

                

                

                
                
            </div>    

        </div>
        
            


        
        
        
        

    </div>
    
    <script src="./JS/orders-customers.js"></script>
</body>
</html>
