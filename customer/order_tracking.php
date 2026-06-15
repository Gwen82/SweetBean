<?php
// customer/order_tracking.php
require_once '../config/session.php';
require_once '../config/db.php'; // Accessing PDO connection $conn from config directory

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Validate incoming order identifier parameters
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    die("<div style='padding:50px; text-align:center; font-family:sans-serif;'>Error: Missing order identifier parameter.</div>");
}

$order_id = intval($_GET['order_id']);

try {
    // 1. Fetch parent order data records
    $stmtOrder = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmtOrder->execute([$order_id, $user_id]);
    $order = $stmtOrder->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("<div style='padding:50px; text-align:center; font-family:sans-serif;'>Error: Order not found or access denied.</div>");
    }

    // 2. Fetch linked relational line items from order_items
    $stmtItems = $conn->prepare("
        SELECT oi.*, COALESCE(mi.name, oi.menu_id) AS item_name
        FROM order_items oi
        LEFT JOIN menu_items mi ON mi.id = oi.menu_id
        WHERE oi.order_id = ?
        ORDER BY oi.id
    ");
    $stmtItems->execute([$order_id]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Database transaction exception encountered: " . $e->getMessage());
}

// Map database tracking statuses into visual state indexes
$statusList = ['Pending', 'Processing', 'Delivering', 'Completed'];
$currentStatus = $order['status'];
$activeIndex = array_search($currentStatus, $statusList);
if ($activeIndex === false) { $activeIndex = 0; } // Fallback safe fallback
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order #<?php echo $order_id; ?> - Sweet Bean Cafe</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-cream: #E5DDD3;      
            --text-coffee: #5A4033;    
            --border-dark: #333333;    
            --white: #FFFFFF;
            --accent-green: #4A6B52;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-cream);
            color: var(--text-coffee);
        }

        .track-container {
            max-width: 800px;
            margin: 40px auto;
            background: var(--white);
            border: 2px solid var(--border-dark);
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .header-block {
            padding: 25px;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.5);
            border-bottom: 2px solid var(--border-dark);
        }

        .header-block h1 {
            font-size: 26px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .content-block {
            padding: 30px;
        }

        /* Timeline Tracker System */
        .timeline-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 30px 0 40px 0;
            position: relative;
        }

        .timeline-line {
            position: absolute;
            top: 25px;
            left: 5%;
            right: 5%;
            height: 3px;
            background-color: #E0E0E0;
            z-index: 1;
        }

        .timeline-progress {
            position: absolute;
            top: 25px;
            left: 5%;
            height: 3px;
            background-color: var(--text-coffee);
            z-index: 2;
            width: <?php echo ($activeIndex / (count($statusList) - 1)) * 90; ?>%;
            transition: width 0.4s ease;
        }

        .status-node {
            position: relative;
            z-index: 3;
            text-align: center;
            flex: 1;
        }

        .icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #FFF;
            border: 2px solid #E0E0E0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #AAA;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .status-node.active .icon-circle {
            border-color: var(--text-coffee);
            background-color: var(--text-coffee);
            color: var(--white);
        }

        .status-node.completed .icon-circle {
            border-color: var(--accent-green);
            background-color: var(--accent-green);
            color: var(--white);
        }

        .node-label {
            font-size: 13px;
            font-weight: bold;
            color: #AAA;
        }

        .status-node.active .node-label {
            color: var(--text-coffee);
        }

        /* Information Details Panels */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-card {
            border: 2px solid var(--border-dark);
            padding: 20px;
            background: #FAF8F5;
        }

        .info-card h3 {
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 10px;
            border-bottom: 1px dashed var(--border-dark);
            padding-bottom: 5px;
        }

        .info-card p {
            font-size: 13px;
            line-height: 1.6;
        }

        /* Order Summary Table UI */
        .summary-box {
            border: 2px solid var(--border-dark);
        }

        .summary-header {
            background: #FAF8F5;
            padding: 12px 20px;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 2px solid var(--border-dark);
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 20px;
            border-bottom: 1px solid #EEE;
            font-size: 14px;
        }

        .totals-block {
            padding: 15px 20px;
            background: #FAF8F5;
            border-top: 1px solid var(--border-dark);
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .grand-total {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px dashed #DDD;
        }

        .action-link {
            display: inline-block;
            margin-top: 25px;
            color: var(--text-coffee);
            font-weight: bold;
            text-decoration: none;
            font-size: 14px;
        }
        .action-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <?php include '../navbar.php'; ?>

    <div class="track-container">
        <div class="header-block">
            <h1>Track Order #<?php echo $order_id; ?></h1>
            <p style="font-size: 13px; color: #8C766B; margin-top: 5px;">Placed on: <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></p>
        </div>

        <div class="content-block">
            
            <div class="timeline-wrapper">
                <div class="timeline-line"></div>
                <div class="timeline-progress"></div>
                
                <div class="status-node <?php echo $activeIndex >= 0 ? 'active' : ''; ?>">
                    <div class="icon-circle"><i class="fa-solid fa-receipt"></i></div>
                    <div class="node-label">Pending</div>
                </div>
                <div class="status-node <?php echo $activeIndex >= 1 ? 'active' : ''; ?>">
                    <div class="icon-circle"><i class="fa-solid fa-mug-hot"></i></div>
                    <div class="node-label">Processing</div>
                </div>
                <div class="status-node <?php echo $activeIndex >= 2 ? 'active' : ''; ?>">
                    <div class="icon-circle"><i class="fa-solid fa-truck"></i></div>
                    <div class="node-label">In Transit</div>
                </div>
                <div class="status-node <?php echo $activeIndex >= 3 ? 'completed' : ''; ?>">
                    <div class="icon-circle"><i class="fa-solid fa-box-open"></i></div>
                    <div class="node-label">Completed</div>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <h3>Fulfillment Method</h3>
                    <p><strong>Type:</strong> <?php echo ucfirst($order['method']); ?></p>
                    <p><strong>Target Destination:</strong><br><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                </div>
                <div class="info-card">
                    <h3>Status Specifications</h3>
                    <p><strong>Current Node:</strong> <?php echo $currentStatus; ?></p>
                    <p style="margin-top: 8px; font-size:12px; color:#777;">
                        <?php 
                        if($currentStatus === 'Pending') echo "We have received your order details and are preparing to queue it.";
                        elseif($currentStatus === 'Processing') echo "Our barista is currently crafting your premium coffee choice.";
                        elseif($currentStatus === 'Delivering') echo "Your package is on its way to your specified delivery address.";
                        else echo "Order complete! Enjoy your coffee from Sweet Bean Cafe!";
                        ?>
                    </p>
                </div>
            </div>

            <div class="summary-box">
                <div class="summary-header">Items Summary</div>
                <?php foreach ($items as $item): ?>
                    <div class="item-row">
                        <div>
                            <span><?php echo htmlspecialchars($item['item_name']); ?></span>
                            <span style="color:#888; margin-left: 10px;">x<?php echo $item['qty']; ?></span>
                        </div>
                        <strong>NT$ <?php echo number_format($item['price'] * $item['qty'], 2); ?></strong>
                    </div>
                <?php endforeach; ?>
                
                <div class="totals-block">
                    <div class="total-row">
                        <span>Items Subtotal</span>
                        <span>NT$ <?php echo number_format($order['subtotal'], 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span>Delivery Logistics Fee</span>
                        <span>NT$ <?php echo number_format($order['delivery_fee'], 2); ?></span>
                    </div>
                    <div class="total-row grand-total">
                        <strong>Grand Total</strong>
                        <strong>NT$ <?php echo number_format($order['total_price'], 2); ?></strong>
                    </div>
                </div>
            </div>

            <a href="../index.php" class="action-link"><i class="fa-solid fa-arrow-left"></i> Return to Store Front</a>
        </div>
    </div>

</body>
</html>
