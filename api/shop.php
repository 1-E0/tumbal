<?php//adlwin
session_start();
require_once '../Controllers/ShopController.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$shopObj = new ShopController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'create_shop') {
        echo $shopObj->createShop(
            $_SESSION['user_id'],
            $_POST['nama_toko'],
            $_POST['deskripsi_toko'],
            $_POST['alamat_toko']
        );
    }
}
?>