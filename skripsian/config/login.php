<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Antrian</title>
    <link rel="stylesheet" href="../assets/login.css">
    <link rel="shortcut icon" href="../assets/img/ikon.png" type="image/x-icon">
</head>

<body>
    <div class="container">
        <img src="../assets/img/logo.png" alt="Logo" class="logo">
        <form action="" method="post" role="form">
            <div class="input-group">
                <label for="user">Username</label>
                <input type="text" name="user" id="user" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" name="submit" class="button">Login</button>
        </form>
        <a href="" class="forgot" onclick="forgotPassword()">Lupa Password?</a>
        <?php
        session_start();
        include("config.php");

        if (isset($_POST['submit'])) {
            $User = $_POST['user'];
            $Password = $_POST['password'];
        
            $query_sql = "SELECT * FROM admin WHERE user = '$User' AND password = '$Password'";
            $result = mysqli_query($conn, $query_sql);
        
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $_SESSION['user_id'] = $row['id'];  // pastikan 'id' ada di database
                $_SESSION['user'] = $row['user'];
                header("Location: ../dashboard.php");
            } else {
                echo "<script>alert('Anda Gagal Login');window.location='login.php';</script>";
            }
        }
        ?>
    </div>
    <script>
        function forgotPassword() {
            var phoneNumber = "6282358775832";
            var message = "Halo Admin, saya dari operator Puskesmas Kampung Samburakat. Saat ini kami tidak bisa login karena lupa password. Mohon bantuannya untuk reset atau informasi login kembali ya. Terima kasih sebelumnya.";
            var encodedMessage = encodeURIComponent(message);
            var waLink = "https://wa.me/" + phoneNumber + "?text=" + encodedMessage;
            var userResponse = confirm("Silakan hubungi admin di WhatsApp: " + phoneNumber + "\nKlik 'OK' untuk chat langsung.");
            if (userResponse) {
                window.location.href = waLink;
            }
        }
    </script>
</body>

</html>