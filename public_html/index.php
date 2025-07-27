<?php
header("Cache-Control: no-cache");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); 
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'main/home.php'; 
?>
