<?php
session_start();
$role = $_SESSION['user_role'] ?? '';
$is_admin = in_array(strtolower($role), ['admin'], true);
require_once __DIR__ . '/authorize.php';
require_login();
require_once __DIR__ . '/navbar.php';

function normalize_requested_status(?string $status): string
{
    $status = strtolower(trim((string) $status));
    $status = str_replace(' ', '-', $status);
    $map = [
        'pending'      => 'pending',
        'in-progress'  => 'in-progress',
        'in_progress'  => 'in-progress',
        'sent'         => 'in-progress',
        'completed'    => 'sent',
        'complete'     => 'sent',
        'finished'     => 'sent',
    ];
    return $map[$status] ?? 'pending';
}

function format_status_label(?string $status): string
{
    switch ($status) {
        case 'in-progress':
            return 'In Progress';
        case 'sent':
            return 'Completed';
        case 'pending':
        default:
            return 'Pending';
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_change'])) {
    $order_id = $_POST['order_id'] ?? null;
    $requested_status = normalize_requested_status($_POST['status'] ?? '');
    $requested_f_date_raw = $_POST['finish_date'] ?? null;
    $requested_f_date = ($requested_f_date_raw === '' || $requested_f_date_raw === null)
        ? null
        : date('Y-m-d', strtotime($requested_f_date_raw));
    $requested_p_status = $_POST['payment_status'] ?? '';
    
    if ($order_id) {
        require_once 'config.php';
        $conn = getDBConnection();
        

        $sql1 = 'UPDATE orders SET status = ?, finish_date = ?, payment_status = ? WHERE id = ?';
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("sssi", $requested_status, $requested_f_date, $requested_p_status, $order_id);
        

        $sql2 = 'UPDATE requests SET req_status = 1 WHERE order_id = ?';
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("i", $order_id);
        
        if ($stmt1->execute() && $stmt2->execute()) {
            echo "<script>alert('Employee request approved! Order changes have been applied successfully.'); window.location.href='orderdetailtemplate.php?id=$order_id';</script>";
            exit;
        } else {
            echo "<script>alert('Failed to approve request and apply changes.');</script>";
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
    <title>Review Change Request</title> 
    <link rel="stylesheet" href="./CSS/odetailtemplate copy.css">
</head>
<body>

    <?php load_topnav(); ?>

    <?php load_sidebar($is_admin); ?>

    <div class="main" id="main">
        <h1>Review Change Request</h1>
        <div class="container-main">
            <?php
    
                        $id = $_GET['id'] ?? null;
                        

                        if (empty($id) || !is_numeric($id)) {
                            echo "<h2>Invalid Request</h2>";
                            echo "<p>No valid Order ID provided in the URL.</p>";
                            echo "<p>Please access this page with a valid order ID: requestdetailtemplate.php?id=[order_id]</p>";
                            echo "<p><a href='pending.php'>← Back to Pending Requests</a></p>";
                            exit;
                        }


                        require_once 'config.php';
                        $conn = getDBConnection();
                        
                       
                        $sql = "SELECT order_id, employee_id, status, finish_date, payment_status, req_status FROM requests WHERE order_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
         
                        if ($result->num_rows > 0) {

                            $request_row = $result->fetch_assoc();
                            $order_id = $request_row["order_id"];
                            $employee_id = $request_row["employee_id"];
                            $requested_status_raw = $request_row["status"];
                            $requested_status = normalize_requested_status($requested_status_raw);
                            $requested_f_date_raw = $request_row["finish_date"];
                            $requested_f_date = ($requested_f_date_raw === '' || $requested_f_date_raw === null)
                                ? null
                                : date('Y-m-d', strtotime($requested_f_date_raw));
                            $requested_p_status = $request_row["payment_status"];
                            $req_status = $request_row["req_status"];
                            
        
                            $current_sql = "SELECT status, finish_date, payment_status FROM orders WHERE id = ?";
                            $current_stmt = $conn->prepare($current_sql);
                            $current_stmt->bind_param("i", $order_id);
                            $current_stmt->execute();
                            $current_result = $current_stmt->get_result();
                            $current_order = $current_result->fetch_assoc();

         
                            $sql2 = "SELECT name FROM users WHERE id = ?"; 
                            $stmt2 = $conn->prepare($sql2);
                            $stmt2->bind_param("i", $employee_id);
                            $stmt2->execute();
                            $res2 = $stmt2->get_result();
                            $row2 = $res2->fetch_assoc();
                            $employee_name = htmlspecialchars($row2['name'] ?? 'Unknown');
                  
                            echo '<form method="POST" action="">';
                            echo '<input type="hidden" name="order_id" value="'.$order_id.'">';
                            echo '<input type="hidden" name="status" value="'.$requested_status.'">';
                            echo '<input type="hidden" name="finish_date" value="'.$requested_f_date.'">';
                            echo '<input type="hidden" name="payment_status" value="'.$requested_p_status.'">';
                            
                            echo'<div class="item">
                            <h3>Order ID: </h3>
                            <p>#'.str_pad($order_id, 10, '0', STR_PAD_LEFT).'</p>
                        </div>
                        <div class="item">
                            <h3>Employee ID: </h3>
                            <p>#'.str_pad($employee_id, 5, '0', STR_PAD_LEFT).'</p>
                        </div>
                        <div class="item">
                            <h3>Employee Name: </h3>
                            <p>'.$employee_name.'</p>
                        </div>';
                        

                        echo '<h2 style="margin-top: 30px; color: #333;">Requested Changes</h2>';

                        echo '<div class="item">
                            <h3>Status Change: </h3>
                            <p><span style="color: #666;">Current:</span> '.format_status_label($current_order['status']).' → <span style="color: #2196F3;">Requested:</span> '.format_status_label($requested_status).'</p>
                        </div>';
                        

                        echo '<div class="item">
                            <h3>Finish Date Change: </h3>
                            <p><span style="color: #666;">Current:</span> '.($current_order['finish_date'] ?: 'Not set').' → <span style="color: #2196F3;">Requested:</span> '.($requested_f_date ?: 'Not set').'</p>
                        </div>';


                        $current_payment = $current_order['payment_status'] ? 'Paid' : 'Unpaid';
                        $requested_payment = $requested_p_status ? 'Paid' : 'Unpaid';
                        echo '<div class="item">
                            <h3>Payment Status Change: </h3>
                            <p><span style="color: #666;">Current:</span> '.$current_payment.' → <span style="color: #2196F3;">Requested:</span> '.$requested_payment.'</p>
                        </div>';
                        
                        echo '<div class="item">
                            <h3>Request Status: </h3>
                            <p>'.($req_status ? 'Approved' : 'Pending').'</p>
                        </div>';
                        

                        if (!$req_status) {
                            echo '<button type="submit" name="confirm_change" class="req-btn">Approve Changes</button>';
                        } else {
                            echo '<p style="color: green; font-weight: bold;">This request has already been approved and changes have been applied.</p>';
                        }
                        
                        echo '</form>';
                        } else {
                            echo "<h2>Request Not Found</h2>";
                            echo "<p>No change request found for Order ID: #".str_pad($id, 10, '0', STR_PAD_LEFT)."</p>";
                            echo "<p><a href='pending.php'>← Back to Pending Requests</a></p>";
                        }

                        $conn->close();
                    ?>
        </div>
    </div>
    
    <script src="./JS/orders-customers.js"></script>
</body>
</html>
