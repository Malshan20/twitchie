<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';

// Premium video details
$premium_video_title = "Premium Video Access";
$premium_video_price = 5.00;  // The price for premium videos

// PayPal client ID
$paypalClientID = "AXMh5RGo61jXruCVBBnIqJHSRKZ1OYtPCZm5YOpBDeWngJSo1aj6DsbErckJxQIhIkVG3cToEYBUEiS2";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Video Access</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
    <style>
        .premium-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }
    </style>
</head>

<body class="bg-gray-900 text-gray-200">
    <!-- Navigation Bar -->
    <nav class="flex items-center justify-between p-4 bg-gray-800">
        <div class="flex items-center space-x-4">
            <h1 class="text-white text-2xl">Twitchie</h1>
            <a href="index.php" class="text-gray-400 hover:text-white">Home</a>
            <a href="live.php" class="text-gray-400 hover:text-white">Live Stream</a>
        </div>
        <div>
            <a href="premium_content.php" class="text-gray-400 hover:text-white mr-2">More Videos</a>
            <a href="help.php" class="text-gray-400 hover:text-white mr-2">Help</a>
            <?php if (isset($_SESSION['user_name'])): ?>
                <a href="profile.php"><span class="text-gray-400 hover:text-white">Welcome, <?php echo $_SESSION['user_name']; ?></span></a>
                <a href="logout.php" class="text-red-400 hover:text-white">Logout</a>
            <?php else: ?>
                <a href="login.php" class="text-gray-400 hover:text-white">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto p-8">
        <div class="premium-card p-10 rounded-lg text-center shadow-lg">
            <h2 class="text-3xl font-bold mb-4 text-white"><?php echo $premium_video_title; ?></h2>
            <p class="text-lg mb-6 text-gray-100">
                Access our premium content for just $<?php echo number_format($premium_video_price, 2); ?>.
                Unlock exclusive videos!
            </p>

            <!-- Payment Buttons -->
            <div class="flex justify-center space-x-4 mb-8">
                <!-- PayPal Button -->
                <div id="paypal-button-container" class="w-1/2"></div>

                <!-- MetaMask Button -->
                <button id="metamask-button" class="w-1/2 h-[50px] flex items-center justify-center bg-yellow-500 hover:bg-yellow-400 text-white font-bold py-3 px-5 rounded-lg shadow-lg transition">
                    <img src="resources/metamask.svg" alt="MetaMask" class="h-6 w-6 mr-2">
                    Pay with MetaMask
                </button>
            </div>

            <div class="mt-6">
                <a href="index.php" class="text-blue-500 hover:underline">Go Back to Home</a>
            </div>
        </div>
    </main>

    <!-- PayPal SDK Script -->
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo $paypalClientID; ?>&currency=USD"></script>
    <script>
        paypal.Buttons({
            style: {
                shape: 'pill',
                color: 'blue',
                layout: 'vertical',
                label: 'pay',
            },
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '<?php echo $premium_video_price; ?>'
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    alert('Transaction completed by ' + details.payer.name.given_name);
                    fetch('update_payment_status.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ user_id: '<?php echo $_SESSION['user_id']; ?>' })
                    }).then(response => response.json())
                      .then(data => { if (data.success) window.location.href = "premium_content.php"; });
                });
            }
        }).render('#paypal-button-container');
    </script>

    <!-- MetaMask Payment Script -->
    <script>
        document.getElementById('metamask-button').addEventListener('click', async function() {
            if (typeof window.ethereum !== 'undefined') {
                try {
                    const accounts = await ethereum.request({ method: 'eth_requestAccounts' });
                    const account = accounts[0];

                    // Convert USD to ETH assuming 1 ETH = 300 USD (exchange rate may vary)
                    const ethAmount = 5 / 1000; // 5 USD in ETH
                    const weiAmount = ethAmount * 10**18; // Convert ETH to Wei

                    const transactionParams = {
                        to: '0x45EA0d662163583a70D28EdBe1E72C9537b0Ee1f',  // Your wallet address to receive payments
                        from: account,
                        value: '0x' + weiAmount.toString(16),  // Value in Wei
                        chainId: '0x1'  // Mainnet chain ID
                    };

                    const txHash = await ethereum.request({
                        method: 'eth_sendTransaction',
                        params: [transactionParams]
                    });

                    alert('Payment successful! Transaction hash: ' + txHash);
                    // Optionally, update your database here with an AJAX call

                } catch (error) {
                    console.error('Payment failed:', error);
                    alert('There was an error with the payment.');
                }
            } else {
                alert('MetaMask is not installed. Please install MetaMask and try again.');
            }
        });
    </script>
</body>

</html>
