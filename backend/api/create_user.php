<?php
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die(json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya untuk Admin.']));
}

$data = json_decode(file_get_contents('php://input'), true);
$new_username = $data['username'];
$new_license_key = bin2hex(random_bytes(16));
$active_days = (int)$data['active_days'];
$expiry_date = date('Y-m-d', strtotime("+$active_days days"));
$is_admin = isset($data['isAdmin']) && $data['isAdmin'] === true ? 1 : 0;

$stmt = $conn->prepare("INSERT INTO users (username, license_key, expiry_date, is_admin) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $new_username, $new_license_key, $expiry_date, $is_admin);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'User berhasil dibuat!',
        'username' => $new_username,
        'license_key' => $new_license_key,
        'expiry_date' => $expiry_date
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal membuat user: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>