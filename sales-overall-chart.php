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
    <title>Sales Trends Chart</title> 
    <link rel="stylesheet" href="./CSS/orders-customers.css">
</head>
<body>

    <?php load_topnav(); ?>


    <?php load_sidebar($is_admin); ?>

    <div class="main" id="main">
        <h1>Sales Trends Over Time</h1>
        
        <?php
            
            $timeGrouping = isset($_GET['grouping']) ? $_GET['grouping'] : 'DAY';
        ?>
        
        <div class="controls" style="margin: 20px 0; text-align: center;">
            <label for="timeGrouping" style="margin-right: 10px; font-weight: bold;">Group by:</label>
            <select id="timeGrouping" onchange="changeTimeGrouping()" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <option value="DAY" <?php echo ($timeGrouping == 'DAY') ? 'selected' : ''; ?>>Daily</option>
                <option value="WEEK" <?php echo ($timeGrouping == 'WEEK') ? 'selected' : ''; ?>>Weekly</option>
                <option value="MONTH" <?php echo ($timeGrouping == 'MONTH') ? 'selected' : ''; ?>>Monthly</option>
            </select>
        </div>
        
        
        <?php
            require_once 'config.php';
            $conn = getDBConnection();

            $labels = [];
            $data = [];
            
            switch($timeGrouping) {
                case 'MONTH':
                    $sql = "SELECT DATE_FORMAT(order_date, '%Y-%m') as time_period, COUNT(id) AS order_count
                            FROM orders 
                            WHERE order_date IS NOT NULL
                            GROUP BY DATE_FORMAT(order_date, '%Y-%m')
                            ORDER BY DATE_FORMAT(order_date, '%Y-%m')";
                    break;
                case 'WEEK':
                    $sql = "SELECT DATE_FORMAT(order_date, '%Y-%u') as time_period, COUNT(id) AS order_count
                            FROM orders 
                            WHERE order_date IS NOT NULL
                            GROUP BY DATE_FORMAT(order_date, '%Y-%u')
                            ORDER BY DATE_FORMAT(order_date, '%Y-%u')";
                    break;
                default: // DAY
                    $sql = "SELECT DATE(order_date) as time_period, COUNT(id) AS order_count
                            FROM orders 
                            WHERE order_date IS NOT NULL
                            GROUP BY DATE(order_date)
                            ORDER BY DATE(order_date)";
            }
            
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $labels[] = $row['time_period'];
                    $data[] = (int)$row['order_count'];
                }
            }

            $conn->close();
        ?>


        <div class="line-chart" id="chart-container">
            <canvas id="salesLineChart" width="800" height="400"></canvas>
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
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Orders per Period',
                    data: data,
                    borderColor: '#1BA32E',
                    backgroundColor: 'rgba(27, 163, 46, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.2,
                    pointBackgroundColor: '#1BA32E',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Orders',
                            font: {
                                weight: 'bold',
                                size: 16
                            }
                        },
                        ticks: {
                            font: {
                                weight: 'bold'
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Time Period',
                            font: {
                                weight: 'bold',
                                size: 16
                            }
                        },
                        ticks: {
                            font: {
                                weight: 'bold'
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Sales Trends Over Time'
                    }
                }
            }
        };

        new Chart(
            document.getElementById('salesLineChart'),
            config
        );

       
        function changeTimeGrouping() {
            const timeGrouping = document.getElementById('timeGrouping').value;
           
            window.location.href = '?grouping=' + timeGrouping;
        }
    </script>
    <script src="./JS/sales.js"></script>
    
    
</body>
</html>