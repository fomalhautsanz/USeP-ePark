<?php

$host = "127.0.0.1";
$user = "root";
$password = "";
$dbname = "usep_epark";

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

?>  