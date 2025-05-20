<?php

include("config/config.php");

$queueFile = "config/log.txt";
$printFile = "config/print.bin";
$printer_name = "POS-58";
$currentDate = date("Y-m-d");
$currentTime = date("H:i:s");
$admin_id = 1;

$sql = "SELECT nomor_antrian FROM antrian WHERE tanggal = '$currentDate' ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $queueNumber = $row['nomor_antrian'] + 1;
} else {
    $queueNumber = 1;
}

$insertQuery = "INSERT INTO antrian (nomor_antrian, tanggal, waktu_ambil, status, admin_id) 
                VALUES ('$queueNumber', '$currentDate', '$currentTime', 'tunggu', '$admin_id')";
if ($conn->query($insertQuery) === TRUE) {
    echo "Antrean $queueNumber berhasil ditambahkan ke database.";
} else {
    echo "Gagal menambahkan antrean: " . $conn->error;
}

file_put_contents($queueFile, "$currentDate|$queueNumber");
$data = "\n";
$data .= "\x1B\x40"; 
$data .= "\x1B\x61\x01";
$data .= "\x1B\x21\x20";
$data .= "\x1B\x45\x01";
$data .= "PUSKESMAS\n";
$data .= "SAMBURAKAT\n";
$data .= "";
$data .= "\x1B\x45\x00";
$data .= "\n\n";
$data .= "\x1B\x21\x20";
$data .= "ANTRIAN\n\n";
$data .= "\x1B\x21\x38";
$data .= "\x1B\x45\x01";
$data .= "";
$data .= "A " . $queueNumber . "\n\n";
$data .= "\x1B\x45\x00"; 
$data .= "\x1B\x21\x00";
$data .= "";
$data .= "";
$data .= "\x1B\x45\x01";
$data .= date("d-m-Y H:i:s") . "\n\n";

function escpos_qr_code($dataString) {
    $cmd = "";
    $cmd .= "\x1D\x28\x6B";
    $pL = strlen($dataString) + 3;
    $pH = 0;
    $cmd .= chr($pL & 0xFF) . chr($pH);
    $cmd .= "\x31\x50\x30";
    $cmd .= $dataString;
    $cmd .= "\x1D\x28\x6B\x03\x00\x31\x45\x31";
    $cmd .= "\x1D\x28\x6B\x03\x00\x31\x43\x05"; 
    $cmd .= "\x1D\x28\x6B\x03\x00\x31\x51\x30";
    return $cmd;
}
$data .= escpos_qr_code("http://192.168.231.59/skripsian/tampilan.php");
$data .= "\n\n";

$data .= "Silahkan menunggu Antrian Anda\n";
$data .= "Terima Kasih Sudah Berkunjung\n";
$data .= "Get Well Soon :D\n";
$data .= "\x1D\x56\x00";

file_put_contents($printFile, $data);

shell_exec('cmd /c copy /b C:\\xampp\\htdocs\\skripsian\\config\\print.bin \\\\localhost\\POST-58');

$conn->close();
?>
