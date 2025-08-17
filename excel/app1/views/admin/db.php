<?php
$con = mysql_connect("localhost","injazman_injaz88","gbKtAK6yO8$k");
mysql_set_charset ( "latin1_swedish_ci", $con );
if (!$con)
{
die('Could not connect: ' . mysql_error());
}
mysql_select_db("injazman_jobby", $con);


?>




