<?php
// will be used soon 
// NAGAMIT NAJUD - MAY 13, 2026 
session_start();
session_unset();
session_destroy();
header('Location: ../../../Login/login.html');
exit;
?>