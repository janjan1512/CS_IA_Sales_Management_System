
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
    'name'    => '',
    'address'     => '',
    'phone'       => '',
    'email'      => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cust'])) {
    $form['name']    = sanitize_input($_POST['name'] ?? '');
    $form['address'] = sanitize_input($_POST['address'] ?? '');
    $form['phone']   = sanitize_input($_POST['phone'] ?? '');
    $form['email']   = sanitize_input($_POST['email'] ?? '');

    $errors[] = validate_name($form['name']);
    $errors[] = validate_address($form['address']);
    $errors[] = validate_phone($form['phone']);
    $errors[] = validate_email($form['email']);
    $errors = array_values(array_filter($errors));


    if ($errors === []) {
        require_once 'config.php';
        $conn = getDBConnection();
        $sql = 'INSERT INTO customers (name, address, phone, email, license) VALUES (?, ?, ?, ?, 1)';
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param(
                'ssss',
                $form['name'],
                $form['address'],
                $form['phone'],
                $form['email']
            );
            if ($stmt->execute()) {
                $newCustomerId = $conn->insert_id;
                $stmt->close();
                $conn->close();
                header("Location: customerdetailtemplate.php?id={$newCustomerId}");
                exit;
            }
            $stmt->close();
            $errors[] = 'Customer creation failed.';
        } else {
            $errors[] = 'Failed to prepare customer insert statement.';
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
    <title>newcust</title> 
    <link rel="stylesheet" href="./CSS/neworder-cust-user.css">
</head>
<body>

    <?php load_topnav(); ?>

    <?php load_sidebar($is_admin); ?>

    <div class="main" id="main">
        <h1>Add New Customer</h1>
        <div class="container-main">
            <?php if ($errors): ?>
                <div class="form-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="create-customer.php">
                <div class="input">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($form['name']); ?>" required>
                </div>

                <div class="input">
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($form['address']); ?>" required>
                </div>

                <div class="input">
                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($form['phone']); ?>" required>
                </div>

                <div class="input">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($form['email']); ?>" required>
                </div>


                <div class="input">
                    <button type="submit" name="add_cust">Add</button>
                </div>
            </form>

                
                

            
        </div>
        
            


        
        
        
        

    </div>
    
    <script src="./JS/orders-customers.js"></script>
</body>
</html>
