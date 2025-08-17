<?php
session_start();

?>
<!doctype html>
  <script type="text/javascript" src="datepicker/public/javascript/jquery-1.8.2.js"></script>

    
        <script type="text/javascript" src="datepicker/public/javascript/zebra_datepicker.js"></script>
        <script type="text/javascript" src="datepicker/public/javascript/functions.js"></script>
 <link rel="stylesheet" href="datepicker/public/css/zebra_datepicker.css" type="text/css">


<style>
	#formclass input{ width:300px;padding:3px;}
	
</style>
<div style="width:750px;" id="formclass">
<?php
include 'db.php';
$apps = mysql_query("SELECT * FROM p_information where p_id=".$_GET['p_id']);
$update = mysql_fetch_array($apps);

?>
  <script type="text/javascript" src="datepicker/public/javascript/jquery-1.8.2.js"></script>
<script type="text/javascript" src="datepicker/public/javascript/zebra_datepicker.js"></script>
<script type="text/javascript" src="datepicker/public/javascript/functions.js"></script>
<form action="patients.php" method="POST">
<h3>New Patient Information - <?=$update['p_id']?></h3> 
<hr>
<table style="width:600px;float:left;border:0;">
<tr>
<td style="300px">Examination Date</td>
<td><input type="text"  name="dateadded" id="datepicker-example1" value="<?=$update['dateadded']?>"/></td></tr>
</tr>


<?php if($_SESSION['name']=='adonis') { ?>
<tr>
<td>Medical Type</td>
<td>
<select name="pack"  style="width:310px;padding:3px;" disable>
<option value="FULL MEDICAL" <?php
if($update['pack']=="FULL MEDICAL")
{ echo "selected='selected'";}
?>>FULL MEDICAL</option>

<option value="PT ONLY" <?php
if($update['pack']=="PT ONLY")
{ echo "selected='selected'";}
?>>PT ONLY</option>	

<option value="X RAY ONLY" <?php
if($update['pack']=="X RAY ONLY")
{ echo "selected='selected'";}
?>>X RAY ONLY</option>	


<option value="X RAY HBSAG" <?php
if($update['pack']=="X RAY HBSAG")
{ echo "selected='selected'";}
?>>X RAY HBSAG</option>	

<option value="VACCINE" <?php
if($update['pack']=="VACCINE")
{ echo "selected='selected'";}
?>>VACCINE</option>	

<option value="DRUG TEST" <?php
if($update['pack']=="DRUG TEST")
{ echo "selected='selected'";}
?>>DRUG TEST</option>	


<option value="HIV" <?php
if($update['pack']=="HIV")
{ echo "selected='selected'";}
?>>HIV</option>	


</select>

</td>
</tr>

<?php } ?>

<tr>
<td>TYPE OF WORK</td>
<td>
<select name="ofw_local" style="width:310px;padding:3px;" >
<option value="OFW" <?php
if($update['ofw_local']=="OFW")
{ echo "selected='selected'";}
?>>OFW</option>
	
<option value="LOCAL" <?php
if($update['ofw_local']=="LOCAL")
{ echo "selected='selected'";}
?>>LOCAL</option>

</select>

</td>
</tr>

<tr>
<td>Firts Name</td>
<td><input type="text" name="fname" value="<?=$update['fname'] ?>" required/></td></tr>
<tr>
<td>Last Name</td>
<td><input type="text" name="lname" value="<?=$update['lname']?>" required/></td></tr>
<tr>
<td>Middle Name</td>
<td><input type="text" name="mname" value="<?=$update['mname']?>" required/></td>
</tr>

<tr>
<td>Religion</td>
<td>
<input type="text" name="religion" value="<?=$update['religion']?>" required/>
</td>
</tr>

<tr>
<td>Passport #</td>
<td><input type="text" name="ppt" value="<?=$update['ppt']?>" /></td></tr>
<tr>

<tr>
<td>SEAMAN'S BOOK NO.</td>
<td><input type="text" name="seaman_book" value="<?=$update['seaman_book']?>" /></td>
</tr>



<td>Address</td>
<td>
<textarea name="address" style="width:300px; height:50px" ><?=$update['address']?></textarea>
</td>

</tr>
<tr>
<td>Birthday-<span style="color:#FF0000"> (Y-M-D)</span></td>
<td><input type="text" name="bday" value="<?=$update['bday']?>" style="width:159px" required/> 
</td>
</tr>
<tr>
<td>Age</td>
<td><input type="text" name="age" value="<?=$update['age']?>"   style="width:40px"/></td>
</tr>
<tr>
<td>Marital Staus</td>
<td>
<select name="marital" style="width:310px;padding:3px;" >
<option value="SINGLE" <?php
if($update['marital']=="SINGLE")
{ echo "selected='selected'";}
?>>SINGLE</option>
	
<option value="MARRIED" <?php
if($update['marital']=="MARRIED")
{ echo "selected='selected'";}
?>>MARRIED</option>
<option value="SEPARATED" <?php
if($update['marital']=="SEPARATED")
{ echo "selected='selected'";}
?>>SEPARATED</option>

<option value="WIDOWED" <?php
if($update['marital']=="WIDOWED")
{ echo "selected='selected'";}
?>>WIDOWED</option>


</select>

</td>
</tr>
<tr>
<td>Citizenship</td>
<td><input type="text" name="citizenship" value="<?=$update['citizenship']?>" /></td>
</tr>
<tr>
<td>Sex</td>
<td>
<select name="sex" style="width:310px;padding:3px;" >
<option value="MALE" <?php
if($update['sex']=="MALE")
{ echo "selected='selected'";}
?>>MALE</option>
	
<option value="FEMALE" <?php
if($update['sex']=="FEMALE")
{ echo "selected='FEMALE'";}
?>>FEMALE</option>

</select>
</td>
</tr>
<tr>
<td>Agency name</td>

<?php
$usertype = mysql_query("SELECT * FROM agency where name!='".$update['agency']."'
ORDER BY name asc");
?>
<td><select name="agency">
<OPTION value="<?=$update['agency']?>"><?=$update['agency']?></option>
<?php
while($rows=mysql_fetch_array($usertype))
{
echo'<option value="'.$rows["name"].'">'.$rows["name"].'</option>';
}
?>
</select>
</td>
</tr>
<tr>
<td>Country</td>
<td><input type="text" name="country" value="<?=$update['country']?>"/></td>
</tr>


<tr>
<td>Jobdescription</td>
<td><input type="text" name="jobdesc" value="<?=$update['jobdesc']?>"/></td>
</tr>



<tr>
<td>TYPE OF WORK</td>
<td>
<select name="type_work" style="width:310px;padding:3px;" >
<option value="DECK" <?php
if($update['type_work']=="DECK")
{ echo "selected='selected'";}
?>>DECK</option>
	
<option value="ENGINE" <?php
if($update['type_work']=="ENGINE")
{ echo "selected='selected'";}
?>>ENGINE</option>
<option value="STEWARD" <?php
if($update['type_work']=="STEWARD")
{ echo "selected='selected'";}
?>>STEWARD</option>

<option value="OTHERS" <?php
if($update['type_work']=="OTHERS")
{ echo "selected='selected'";}
?>>OTHERS</option>


</select>

</td>
</tr>



<tr>

<tr>
<td>Type</td>
<td>
<select name="status_app" style="width:310px;padding:3px;" >
<option value="LANDBASED" <?php
if($update['status_app']=="LANDBASED")
{ echo "selected='selected'";}
?>>LANDBASED</option>
	
<option value="SEABASED" <?php
if($update['status_app']=="SEABASED")
{ echo "selected='selected'";}
?>>SEABASED</option>

</select>

</td>
</tr>
<td colspan="1"></td>
<td>
<input type="hidden" name="p_id" value="<?=$update['p_id']?>"/>
<input type="submit" id="button" name="update_p" value="SAVE"/></td>
</tr>
</table>

</form>


</div>

<div style="clear:both;height:10px;"></div>

