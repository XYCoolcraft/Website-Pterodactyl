<?php
header('Content-Type: application/json');
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'];
$license_key = $data['license_key'];

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND license_key = ?");
$stmt->bind_param("ss", $username, $license_key);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (strtotime($user['expiry_date']) < time()) {
        echo json_encode(['success' => false, 'message' => 'Lisensi Anda sudah kedaluwarsa.']);
    } else {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        echo json_encode(['success' => true, 'isAdmin' => (bool)$user['is_admin']]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Username atau Key License salah.']);
}

$stmt->close();
$conn->close();
?>