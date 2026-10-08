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
    <title>Weekly Sales Forecast</title>
    <link rel="stylesheet" href="./CSS/orders-customers.css">
</head>
<body>
    <?php load_topnav(); ?>
    <?php load_sidebar($is_admin); ?>

    <div class="main" id="main">
        <h1>Weekly Sales Forecast</h1>


        <div class="line-chart" id="chart-container">
            <canvas id="salesLineChart" width="800" height="400"></canvas>
            <div id="Status" style="text-align:center;margin-top:20px;padding:15px;background-color:#d3f8d8;border:1px solid #13892A;border-radius:4px;">
                Loading forecast…
            </div>
        </div>
    </div>

    <div class="buttons" id="buttons">
        <button id="generate-pdf">Generate PDF</button>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        let currentChart;

        function renderWeeklyForecast(payload) {
            const labels = payload.old_dates.concat(payload.forecast_dates);
            const old = payload.old_orders.concat(new Array(payload.forecast_dates.length).fill(null));
            const forecast = new Array(payload.old_dates.length).fill(null).concat(payload.forecast_orders);

            if (currentChart) currentChart.destroy();

            currentChart = new Chart(document.getElementById('salesLineChart'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Existing Orders',
                            data: old,
                            borderColor: '#1BA32E',
                            backgroundColor: 'rgba(27,163,46,0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.2,
                        },
                        {
                            label: 'Forecast (Next 8 Weeks)',
                            data: forecast,
                            borderColor: '#ff6b6b',
                            backgroundColor: 'rgba(255,107,107,0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.2,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Orders' }
                        },
                        x: {
                            title: { display: true, text: 'Week' }
                        }
                    },
                    plugins: {
                        legend: { display: true, position: 'top' },
                    title: { display: true, text: 'Forecast for the next 8 weeks' }
  
                    }
                }
            });
        }

        function loadForecast() {
            fetch('forecast-endpoint.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('Status').style.display = 'none';
                        renderWeeklyForecast(data);
                    } else {
                        document.getElementById('Status').textContent = 'Forecast error: ' + (data.error || 'Unable to load data.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById('Status').textContent = 'Network error while loading forecast.';
                });
        }

        document.addEventListener('DOMContentLoaded', loadForecast);
    </script>
    <script src="./JS/sales.js"></script>
</body>
</html>
