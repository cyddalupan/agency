<?php
$con = mysql_connect("localhost","iwebphil_demo","adonis1322");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
mysql_select_db("iwebphil_demomed", $con);
?>


