
<?php
session_start();
$role = $_SESSION['user_role'] ?? '';
$is_admin = in_array(strtolower($role), ['admin'], true);
require_once __DIR__ . '/authorize.php';
require_login();

require_once __DIR__ . '/navbar.php';
require_once __DIR__ . '/validate.php';

if (!$is_admin) {
    echo "<script>alert('Only administrators can modify customer details.'); window.location.href='customers.php';</script>";
    exit;
}

$errors = [];
$customerOrders = [];
$customerIdRaw = sanitize_input($_GET['id'] ?? '');
$customerId = ctype_digit($customerIdRaw) ? (int) $customerIdRaw : 0;

if ($customerId <= 0) {
    echo "<script>alert('Invalid customer ID.'); window.location.href='customers.php';</script>";
    exit;
}

require_once 'config.php';
$conn = getDBConnection();

$stmt = $conn->prepare("SELECT id, name, address, phone, email, license FROM customers WHERE id = ?");
$stmt->bind_param('i', $customerId);
$stmt->execute();
$customerResult = $stmt->get_result();
$customer = $customerResult->fetch_assoc();
$stmt->close();

if (!$customer) {
    echo "<script>alert('Customer not found.'); window.location.href='customers.php';</script>";
    exit;
}

$ordersStmt = $conn->prepare("SELECT id, order_date FROM orders WHERE customer_id = ? ORDER BY order_date DESC");
$ordersStmt->bind_param('i', $customerId);
$ordersStmt->execute();
$ordersResult = $ordersStmt->get_result();
while ($row = $ordersResult->fetch_assoc()) {
    $customerOrders[] = $row;
}
$ordersStmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_change'])) {
    $name    = sanitize_input($_POST['name'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $phone   = sanitize_input($_POST['phone'] ?? '');
    $email   = sanitize_input($_POST['email'] ?? '');
    $license = $_POST['license'] ?? '';

    $errors[] = validate_name($name);
    $errors[] = validate_address($address);
    $errors[] = validate_phone($phone);
    $errors[] = validate_email($email);
    if (!in_array($license, ['0', '1'], true)) {
        $errors[] = 'License status must be Active or Inactive.';
    }
    $errors = array_values(array_filter($errors));

    if (!$errors) {
        $sql = "UPDATE customers SET name = ?, address = ?, phone = ?, email = ?, license = ? WHERE id = ?";
        $licenseInt = (int) $license;
        $update = $conn->prepare($sql);
        if ($update) {
            $update->bind_param('ssssii', $name, $address, $phone, $email, $licenseInt, $customer['id']);
            if ($update->execute()) {
                $update->close();
                $conn->close();
                echo "<script>alert('Customer details updated successfully.'); window.location.href='customerdetailtemplate.php?id={$customer['id']}';</script>";
                exit;
            }
            $errors[] = 'Failed to update customer details.';
            $update->close();
        } else {
            $errors[] = 'Failed to prepare update statement.';
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>orderdetailchange</title> 
    <link rel="stylesheet" href="./CSS/cdetailtemplate.css">
</head>
<body>

    <?php load_topnav(); ?>

    <?php load_sidebar($is_admin); ?>

    <div class="main" id="main">
        <h1>Change Customer Details</h1>
        <div class="container-main">
            <div class="left">
                <?php if ($errors): ?>
                    <div class="form-error">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="customer_id" value="<?php echo (int) $customer['id']; ?>">
                            <div class="item">
                                <h3>ID:</h3>
                                <p>#<?php echo str_pad($customer['id'], 5, '0', STR_PAD_LEFT); ?></p>
                            </div>
                            <div class="item">
                                <label for="name"><strong>Name:</strong></label><br>
                                <input class="detail-input" type="text" id="name" name="name" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
                            </div>
                            <div class="item">
                                <label for="address"><strong>Address:</strong></label><br>
                                <input class="detail-input" type="text" id="address" name="address" value="<?php echo htmlspecialchars($customer['address']); ?>" required>
                            </div>
                            <div class="item">
                                <label for="phone"><strong>Phone:</strong></label><br>
                                <input class="detail-input" type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>" required>
                            </div>
                            <div class="item">
                                <label for="email"><strong>Email:</strong></label><br>
                                <input class="detail-input" type="email" id="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
                            </div>
                            <div class="item">
                                <label for="license"><strong>License Status:</strong></label><br>
                                <select class="detail-input" id="license" name="license" required>
                                    <option value="1" <?php echo $customer['license'] === '1' ? 'selected' : ''; ?>>Active</option>
                                    <option value="0" <?php echo $customer['license'] === '0' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <button class="req-btn" type="submit" name="apply_change">Save Changes</button>
                        </form>
                    </div>

                    <div class="right">
                <h2>Order History</h2>
                <div class="card-container">
                    <?php if ($customerOrders): ?>
                        <?php foreach ($customerOrders as $order): ?>
                            <div class="card">
                                <a class="left" href="./orderdetailtemplate.php?id=<?php echo (int) $order['id']; ?>">
                                    <h2>#<?php echo str_pad($order['id'], 10, '0', STR_PAD_LEFT); ?></h2>
                                </a>
                                <p class="right"><?php echo htmlspecialchars($order['order_date']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No orders found for this customer.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="./JS/orders-customers.js"></script>
</body>
</html>
