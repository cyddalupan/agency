<?php include'inc/header1.php';
include'tracking.php';
include'inc/expenses.php';
if(isset($_POST['delete'])){
$app11 = mysql_query("delete  FROM email_address where e_id=".$_GET['e_id']." ");
echo'<h2>deleted</h2>';
}


if(isset($_POST['delete_orders'])){
foreach ($_POST['delall'] as $delall) {
mysql_query("delete  FROM e_liq
WHERE l_id = ".$delall)


or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted.</p>";
}

$app = mysql_query("SELECT * FROM email_address where e_id=".$_GET['e_id']." ");
$row=mysql_fetch_array($app);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);

$staff = mysql_query("SELECT * FROM user where user_id=".$row['staff_id']." ");
$staffr=mysql_fetch_array($staff);


$sup = mysql_query("SELECT * FROM  suppliers where sup_id=".$row['sup_id']." ");
$sup1=mysql_fetch_array($sup);


$bank = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$row['fund_source_id']." ");
$bank1=mysql_fetch_array($bank);

if($row["request_payment"]==0){$request='Pending';$idrequest=0;$rcolor='black';}
if($row["request_payment"]==1){$request='Approved';$idrequest=1;$rcolor='green';}
if($row["request_payment"]==2){$request='For Relassse';$idrequest=2;$rcolor='green';}
if($row["request_payment"]==3){$request='Released';$idrequest=3;$rcolor='green';}
if($row["request_payment"]==5){$request='Return Maker';$idrequest=5;$rcolor='red';}




?>
<script>
	function popUp(strURL,strType,strHeight,strWidth) {
var strOptions="";
if (strType=="console") strOptions="resizable,height="+strHeight+",width="+strWidth;
if (strType=="fixed") strOptions="status,height="+strHeight+",width="+strWidth;
if (strType=="elastic") strOptions="toolbar,menubar,scrollbars,resizable,location,height="+strHeight+",width="+strWidth;
window.open(strURL, 'newWin', strOptions);
}
</script>
<body class="nav-md" style="background:#F5F5F5">


<?php $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>


<!-- page content -->
<div class="right_col" role="main">
<div class="">
<div class="col-md-12 col-sm-12 col-xs-12">    

 <?php if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){ ?>  
<form class="form-horizontal form-label-left input_mask" method="post" action="">
<a href="delete_methode_link" onclick="return confirm('Are you sure you want to Remove?');">	
<button type="submit" class="btn btn-danger pull-left"  name="delete">Delete  All  Transaction</button>
</a>
</form>

 <?php } ?>

		
</DIV>

<div class="clearfix"></div>

<div class="row" style="width:90%;margin:0 auto;margin-top:20px;margin-bottom:20px">
<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">

<div class="x_content">

<a href="<?=$actual_link;?>" style="color:Red;text-align:right">Refresh</a></br></br>



<table  cellspacing="0" id="customers">


<tr>
<td>Code # : <?=$row["e_id"]?></td>
<td>Request Amount : <b style="color:Red"><?=$row["amount"]?> </b></td>
<td>Agent:   <?=$row1["agent_last"]?>, <b><?=$row1["agent_first"]?></td>
<td>agent_contacts : <?=$row1["agent_contacts"]?></td>
<td>agent_email : <?=$row1["agent_email"]?></td>
<td>Agreement : <?=$row1["agent_remarks"]?></td>

<td><a href="profile.php?agent=<?=$row1['agent_id']?>" onclick="popUp(this.href,'console',700,1200);return false;" target="newWin">
<button type="submit" class="btn btn-warning btn-xs" style="">View Accounts</button></b></a></td>
</tr>

</table>


<?php
if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){
?>



<?php }  ?>

<DIV style="clear:both;height:30px"></DIV>


<div class="" role="tabpanel" data-example-id="togglable-tabs">
<ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
<li role="presentation" class="active"><a href="#tab_content1" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Expeses Info</a>
</li>
<li role="presentation" ><a href="#tab_content2" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Liquidation Details</a>
</li>

<li role="presentation" class=""><a href="#tab_content3" role="tab" id="profile-tab3" data-toggle="tab" aria-expanded="false">Uplaod Receipt/Docs </a>
</li>

</ul>
<div id="myTabContent" class="tab-content">
<div role="tabpanel" class="tab-pane fade active in" id="tab_content1" aria-labelledby="home-tab">

<?php include'form/expens_profile1.php';?>

</div>




<div role="tabpanel" class="tab-pane fade" id="tab_content2" aria-labelledby="profile-tab2">
<?php include'form/liq1.php';?>
</div>












<div role="tabpanel" class="tab-pane fade" id="tab_content3" aria-labelledby="profile-tab2">
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