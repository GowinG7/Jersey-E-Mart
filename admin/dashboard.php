<?php require('../shared/commonlinks.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Dashboard - Jersey E-Mart</title>
    <link rel="stylesheet" href="css/header.css">
</head>

<body style="background-color: lightgray;">

    <!-- Header + Sidebar -->
    <?php require('header.php'); ?>

    <div class="container-fluid" id="main-content">
        <div class="row">
            <div class="col-lg-10 ms-auto p-4 overflow-hidden">

                <h3 class="mb-4">Dashboard - Jersey E-Mart</h3>

                <!-- Charts Row -->
                <div class="row mb-4">

                    <!-- Bar Chart -->
                    <div class="col-lg-8 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-header">
                                <h5>Orders Overview (Weekly / Monthly / Yearly)</h5>
                            </div>
                            <div class="card-body" style="background-color: whitesmoke;">
                                <canvas id="ordersBarChart" height="120"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Pie Chart -->
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-header">
                                <h5>Order Status</h5>
                            </div>
                            <div class="card-body" style="background-color: whitesmoke;">
                                <canvas id="ordersPieChart"></canvas>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Info Section -->
                <div class="card shadow-sm border-0">
                    <div class="card-body" style="background-color: whitesmoke;">
                        <p class="mb-0">
                            This dashboard shows a visual summary of jersey orders.
                            The data displayed above is static and will be connected
                            to the database after backend implementation.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        /* BAR CHART (STATIC DATA) */
        const barCtx = document.getElementById('ordersBarChart').getContext('2d');

        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Jan', 'Feb', '2024', '2025'],
                datasets: [{
                    label: 'Number of Orders',
                    data: [12, 18, 10, 22, 60, 75, 420, 510],
                    backgroundColor: 'rgba(54, 162, 235, 0.7)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        /* PIE CHART (STATIC DATA) */
        const pieCtx = document.getElementById('ordersPieChart').getContext('2d');

        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Pending Orders', 'Delivered Orders', 'Cancelled Orders'],
                datasets: [{
                    data: [45, 120, 15],
                    backgroundColor: [
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 99, 132, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>

</body>

</html>