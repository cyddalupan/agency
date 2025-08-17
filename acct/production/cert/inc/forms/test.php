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

<h4 style="background:#000000; color:#FFFFFF; padding:3px">Patient Test</h4>


          <table width="100%" cellpadding="0" cellspacing="0" id="box-table-a" summary="Employee Pay Sheet">
            <thead>
              <tr>
                <th width="300" scope="col">Package</th>
                <th width="150" scope="col">Date Exam</th>				 
              </tr>
            </thead>
            <tbody>
<form action="?p_id=<?=$_GET['p_id']?>&p=4" method="post"/>			
<?PHP
$rs = mysql_query("SELECT * FROM cashier
 where p_id='".$_GET['p_id']."' order by id desc");
while($row=mysql_fetch_array($rs))
{
$package = mysql_query("SELECT * FROM payments_desc where id=".$row['pack_id']);
$package=mysql_fetch_array($package);

	 echo "</tr>";
             
echo"<td>".$package['packages']."</td>";
echo"<td style='font-size:10px;text-transform:uppercase;color:".$aa."'>";?>
<?php echo date('Y-M-d', strtotime($row['dateadded'])); ?> 
<?php
echo"</td>";
?>
<?php
 echo "</tr>";
}

                 
?>
              <tr class="footer">
                <td colspan="4">
				<input type='HIDDEN' name='deletex' value='Delete' id='button'>
				<td align="right">&nbsp;</td>
                <td colspan="3" align="right">
	  
                </td>
				<td colspan="3" align="right">
	  
                </td>
              </tr>
            </tbody>
 </table>

</form>


