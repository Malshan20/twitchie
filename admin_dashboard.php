<?php
include 'db.php'; // Include your database connection

// Fetch total balance from earnings table
$query_total_balance = "SELECT SUM(amount) as total_balance FROM earnings";
$result_balance = $conn->query($query_total_balance);
$row_balance = $result_balance->fetch_assoc();
$total_balance = $row_balance['total_balance'];

// Fetch user count by month from the users table
$query_user_count = "
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS user_count 
    FROM users 
    GROUP BY month 
    ORDER BY month ASC
";
$result_user_count = $conn->query($query_user_count);

$months = [];
$user_counts = [];

while($row = $result_user_count->fetch_assoc()) {
    $months[] = $row['month'];
    $user_counts[] = $row['user_count'];
}

// Fetch top creators by earnings
$query_top_creators = "
    SELECT users.username, SUM(earnings.amount) AS total_earnings 
    FROM users 
    INNER JOIN earnings ON users.id = earnings.user_id 
    WHERE users.role = 'creator' 
    GROUP BY users.username 
    ORDER BY total_earnings DESC 
    LIMIT 5
";
$result_top_creators = $conn->query($query_top_creators);

$creators = [];
$earnings = [];

while($row = $result_top_creators->fetch_assoc()) {
    $creators[] = $row['username'];
    $earnings[] = $row['total_earnings'];
}

// Fetch withdrawals data
$query_withdrawals = "
    SELECT withdrawals.*, users.username 
    FROM `withdrawals` 
    INNER JOIN users ON withdrawals.user_id = users.id
";
$result_withdrawals = $conn->query($query_withdrawals);

// If no earnings, default balance is 0
if (!$total_balance) {
    $total_balance = 0;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
    <style>
        /* Additional styles for better visual effects */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .stats-box {
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
        }
        .rounded-card {
            border-radius: 16px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation Bar -->
        <nav class="bg-white p-4 shadow-lg flex justify-between items-center">
            <div class="text-2xl font-bold text-gray-700">Admin Dashboard</div>
            <div>
                <ul class="flex space-x-4">
                    <li><a href="admin_dashboard.php" class="text-gray-600 hover:text-black">Home</a></li>
                    <li><a href="a_creators.php" class="text-gray-600 hover:text-black">Creators</a></li>
                    <li><a href="a_fans.php" class="text-gray-600 hover:text-black">Fans</a></li>
                    <li><a href="a_earing.php" class="text-gray-600 hover:text-black">Earnings</a></li>
                    <li><a href="a_contact.php" class="text-gray-600 hover:text-black">Contact Us</a></li>
                    <li><a href="logout_admin.php" class="text-red-600 hover:text-black">Log Out</a></li>
                </ul>
            </div>
        </nav>

        <!-- Main Dashboard Content -->
        <div class="flex-1 p-8 bg-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <!-- Overall Balance Card -->
                <div class="gradient-bg p-6 rounded-card text-white">
                    <h3 class="text-lg font-bold">Overall Balance</h3>
                    <p class="text-3xl font-bold mt-4">$<?php echo number_format($total_balance, 2); ?></p>
                    <div class="mt-4">
                        <button class="bg-white text-black px-4 py-2 rounded-full"><a href="send.php">Send</a></button>
                    </div>
                </div>

                <!-- Profit & Shares Card -->
                <div class="stats-box p-6 rounded-card text-white">
                    <h3 class="text-lg font-bold">User Growth</h3>
                    <canvas id="usersChart" class="mt-4"></canvas>
                </div>

                <!-- Top Creators by Earnings Card -->
                <div class="stats-box p-6 rounded-card text-white">
                    <h3 class="text-lg font-bold">Top Creators by Earnings</h3>
                    <canvas id="creatorsChart" class="mt-4"></canvas>
                </div>
            </div>

            <!-- Withdrawals Section -->
            <div class="mt-10">
                <h3 class="text-2xl font-bold mb-4">Withdrawal Requests</h3>
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">Creator's Name</th>
                            <th class="py-2 px-4 border-b">Email</th>
                            <th class="py-2 px-4 border-b">Withdraw Amount</th>
                            <th class="py-2 px-4 border-b">Status</th>
                            <th class="py-2 px-4 border-b">Date Requested</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_withdrawals->fetch_assoc()) { ?>
                        <tr>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['paypal_email']); ?></td>
                            <td class="py-2 px-4 border-b">$<?php echo number_format($row['amount'], 2); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['status']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js for chart rendering -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // User Growth Chart
        const ctxUsers = document.getElementById('usersChart').getContext('2d');
        const usersChart = new Chart(ctxUsers, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>, // Months from DB
                datasets: [{
                    label: 'Users Count',
                    data: <?php echo json_encode($user_counts); ?>, // User counts from DB
                    borderColor: '#764ba2',
                    backgroundColor: 'rgba(118, 75, 162, 0.2)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Top Creators by Earnings Chart
        const ctxCreators = document.getElementById('creatorsChart').getContext('2d');
        const creatorsChart = new Chart(ctxCreators, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($creators); ?>, // Creators usernames
                datasets: [{
                    label: 'Earnings ($)',
                    data: <?php echo json_encode($earnings); ?>, // Earnings per creator
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>
