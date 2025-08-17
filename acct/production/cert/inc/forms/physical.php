<style>
#physicals{padding:0; margin:0; margin-top:-10px}
#physicals td{padding:2px;padding-left:10px;font-size:12px;}
select{ font-size:12px; margin-right:30px;width:150px}
input{ padding:2px; font-size:12px}
#far{float:left;}
</style>
<head>
<?php include'../scripts/check_editor.php';?>
</head>
<?php 
//include '../inc/functions.php';
$apps = mysql_query("SELECT * FROM p_information where p_id=".$_GET['p_id']);
$name = mysql_fetch_array($apps);
$appss = mysql_query("SELECT * FROM physical_2 where p_id=".$_GET['p_id']);
$update = mysql_fetch_array($appss);
 $check="checked";
 $checkf="";
?>
<div style="width:850px;margin-top:-20px;">
<?php /*?><h3 style="font-weight:bold;text-transform:capitalize">
PATIENT ID : <?=$_GET['p_id']?>    |  
PATIENT NAME : <?=$name['lname']?> ,  <?=$name['fname']?>  <?=$name['mname']?> | 
DATE REGISTERD : <?=$name['dateadded']?> </h3><?php */?>

<h4 style="background:#000000; color:#FFFFFF; padding:3px"> EXAMINATION REPORT</h4>
<form action="?p_id=<?=$_GET['p_id']?>&p=3" method="POST">

<div style="float:left;width:800px;margin-left:10px">
<div style="width:40%;float:left">VISUAL ACUITY MEETS STANDARDS
<select name="withs">
<option value="YES" <?php
if($update['withs']=="YES")
{ echo "selected='selected'";}
?>>YES</option>

<option value="NO" <?php
if($update['withs']=="NO")
{ echo "selected='selected'";}
?>>NO</option>

</select>
</div>
<div style="width:30%;float:left">
SATISFACTORY HEARING
<select name="sat_hearing">
<option value="NOT APPLICABLE" <?php
if($update['sat_hearing']=="NOT APPLICABLE")
{ echo "selected='selected'";}
?>>NOT APPLICABLE</option>


<option value="YES" <?php
if($update['sat_hearing']=="YES")
{ echo "selected='selected'";}
?>>YES</option>

<option value="NO" <?php
if($update['sat_hearing']=="NO")
{ echo "selected='selected'";}
?>>NO</option>
</select>

</div>

<div style="width:30%;float:left">COLOR VISION
<select name="color_v">

<option value="" <?php
if($update['color_v']=="")
{ echo "selected='selected'";}
?>>***</option>

<option value="ADEQUATE" <?php
if($update['color_v']=="ADEQUATE")
{ echo "selected='selected'";}
?>>ADEQUATE</option>

<option value="DEFECIENT" <?php
if($update['color_v']=="DEFECIENT")
{ echo "selected='selected'";}
?>>DEFECIENT</option>
</select>
</div>

<div style="width:100%;float:left;font-size:11px;margin:10px">

IS APPLICANT SUFFERING FROM ANY MEDICAL CONDITION LIKELY TO BE AGGRAVETED BY LANDBASED OVERSEAS WORK OR TO RENDER THE 
APPLICANT SUCH SERVICE OR TO ENDANGER THE HEALTH OF OTHER PERSONS ON BOARD?
	<input type = "radio"
	 name = "med_con"
	 value = "YES" id="yes" <?php if($update['med_con']=='YES'){ echo "checked=".$check;}else{}?>/>
	Yes
	  <input type = "radio"
	 name = "med_con"
	 value = "NO" id="no"  <?php if($update['med_con']=='NO'){ echo "checked=".$check;}else{}?>/>
	 No
	  
	  
</div><br>
<hr>




<p style="color:red">WITHOUT GLASSES</p>
FAR VISION OD 20/
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    FAR VISION OS 20/
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
NEAR VISION OD 20/
&nbsp;&nbsp;&nbsp;&nbsp;
NEAR VISION OS 20/
<br>
<div id="far"><input type="text" name="far_v" value="<?=$update['far_v']?>"/></div>
<div id="far"><input type="text" name="far_v2" value="<?=$update['far_v2']?>"/></div>
<div id="far" style="margin-left:50px">
<input type="text" name="near_v" value="<?=$update['near_v']?>"/></div>
<div id="far"><input type="text" name="near_v2" value="<?=$update['near_v2']?>"/></div>
</div>


<div style="float:left;width:800px;margin-left:10px">
<p style="color:red">WITH GLASSES</p>
FAR VISION OD 20/
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    FAR VISION OS 20/
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
NEAR VISION OD 20/
&nbsp;&nbsp;&nbsp;&nbsp;
NEAR VISION OS 20/
<br>
<div id="far"><input type="text" name="f1" value="<?=$update['f1']?>"/></div>
<div id="far"><input type="text" name="f2" value="<?=$update['f2']?>"/></div>
<div id="far" style="margin-left:50px">
<input type="text" name="n1" value="<?=$update['n1']?>"/></div>
<div id="far"><input type="text" name="n2" value="<?=$update['n2']?>"/></div>
<br>
</div>


<table id="physicals">
<tr>
<td>HEIGHT</td>
<td><input type="text" name="height" value="<?=$update['height']?>"/></td>
<td>WEIGHT</td>
<td><input type="text" name="weight" value="<?=$update['weight']?>"/></td>
<td>BLOOD PRESSURE</td>
<td><input type="text" name="blood_p" value="<?=$update['blood_p']?>"/></td>
<td>
<tr>

<tr>
<td>SYSTOLIC</td>
<td><input type="text" name="systolic" value="<?=$update['systolic']?>"/></td>
<td>DIASTOLIC</td>
<td><input type="text" name="diastolic" value="<?=$update['diastolic']?>"/></td>
<tr>



<td>PULSE RATE/MIN</td>
<td><input type="text" name="pulse" value="<?=$update['pulse']?>"/></td>
<td>RESPIRATION</td>
<td><input type="text" name="respiration" value="<?=$update['respiration']?>"/></td>
</tr>
</table>



</br>
<table id="physicals">

<tr>

<td>HEARING RT</td>
<td>
<select name="hearing">
<option value="" <?php
if($update['hearing']=="")
{ echo "selected='selected'";}
?>></option>




<option value="NORMAL" <?php
if($update['hearing']=="NORMAL")
{ echo "selected='selected'";}
?>>NORMAL</option>

<option value="MIND LOSS" <?php
if($update['hearing']=="MIND LOSS")
{ echo "selected='selected'";}
?>>MIND LOSS</option>

<option value="MODERATE LOSS" <?php
if($update['hearing']=="MODERATE LOSS")
{ echo "selected='selected'";}
?>>MODERATE LOSS</option>

<option value="SEVERE LOSE" <?php
if($update['hearing']=="SEVERE LOSE")
{ echo "selected='selected'";}
?>>SEVERE LOSE</option>

<option value="PROFOUND LOSS" <?php
if($update['hearing']=="PROFOUND LOSS")
{ echo "selected='selected'";}
?>>PROFOUND LOSS</option>

	
</select>

</td>
<td>HEARING LF</td>
<td>
<select name="hearing1">
<option value="" <?php
if($update['hearing1']=="")
{ echo "selected='selected'";}
?>></option>

<option value="NORMAL" <?php
if($update['hearing1']=="NORMAL")
{ echo "selected='selected'";}
?>>NORMAL</option>

<option value="MIND LOSS" <?php
if($update['hearing1']=="MIND LOSS")
{ echo "selected='selected'";}
?>>MIND LOSS</option>

<option value="MODERATE LOSS" <?php
if($update['hearing1']=="MODERATE LOSS")
{ echo "selected='selected'";}
?>>MODERATE LOSS</option>

<option value="SEVERE LOSE" <?php
if($update['hearing1']=="SEVERE LOSE")
{ echo "selected='selected'";}
?>>SEVERE LOSE</option>

<option value="PROFOUND LOSS" <?php
if($update['hearing1']=="PROFOUND LOSS")
{ echo "selected='selected'";}
?>>PROFOUND LOSS</option>

</select>

<td>BODY BUILT</td>
<td>
<input type="text" name="body_w" value="<?=$update['body_w']?>"/>
</td>


</tr>
<tr>


<td>URINE GLUCOSE</td>
<td><input type="text" name="u_glucose" value="<?=$update['u_glucose']?>"/></td>
<td>URINE,PROTEIN</td>
<td><input type="text" name="u_protein" value="<?=$update['u_protein']?>"/></td>

</tr>
<tr>
<td>URINE BLOOD</td>
<td><input type="text" name="u_blood" value="<?=$update['u_blood']?>"/></td>
<td>SGPT</td>
<td><input type="text" name="l_got" value="<?=$update['l_got']?>"/></td>
<td>SGOT</td>
<td><input type="text" name="l_opt" value="<?=$update['l_opt']?>"/></td>

</tr>
<tr>
</td>

<TR>
<td>CHOLESTEROL</td>
<td><input type="text" name="cholesterol" value="<?=$update['cholesterol']?>"/></td>

<td>PYSCHO TEST</td>
<td><input type="text" name="pychotest" value="<?=$update['pychotest']?>"/></t
</tr>

<tr>
<td>
<input type="HIDDEN" name="p_id" value="<?=$_GET['p_id']?>"/>
</tr>
</table>
<?php if($name['print_status']!='Printed') { ?>
	<input type="submit" id="button" name="physical2" value="SAVE" style="float:right; font-size:16px; text-transform:uppercase"/></td>
<?php } ?>
</form><div style="clear:both;"></div>