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
$select_feca = mysql_query("SELECT * FROM feca where p_id=".$_GET['p_id']);
$result_feca = mysql_fetch_array($select_feca);
?>
<div style="width:850px;margin-top:-20px;">
<h3 style="font-weight:bold;text-transform:capitalize">
PATIENT ID : <?=$_GET['p_id']?>    |  
PATIENT NAME : <?=$name['lname']?> ,  <?=$name['fname']?>  <?=$name['mname']?> | 
DATE REGISTERD : <?=$name['dateadded']?> </h3>
<h4 style="background:#000000; color:#FFFFFF; padding:10px">FECALYSIS</h4>
</br>
<form action="?p_id=<?=$_GET['p_id']?>&p=7" method="POST">

<table id="physicals">



<tr>
<td>Color<input type="text" name="feca_color" value="<?=$result_feca['feca_color']?>"/></td>
<td>Consistency<input type="text" name="consistency" value="<?=$result_feca['consistency']?>"/></td>
<td>Bacteria<input type="text" name="bacteria" value="<?=$result_feca['bacteria']?>"/></td>	
</TR>

<tr>
<td>RBC<input type="text" name="rbc" value="<?=$result_feca['rbc']?>"/></td>
<td>WBC<input type="text" name="wbc" value="<?=$result_feca['wbc']?>"/></td>
<td>Occult Blood<input type="text" name="occult" value="<?=$result_feca['occult']?>"/></td>	
</TR>

<tr>
<td>Result<input type="text" name="feca_result" value="<?=$result_feca['feca_result']?>"/></td>
</TR>









<td colspan="1"></td>
<td>
<input type="HIDDEN" name="p_id" value="<?=$_GET['p_id']?>" />
</tr>
</table>

Other:
</br>
<textarea name="feca_remarks" cols="60" rows="5"><?=$result_feca['feca_remarks']?></textarea>


</br>
<?php if($name['print_status']!='Printed') { ?>
<input type="submit" id="button" name="feca_submit" value="SAVE" style="margin-left:480px; font-size:16px; text-transform:uppercase"></td>
<?php } ?>
	
</form>

<div style="height:10px; clear:both"></div>
