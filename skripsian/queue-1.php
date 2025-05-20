<?php
session_start();
include("config/config.php");

if (!isset($_SESSION['user'])) {
    header("location: config/login.php");
}
 
$queueFile = "config/queue_log.txt";
$printFile = "config/print.txt";
$printer_name = "POS-58";
$currentDate = date("Y-m-d");
$admin_id = $_SESSION['user_id'];

$sql = "SELECT nomor_antrian FROM antrian WHERE tanggal = '$currentDate' ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $queueNumber = $row['nomor_antrian'] + 1;
} else {
    $queueNumber = 1;
}

$insertQuery = "INSERT INTO antrian (nomor_antrian, tanggal, status, admin_id) 
                VALUES ('$queueNumber', '$currentDate', 'tunggu', '$admin_id')";
if ($conn->query($insertQuery) === TRUE) {
    echo "Antrean $queueNumber berhasil ditambahkan ke database.";
} else {
    echo "Gagal menambahkan antrean: " . $conn->error;
}

file_put_contents($queueFile, "$currentDate|$queueNumber");

file_put_contents($printFile, " PUSKESMAS SAMBURAKAT\n");
file_put_contents($printFile, "======================\n", FILE_APPEND);
file_put_contents($printFile, "        ANTRIAN\n\n", FILE_APPEND);
file_put_contents($printFile, "         A " . $queueNumber . "\n\n", FILE_APPEND);
file_put_contents($printFile, date("  d-m-Y H:i:s") . "\n\n", FILE_APPEND);
file_put_contents($printFile, "   Silahkan menunggu\n", FILE_APPEND);
file_put_contents($printFile, "     Antrian Anda\n", FILE_APPEND);
file_put_contents($printFile, "     Terima Kasih\n", FILE_APPEND);
file_put_contents($printFile, "======================\n", FILE_APPEND);

shell_exec('cmd /c copy /b C:\\xampp\\htdocs\\skripsian\\config\\print.bin \\\\localhost\\POS-58');


$conn->close();
?>
