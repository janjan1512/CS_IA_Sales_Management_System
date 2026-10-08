<?php
session_start();
$role = $_SESSION['user_role'] ?? '';
$is_admin = in_array(strtolower($role), ['admin'], true);
require_once __DIR__ . '/authorize.php';
require_login();
require_once __DIR__ . '/navbar.php';
require_once __DIR__ . '/validate.php';

$sortParam = sanitize_input($_GET['sort'] ?? '');
$allowedSorts = ['id_asc', 'id_desc', 'name_asc', 'name_desc'];
$sort = in_array($sortParam, $allowedSorts, true) ? $sortParam : '';
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
        <h1>Customers</h1>

        <div class="uppersection">
            <div class="filter">
                <svg id = "filter-btn" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                </svg>
                <form id = "filter" method="GET" action="">
                    <label for="sort">Sort by:</label>
                    <select name="sort" id="sort" onchange="this.form.submit()">
                        <option value="">-- Select --</option>
                        <option value="id_asc" <?php echo ($sort ?? '') === 'id_asc' ? 'selected' : ''; ?>>ID (Ascending)</option>
                        <option value="id_desc" <?php echo ($sort ?? '') === 'id_desc' ? 'selected' : ''; ?>>ID (Descending)</option>
                        <option value="name_asc" <?php echo ($sort ?? '') === 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                        <option value="name_desc" <?php echo ($sort ?? '') === 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                    </select>
                </form>
            </div>

            <?php if ($is_admin): ?>
                <a href="create-customer.php" class="btn">New Customer</a>
            <?php endif; ?>

        </div>
        <?php
            require_once 'config.php';
            $conn = getDBConnection();

            //default sort
            $orderBy = "";

            switch ($sort) {
                case 'id_asc':
                    $orderBy = "ORDER BY id ASC";
                    break;
                case 'id_desc':
                    $orderBy = "ORDER BY id DESC";
                    break;
                
                case 'name_desc':
                    $orderBy = "ORDER BY name DESC";
                    break;
                case 'name_asc':
                    $orderBy = "ORDER BY name ASC";
                    break;
            }




            $sql = "SELECT id, name, license FROM customers $orderBy"; 
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $name = htmlspecialchars($row['name']);
                    $id = htmlspecialchars($row['id']);
                    $status = htmlspecialchars($row['license']);
                    if($status == 1){
                        $status = "Active";
                    } else {
                        $status = "Inactive";
                    }

                    echo '
                    <div class="card">
                        <a class="left" href="./customerdetailtemplate.php?id=' . $id . '"><h2>' . $name . '</h2></a>
                        <p class="mid">#' . str_pad($id, 5, '0', STR_PAD_LEFT) . '</p>
                        <p class="right">*' . $status . '*</p>
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
