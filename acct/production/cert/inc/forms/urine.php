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
$select_feca = mysql_query("SELECT * FROM urine where p_id=".$_GET['p_id']);
$urine = mysql_fetch_array($select_feca);
?>
<div style="width:850px;margin-top:-20px;">
<h3 style="font-weight:bold;text-transform:capitalize">
PATIENT ID : <?=$_GET['p_id']?>    |  
PATIENT NAME : <?=$name['lname']?> ,  <?=$name['fname']?>  <?=$name['mname']?> | 
DATE REGISTERD : <?=$name['dateadded']?> </h3>

<form action="?p_id=<?=$_GET['p_id']?>&p=8" method="POST">

<table id="physicals">
<tr>
<td colspan="3"><h4 style="background:#000000; color:#FFFFFF; padding:10px">URINALYSIS</h4></td>
</tr>


<tr>
<td>Color<input type="text" name="Color" value="<?=$urine['Color']?>"/></td>
<td>Transparency<input type="text" name="Transparency" value="<?=$urine['Transparency']?>"/></td>
<td>pH<input type="text" name="pH" value="<?=$urine['pH']?>"/></td>	
</TR>

<tr>
<td>Sp. Gravity<input type="text" name="Sp" value="<?=$urine['Sp']?>"/></td>
<td>Sugar<input type="text" name="Sugar" value="<?=$urine['Sugar']?>"></td>
<td>Albumin<input type="text" name="Albumin" value="<?=$urine['pH']?>"></td>
</TR>

<tr>
<td>Bile<input type="text" name="Bile" value="<?=$urine['Bile']?>"></td>
<td>Urgobilinogen<input type="text" name="Urgobilinogen" value="<?=$urine['Urgobilinogen']?>"></td>
<td>Acetone<input type="text" name="Acetone" value="<?=$urine['Acetone']?>"></td>	
</TR>

<tr>
<td>Blood<input type="text" name="Blood" value="<?=$urine['Blood']?>"></td>
<td>Leucocytes<input type="text" name="Leucocytes" value="<?=$urine['Leucocytes']?>"></td>
<td>Nitrate<input type="text" name="Nitrate" value="<?=$urine['Nitrate']?>"></td>	
</TR>

<tr>
<td>Ketones<input type="text" name="Ketones" value="<?=$urine['Ketones']?>"></td>
</TR>

<tr>
<td colspan="3"><h4 style="background:#000000; color:#FFFFFF; padding:10px">Microscopic</h4></td>
</tr>

<tr>
<td>RBC<input type="text" name="RBC" value="<?=$urine['RBC']?>"/></td>
<td>Pus Cells<input type="text" name="Pus" value="<?=$urine['Pus']?>"/></td>
<td>Casts<input type="text" name="Casts" value="<?=$Casts['Acetone']?>"/></td>	
</TR>


<tr>
<td>A.Urates/Phosphate<input type="text" name="Phosphate" value="<?=$urine['Phosphate']?>"></td>
<td>Epithelial Cells<input type="text" name="Epithelial" value="<?=$urine['Epithelial']?>"></td>
<td>Mocus Threads<input type="text" name="Mocus" value="<?=$urine['Mocus']?>"></td>
</TR>

<tr>
<td>Bacteria<input type="text" name="Bacteria" value="<?=$urine['Bacteria']?>"></td>
<td>Renal Cells<input type="text" name="Renal" value="<?=$urine['Renal']?>"></td>
</TR>


<td colspan="1"></td>
<td>
<input type="HIDDEN" name="p_id" value="<?=$_GET['p_id']?>" />
</tr>
</table>

Others:
</br>
<textarea name="Others" cols="60" rows="5"><?=$urine['Others']?></textarea>


</br>
<?php if($name['print_status']!='Printed') { ?>
<input type="submit" id="button" name="urine_submit" value="SAVE" style="margin-left:480px; font-size:16px; text-transform:uppercase"></td>

<?php } ?>	
</form>

<div style="height:10px; clear:both"></div>
