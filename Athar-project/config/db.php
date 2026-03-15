<?php

$servername = "localhost";
$username   = "root";
$password   = "root";
$dbname     = "athar_db";

$conn = mysqli_connect($servername, $username, $password, $dbname, 8889);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

?>