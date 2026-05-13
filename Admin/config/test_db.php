<?php
$conn = mysqli_connect("127.0.0.1", "root", "Monoia_mikaelson23", "usep_epark");

if (!$conn) {
    echo "FAILED: " . mysqli_connect_error();
} else {
    echo "Connected successfully!";
}
?>