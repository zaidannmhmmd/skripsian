<?php
include("config/config.php");

$bulanLalu = date("m", strtotime("first day of last month"));
$tahunLalu = date("Y", strtotime("first day of last month"));

$insertToArsip = "
    INSERT INTO antrian_arsip (nomor_antrian, tanggal, waktu_ambil, waktu_selesai, status, admin_id)
    SELECT nomor_antrian, tanggal, waktu_ambil, waktu_selesai, status, admin_id
    FROM antrian
    WHERE MONTH(tanggal) = '$bulanLalu' AND YEAR(tanggal) = '$tahunLalu'
";
$conn->query($insertToArsip);

$deleteOld = "
    DELETE FROM antrian
    WHERE MONTH(tanggal) = '$bulanLalu' AND YEAR(tanggal) = '$tahunLalu'
";
$conn->query($deleteOld);
echo "<script>alert('Data Berhasil Dicadangkan dari $bulanLalu-$tahunLalu');window.location='rekapan.php';</script>";
$conn->close();
?>
