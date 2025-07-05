<?php
header('Content-Type: application/json');
require_once '../config.php';

// Langkah 1: Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu.']));
}

// Langkah 2: Ambil data dari frontend
$data = json_decode(file_get_contents('php://input'), true);
$user_email = $data['email'];
$user_username = $data['username'];
$user_password = $data['password'];
$telegram_id = $data['telegram_id'];

// Langkah 3: Ambil dan validasi resource tanpa batas atas
$ram = max(1, (int)$data['ram']);
$disk = max(1, (int)$data['disk']);
$cpu = max(0, (int)$data['cpu']); // 0 berarti unlimited untuk CPU

// Fungsi untuk memanggil API Pterodactyl
function callPterodactylAPI($endpoint, $method = 'POST', $data = []) {
    $url = PTERO_DOMAIN . '/api/application/' . $endpoint;
    $ch = curl_init($url);
    $headers = [
        'Authorization: Bearer ' . PTERO_APP_API_KEY,
        'Content-Type: application/json',
        'Accept: Application/vnd.pterodactyl.v1+json',
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $http_code, 'response' => json_decode($response, true)];
}

// Fungsi untuk mengirim pesan ke Telegram
function sendTelegramMessage($chat_id, $message) {
    $url = 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage';
    $data = ['chat_id' => $chat_id, 'text' => $message, 'parse_mode' => 'Markdown'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

// Langkah 4: Buat atau cari user di Pterodactyl
$userData = ['email' => $user_email, 'username' => $user_username, 'first_name' => $user_username, 'last_name' => 'User', 'password' => $user_password];
$userResult = callPterodactylAPI('users', 'POST', $userData);
$ptero_user_id = null;
if ($userResult['code'] == 201) {
    $ptero_user_id = $userResult['response']['attributes']['id'];
} elseif ($userResult['code'] == 422) {
    $usersList = callPterodactylAPI('users?filter[email]=' . urlencode($user_email), 'GET');
    if ($usersList['code'] == 200 && count($usersList['response']['data']) > 0) {
        $ptero_user_id = $usersList['response']['data'][0]['attributes']['id'];
    }
}
if (!$ptero_user_id) {
    die(json_encode(['success' => false, 'message' => 'Gagal membuat atau menemukan akun Pterodactyl.']));
}

// Langkah 5: Siapkan data dan buat server
$serverData = [
    'name' => 'Bot WhatsApp - ' . $user_username,
    'user' => $ptero_user_id,
    'egg' => DEFAULT_EGG_ID,
    'docker_image' => 'ghcr.io/pterodactyl/yolks:nodejs_18',
    'startup' => 'node {{SERVER_STARTUP_SCRIPT}}',
    'environment' => ['SERVER_STARTUP_SCRIPT' => 'index.js'],
    'limits' => ['memory' => $ram, 'swap' => 0, 'disk' => $disk, 'io' => 500, 'cpu' => $cpu],
    'feature_limits' => ['databases' => 0, 'allocations' => 1, 'backups' => 1],
    'deploy' => ['locations' => [DEFAULT_LOCATION_ID], 'dedicated_ip' => false, 'port_range' => []]
];
$serverResult = callPterodactylAPI('servers', 'POST', $serverData);

// Langkah 6: Beri respons ke pengguna
if ($serverResult['code'] == 201) {
    // Jika sukses, kirim notifikasi Telegram
    $message = "✅ *Panel Bot WhatsApp Anda Siap!* ✅\n\n";
    $message .= "Gunakan detail ini untuk login ke panel:\n";
    $message .= "-------------------------------------\n";
    $message .= "*URL Panel:* `" . PTERO_DOMAIN . "`\n";
    $message .= "*Username:* `$user_username`\n";
    $message .= "*Password:* `$user_password`\n";
    $message .= "-------------------------------------\n\n";
    $message .= "Upload file bot Anda melalui File Manager dan nyalakan servernya.";
    sendTelegramMessage($telegram_id, $message);
    echo json_encode(['success' => true, 'message' => 'Server berhasil dibuat! Cek pesan di Telegram Anda.']);
} else {
    // Jika gagal, beri pesan error
    echo json_encode(['success' => false, 'message' => 'Gagal membuat server.', 'details' => $serverResult['response']]);
}

$conn->close();
?>