<?php
$con = mysql_connect("localhost","property_marin","#@QC&gGHBYVL");
mysql_set_charset ( "latin1_swedish_ci", $con );
if (!$con)
{
die('Could not connect: ' . mysql_error());
}
mysql_select_db("property_scs1", $con);


?>




