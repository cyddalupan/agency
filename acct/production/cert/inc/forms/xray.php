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


<div style="height:10px; clear:both"></div>

<h4 style="background:#000000; color:#FFFFFF; padding:3px">X-RAY EXAMINATION</h4>

<form action="?p_id=<?=$_GET['p_id']?>&p=4" method="POST">

<table id="physicals">

<input type="hidden" name="dateadded" value="<?=$name['dateadded']?>"/>
<tr>
<td>X-RAY EXAM</td>
<td>
<select name="chest_exam">
<option value="" <?php
if($update['chest_exam']=="")
{ echo "selected='selected'";}
?>></option>

	<option value="not requested" <?php
if($update['chest_exam']=="not requested")
{ echo "selected='selected'";}
?>>NOT REQUESTED</option>
	
<option value="ESSENTIALLY NORMAL" <?php
if($update['chest_exam']=="ESSENTIALLY NORMAL")
{ echo "selected='selected'";}
?>>ESSENTIALLY NORMAL</option>
	
<option value="ESSENTIALLY NEGATIVE" <?php
if($update['chest_exam']=="ESSENTIALLY NEGATIVE")
{ echo "selected='selected'";}
?>>ESSENTIALLY NEGATIVE</option>

<option value="SIGNIFICANT FINDINGS" <?php
if($update['chest_exam']=="SIGNIFICANT FINDINGS")
{ echo "selected='selected'";}
?>>SIGNIFICANT FINDINGS</option>


<option value="PENDING" <?php
if($update['chest_exam']=="PENDING")
{ echo "selected='selected'";}
?>>PENDING</option>


</select>
</td>
<td>X-RAY REMARKS</td>
<td><input type="text" name="chest_desc" value="<?=$update['chest_desc']?>"
STYLE="padding:2px;width:150px;height:50px"/></td>

<td>
</tr>

<td colspan="1"></td>
<td>
<input type="HIDDEN" name="p_id" value="<?=$_GET['p_id']?>" />
</tr>
</table>
<?php if($name['print_status']!='Printed') { ?>
<input type="submit" id="button" name="xray" value="SAVE" style="margin-top:-40px;margin-left:580px; font-size:16px; text-transform:uppercase"></td>
<?php } ?>	
</form>
<form action="?p_id=<?=$_GET['p_id']?>&p=4" method="post">	
          <table width="100%" cellpadding="0" cellspacing="0" id="box-table-a" summary="Employee Pay Sheet">
            <thead>
              <tr>
                <th width="34" scope="col"></th>
                <th width="136" scope="col">X-RAY </th>
                <th width="102" scope="col">FINDINGS</th>
                <th width="123" scope="col">DATE EXAM</th>				 
              </tr>
            </thead>
            <tbody>

<?PHP
$rs = mysql_query("SELECT * FROM x_ray
 where p_id='".$_GET['p_id']."' order by id desc");
while($row=mysql_fetch_array($rs))
{
	
 echo "</tr>";
             
 echo "<td><input type='checkbox' name='delall[]' value='".$row['id']."'/></td> ";
echo"<td>".$row['x_ray']."</td>";
echo"<td>".$row['remarks']."</td>";

echo"<td style='font-size:10px;text-transform:uppercase;color:".$aa."'>";?>
<?php echo date('Y-M-d', strtotime($row['datexam'])); ?> 
<?php
echo"</td>";
?>
<?php
 echo "</tr>";
}

                 
?>
              <tr class="footer">
                <td colspan="4">
				
				<input type="submit" name="deletex" value="Delete" id="button" />
		
				<td align="right">&nbsp;</td>
                <td colspan="3" align="right">
	  
                </td>
				<td colspan="3" align="right">
	  
                </td>
              </tr>
            </tbody>
 </table>

</form>


