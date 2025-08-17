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
<h4 style="background:#000000; color:#FFFFFF; padding:3px">ECG AND LAB EXAMINATION REPORT</h4>
<form action="?p_id=<?=$_GET['p_id']?>&p=3" method="POST">

<table id="physicals">

<input type="hidden" name="dateadded" value="<?=$name['dateadded']?>"/>
<tr>
<td>LAB NO.</td>
<td><input type="text" name="xray_no" value="<?=$update['xray_no']?>"/></td>

<td>CREATININE</td>
<td>
<select name="crea">
<option value="" <?php
if($update['crea']=="")
{ echo "selected='selected'";}
?>></option>

<option value="not requested" <?php
if($update['crea']=="not requested")
{ echo "selected='selected'";}
?>>NOT REQUESTED</option>
	
<option value="NORMAL" <?php
if($update['crea']=="NORMAL")
{ echo "selected='selected'";}
?>>NORMAL</option>
	<option value="ABNORMAL" <?php
if($update['crea']=="ABNORMAL")
{ echo "selected='selected'";}
?>>ABNORMAL</option>
</select>
</td>
</tr>

<tr>
<td>ECG REPORT</td>
<td>
<select name="egg_report">
<option value="" <?php
if($update['egg_report']=="")
{ echo "selected='selected'";}
?>></option>


<option value="not requested" <?php
if($update['egg_report']=="not requested")
{ echo "selected='selected'";}
?>>NOT REQUESTED</option>
	
<option value="WITHIN NORMAL LIMITS" <?php
if($update['egg_report']=="WITHIN NORMAL LIMITS")
{ echo "selected='selected'";}
?>>WITHIN NORMAL LIMITS</option>
	<option value="SIGNIFICANT FINDINGS" <?php
if($update['egg_report']=="SIGNIFICANT FINDINGS")
{ echo "selected='selected'";}
?>>SIGNIFICANT FINDINGS</option>

	
</select>
</td>
<td>HEMATOLOGY</td>
<td>
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



<tr>
<td>URINALYSIS</td>
<td>
<select name="urinalysis">
<option value="" <?php
if($update['urinalysis']=="")
{ echo "selected='selected'";}
?>></option>

<option value="not requested" <?php
if($update['urinalysis']=="not requested")
{ echo "selected='selected'";}
?>>NOT REQUESTED</option>
	
<option value="NORMAL" <?php
if($update['urinalysis']=="NORMAL")
{ echo "selected='selected'";}
?>>NORMAL</option>
	<option value="ABNORMAL" <?php
if($update['urinalysis']=="ABNORMAL")
{ echo "selected='selected'";}
?>>ABNORMAL</option>
</select>
</td>




<td>STOOL EXAMINATION</td>
<td>
<select name="stool_exam">
<option value="" <?php
if($update['stool_exam']=="")
{ echo "selected='selected'";}
?>></option>

<option value="not requested" <?php
if($update['stool_exam']=="not requested")
{ echo "selected='selected'";}
?>>NOT REQUESTED</option>
	
<option value="NO INTESTINAL PARASITE SEEN" <?php
if($update['stool_exam']=="NO INTESTINAL PARASITE SEEN")
{ echo "selected='selected'";}
?>>NO INTESTINAL PARASITE SEEN</option>
</select>
</td>
</tr>



<tr>
<td>VDRL</td>
<td>
<select name="vdrl">
<option value="" <?php
if($update['vdrl']=="")
{ echo "selected='selected'";}
?>></option>

<option value="not requested" <?php
if($update['vdrl']=="not requested")
{ echo "selected='selected'";}
?>>NOT REQUESTED</option>
	
<option value="nonreactive" <?php
if($update['vdrl']=="nonreactive")
{ echo "selected='selected'";}
?>>NONREACTIVE</option>
	<option value="reactive" <?php
if($update['vdrl']=="reactive")
{ echo "selected='selected'";}
?> style="color:red">REACTIVE</option>


	<option value="weakly reactive" <?php
if($update['vdrl']=="weakly reactive")
{ echo "selected='selected'";}
?>  style="color:red">WEAKLY REACTIVE</option>

</select>
</td>




<td>HBSAG</td>
<td>
<select name="hbsag">
<option value="" <?php
if($update['hbsag']=="")
{ echo "selected='selected'";}
?>></option>

<option value="not requested" <?php
if($update['hbsag']=="not requested")
{ echo "selected='selected'";}
?>>NOT REQUESTED</option>
	
<option value="nonreactive" <?php
if($update['hbsag']=="nonreactive")
{ echo "selected='selected'";}
?>>NONREACTIVE</option>
	<option value="reactive" <?php
if($update['hbsag']=="reactive")
{ echo "selected='selected'";}
?>  style="color:red">REACTIVE</option>
</select>
</td>

<td>ELISA</td>
<td><input type="text" name="elisa" value="<?=$update['elisa']?>"/></td>

</tr>


<tr>

</tr>





<tr>
<td>HIV</td>
<td>
<select name="hiv">
<option value="" <?php
if($update['hiv']=="")
{ echo "selected='selected'";}
?>></option>

<option value="not requested" <?php
if($update['hiv']=="not requested")
{ echo "selected='selected'";}
?>>NOT REQUESTED</option>
	
<option value="nonreactive" <?php
if($update['hiv']=="nonreactive")
{ echo "selected='selected'";}
?>>NONREACTIVE</option>
	<option value="reactive" <?php
if($update['hiv']=="reactive")
{ echo "selected='selected'";}
?>  style="color:red">REACTIVE</option>
</select>

</td>
</tr>

<tr>
<td>HIV ELISA</td>
<td>
<input type="text" name="hiv_elisa" value="<?=$update['hiv_elisa']?>"/>
</td>
</tr>


<tr>
<td>BLOOD TYPE</td>
<td>
<select name="blood_type">
<option value="" <?php
if($update['blood_type']=="")
{ echo "selected='selected'";}
?>></option>

<option value="A" <?php
if($update['blood_type']=="A")
{ echo "selected='selected'";}
?>>A</option>
<option value="B" <?php
if($update['blood_type']=="B")
{ echo "selected='selected'";}
?>>B</option>

<option value="O" <?php
if($update['blood_type']=="O")
{ echo "selected='selected'";}
?>>O</option>

<option value="AB" <?php
if($update['blood_type']=="AB")
{ echo "selected='selected'";}
?>>AB</option>
</select>
</td>
<td>PREGNANCY TEST</td>
<td>
<select name="pt">
<option value="" <?php
if($update['pt']=="")
{ echo "selected='selected'";}
?>></option>

<option value="not requested" <?php
if($update['pt']=="not requested")
{ echo "selected='selected'";}
?>>NOT REQUESTED</option>
<option value="POSITIVE" <?php
if($update['pt']=="POSITIVE")
{ echo "selected='selected'";}
?>  style="color:red">POSITIVE</option>

<option value="NEGATIVE" <?php
if($update['pt']=="NEGATIVE")
{ echo "selected='selected'";}
?>>NEGATIVE</option>
</select>
</td>

<td>LMP</td>
<td><input type="date" name="ptdate" value="<?=$update['ptdate']?>"/>
<P></P>
LSC
<input type="date" name="pt_lsc" value="<?=$update['pt_lsc']?>"/>
</td>



</tr>




<tr>
<td>Anti HCV</td>
<td>
<select name="hcv">
<option value="" <?php
if($update['hcv']=="")
{ echo "selected='selected'";}
?>></option>

<option value="not requested" <?php
if($update['hcv']=="not requested")
{ echo "selected='selected'";}
?>>NOT REQUESTED</option>
	
<option value="nonreactive" <?php
if($update['hcv']=="nonreactive")
{ echo "selected='selected'";}
?>>NONREACTIVE</option>
	<option value="reactive" <?php
if($update['hcv']=="reactive")
{ echo "selected='selected'";}
?>  style="color:red">REACTIVE</option>
</select>
</td>

<td>TPHA</td>
<td>
<select name="tpha">
<option value="" <?php
if($update['tpha']=="")
{ echo "selected='selected'";}
?>></option>

<option value="not requested" <?php
if($update['tpha']=="not requested")
{ echo "selected='selected'";}
?>>NOT REQUESTED</option>
	
<option value="nonreactive" <?php
if($update['tpha']=="nonreactive")
{ echo "selected='selected'";}
?>>NONREACTIVE</option>
	<option value="reactive" <?php
if($update['tpha']=="reactive")
{ echo "selected='selected'";}
?>  style="color:red">REACTIVE</option>
</select>
</td>


</tr>



<tr>
<td>SGOT</td>
<td>
<select name="sgot">
<option value="" <?php
if($update['sgot']=="")
{ echo "selected='selected'";}
?>></option>

<option value="not requested" <?php
if($update['sgot']=="not requested")
{ echo "selected='selected'";}
?>>NOT REQUESTED</option>
	
<option value="NORMAL" <?php
if($update['sgot']=="NORMAL")
{ echo "selected='selected'";}
?>>NORMAL</option>
	<option value="ABNORMAL" <?php
if($update['sgot']=="ABNORMAL")
{ echo "selected='selected'";}
?>>ABNORMAL</option>
</select>
</td>

<td>SGPT</td>
<td>
<select name="sgpt">
<option value="" <?php
if($update['sgpt']=="")
{ echo "selected='selected'";}
?>></option>

<option value="not requested" <?php
if($update['sgpt']=="not requested")
{ echo "selected='selected'";}
?>>NOT REQUESTED</option>
	
<option value="NORMAL" <?php
if($update['sgpt']=="NORMAL")
{ echo "selected='selected'";}
?>>NORMAL</option>
	<option value="ABNORMAL" <?php
if($update['sgpt']=="ABNORMAL")
{ echo "selected='selected'";}
?>>ABNORMAL</option>
</select>
</td>
</tr>




<tr>
<td>ALKALINE PHOSPHATE</td>
<td>
<select name="alka">
<option value="" <?php
if($update['alka']=="")
{ echo "selected='selected'";}
?>></option>

<option value="not requested" <?php
if($update['alka']=="not requested")
{ echo "selected='selected'";}
?>>NOT REQUESTED</option>
	
<option value="NORMAL" <?php
if($update['alka']=="NORMAL")
{ echo "selected='selected'";}
?>>NORMAL</option>
	<option value="ABNORMAL" <?php
if($update['alka']=="ABNORMAL")
{ echo "selected='selected'";}
?>>ABNORMAL</option>
</select>
</td>

<td>FBS</td>
<td>
<select name="fbs">
<option value="" <?php
if($update['fbs']=="")
{ echo "selected='selected'";}
?>></option>

<option value="not requested" <?php
if($update['fbs']=="not requested")
{ echo "selected='selected'";}
?>>NOT REQUESTED</option>
	
<option value="NORMAL" <?php
if($update['fbs']=="NORMAL")
{ echo "selected='selected'";}
?>>NORMAL</option>
	<option value="ABNORMAL" <?php
if($update['fbs']=="ABNORMAL")
{ echo "selected='selected'";}
?>>ABNORMAL</option>
</select>
</td>
</tr>




<tr>
<td>BUN</td>
<td>
<select name="bun">
<option value="" <?php
if($update['bun']=="")
{ echo "selected='selected'";}
?>></option>

<option value="not requested" <?php
if($update['bun']=="not requested")
{ echo "selected='selected'";}
?>>NOT REQUESTED</option>
	
<option value="NORMAL" <?php
if($update['bun']=="NORMAL")
{ echo "selected='selected'";}
?>>NORMAL</option>
	<option value="ABNORMAL" <?php
if($update['bun']=="ABNORMAL")
{ echo "selected='selected'";}
?>>ABNORMAL</option>
</select>
</td>


<td>DRUG TEST</td>
<td>
<select name="drug">
<option value="" <?php
if($update['drug']=="")
{ echo "selected='selected'";}
?>></option>

<option value="not requested" <?php
if($update['drug']=="not requested")
{ echo "selected='selected'";}
?>>NOT REQUESTED</option>
<option value="POSITIVE" <?php
if($update['drug']=="POSITIVE")
{ echo "selected='selected'";}
?>  style="color:red">POSITIVE</option>

<option value="NEGATIVE" <?php
if($update['drug']=="NEGATIVE")
{ echo "selected='selected'";}
?>>NEGATIVE</option>
</select>
</td>


</tr>


















<td colspan="1"></td>
<td>
<input type="HIDDEN" name="p_id" value="<?=$_GET['p_id']?>" />
</tr>
</table>
<?php if($name['print_status']!='Printed') { ?>
<input type="submit" id="button" name="lab" value="SAVE" style="margin-left:600px; font-size:16px; text-transform:uppercase"></td>
<?php } ?>
	
</form>

<div style="height:10px; clear:both"></div>
<?php 
include 'physical.php';
?>
