<?php
include 'db.php'; // Include your database connection

// Fetch requested withdrawals
$query_withdrawals = "
    SELECT withdrawals.*, users.username 
    FROM `withdrawals` 
    INNER JOIN users ON withdrawals.user_id = users.id
";

$result_withdrawals = $conn->query($query_withdrawals);
$withdrawals = [];

if ($result_withdrawals) {
    while ($row = $result_withdrawals->fetch_assoc()) {
        $withdrawals[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Creator Withdrawals</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Navigation Bar -->
    <nav class="bg-white p-4 shadow-lg flex justify-between items-center">
        <div class="text-2xl font-bold text-gray-700">Pay Creator Withdrawals</div>
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

    <!-- Withdrawals Table -->
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold mb-6">Withdrawals Requests</h1>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg shadow-md">
                <thead>
                    <tr class="bg-gray-800 text-white">
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Username</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">PayPal Email</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Amount</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Requested On</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($withdrawals)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">No withdrawal requests found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($withdrawals as $withdrawal): ?>
                            <tr class="text-gray-700">
                                <td class="py-3 px-4"><?php echo htmlspecialchars($withdrawal['username']); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($withdrawal['paypal_email']); ?></td>
                                <td class="py-3 px-4">$<?php echo number_format($withdrawal['amount'], 2); ?></td>
                                <td class="py-3 px-4"><?php echo date('Y-m-d', strtotime($withdrawal['created_at'])); ?></td>
                                <td class="py-3 px-4">
                                    <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" target="_blank">
                                        <input type="hidden" name="cmd" value="_xclick">
                                        <input type="hidden" name="business" value="<?php echo htmlspecialchars($withdrawal['paypal_email']); ?>">
                                        <input type="hidden" name="item_name" value="Withdrawal to <?php echo htmlspecialchars($withdrawal['username']); ?>">
                                        <input type="hidden" name="amount" value="<?php echo htmlspecialchars($withdrawal['amount']); ?>">
                                        <input type="hidden" name="currency_code" value="USD">
                                        <input type="hidden" name="return" value="https://yourwebsite.com/return_url">
                                        <input type="hidden" name="cancel_return" value="https://yourwebsite.com/cancel_url">
                                        <button type="submit" class="bg-green-500 text-white py-1 px-4 rounded-full">Pay</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
