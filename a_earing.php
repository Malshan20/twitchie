<?php
include 'db.php'; // Include your database connection

// Fetch creators and their earnings
$query_earnings = "
    SELECT users.username, SUM(earnings.amount) AS total_earnings 
    FROM users 
    INNER JOIN earnings ON users.id = earnings.user_id 
    WHERE users.role = 'creator' 
    GROUP BY users.username 
    ORDER BY total_earnings DESC ";
$result_earnings = $conn->query($query_earnings);

// Fetch overall earnings
$query_total_earnings = "SELECT SUM(amount) as total FROM earnings";
$result_total_earnings = $conn->query($query_total_earnings);
$total_earnings = $result_total_earnings->fetch_assoc()['total'];

// Prepare data for Chart.js
$usernames = [];
$earnings = [];

while ($row = $result_earnings->fetch_assoc()) {
    $usernames[] = $row['username'];
    $earnings[] = $row['total_earnings'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creator Earnings</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gray-100">

    <!-- Navigation Bar -->
    <nav class="bg-white p-4 flex justify-between items-center">
        <div class="text-2xl font-bold text-gray-700">Creators Earnings Dashboard</div>
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
    <!-- Header -->
    <div class="container mx-auto p-6 text-center">
        <p class="mt-2 text-lg text-gray-600">A complete overview of creators' earnings and overall platform earnings.</p>
    </div>

    <!-- Earnings Table -->
    <div class="container mx-auto p-8">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Creators Earnings</h2>
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead>
                    <tr class="bg-gray-800 text-white">
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Creator</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Total Earnings</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usernames as $index => $username) { ?>
                        <tr class="border-t border-gray-200 text-gray-700">
                            <td class="py-3 px-4"><?php echo $username; ?></td>
                            <td class="py-3 px-4">$<?php echo number_format($earnings[$index], 2); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Total Earnings Summary -->
    <div class="container mx-auto p-8">
        <div class="bg-white shadow-md rounded-lg p-6 text-center">
            <h2 class="text-2xl font-bold text-gray-700">Total Earnings: $<?php echo number_format($total_earnings, 2); ?></h2>
        </div>
    </div>

    <!-- Chart for Creator Earnings -->
    <div class="container mx-auto p-8">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Earnings Chart</h2>
            <canvas id="earningsChart"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('earningsChart').getContext('2d');
        const earningsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($usernames); ?>,
                datasets: [{
                    label: 'Earnings ($)',
                    data: <?php echo json_encode($earnings); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

</body>

</html>