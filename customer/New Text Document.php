<?php
$order_id = $_GET['id'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Success</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fdfaf7;
            padding: 40px;
            text-align: center;
            color: #2d2219;
        }

        .box {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 35px;
            border-radius: 22px;
            box-shadow: 0 10px 30px rgba(111,78,55,0.08);
        }

        h1 {
            color: #6f4e37;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 18px;
            background: #6f4e37;
            color: white;
            text-decoration: none;
            border-radius: 12px;
        }
    </style>
</head>
<body>

<div class="box">
    <h1>Order Successful!</h1>
    <p>Your order ID is:</p>
    <h2>#<?php echo htmlspecialchars($order_id); ?></h2>

    <a href="menu.php">Back to Menu</a>
</div>

</body>
</html>