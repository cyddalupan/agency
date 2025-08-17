<?php
include'../tracking.php';
?>
<style>
#physicals{padding:0; margin:0; margin-top:10px;font-size:12px;}
#physicals td{padding:2px;padding-left:10px;font-size:12px;}
select{ font-size:12px; margin-right:30px}
input{ padding:2px; font-size:14px}

</style>
<head>
<?php include'../scripts/check_editor.php';?>
<script src="scripts/j.js"></script>
<script src="../scripts/j.js"></script>
</head>

<script>
$(document).ready(function(){
$("#hide").hide();
  $("#yes").click(function(){
    $("#hide").fadeOut();
  });
    $("#no").click(function(){
    $("#hide").fadeOut();
  });
    $("#res").click(function(){
    $("#hide").fadeIn();
  });
  
  
});
</script>
<?php 
include '../inc/functions.php';
$apps = mysql_query("SELECT * FROM p_information where p_id=".$_GET['p_id']);
$name = mysql_fetch_array($apps);
$appss = mysql_query("SELECT * FROM xray_ecg_lab where p_id=".$_GET['p_id']);
$update = mysql_fetch_array($appss);
 $check="checked";
 $checkf="";
?>
<div id ="physicals" style="width:850px;margin-top:-20px;">
<h3 style="font-weight:bold;text-transform:capitalize">
PATIENT ID : <?=$_GET['p_id']?>    |  
PATIENT NAME : <?=$name['lname']?> ,  <?=$name['fname']?>  <?=$name['mname']?> | 
DATE REGISTERD : <?=$name['dateadded']?> </h3>
<h4 style="background:#000000; color:#FFFFFF; padding:3px">REMARKS AND RECOMMENDATION</h4>
<form action="?p_id=<?=$_GET['p_id']?>&p=5" method="POST">
	<input type="hidden" name="stat" style="float:left" value="<?=$date1?>">


<h3 style="color:red">REMARKS</h3>

<input type = "radio"
	 name = "remarks"
	 value = "" id="yes" <?php if($name['remarks']==''){ echo "checked=".$check;}else{}?>/>
	BLANK

	<input type = "radio"
	 name = "remarks"
	 value = "fit to work" id="yes" <?php if($name['remarks']=='fit to work'){ echo "checked=".$check;}else{}?>/>
	FIT TO WORK
	  <input type = "radio"
	 name = "remarks"
	 value = "UNFIT FOR EMPLOYMENT" id="no"  <?php if($name['remarks']=='UNFIT FOR EMPLOYMENT'){ echo "checked=".$check;}else{}?>/>
	 UNFIT FOR EMPLOYMENT
	 	  <input type = "radio"
	 name = "remarks"
	 value = "FIT WITH RESTRICTION" id="res"  <?php if($name['remarks']=='FIT WITH RESTRICTION'){ echo "checked=".$check;}else{}?>/>
	 FIT WITH RESTRICTION
	 	  <input type = "radio"
	 name = "remarks"
	 value = "pending" id="res"  <?php if($name['remarks']=='pending'){ echo "checked=".$check;}else{}?>/>
	PENDING
<BR>	 
<div id="hide" style="float:right;border:0px solid black;width:400px;MARGIN-left:20px">	 
<input type="text" name="fit" placeholder="RESTRICTION" value="<?=$name['fit']?>"/>
</div>
	 
<br><br>	 
<h3 style="color:red">REMARKS/RECOMMENDTIONS</h3>
<textarea name="remarks2" cols="80" rows="7"><?=$name['remarks2']?></textarea>

<BR>
<input type="HIDDEN" name="p_id" value="<?=$_GET['p_id']?>"/>
<?php if($name['print_status']!='Printed') { ?>
<input type="submit" id="button" name="rem" value="SAVE"/></td>
<?php } ?>	
</form>
<div style="height:10px; clear:both"></div>



