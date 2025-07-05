<?php
// Pengaturan Database
define('DB_HOST', 'localhost');
define('DB_USER', 'xaycool');
define('DB_PASS', 'XYZAGEN123');
define('DB_NAME', 'xycool_paneldb');

// Koneksi ke Database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

// Pengaturan Pterodactyl
define('PTERO_DOMAIN', 'https://xayz-pub.orang-gantengg.biz.id');
define('PTERO_APP_API_KEY', 'ptla_XgvhkEFaDuKdWtrwhpM0RdgzmUotXV0MJGam6LFMeoj');

// Pengaturan Konfigurasi Server Default
define('DEFAULT_EGG_ID', 16); // PENTING: Ganti dengan ID Egg Node.js Anda
define('DEFAULT_LOCATION_ID', 1);

// Pengaturan Bot Telegram
define('TELEGRAM_BOT_TOKEN', '7269955465:AAEbmaw6JRmeXQHoTJZl5Z266WwjfUNdoxk');

// Mulai Session untuk menyimpan status login
session_start();
?>