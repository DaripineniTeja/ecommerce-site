<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

// Fetch the user's cart items
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT cart.id AS cart_id, products.name, products.price, cart.quantity
                        FROM cart
                        JOIN products ON cart.product_id = products.id
                        WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle quantity update
if (isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$quantity, $user_id, $product_id]);
    header("Location: cart.php"); // Refresh to reflect changes
    exit();
}

// Handle item removal
if (isset($_POST['remove_from_cart'])) {
    $product_id = $_POST['product_id'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    header("Location: cart.php"); // Refresh to reflect changes
    exit();
}

$total_cost = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart</title>
    
</head>
<body>
    <h1>Your Cart</h1>
    <table border="1">
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
            <th>Action</th>
        </tr>
        <?php foreach ($cart_items as $item): ?>
            <?php $subtotal = $item['price'] * $item['quantity']; $total_cost += $subtotal; ?>
            <tr>
                <td><?= htmlspecialchars($item['name']); ?></td>
                <td>$<?= number_format($item['price'], 2); ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?= $item['cart_id']; ?>">
                        <input type="number" name="quantity" value="<?= $item['quantity']; ?>" min="1">
                        <button type="submit" name="update_quantity">Update</button>
                    </form>
                </td>
                <td>$<?= number_format($subtotal, 2); ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?= $item['cart_id']; ?>">
                        <button type="submit" name="remove_from_cart">Remove</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Total: $<?= number_format($total_cost, 2); ?></h3>
</body>
</html>
