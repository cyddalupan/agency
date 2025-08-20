<?php
require_once(__DIR__ . '/../config.php');
$con=mysql_connect(DB_HOST,DB_USER,DB_PASS)or die ('cannot connect');
mysql_select_db(DB_NAME,$con);
?>
