<?php
require '../../vendor/autoload.php';


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Thông tin kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cuoiky";

// Kết nối tới cơ sở dữ liệu
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// Hàm xuất file Excel
function exportExcel($spreadsheet, $filename) {
    if (ob_get_length()) {
        ob_end_clean();
    }
    $writer = new Xlsx($spreadsheet);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;
}

// Kiểm tra nếu có `order_id` thì xuất từng đơn hàng, nếu không thì xuất toàn bộ đơn hàng
if (isset($_GET['order_id'])) {
    $order_id = htmlspecialchars($_GET['order_id']);

    // Truy vấn lấy thông tin đơn hàng theo `order_id`
    $sql = 'SELECT o.order_id, d.name, o.total, o.status, o.quantity, o.date_create
            FROM orders o 
            JOIN dangky d ON o.username = d.username
            WHERE o.order_id = :order_id';
    $statement = $pdo->prepare($sql);
    $statement->execute(['order_id' => $order_id]);
    $order = $statement->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Thiết lập tiêu đề cột
        $headers = ['Mã đơn hàng', 'Người dùng', 'Giá niêm yết', 'Trạng thái', 'Ngày đặt', 'Số lượng', 'Tổng tiền'];
        $sheet->fromArray($headers, null, 'A1');

        // Thêm dữ liệu đơn hàng
        $total_money = $order['quantity'] * $order['total'];
        $data = [
            $order['order_id'],
            $order['name'],
            number_format($order['total'], 2) . ' VND',
            $order['status'],
            $order['date_create'],
            $order['quantity'],
            number_format($total_money, 2) . ' VND'
        ];
        $sheet->fromArray($data, null, 'A2');

        // Xuất file Excel cho từng đơn hàng
        $filename = 'order_' . $order_id . '_export.xlsx';
        exportExcel($spreadsheet, $filename);
    } else {
        echo "Đơn hàng không tồn tại.";
    }
} else {
    // Xuất toàn bộ đơn hàng
    $sql = 'SELECT o.order_id, d.name, o.total, o.status, o.quantity, o.date_create
            FROM orders o 
            JOIN dangky d ON o.username = d.username';
    $statement = $pdo->prepare($sql);
    $statement->execute();
    $orders = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($orders)) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Thiết lập tiêu đề cột
        $headers = ['Mã đơn hàng', 'Người dùng', 'Giá niêm yết', 'Trạng thái', 'Ngày đặt', 'Số lượng', 'Tổng tiền'];
        $sheet->fromArray($headers, null, 'A1');

        // Thêm dữ liệu tất cả đơn hàng
        $row = 2;
        foreach ($orders as $order) {
            $total_money = $order['quantity'] * $order['total'];
            $data = [
                $order['order_id'],
                $order['name'],
                number_format($order['total'], 2) . ' VND',
                $order['status'],
                $order['date_create'],
                $order['quantity'],
                number_format($total_money, 2) . ' VND'
            ];
            $sheet->fromArray($data, null, 'A' . $row);
            $row++;
        }

        // Xuất file Excel cho toàn bộ đơn hàng
        $filename = 'all_orders_export.xlsx';
        exportExcel($spreadsheet, $filename);
    } else {
        echo "Không có đơn hàng nào để xuất.";
    }
}
?>