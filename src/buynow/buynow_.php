<?php
session_start();

try {
    // Define your database connection
    $conn = new PDO("mysql:host=localhost;dbname=cuoiky", "root", "");
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Retrieve the username from the session
$username = $_SESSION['username'];

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $product_id = $_POST['product_id'];
    $size = $_POST['size'];
    $color = $_POST['color'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $note = $_POST['note'];
    $payment = $_POST['payment'];
    $birthday = $_POST['birthday'];
    $voucher_code = $_POST['voucher_code'];

    // Calculate total price
    $total_price = $quantity * $price; // Adjust this if applying discounts

    // Get voucher_id from voucher table if voucher_code is provided
    $voucher_id = 0;
    if (!empty($voucher_code)) {
        $sql_voucher = "SELECT vourcher_id FROM vourcher WHERE vourcher_code = :voucher_code AND status = 1";
        $stmt_voucher = $conn->prepare($sql_voucher);
        $stmt_voucher->execute([':voucher_code' => $voucher_code]);
        $voucher = $stmt_voucher->fetch(PDO::FETCH_ASSOC);
        if ($voucher) {
            $voucher_id = $voucher['vourcher_id'];
        }
    }

    // Retrieve the product name using product_id
    $sql_product = "SELECT name_product FROM product WHERE product_id = :product_id";
    $stmt_product = $conn->prepare($sql_product);
    $stmt_product->execute([':product_id' => $product_id]);
    $product = $stmt_product->fetch(PDO::FETCH_ASSOC);
    $name_product = $product['name_product'];

    // Retrieve the highest order_id and increment by 1
    $sql_max_order = "SELECT MAX(order_id) AS max_order_id FROM orders";
    $stmt_max_order = $conn->query($sql_max_order);
    $result = $stmt_max_order->fetch(PDO::FETCH_ASSOC);
    $new_order_id = $result['max_order_id'] + 1;

    // Prepare SQL query to insert order into orders table
    $sql = "INSERT INTO orders (order_id, username, date_create, total, note, status, name, email, phone, vourcher_code, voucher_id, address, name_product, color, size, quantity, payment) 
            VALUES (:order_id, :username, NOW(), :total, :note, 'Đã đặt hàng', :name, :email, :phone, :voucher_code, :voucher_id, :address, :name_product, :color, :size, :quantity, :payment)";
    
    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':order_id' => $new_order_id,
        ':username' => $username,
        ':total' => $total_price,
        ':note' => $note,
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':voucher_code' => $voucher_code,
        ':voucher_id' => $voucher_id,
        ':address' => $address,
        ':name_product' => $name_product, // Use the product name here
        ':color' => $color,
        ':size' => $size,
        ':quantity' => $quantity,
        ':payment' => $payment,
    ]);

    // Check if the order was successfully created
    if ($stmt->rowCount() > 0) {
        echo "Order placed successfully!";
        // You can redirect to a success page or show a success message
    } else {
        echo "Failed to place order. Please try again.";
    }
}
?>
