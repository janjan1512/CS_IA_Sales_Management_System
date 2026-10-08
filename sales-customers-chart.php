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
        <h1>Pie Chart</h1>
        
        
        <?php
            require_once 'config.php';
            $conn = getDBConnection();

            $labels = [];
            $data = [];

            $sql = "SELECT customers.name, COUNT(orders.id) AS order_count
                    FROM customers
                    LEFT JOIN orders ON customers.id = orders.customer_id
                    GROUP BY customers.id";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $labels[] = htmlspecialchars($row['name']);
                    $data[] = (int)$row['order_count'];
                }
            }

            $conn->close();
        ?>


        <div class = "pie" id="chart-container">
            <canvas id="customerPieChart" width="400" height="400"></canvas>
        </div>
        
        
        

    </div>
    <div class="buttons" id="buttons">
        <a href="./sales-customers-table.php">Table</a>
        <button id="generate-pdf">Generate PDF</button>

    </div>
    

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        const labels = <?php echo json_encode($labels); ?>;
        const data = <?php echo json_encode($data); ?>;

        const config = {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: [
                        '#1BA32E', '#d3f8d8', '#13892A', '#A3E635', '#60A5FA', '#F472B6','#FBBF24', '#F87171'
                    ],
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        };

        new Chart(
            document.getElementById('customerPieChart'),
            config
        );
    </script>
    <script src="./JS/sales.js"></script>
    
    
</body>
</html>