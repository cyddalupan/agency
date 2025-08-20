<?php
require_once(__DIR__ . '/../config.php');

$conn=mysqli_connect(DB_HOST,DB_USER,DB_PASS) or die("Could not connect");
mysqli_select_db($conn,DB_NAME) or die("could not connect database");
?>
