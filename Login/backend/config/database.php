<?php

$host = "127.0.0.1";
$user = "root";
$password = "Monoia_mikaelson23";
$dbname = "usep_epark";

$conn = mysqli_connect($host,$user,$password,$dbname);

if(!$conn){
    die("Database connection failed");
}

?>