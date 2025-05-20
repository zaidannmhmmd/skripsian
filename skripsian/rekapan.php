<?php
session_start();
include("config/config.php");
if (!isset($_SESSION['user'])) {
    header("location: config/login.php");
}

// Ambil filter (default: bulan ini, tetapi tidak mempengaruhi hasil utama)
$bulanFilter = isset($_GET['bulan']) ? $_GET['bulan'] : date("Y-m");

// Query untuk menghitung jumlah antrean dari tabel 'antrian' dan 'antrian_arsip'
$sqlAntrian = "
    SELECT DATE_FORMAT(tanggal, '%Y-%m') AS bulan, COUNT(*) AS total_antrian
    FROM antrian 
    GROUP BY bulan
    ORDER BY bulan DESC
";
$sqlArsip = "
    SELECT DATE_FORMAT(tanggal, '%Y-%m') AS bulan, COUNT(*) AS total_antrian
    FROM antrian_arsip
    GROUP BY bulan
    ORDER BY bulan DESC
";

// Menjalankan query untuk 'antrian'
$resultAntrian = $conn->query($sqlAntrian);
$rekapanAntrian = [];
while ($row = $resultAntrian->fetch_assoc()) {
    $rekapanAntrian[] = $row;
}

// Menjalankan query untuk 'antrian_arsip'
$resultArsip = $conn->query($sqlArsip);
$rekapanArsip = [];
while ($row = $resultArsip->fetch_assoc()) {
    $rekapanArsip[] = $row;
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapan Antrian</title>
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
        <h2>REKAPAN ANTRIAN</h2>

        <!-- Filter Form -->
        <!-- <form method="GET" class="filter-form">
            <label for="bulan">Pilih Bulan:</label>
            <input type="month" id="bulan" name="bulan" value="<?php echo $bulanFilter; ?>">
            <button type="submit">Filter</button>
        </form> -->

        <a href="arsip_antrian.php" class="back-button">Cadangkan</a>
        <table class="rekapan-table">
            <tr>
                <th>Bulan</th>
                <th>Jumlah Antrian</th>
                <th>Operator</th>
                <th>Aksi</th>
            </tr>
            <?php if (count($rekapanAntrian) > 0): ?>
                <?php foreach ($rekapanAntrian as $data): ?>
                    <tr>
                        <td><?php echo date("F Y", strtotime($data['bulan'] . "-01")); ?></td>
                        <td><?php echo $data['total_antrian']; ?></td>
                        <td style="font-weight: bold;">2111102441042</td>
                        <td>
                            <a href="rekapan_detail.php?type=harian&bulan=<?php echo $data['bulan']; ?>">Lihat Harian</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (count($rekapanArsip) > 0): ?>
                <?php foreach ($rekapanArsip as $data): ?>
                    <tr>
                        <td><?php echo date("F Y", strtotime($data['bulan'] . "-01")); ?></td>
                        <td><?php echo $data['total_antrian']; ?></td>
                        <td style="font-weight: bold;">2111102441042</td>
                        <td>
                            <a href="rekapan_detail.php?type=harian&bulan=<?php echo $data['bulan']; ?>">Lihat Harian</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

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