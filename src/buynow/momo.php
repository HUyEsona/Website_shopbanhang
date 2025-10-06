<?php
session_start();

if (!isset($_SESSION['order_data'])) {
    echo "<script>alert('Không có thông tin đơn hàng!'); window.location.href='../index.php';</script>";
    exit();
}

// Kết nối CSDL
try {
    $conn = new PDO("mysql:host=localhost;dbname=cuoiky", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$order = $_SESSION['order'] ?? [];

if (empty($order)) {
    die("Không có dữ liệu đơn hàng.");
}

$voucher_id = $order['voucher_id'] ?? 0;
$name_product = $order['name_product'] ?? '';


// Lấy dữ liệu đơn hàng từ session
$data = $_SESSION['order_data'];

$order_id = 1;
// Tìm order_id cao nhất hiện có
$stmt = $conn->query("SELECT MAX(order_id) AS max_id FROM orders");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row && $row['max_id']) {
    $order_id = $row['max_id'] + 1;
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=cuoiky;charset=utf8", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("INSERT INTO orders (
        order_id, username, date_create, total, note, status, name, email, phone, 
        vourcher_code, voucher_id, address, name_product, color, size, quantity, payment
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$order_id = 1;
// Tìm order_id cao nhất hiện có
$stmt = $conn->query("SELECT MAX(order_id) AS max_id FROM orders");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row && $row['max_id']) {
    $order_id = $row['max_id'] + 1;
}

    $stmt->execute([
        $order['order_id'],
        $order['username'],
        $order['date_create'],
        $order['total'],
        $order['note'],
        $order['status'],
        $order['name'],
        $order['email'],
        $order['phone'],
        $order['voucher_code'],
        $voucher_id,
        $order['address'],
        $name_product,
        $order['color'],
        $order['size'],
        $order['quantity'],
        'momo'
    ]);
} catch (PDOException $e) {
    die("Lỗi lưu đơn hàng: " . $e->getMessage());
}


// Thanh toán MoMo
$endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
$partnerCode = 'MOMOBKUN20180529';
$accessKey = 'klm05TvNBzhg7h7j';
$secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
$orderInfo = "Thanh toán qua MoMo";
$amount = $data['total'];
$orderId = time() . "";
$redirectUrl = "http://localhost/duan1/index.php"; // sau thanh toán sẽ quay lại
$ipnUrl = "http://localhost/duan1/index.php";
$requestId = time() . "";
$requestType = "payWithATM";
$extraData = "";

$rawHash = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=$requestType";
$signature = hash_hmac("sha256", $rawHash, $secretKey);

$data = [
    'partnerCode' => $partnerCode,
    'partnerName' => "Test",
    "storeId" => "MomoTestStore",
    'requestId' => $requestId,
    'amount' => $amount,
    'orderId' => $orderId,
    'orderInfo' => $orderInfo,
    'redirectUrl' => $redirectUrl,
    'ipnUrl' => $ipnUrl,
    'lang' => 'vi',
    'extraData' => $extraData,
    'requestType' => $requestType,
    'signature' => $signature
];

// Hàm gửi POST
function execPostRequest($url, $data)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($data))
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

$result = execPostRequest($endpoint, $data);
$jsonResult = json_decode($result, true);

// Chuyển hướng tới trang thanh toán
header('Location: ' . $jsonResult['payUrl']);
exit();
