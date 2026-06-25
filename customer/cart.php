<?php
session_start();

$cart = $_SESSION['cart'] ?? [];
$total = 0;

if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header("Location: customer_cart.php");
    exit();
}
?>

<h1>Shopping Cart</h1>

<?php if(empty($cart)): ?>

<p>Your cart is empty.</p>
<a href="menu.php">Back to Menu</a>

<?php else: ?>

<table border="1" cellpadding="10">
<tr>
    <th>Product</th>
    <th>Ice</th>
    <th>Sugar</th>
    <th>Qty</th>
    <th>Price</th>
    <th>Subtotal</th>
    <th>Action</th>
</tr>

<?php foreach($cart as $key => $item): ?>
<?php
$subtotal = $item['price'] * $item['qty'];
$total += $subtotal;
?>

<tr>
    <td><?= htmlspecialchars($item['name']); ?></td>
    <td><?= htmlspecialchars($item['ice_level']); ?></td>
    <td><?= htmlspecialchars($item['sugar_level']); ?></td>
    <td><?= $item['qty']; ?></td>
    <td>NT$ <?= number_format($item['price']); ?></td>
    <td>NT$ <?= number_format($subtotal); ?></td>
    <td>
        <a href="customer_cart.php?remove=<?= urlencode($key); ?>">
            Remove
        </a>
    </td>
</tr>

<?php endforeach; ?>

<tr>
    <th colspan="5">Total</th>
    <th colspan="2">NT$ <?= number_format($total); ?></th>
</tr>
</table>

<br>

<a href="menu.php">Continue Shopping</a>
<a href="checkout.php">Checkout</a>

<?php endif; ?>