
<style>
	#formclass input{ width:70px;padding:3px;}
	#formclass select{ width:270px;padding:3px;}
</style>
<div style="width:900px;" id="formclass">
<?php
include 'db.php';
$apps = mysql_query("SELECT * FROM audio where p_id=".$_GET['p_id']);
$update = mysql_fetch_array($apps);

?>

<form action="patients.php" method="POST">

<h3>Audio Examination - <?=$update['p_id']?></h3> 
<hr>
<table style="width:400px;float:left;border:1;">

<tr>
<td>AIR</td>
<td></td><td></td>
<td>500</td>
<td>750</td>
<td>1000</td>
<td>1500</td>
<td>2000</td>
<td>3000</td>
<td>4000</td>
<td>6000</td>
<td>8000</td>
</tr>
<tr>
<td>R.E</td>
<td></td><td></td>
<td><input type="text" name="re8" value="<?=$update['re8']?>"/></td>
<td><input type="text" name="re1" value="<?=$update['re1']?>"/></td>
<td><input type="text" name="re2" value="<?=$update['re2']?>"/></td>
<td><input type="text" name="re3" value="<?=$update['re3']?>"/></td>
<td><input type="text" name="re4" value="<?=$update['re4']?>"/></td>
<td><input type="text" name="re5" value="<?=$update['re5']?>"/></td>
<td><input type="text" name="re6" value="<?=$update['re6']?>"/></td>
<td><input type="text" name="re7" value="<?=$update['re7']?>"/></td>
<td><input type="text" name="re9" value="<?=$update['re9']?>"/></td>
</tr>

<tr>
<td>L.E</td>
<td></td><td></td>
<td><input type="text" name="le8" value="<?=$update['le8']?>"/></td>
<td><input type="text" name="le1" value="<?=$update['le1']?>"/></td>
<td><input type="text" name="le2" value="<?=$update['le2']?>"/></td>
<td><input type="text" name="le3" value="<?=$update['le3']?>"/></td>
<td><input type="text" name="le4" value="<?=$update['le4']?>"/></td>
<td><input type="text" name="le5" value="<?=$update['le5']?>"/></td>
<td><input type="text" name="le6" value="<?=$update['le6']?>"/></td>
<td><input type="text" name="le7" value="<?=$update['le7']?>"/></td>
<td><input type="text" name="le9" value="<?=$update['le9']?>"/></td>
</tr>
<tr>
<td>REEMARKS</td>
<td></td><td></td>
<td colspan="20">
<select name="remarks">
<option value="">----</option>
<option value="Bilateral Normal Hearing Acuity">Bilateral Normal Hearing Acuity</option>
<option value="Bilateral Mild Hearing Loss">Bilateral Mild Hearing Loss</option>
<option value="Left Ear= Normal, Right Ear=Mild Hearing Loss">Left Ear= Normal, Right Ear=Mild Hearing Loss</option>
<option value="Left Ear= Mild Hearing Loss;Right Ear = Normal ">Left Ear= Mild Hearing Loss;Right Ear = Normal </option>
<option value="Bilateral Moderate  Hearing Loss">Bilateral Moderate  Hearing Loss</option>
</select>
</td>
<br>
</tr>
<td colspan="20">
<td>
<input type="HIDDEN" name="p_id" value="<?=$_GET['p_id']?>"/>
<input type="submit" id="button" name="audios" value="SAVE"/></td>
</tr>
</table>

</form>

</div>

<div style="clear:both;height:10px;"></div>

