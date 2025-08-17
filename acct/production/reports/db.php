<?php
$con = mysql_connect("localhost","abbaphil_jinhel","67PtE01S4KL4hKIqUQyoP2aKhDwud7BV");
mysql_set_charset ( "latin1_swedish_ci", $con );
if (!$con)
{
die('Could not connect: ' . mysql_error());
}
mysql_select_db("abbaphil_jinhel", $con);
?>
