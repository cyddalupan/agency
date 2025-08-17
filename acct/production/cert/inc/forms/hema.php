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
<h4 style="background:#000000; color:#FFFFFF; padding:10px">HEMATOLOGY</h4>
</br>
<form action="?p_id=<?=$_GET['p_id']?>&p=6" method="POST">

<table id="physicals">

<input type="hidden" name="dateadded" value="<?=$name['dateadded']?>"/>
<tr>

<td>
Result</br>
<select name="hematology">
<option value="" <?php
if($update['hematology']=="")
{ echo "selected='selected'";}
?>></option>


<option value="not requested" <?php
if($update['hematology']=="not requested")
{ echo "selected='selected'";}
?>>NOT REQUESTED</option>
	
<option value="NORMAL" <?php
if($update['hematology']=="NORMAL")
{ echo "selected='selected'";}
?>>NORMAL</option>
	<option value="ABNORMAL" <?php
if($update['hematology']=="ABNORMAL")
{ echo "selected='selected'";}
?>>ABNORMAL</option>
</select>
</td>
<td>hemoglobin<input type="text" name="hemoglobin" value="<?=$update['hemoglobin']?>"/></td>
</tr>


</tr>
<td>Hemotocrit<input type="text" name="hemotocrit" value="<?=$update['hemotocrit']?>"/></td>
<td>Erythocyte<input type="text" name="erythocyte" value="<?=$update['erythocyte']?>"/></td>
<td>Leucoyte<input type="text" name="leucoyte" value="<?=$update['leucoyte']?>"/></td>	
</TR>

<tr>
<td colspan="3"><h4 style="background:#000000; color:#FFFFFF; padding:10px">Differential Count</h4></td>
</tr>

</tr>
<td>Neutrophils<input type="text" name="neutrophils" value="<?=$update['neutrophils']?>"/></td>
<td>Lymphocytes<input type="text" name="lymphocytes" value="<?=$update['lymphocytes']?>"/></td>
<td>Eosinophis<input type="text" name="eosinophis" value="<?=$update['eosinophis']?>"/></td>	
</TR>

</tr>
<td>Monocytes<input type="text" name="monocytes" value="<?=$update['monocytes']?>"/></td>
<td>Basophils<input type="text" name="basophils" value="<?=$update['basophils']?>"/></td>
</TR>













<td colspan="1"></td>
<td>
<input type="HIDDEN" name="p_id" value="<?=$_GET['p_id']?>" />
</tr>
</table>
</br>
<?php if($name['print_status']!='Printed') { ?>
<input type="submit" id="button" name="lab" value="SAVE" style="margin-left:480px; font-size:16px; text-transform:uppercase"></td>
<?php } ?>
	
</form>

<div style="height:10px; clear:both"></div>
