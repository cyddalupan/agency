<!doctype html>
  <script type="text/javascript" src="datepicker/public/javascript/jquery-1.8.2.js"></script>

    
        <script type="text/javascript" src="datepicker/public/javascript/zebra_datepicker.js"></script>
        <script type="text/javascript" src="datepicker/public/javascript/functions.js"></script>
 <link rel="stylesheet" href="datepicker/public/css/zebra_datepicker.css" type="text/css">


<style>
	#formclass input{ width:100px;padding:3px;}
	
</style>
<div style="width:150px;" id="formclass">
<?php
include 'db.php';
$apps = mysql_query("SELECT p_id,print_status,age,bday FROM p_information where p_id=".$_GET['p_id']);
$update = mysql_fetch_array($apps);

?>
  <script type="text/javascript" src="datepicker/public/javascript/jquery-1.8.2.js"></script>
<script type="text/javascript" src="datepicker/public/javascript/zebra_datepicker.js"></script>
<script type="text/javascript" src="datepicker/public/javascript/functions.js"></script>
<form action="" method="POST">
<input type="hidden" name="bday" value="<?=$update['bday']?>" style="width:159px" required/> 
<input type="hidden" name="age" value="<?=$update['age']?>"  
<h3>New Patient Information - <?=$update['p_id']?></h3> 
<hr>
<table style="width:400px;float:left;border:0;">
<tr>
<td>Already Print? </td>
<td>

<select name="print_status" style="width:210px;padding:3px;" >
<option value="" <?php
if($update['print_status']=="")
{ echo "selected='selected'";}
?>></option>
	
<option value="Printed" <?php
if($update['print_status']=="Printed")
{ echo "selected='selected'";}
?>>Printed</option>

<option value="Others" <?php
if($update['print_status']=="Others")
{ echo "selected='selected'";}
?>>Others</option>
</select>

</td>
</tr>
<tr>
<td colspan="1"></td>
<td>
<input type="hidden" name="p_id" value="<?=$update['p_id']?>"/>
<input type="submit" id="button" name="update_p" value="SAVE"/></td>
</tr>
</table>

</form>


</div>

<div style="clear:both;height:10px;"></div>

