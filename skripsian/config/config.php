<?php
date_default_timezone_set('Asia/Makassar');

// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "skripsian";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}