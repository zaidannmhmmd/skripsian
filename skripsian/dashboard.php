<?php
session_start();
include("config/config.php");
if (!isset($_SESSION['user'])) {
    header("location: config/login.php");
}

?>

<?php
include("config/config.php");

$currentDate = date("Y-m-d");

// Antrian Waktu Sekarang
$sql = "SELECT nomor_antrian, waktu_selesai FROM antrian WHERE tanggal = '$currentDate' AND status = 'selesai' ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $currentQueue = $row['nomor_antrian'];
    $currentTime = date("H:i:s", strtotime($row['waktu_selesai']));
} else {
    $currentQueue = "-";
    $currentTime = "-";
}

// Hitung total antrean hari ini
$sqlTotal = "SELECT COUNT(*) as total FROM antrian WHERE tanggal = '$currentDate'";
$totalResult = $conn->query($sqlTotal);
$totalQueues = ($totalResult->num_rows > 0) ? $totalResult->fetch_assoc()['total'] : 0;

// Ambil antrean selanjutnya yang belum dipanggil
$sqlNext = "SELECT nomor_antrian FROM antrian WHERE tanggal = '$currentDate' AND status = 'tunggu' ORDER BY id ASC LIMIT 1";
$nextResult = $conn->query($sqlNext);
$nextQueue = ($nextResult->num_rows > 0) ? $nextResult->fetch_assoc()['nomor_antrian'] : null;

// Jika tombol Next ditekan, update status antrean
if (isset($_POST['next'])) {
    if ($nextQueue !== null) {
        $conn->query("UPDATE antrian SET status = 'selesai', waktu_selesai = NOW() WHERE nomor_antrian = '$nextQueue' AND tanggal = '$currentDate'");
        header("Location: dashboard.php?next=$nextQueue");
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <meta http-equiv="refresh" content="5"> -->
    <title>Dashboard Antrian</title>
    <link rel="stylesheet" href="assets/style.css">
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
        <div class="box">
            <p>ANTRIAN SEDANG BERJALAN</p>
            <h2 style="font-size: 200px; margin-top: -100px; margin-bottom: 0px;">A<?php echo $currentQueue; ?></h2>
            <small>Waktu : <?php echo $currentTime; ?></small>
        </div>
        <div class="box">
            <p>JUMLAH ANTRIAN</p>
            <h2 style="font-size: 200px; margin-top: -100px;">A<?php echo $totalQueues; ?></h2>
        </div>
    </div>

    <form method="post" class="button-container">
        <button type="submit" class="btn btn-call-again" onclick="playSound('repeat', '<?php echo $currentQueue; ?>')">
            Panggil Ulang Antrian A <?php echo $currentQueue; ?>
        </button>
        <button type="submit" name="next" class="btn btn-next" <?php echo ($nextQueue === null) ? "disabled" : ""; ?> onclick="playSound('next', '<?php echo $nextQueue; ?>')">
            <?php echo ($nextQueue !== null) ? "Panggil Antrian Berikutnya (No. $nextQueue)" : "Tidak Ada Antrian"; ?>
        </button>
    </form>

    <script>
        function playSound(type, number) {
            let text = "";
            if (type === "repeat") {
                text = `Antrian nomor A ${number}`;
            } else if (type === "next") {
                text = `Selanjutnya antrian nomor A ${number}`;
            }
            let utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = "id-ID";
            speechSynthesis.speak(utterance);
        };
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