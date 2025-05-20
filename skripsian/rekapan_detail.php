<?php
session_start();
include("config/config.php");

if (!isset($_SESSION['user'])) {
    header("location: config/login.php");
    exit();
}

$bulanFilter = isset($_GET['bulan']) ? $_GET['bulan'] : date("Y-m");

// Fungsi untuk menghitung durasi
function hitungDurasi($conn, $tanggal, $tabel) {
    $query = "SELECT waktu_ambil, waktu_selesai 
              FROM $tabel 
              WHERE DATE(tanggal) = '$tanggal' 
              AND waktu_ambil IS NOT NULL 
              AND waktu_selesai IS NOT NULL";

    $result = $conn->query($query);
    $durasiList = [];

    while ($row = $result->fetch_assoc()) {
        $start = strtotime($row['waktu_ambil']);
        $end = strtotime($row['waktu_selesai']);
        if ($end >= $start) {
            $durasi = round(($end - $start) / 60); // dalam menit
            $durasiList[] = $durasi;
        }
    }

    if (count($durasiList) > 0) {
        return [
            'tercepat' => min($durasiList),
            'terlama' => max($durasiList),
            'rata2' => round(array_sum($durasiList) / count($durasiList)),
            'total' => count($durasiList),
        ];
    } else {
        return [
            'tercepat' => '-',
            'terlama' => '-',
            'rata2' => '-',
            'total' => 0,
        ];
    }
}

// Ambil rekap dari antrian dan antrian_arsip
$dataRekap = [];

foreach (['antrian', 'antrian_arsip'] as $tabel) {
    $sql = "SELECT DATE(tanggal) as tgl, COUNT(*) as jumlah 
            FROM $tabel 
            WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$bulanFilter'
            GROUP BY tgl ORDER BY tgl DESC";

    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $durasi = hitungDurasi($conn, $row['tgl'], $tabel);
        $dataRekap[] = [
            'tanggal' => date("d-m-Y", strtotime($row['tgl'])),
            'jumlah' => $row['jumlah'],
            'tercepat' => $durasi['tercepat'],
            'terlama' => $durasi['terlama'],
            'rata2' => $durasi['rata2'],
        ];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Rekapan Antrian Harian</title>
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="shortcut icon" href="assets/img/ikon.png" type="image/x-icon">
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <a href="dashboard.php">Antrian</a>
            <a href="rekapan.php">Rekapan</a>
        </div>
        <div class="nav-right">
            <span id="clock" style="margin-right: 15px; font-weight: bold;"></span>
            <?= $_SESSION['user'] ?>
            <a href="config/logout.php" id="logoutBtn" class="logout-button" style="color: red; text-decoration: none; font-weight: bold; margin-left:20px;">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h2>DETAIL REKAPAN HARIAN BULAN <?= $bulanFilter ?></h2>
        <a href="rekapan.php" class="back-button">← Kembali</a>

        <table class="rekapan-table">
            <tr>
                    <th>Tanggal</th>
                    <th>Jumlah Antrian</th>
                    <th>Tercepat</th>
                    <th>Terlama</th>
                    <th>Rata-rata</th>
                </tr>

            <?php foreach ($dataRekap as $row): ?>
                <tr>
                        <td><?= $row['tanggal'] ?></td>
                        <td><?= $row['jumlah'] ?></td>
                        <td><?= $row['tercepat'] ?> Menit</td>
                        <td><?= $row['terlama'] ?> Menit</td>
                        <td><?= $row['rata2'] ?> Menit</td>
                    </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <script>
        document.getElementById("logoutBtn").addEventListener("click", function(event) {
            const konfirmasi = confirm("Apakah Anda yakin ingin logout?");
            if (!konfirmasi) {
                event.preventDefault();
            }
        });

        function updateClock() {
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            const dayName = days[now.getDay()];
            const day = now.getDate().toString().padStart(2, '0');
            const month = months[now.getMonth()];
            const year = now.getFullYear();

            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');

            const formattedTime = `${dayName}, ${day} ${month} ${year} - ${hours}:${minutes}:${seconds}`;
            document.getElementById('clock').textContent = formattedTime;
        }

        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>
