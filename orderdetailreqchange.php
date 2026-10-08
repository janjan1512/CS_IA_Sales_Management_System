
<?php
session_start();
$role = $_SESSION['user_role'] ?? '';
$is_admin = in_array(strtolower($role), ['admin'], true);
require_once __DIR__ . '/authorize.php';
require_login();

require_once __DIR__ . '/navbar.php';

function normalizeOrderStatus(?string $status): string
{
    $status = strtolower(trim((string) $status));
    $status = str_replace(' ', '-', $status);
    $map = [
        'pending'      => 'pending',
        'in-progress'  => 'in-progress',
        'in_progress'  => 'in-progress',
        'sent'         => 'sent',
        'completed'    => 'sent',
        'complete'     => 'sent',
    ];
    return $map[$status] ?? 'pending';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['order_id'] ?? ($_GET['id'] ?? null);
    if ($id !== null) {
        $status = normalizeOrderStatus($_POST['status'] ?? '');
        $finish_date = trim($_POST['finish_date'] ?? '')?: NULL;

        $payment_status = ($_POST['payment_status'] ?? '') === '1' ? 1 : 0;
        $employee_id = $_SESSION['user_id'];

        require_once 'config.php';
        $conn = getDBConnection();

        if ($is_admin && isset($_POST['apply_change'])) {
            $sql = "UPDATE orders SET status = ?, finish_date = ?, payment_status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssii", $status, $finish_date, $payment_status, $id);
                if ($stmt->execute()) {
                    echo "<script>alert('Order updated successfully.'); window.location.href='orderdetailtemplate.php?id=$id';</script>";
                    exit;
                }
                $stmt->close();
            }
            echo "<script>alert('Failed to update order.');</script>";
        } elseif (isset($_POST['request_change'])) {
            $sql = "INSERT INTO requests (order_id, status, finish_date, payment_status, employee_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("issii", $id, $status, $finish_date, $payment_status, $employee_id);
                if ($stmt->execute()) {
                    echo "<script>alert('Change request submitted successfully!'); window.location.href='orderdetailtemplate.php?id=$id';</script>";
                    exit;
                }
                $stmt->close();
            }
            echo "<script>alert('Update failed.');</script>";
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
    <title>orderdetailchange</title> 
    <link rel="stylesheet" href="./CSS/odetailtemplate copy.css">
</head>
<body>

    <?php load_topnav(); ?>

    <?php load_sidebar($is_admin); ?>

    <div class="main" id="main">
        <h1>Change Details</h1>
        <div class="container-main">
            <?php
                        
                        $id = $_GET['id'] ?? null;

                      
                        require_once 'config.php';
                        $conn = getDBConnection();

      
                        $sql = "SELECT * FROM orders WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        
           
                        if ($result->num_rows > 0) {

                            $row = $result->fetch_assoc();
                            $id = $row["id"];
                            $idc = $row["customer_id"];
                            $status_value = $row["status"];
                            $status_label = match ($status_value) {
                                'in-progress' => 'In Progress',
                                'sent'        => 'Completed',
                                default       => 'Pending',
                            };
                            $product = $row["product_id"];
                            $amount = $row["amount"];
                            $orderdate = $row["order_date"];
                            $duedate = $row["due_date"];
                            $finishdate = $row["finish_date"];
                            $price = $row["price"];
                            $paymentstatus_value = (int) $row["payment_status"];
                            $paymentstatus_label = $paymentstatus_value === 1 ? 'Paid' : 'Unpaid';
                            $sql2 = "SELECT address, phone, email FROM customers WHERE id = $idc"; 
                            $res2 = $conn->query($sql2);
                            $address = "Unknown";
                            $phone = "Unknown";
                            $email = "Unknown";
                        
                            if ($res2 && $res2->num_rows > 0) {
                                $row2 = $res2->fetch_assoc();
                                $address = $row2["address"];
                                $phone = $row2["phone"];
                                $email = $row2["email"];
                            }

                            ?>
                            <div class="left">
                                <div class="item">
                                    <h3>ID: </h3>
                                    <p>#<?php echo str_pad($id, 10, '0', STR_PAD_LEFT); ?></p>
                                </div>
                                <div class="item">
                                    <h3>Customer ID: </h3>
                                    <p>#<?php echo str_pad($idc, 5, '0', STR_PAD_LEFT); ?></p>
                                </div>
                        <div class="item">
                            <h3>Item: </h3>
                            <p><?php echo htmlspecialchars($product); ?></p>
                        </div>
                        <div class="item">
                            <h3>Quantity: </h3>
                            <p><?php echo htmlspecialchars($amount); ?></p>
                        </div>
                        <div class="item">
                            <h3>Total: </h3>
                            <p>Rp. <?php echo number_format($price, 2, '.', ','); ?></p>
                        </div>

                        <div class="item">
                            <h3>Address: </h3>
                            <p><?php echo htmlspecialchars($address); ?></p>
                        </div>
                        <div class="item">
                            <h3>Phone: </h3>
                            <p><?php echo htmlspecialchars($phone); ?></p>
                        </div>
                        <div class="item">
                            <h3>Email: </h3>
                            <p><?php echo htmlspecialchars($email); ?></p>
                        </div>
                    </div>
                    <div class="right">
                        <form method="POST" action="">
                            <input type="hidden" name="order_id" value="<?php echo (int) $id; ?>">
                            <div class="item">
                                <label for="status"><strong>Status:</strong></label><br>
                                <select name="status" id="status">
                                    <option value="pending" <?php echo $status_value === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="in-progress" <?php echo $status_value === 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="sent" <?php echo $status_value === 'sent' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>

                            <div class="item">
                                <h3>Order Date: </h3>
                                <p><?php echo htmlspecialchars($orderdate); ?></p>
                            </div>
                            <div class="item">
                                <h3>Due Date: </h3>
                                <p><?php echo htmlspecialchars($duedate); ?></p>
                            </div>

                            <div class="item">
                                <label for="finish_date"><strong>Finish Date:</strong></label><br>
                                <input type="date" name="finish_date" id="finish_date" value="<?php echo htmlspecialchars($finishdate); ?>">
                            </div>

                            <div class="item">
                                <label for="payment_status"><strong>Payment Status:</strong></label><br>
                                <select name="payment_status" id="payment_status">
                                    <option value="1" <?php echo $paymentstatus_value === 1 ? 'selected' : ''; ?>>Paid</option>
                                    <option value="0" <?php echo $paymentstatus_value === 0 ? 'selected' : ''; ?>>Unpaid</option>
                                </select>
                            </div>

                            <?php if ($is_admin): ?>
                                <button class="req-btn" type="submit" name="apply_change">Change</button>
                            <?php else: ?>
                                <button class="req-btn" type="submit" name="request_change">Request Change</button>
                            <?php endif; ?>
                        </form>
                    </div>
                    <?php
                        } else {
                            echo "Customer not found.";
                        }

                        
                        

                        $conn->close();
                    ?>
                
                

            
        </div>
        
            


        
        
        
        

    </div>
    
    <script src="./JS/orders-customers.js"></script>
</body>
</html>
