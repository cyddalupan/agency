<style>
#physicals{padding:0; margin:0; margin-top:-10px}
#physicals td{padding-left:10px;font-size:12px;}
select{ font-size:12px; margin-right:30px;WIDTH:150PX}
input{font-size:12px}
</style>
<?php 
include '../inc/functions.php';
$apps = mysql_query("SELECT * FROM p_information where p_id=".$_GET['p_id']);
$name = mysql_fetch_array($apps);
$appss = mysql_query("SELECT * FROM xray_ecg_lab where p_id=".$_GET['p_id']);
$update = mysql_fetch_array($appss);
?>
<div style="width:850px;margin-top:-20px;">
<h3 style="font-weight:bold;text-transform:capitalize">
PATIENT ID : <?=$_GET['p_id']?>    |  
PATIENT NAME : <?=$name['lname']?> ,  <?=$name['fname']?>  <?=$name['mname']?> | 
DATE REGISTERD : <?=$name['dateadded']?> </h3>
<h4 style="background:#000000; color:#FFFFFF; padding:3px">X-RAY ,ECG AND LAB EXAMINATION REPORT</h4>
<form action="?p_id=<?=$_GET['p_id']?>&p=3" method="POST">
<?php include'../api.php';?>
	
</form>

<div style="height:10px; clear:both"></div>
