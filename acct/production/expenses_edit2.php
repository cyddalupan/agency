<?php include'inc/header1.php';
include'tracking.php';
include'inc/expenses.php';
if(isset($_POST['delete'])){
$app11 = mysql_query("delete  FROM email_address where e_id=".$_GET['e_id']." ");
echo'<h2>deleted</h2>';
}

$app = mysql_query("SELECT * FROM email_address where e_id=".$_GET['e_id']." ");
$row=mysql_fetch_array($app);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);

$staff = mysql_query("SELECT * FROM user where user_id=".$row['staff_id']." ");
$staffr=mysql_fetch_array($staff);


if(isset($_POST['delete_orders'])){
foreach ($_POST['delall'] as $delall) {
mysql_query("UPDATE e_liq
SET l_status = 1 
WHERE l_id = ".$delall)
or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted</p>";
}

?>

<body class="nav-md" style="background:#F5F5F5">


<?php $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>


<!-- page content -->
<div class="right_col" role="main">
<div class="">


<div class="clearfix"></div>

<div class="row" style="width:90%;margin:0 auto;margin-top:20px;margin-bottom:20px">
<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">

<div class="x_content">

<a href="<?=$actual_link;?>" style="color:Red;text-align:right">Refresh</a>
</br></br>
Date Request : <?php
echo''; echo date('Y-M-d', strtotime($row['date_ad']));echo'';

?>


<?php
if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){



?>
<div class="col-md-12 col-sm-12 col-xs-12">      
<form class="form-horizontal form-label-left input_mask" method="post" action="">	
<button type="submit" class="btn btn-danger pull-right"  name="delete">Delete  All  Transaction</button>
</form>		
</DIV>


<?php }  ?>
<DIV style="clear:both;height:30px"></DIV>


<div class="" role="tabpanel" data-example-id="togglable-tabs">
<ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
<li role="presentation" class="active"><a href="#tab_content1" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Liquidation Details</a>
</li>
<li role="presentation" class=""><a href="#tab_statuss" role="tab" id="profile-tab3" data-toggle="tab" aria-expanded="false">Uplaod Receipt/Docs </a>
</li>



</ul>


<div id="myTabContent" class="tab-content">
<div role="tabpanel" class="tab-pane fade active in" id="tab_content1" aria-labelledby="home-tab">

<form class="form-horizontal form-label-left input_mask" method="post" action="">	

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">DATE TRANSACTION</label>
<div class="col-md-4 col-sm-6 col-xs-12">
<input type="date" class="form-control" name="l_date" value="<?=$row['date_ad']?>" required>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Type</label>
<div class="col-md-4 col-sm-9 col-xs-12" required>
<select class="form-control" name="l_type"   required>
<option value="">------</option>

<?php 


if($_GET['expert']=="you") {
echo'<option>Incentive</option>';
echo'<option>Allowance</option>';
echo'<option>Absences</option>';
echo'<option>Deduction</option>';
echo'<option>Loan</option>';
}
if($_GET['expert']=="me") {
$hide="style='none:'";	
}
if($_GET['expert']=="you") {
$hide="none";	
}


?>

</select>
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Currency</label>
<div class="col-md-4 col-sm-9 col-xs-12" required>
<select class="form-control" name="l_currency"   >
<option>PHP</option>
</select>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Staff </label>
<div class="col-md-4 col-sm-6 col-xs-12">
<select class="form-control" required  name="l_staff_id" required>
<option value="<?=$staffr['user_id']?>"> <?=$staffr['user_fullname']?> </option>
<option value="0">N/A </option>
<?php
$usertype1 = mysql_query("SELECT * FROM user ORDER BY user_fullname asc");
while($rows1=mysql_fetch_array($usertype1))
{
echo'<option value="'.$rows1["user_id"].'">  '.$rows1["user_fullname"].'</option>';
}
?>
</select>
</div>
</div>




<?php
if ($row1['agent_id']==0){$getme="";}
if ($row1['agent_id']==3){$getme="";}
if ($row1['agent_id']!=0){$getme="where applicant_source=".$row1['agent_id']."";}
?>



<div class="form-group" style="display:<?=$hide?>">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Applicant</label>
<div class="col-md-4 col-sm-6 col-xs-12">


<select class="selectpicker" single data-live-search="true" name="liq_app" >
<?php
echo'<option></option>
<option value="9999999">Others</option>
';

$applicant = mysql_query("SELECT * FROM applicant $getme ORDER BY applicant_date_applied asc");
while($rows12222=mysql_fetch_array($applicant))
{
echo'<option value="'.$rows12222["applicant_id"].'">'.$rows12222["applicant_last"].'  '.$rows12222["applicant_middle"].'  '.$rows12222["applicant_first"].'</option>';
}
?>
</select>





</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="l_amount" step="any"  required value="">
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Description</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="l_remarks" required value="">
</div>
</div>


<div class="form-group">
<div class="col-md-4 col-sm-6 col-xs-12">
<input type="hidden" class="form-control" name="l_e_d" value="<?=$_GET['e_id']?>">
<button type="submit" class="btn btn-danger pull-right"  name="liq">Save</button>
</div>
</div>
</form>	

<style>
#customers {
  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 5px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #4CAF50;
  color: white;
}
</style>



</br>
<h3>Liquidation </h3>

  <form action="" method="post">

<table  cellspacing="0" id="customers">
<thead>
<tr>
<th STYLE="width:2%"></th>

<th STYLE="width:2%">Ref #</th>
<th STYLE="width:3%">DATE</th>
<th STYLE="width:5%">Applicant </th>
<th STYLE="width:5%">Req.By</th>
<th STYLE="width:5%">Type</th>
<th STYLE="width:5%">Description</th>
<th STYLE="width:5%">Currency</th>
<th STYLE="width:5%">Amount</th>
</tr>
</thead>
<tbody>
<?php	



$app = mysql_query("SELECT * FROM e_liq where l_status=0
AND l_e_d=".$_GET['e_id']."
order by l_id desc");

while($row22222=mysql_fetch_array($app))
{
    
$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$row22222['liq_app']." ");
$rowp=mysql_fetch_array($applicant);

$USER = mysql_query("SELECT * FROM  user where user_id=".$row22222['l_staff_id']." ");
$rowu=mysql_fetch_array($USER);

$total1=$row22222['l_amount']+$total1;
$amt = number_format($row22222["l_amount"], 2);


echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$row22222['l_id']."' ></td> ";
echo'<td>000'.$row22222["l_e_d"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row22222['l_date']));echo'</td>';
echo'<td>'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td>'.$row['requestby'].'</td>';
echo'<td>'.$row22222["l_type"].'</td>';
echo'<td>'.$row22222["l_remarks"].'</td>';
echo'<td>'.$row22222["l_currency"].'</td>';
echo'<td>'.$amt.'</td>';
echo'</tr>';
}
$tot1 = number_format($total1, 2);
?>

<tr>
<td  colspan="8"  style="font-weight:bold;text-align:right;font-size:14px;color:red">Total Liquidation:</td>
<td  style="font-weight:bold;text-align:left;font-size:14px;color:red"> <?=$tot1;?> </td>
</TR>

</tbody>
</table>
</br></br>

<?php
if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){



?>


<div class="row">
<div class="col-6" style="width:100%,float:right">		
<input type="submit" value="Delete" name="delete_orders" style="width:130px;background:blue;padding:5px;margin-left:20px;margin-top:-1px;color:white">

</div>
</div>

<?php }  ?>
</form>		
		


</div>




<div role="tabpanel" class="tab-pane fade" id="tab_statuss" aria-labelledby="profile-tab2">


<?php
@set_time_limit(3600); //1 hour
@ini_set('max_execution_time', 3600); //1 hour
?>
  
    <div style="clear:both;height:10px"></div>


  <div style="clear:both;height:10px"></div>

<?php
 include 'db.php';
  include 'functions.php';
 $obj = new SimpleImage;
 define ("MAX_SIZE","1400");
$extensions='jpg';
 $errors=0;
  function getExtension($str) {
         $i = strrpos($str,".");
         if (!$i) { return ""; }
         $l = strlen($str) - $i;
         $ext = substr($str,$i+1,$l);
         return $ext;
 }
 

 if(isset($_POST['uploadphoto']))
 {
  while(list($key,$value) = each($_FILES['file']['name']))
		{
        $image =$_FILES["file"]["name"][$key];
		
 $uploadedfile = $_FILES['file']['tmp_name'][$key];
 $obj->load($uploadedfile);
 


  if ($image) 
  {
  $filename = stripslashes($_FILES['file']['name'][$key]);
        $extension = getExtension($filename);
  $extension = strtolower($extension);
 if (($extension != "jpg") && ($extension != "jpeg") 

&& ($extension != "png") && ($extension != "gif")) 
  {
echo ' Unknown Image extension ';
$errors=1;
  }
 else
{
   $size=filesize($_FILES['file']['tmp_name'][$key]);
 
if ($size > MAX_SIZE*11024)
{
 //echo "You have exceeded the size limit";
 //$errors=1;
}
 
if($extension=="jpg" || $extension=="jpeg" )
{
$uploadedfile = $_FILES['file']['tmp_name'][$key];
$src = imagecreatefromjpeg($uploadedfile);
}
else if($extension=="png")
{
$uploadedfile = $_FILES['file']['tmp_name'][$key];
$src = imagecreatefrompng($uploadedfile);
}
else 
{
$src = imagecreatefromgif($uploadedfile);
}
 
list($width,$height)=getimagesize($uploadedfile);


 if($obj->getWidth()>=701){
  $newwidths=700;
 }else{
 $newwidths=$width;
 }
$newwidth=$newwidths;



$newheight=($height/$width)*$newwidth;
$tmp=imagecreatetruecolor($newwidth,$newheight);

$newwidth1=700;

$newheight1=($height/$width)*$newwidth1;
$tmp1=imagecreatetruecolor($newwidth1,$newheight1);

imagecopyresampled($tmp,$src,0,0,0,0,$newwidth,$newheight,

 $width,$height);

imagecopyresampled($tmp1,$src,0,0,0,0,$newwidth1,$newheight1, 

$width,$height);
$datenow=strtotime("now");
$filename = "photos/".$datenow."".$_FILES['file']['name'][$key];
$filename1 = $filename;




$insert=mysql_query("INSERT INTO resibo (resibo_connect,resibo_path,file_type)
VALUES (".$_GET['e_id'].",'".$filename1."','".$_POST['file_type']."' )");
echo'Added';

imagejpeg($tmp1,$filename1,100);

imagedestroy($src);
imagedestroy($tmp);
imagedestroy($tmp1);
}
}
} 
}if(isset($_POST['uploadphoto']) && !$errors) 
 {
 }
  ?>  
<form name="frmupload" method="post" action="<?=$actual_link?>" enctype="multipart/form-data" id='imagesForm'>
<label style="color:red">Upload Receipt</label>
<input type="hidden" name="p_id"  value="<?=$datenow?>" style="width:300px;">

<DIV style="clear:both;height:30px"></DIV>
<select class="form-control" name="file_type"  style="width:300px;border:1px solid gray;margin:5px" >
<option value="">-Select file Type-</option>
<option>Receipt</option>
<option>Payments</option>

</select>

<input type="file" name="file[]" id="input1"><input type="submit" name="uploadphoto" value="Upload Photo" style="padding:5px;background:orange;color:white;margin-top:25px">

	
 </form>
 </br>
 
<?php

if(isset($_POST['delete_orders111'])){
foreach ($_POST['delall'] as $delall) {
$app11 = mysql_query("delete  FROM resibo where resibo_id= ".$delall)

or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted</p>";
}
?> 
 
<form action="" method="post">
<table  cellspacing="0" id="customers">
<thead>
<tr>

<th STYLE="width:2%">Ref #</th>
<th STYLE="width:12%">Ref #</th>
<th STYLE="width:30%">Path</th>
<th STYLE="width:30%">File Type</th>
</tr>
</thead>
<tbody>
<?php	








$app11111 = mysql_query("SELECT * FROM resibo where resibo_connect=".$_GET['e_id']."
order by resibo_id desc");

while($row111=mysql_fetch_array($app11111))
{



echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$row111['resibo_id']."' ></td> ";
echo'<td>000'.$row111["resibo_connect"].'   </td>';
echo'<td><a  href="'.$row111["resibo_path"].'" target="_blank"><img src="'.$row111["resibo_path"].'" style="width:50px">'.$row111["resibo_path"].'</a></td>';
echo'<td>'.$row111["file_type"].'   </td>';
echo'</tr>';
}
$tot1 = number_format($total1, 2);
?>



</tbody>
</table>
 </br></br>
<div class="row">
<div class="col-5" style="width:100%,float:right">		
<input type="submit" value="Delete" name="delete_orders111" style="width:130px;background:blue;padding:5px;margin-left:20px;margin-top:-1px;color:white">

</div>
</div>
</form>	
</div>











<div role="tabpanel" class="tab-pane fade" id="tab_statuss" aria-labelledby="profile-tab2">

</div>



</div>
</div>


</div>
</div>
</div>
</div>
</div>
</div>
<!-- /page content -->

<!-- footer content -->



</body>
</html>