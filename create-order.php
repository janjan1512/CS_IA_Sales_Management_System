<?php
session_start();

$role = $_SESSION['user_role'] ?? '';
$is_admin = in_array(strtolower($role), ['admin'], true);
require_once __DIR__ . '/authorize.php';
require_login();

require_once __DIR__ . '/navbar.php';
require_once __DIR__ . '/validate.php';

$errors = [];
$form = [
    'customer_id'    => '',
    'order_date'     => '',
    'due_date'       => '',
    'finish_date'    => '',
    'payment_status' => '',
    'product_id'     => '',
    'amount'         => '',
    'link'           => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_order'])) {
    $form['customer_id']    = sanitize_input($_POST['customer_id'] ?? '');
    $form['order_date']     = $_POST['order_date'] ?? '';
    $form['due_date']       = $_POST['due_date'] ?? '';
    $form['finish_date']    = $_POST['finish_date'] ?? '';
    $form['payment_status'] = $_POST['payment_status'] ?? '';
    $form['product_id']     = sanitize_input($_POST['product_id'] ?? '');
    $form['amount']         = sanitize_input($_POST['amount'] ?? '');
    $form['link']           = trim($_POST['link'] ?? '');

    $errors[] = validate_customer_id($form['customer_id']);
    $errors[] = validate_product_id($form['product_id']);
    $errors[] = validate_quantity($form['amount']);
    $errors = array_values(array_filter($errors));

    $paymentStatus = $form['payment_status'];
    if ($paymentStatus === '' || !in_array($paymentStatus, ['0', '1'], true)) {
        $errors[] = 'Select a valid down payment status.';
    }

    if ($errors === []) {
        require_once 'config.php';
        $conn = getDBConnection();
        $customerId = (int) $form['customer_id'];
        $customerStmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM customers WHERE id = ?');
        $customerStmt->bind_param('i', $customerId);
        $customerStmt->execute();
        $customerStmt->bind_result($customerCount);
        $customerStmt->fetch();

        if ((int) $customerCount === 0) {
            $errors[] = 'Customer not found: ' . $customerId;
        }

        $customerStmt->close();

        if (!$errors) {

        

            $productId = (int) $form['product_id'];
            $priceStmt = $conn->prepare('SELECT price, stock FROM products WHERE id = ?');

            if ($priceStmt) {
                $priceStmt->bind_param('i', $productId);
                $priceStmt->execute();
                $priceResult = $priceStmt->get_result();
                $productRow  = $priceResult->fetch_assoc();

                if (!$productRow) {
                    $errors[] = 'Product not found.';
                } elseif ((int) $productRow['stock'] < (int) $form['amount']) {
                    $errors[] = 'Insufficient stock for the requested product.';
                } else {
                    $unitPrice  = (float) ($productRow['price'] ?? 0);
                    $quantity   = (int) $form['amount'];
                    $totalPrice = $unitPrice * $quantity;

                    $customerId   = (int) $form['customer_id'];
                    $orderDate    = $form['order_date'];
                    $dueDate      = $form['due_date'];
                    $finishDate   = $form['finish_date'] === '' ? null : $form['finish_date'];
                    $paymentValue = (int) $form['payment_status'];
                    $link         = $form['link'] === '' ? null : $form['link'];

                    $conn->begin_transaction();

                    $sql = 'INSERT INTO orders
                            (customer_id, status, order_date, due_date, finish_date,
                            payment_status, product_id, amount, link, price)
                            VALUES (?, "pending", ?, ?, ?, ?, ?, ?, ?, ?)';
                    $stmt = $conn->prepare($sql);

                    if (!$stmt) {
                        $errors[] = 'Failed to prepare order insert statement.';
                    } else {
                        $stmt->bind_param(
                            'isssiiisd',
                            $customerId,
                            $orderDate,
                            $dueDate,
                            $finishDate,
                            $paymentValue,
                            $productId,
                            $quantity,
                            $link,
                            $totalPrice
                        );

                        if (!$stmt->execute()) {
                            $errors[] = 'Order creation failed.';
                            $conn->rollback();
                        } else {
                            $newOrderId = $conn->insert_id;
                            $updateStock = $conn->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
                            if (!$updateStock) {
                                $errors[] = 'Failed to prepare stock update statement.';
                                $conn->rollback();
                            } else {
                                $updateStock->bind_param('ii', $quantity, $productId);
                                if (!$updateStock->execute()) {
                                    $errors[] = 'Failed to update product stock.';
                                    $conn->rollback();
                                } else {
                                    $conn->commit();
                                    $stmt->close();
                                    $priceStmt->close();
                                    $conn->close();

                                    header("Location: orderdetailtemplate.php?id={$newOrderId}");
                                    exit;
                                }
                                $updateStock->close();
                            }
                        }
                        $stmt->close();
                    }
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>createorder</title> 
    <link rel="stylesheet" href="./CSS/neworder-cust-user.css">
</head>
<body>

    <?php load_topnav(); ?>

    <?php load_sidebar($is_admin); ?>

    <div class="main" id="main">
        <h1>Add New Order</h1>
        <div class="container-main">
            <?php if ($errors): ?>
                <div class="form-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="create-order.php">
                <div class="input">
                    <label for="customer_id">Customer ID:</label>
                    <input type="text" id="customer_id" name="customer_id" value="<?php echo htmlspecialchars($form['customer_id']); ?>" required>
                </div>

                <div class="input">
                    <label for="order_date">Order Date:</label>
                    <input type="date" id="order_date" name="order_date" value="<?php echo htmlspecialchars($form['order_date']); ?>" required>
                </div>

                <div class="input">
                    <label for="due_date">Due Date:</label>
                    <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($form['due_date']); ?>" required>
                </div>

                <div class="input">
                    <label for="finish_date">Finish Date:</label>
                    <input type="date" id="finish_date" name="finish_date" value="<?php echo htmlspecialchars($form['finish_date']); ?>">
                </div>

                <div class="input">
                    <label for="payment_status">Down Payment status:</label>
                    <select id="payment_status" name="payment_status" required>
                        <option value="">Select</option>
                        <option value="1" <?php echo $form['payment_status'] === '1' ? 'selected' : ''; ?>>Paid</option>
                        <option value="0" <?php echo $form['payment_status'] === '0' ? 'selected' : ''; ?>>Unpaid</option>
                    </select>
                </div>

                <div class="input">
                    <label for="product_id">Product ID:</label>
                    <input type="text" id="product_id" name="product_id" value="<?php echo htmlspecialchars($form['product_id']); ?>" required>
                </div>

                <div class="input">
                    <label for="amount">Quantity of Product:</label>
                    <input type="text" id="amount" name="amount" value="<?php echo htmlspecialchars($form['amount']); ?>" required>
                </div>

                <div class="input">
                    <label for="link">Link to document (if exists):</label>
                    <input type="text" id="link" name="link" value="<?php echo htmlspecialchars($form['link']); ?>">
                </div>

                <div class="input">
                    <button type="submit" name="add_order">Add</button>
                </div>
            </form>

                
                

            
        </div>
        
            


        
        
        
        

    </div>
    
    <script src="./JS/orders-customers.js"></script>
</body>
</html>
