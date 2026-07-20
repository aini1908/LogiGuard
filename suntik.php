<?php
set_time_limit(600);
ini_set('memory_limit', '512M');

echo "=== MEMULAI SUNTIK DATA PELABUHAN GLOBAL UTUH OTOMATIS ===\n";

// 1. Koneksi Database MAMP
$host = '127.0.0.1';
$db   = 'db_LogiGuard';
$user = 'root';
$pass = 'root'; 
$port = '8889'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Koneksi database gagal wee: " . $e->getMessage() . "\n");
}

// 2. Kosongkan Tabel Ports
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0;');
$pdo->exec('TRUNCATE TABLE ports;');
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');
echo "Tabel ports berhasil dikosongkan secara bersih.\n";

// 3. Query Otomatis SELURUH DUNIA dengan Metode Kompresi Titik Pusat (Anti Timeout)
$query = '[out:json][timeout:180];
(
  node["harbour"~"commercial|industrial"];
  way["harbour"~"commercial|industrial"];
  node["industrial"="port"];
  way["industrial"="port"];
);
out center;';

$apiUrl = "https://overpass.kumi.systems/api/interpreter";

echo "Sedang mendownload RIBUAN data pelabuhan riil SELURUH DUNIA dari API Mirror...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['data' => $query]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 240);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$response) {
    die("Server API luar mendeteksi beban jaringan penuh. Langsung jalankan ulang kembali command-nya wee!\n");
}

$data = json_decode($response, true);
$elements = $data['elements'] ?? [];
$insertedCount = 0;

echo "Selesai mendownload seluruh dunia. Mulai menyuntikkan ke phpMyAdmin...\n";

$stmt = $pdo->prepare("INSERT INTO ports (port_name, country_code, country_name, latitude, longitude, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");

foreach ($elements as $element) {
    $tags = $element['tags'] ?? [];
    $name = $tags['name'] ?? $tags['operator'] ?? null;
    if (!$name) continue;

    // Menangani koordinat node biasa atau titik pusat dari tipe objek way
    $lat = $element['lat'] ?? ($element['center']['lat'] ?? null);
    $lng = $element['lon'] ?? ($element['center']['lon'] ?? null);
    
    $countryCode = isset($tags['addr:country']) ? substr(strtoupper($tags['addr:country']), 0, 2) : 'GL';
    $countryName = $tags['addr:country'] ?? 'Global Area';

    if ($lat !== null && $lng !== null) {
        $stmt->execute([substr($name, 0, 255), $countryCode, substr($countryName, 0, 255), $lat, $lng]);
        $insertedCount++;
    }
}

echo "\n=== BOOM! SUKSES TOTAL HURUL! ===\n";
echo "Berhasil otomatis memasukkan {$insertedCount} data pelabuhan riil dunia tanpa dipotong!\n";