<?php
include("config.php");

// Cek IP pengirim (opsional, untuk keamanan)
$ip = $_SERVER['REMOTE_ADDR'];
$allowed_ip = '192.168.10.59'; // Ganti dengan IP ESP8266 kamu

if ($ip !== $allowed_ip) {
    http_response_code(403);
    echo "Forbidden";
    exit;
}

// Proses tambah antrean (mirip kode kamu sebelumnya)
$currentDate = date("Y-m-d");
$currentTime = date("H:i:s");

$sql = "SELECT nomor_antrian FROM antrian WHERE tanggal = '$currentDate' ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

$queueNumber = ($result->num_rows > 0) ? $result->fetch_assoc()['nomor_antrian'] + 1 : 1;

$insertQuery = "INSERT INTO antrian (nomor_antrian, tanggal, waktu_ambil, status, admin_id) 
                VALUES ('$queueNumber', '$currentDate', '$currentTime', 'tunggu', 1)";
if ($conn->query($insertQuery) === TRUE) {
    echo "Antrean $queueNumber berhasil ditambahkan";
} else {
    http_response_code(500);
    echo "Gagal menambahkan antrean: " . $conn->error;
}
$conn->close();
