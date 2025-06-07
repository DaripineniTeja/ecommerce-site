<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';
$user_id = $_SESSION['user_id'];

// ✅ ADD TO CART
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];

    // Check if item already in cart
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        $new_qty = $existing['quantity'] + 1;
        $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $update_stmt->execute([$new_qty, $user_id, $product_id]);
    } else {
        $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insert_stmt->execute([$user_id, $product_id, 1]);
    }

    header("Location: cart.php");
    exit();
}

// ✅ UPDATE QUANTITY
if (isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$quantity, $user_id, $product_id]);
}

// ✅ REMOVE FROM CART
if (isset($_POST['remove_from_cart'])) {
    $product_id = $_POST['product_id'];

    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
}

// ✅ FETCH CART ITEMS
$stmt = $conn->prepare("SELECT cart.product_id, products.name, products.price, cart.quantity
                        FROM cart
                        JOIN products ON cart.product_id = products.id
                        WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_cost = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Your Shopping Cart</h1>
            <nav>
                <a href="../index.php">Home</a>
                <a href="logout.php" class="logout-button">Logout</a>
            </nav>
        </div>
    </header>

    <div class="cart-container">
        <h2>Items in Your Cart</h2>
        <?php if (empty($cart_items)) : ?>
            <p>Your cart is empty.</p>
        <?php else : ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']); ?></td>
                            <td>$<?= number_format($item['price'], 2); ?></td>
                            <td>
                                <form method="POST" style="display: inline-flex;">
                                    <input type="number" name="quantity" value="<?= $item['quantity']; ?>" min="1" style="width: 60px;">
                                    <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                                    <button type="submit" name="update_quantity">Update</button>
                                </form>
                            </td>
                            <td>$<?= number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                                    <button type="submit" name="remove_from_cart">Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php $total_cost += $item['price'] * $item['quantity']; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3>Total: $<?= number_format($total_cost, 2); ?></h3>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?= date('Y'); ?> Online Store</p>
    </footer>
</body>
</html>
