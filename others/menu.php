<style>

ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
  overflow: hidden;
  background-color: #8B0000;
WIDTH:100%;
}

li {
  float: left;
  border-right:1px solid #bbb;
}

li:last-child {
  border-right: none;
}

li a {
  display: block;
  color: white;
  text-align: center;
  padding: 4px 6px;
  text-decoration: none;
}

li a:hover:not(.active) {
  color:white;
   font-weight:bold;
}

.active {
  background-color: #87CEFA;
  color:black;
  font-weight:bold;
}
.pmd-card input{
width:180px;
padding:3px;
border:1px solid black;	
}
</style>





<?php
$ip2= gethostbyaddr($_SERVER['REMOTE_ADDR']);
$timezone = "Asia/Kuala_Lumpur";
if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);
$date_now=date('Y-m-d');
?>
<a href="alldh.php" style="border:1px solid black;padding:10px;color:black;margin:10px;FONT-size:20px;position:absolute;float:left;BACKGROUND:lightblue">All DH</a>
<a href="allskilled.php" style="border:1px solid black;padding:10px;color:black;
margin:10px;FONT-size:20px;position:absolute;float:left;BACKGROUND:lightblue;margin-left:100px">All Skilled</a>

<a href="passporting.php" style="border:1px solid black;padding:10px;color:black;margin:10px;FONT-size:20px;position:absolute;float:left;BACKGROUND:lightblue;margin-left:220px">PASSPORTING</a>

<a href="allno-vaccine.php" style="border:1px solid black;padding:10px;color:black;margin:10px;FONT-size:20px;position:absolute;float:left;BACKGROUND:lightblue;margin-left:390px">NO VACCINE</a>
<a href="local.php" style="border:1px solid black;padding:10px;color:black;margin:10px;FONT-size:20px;position:absolute;float:left;BACKGROUND:LIGHTBLUE;margin-left:540px">Local Flight</a>
<a href="wailtlist.php" style="border:1px solid black;padding:10px;color:black;margin:10px;FONT-size:20px;position:absolute;float:left;BACKGROUND:LIGHTBLUE;margin-left:680px">Hold</a>


<a href="reportsnew.php" style="border:3px solid black;padding:5px;color:red;margin:10px;FONT-size:28px;float:left;margin-left:10px;margin-left:1150px">GENERATE REPORTS</a>
