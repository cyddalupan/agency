<?php
session_start();
$_SESSION['hotel']=NULL; 
unset($_SESSION['hotel']); 
session_destroy();
header("location:login.php");
 exit;
?>