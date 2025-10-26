<?php
// ===============================
// MIDTRANS SERVER SIDE CHARGE API
// ===============================

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

// 1️⃣ Load Midtrans PHP SDK
require_once __DIR__ . '/vendor/autoload.php';

// 2️⃣ Konfigurasi Midtrans
\Midtrans\Config::$serverKey = 'Mid-server-RI64QhOID943k4hjthQQxwWb'; // ← GANTI SERVER KEY
\Midtrans\Config::$isProduction = false; // sandbox mode
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// 3️⃣ Ambil data dari frontend
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
  echo json_encode(["error" => "No JSON data received"]);
  exit;
}

if (!isset($data['items']) || !isset($data['total'])) {
  echo json_encode(["error" => "Missing items or total"]);
  exit;
}

// 4️⃣ Siapkan data transaksi
$params = [
  'transaction_details' => [
    'order_id' => 'ORDER-' . rand(1000, 9999),
    'gross_amount' => (int)$data['total'],
  ],
  'item_details' => array_map(function ($item) {
    return [
      'id' => $item['id'],
      'price' => (int)$item['price'],
      'quantity' => 1,
      'name' => $item['name']
    ];
  }, $data['items']),
  'customer_details' => [
    'first_name' => 'Pelanggan',
    'email' => 'pelanggan@example.com',
  ],
];

// 5️⃣ Dapatkan Snap Token
try {
  $snapToken = \Midtrans\Snap::getSnapToken($params);
  echo json_encode(['token' => $snapToken]);
} catch (Exception $e) {
  echo json_encode(['error' => $e->getMessage()]);
}
