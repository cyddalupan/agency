<?php include'inc/header1.php';
include'tracking.php';
include'inc/agent.php';
include'styles.php';
if(isset($_POST['delete11111'])){
$app11 = mysql_query("delete  FROM suppliers where agent_id=".$_GET['e_id']." ");
echo'<h2 stye="color:Red" >Deleted</h2>';
}

$app = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$_GET['e_id']." ");
$row=mysql_fetch_array($app);
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


<div class="clearfix"></div>




<div class="row" style="width:90%;margin:0 auto;margin-top:20px;margin-bottom:20px">
<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">

<div class="x_content">


<a href="<?=$actual_link;?>" style="color:Red;text-align:right">Refresh</a></br></br>



<table  cellspacing="0" id="customers">


<tr>
<td><b>Agent:  </b>  </br><?=$row["agent_last"]?>, <b><?=$row["agent_first"]?></td>
<td><b>Code # : </b> </br>000<?=$row["agent_id"]?></td>
<td><b>agent_contacts </b>:  </br><?=$row["agent_contacts"]?></td>
<td><b>agent_email :</b> </br> <?=$row["agent_email"]?></td>
<td><b>Agreement :</b> </br><?=$row["bank_remarks"]?></td>
<td><a href="profile.php?agent=<?=$row['agent_id']?>" onclick="popUp(this.href,'console',700,1200);return false;" target="newWin">
<button type="submit" class="btn btn-warning btn-xs" style="">View Accounts</button></b></a></td>
</tr>

</table>




<DIV style="clear:both;height:10px"></DIV>


<div class="" role="tabpanel" data-example-id="togglable-tabs">
<ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
<li role="presentation" class="active"><a href="#tab_content1" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Expeses Info</a>
</li>


<li role="presentation" class=""><a href="#tab_content3" role="tab" id="profile-tab3" data-toggle="tab" aria-expanded="false">Uplaod Receipt/Docs </a>
</li>

</ul>
<div id="myTabContent" class="tab-content">
<div role="tabpanel" class="tab-pane fade active in" id="tab_content1" aria-labelledby="home-tab">
<form class="form-horizontal form-label-left input_mask" method="post" action="">	



<div class="form-group"  >
<label class="control-label col-md-3 col-sm-3 col-xs-12">first name</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="agent_first"  value="<?=$row['agent_first']?>">
</div>
</div>


<div class="form-group"  >
<label class="control-label col-md-3 col-sm-3 col-xs-12">last name</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="agent_last"  value="<?=$row['agent_last']?>">
</div>
</div>



<div class="form-group"  >
<label class="control-label col-md-3 col-sm-3 col-xs-12">agent_contacts</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="agent_contacts"  value="<?=$row['agent_contacts']?>">
</div>
</div>




<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">agent_email</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="agent_email"  value="<?=$row['agent_email']?>">
</div>
</div>




<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">agent_remarks</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="agent_remarks" REQUIRED value="<?=$row['agent_remarks']?>">
</div>
</div>





 <div style="clear:both;height:10px"></div>
<h5 style="color:red;text-align:center;margin:15px;font-size:22px"><u>Bank Information</u></h5>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12" style="color:blue">Bank Name</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="bank_name"   required  value="<?=$row['bank_name']?>">
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Bank Account</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="bank_number" required  value="<?=$row['bank_number']?>">
</div>

 <div style="clear:both;height:10px"></div>
<h5 style="color:red;text-align:center;margin:15px;font-size:22px"><u>Agreement</u></h5>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Type of Payment</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control" name="terms_payment" required>
<option><?=$row['terms_payment']?></option>
<option>Ful Payment</option>
<option>Cash Advance</option>
<option>Partial</option>
<option>Others</option>
</select>
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Mode of Payment</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control" name="mode_payment" required>
<option><?=$row['mode_payment']?></option>
<option>Fund Transfer</option>
<option>Cash</option>
<option>Cheque</option>
<option>Others</option>
</select>
</div>
</div>




<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Agreement Details</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<textarea name="bank_remarks" rowspan="12" colspan="120" style="width:100%;height:150px"><?=$row['bank_remarks']?></textarea>
</div>
</div>


<div class="form-group">
<div class="col-md-4 col-sm-6 col-xs-12">
<input type="hidden" class="form-control" name="agent_id" value="<?=$_GET['e_id']?>">
<button type="submit" class="btn btn-danger pull-right"  name="update_profile">Save</button>
</div>
</div>


</form>	

</div>
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
$filename = "docs/".$datenow."".$_FILES['file']['name'][$key];
$filename1 = $filename;




$insert=mysql_query("INSERT INTO suppliers_docs (sup_id,docs_fie,docs_name,file_status)
VALUES (".$_GET['e_id'].",'".$filename1."','".$_POST['docs_name']."','agent' )");
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

<DIV style="clear:both;height:30px"></DIV>
<select class="form-control" name="docs_name"  style="width:300px;border:1px solid gray;margin:5px" required>
<option value="">-Select file Type-</option>
<option>Business Registration</option>
<option>NBI</option>
<option>ID</option>
<option>PASSPORT</option>
<option>OTHERS</option>
</select>



<input type="file" name="file[]" id="input1"><input type="submit" name="uploadphoto" value="Upload Photo" style="padding:5px;background:orange;color:white;margin-top:25px">
	
 </form>
 </br>
 
<?php

if(isset($_POST['delete_orders111'])){
foreach ($_POST['delall'] as $delall) {
$app11 = mysql_query("delete  FROM suppliers_docs where id= ".$delall)

or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted</p>";
}
?> 
 
<form action="" method="post">
 <table  cellspacing="0" id="customers">
<thead>
<tr>

<th STYLE="width:2%"> #</th>

<th STYLE="width:30%">Path</th>
<th STYLE="width:30%">File Type</th>
</tr>
</thead>
<tbody>
<?php	








$app11111 = mysql_query("SELECT * FROM suppliers_docs where sup_id=".$_GET['e_id']."
AND file_status='agent' order by id desc");

while($row111=mysql_fetch_array($app11111))
{
echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$row111['id']."' ></td> ";
echo'<td><a  href="'.$row111["docs_fie"].'" target="_blank"><img src="'.$row111["docs_fie"].'" style="width:100px;border:1px solid black;margin:2px">'.$row111["docs_fie"].'</a></td>';
echo'<td>'.$row111["docs_name"].'   </td>';
echo'</tr>';
}
$tot1 = number_format($total1, 2);
?>

</tbody>
</table>
 </br></br> </br></br>
<div class="row">
<div class="col-12" style="width:100%,float:right">		
<input type="submit" value="Delete" name="delete_orders111" style="foat:right;width:130px;background:red;padding:5px;margin-left:20px;margin-top:-1px;color:white">

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



<!-- jQuery -->
<script src="../vendors/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- FastClick -->
<script src="../vendors/fastclick/lib/fastclick.js"></script>
<!-- NProgress -->
<script src="../vendors/nprogress/nprogress.js"></script>
<!-- morris.js -->
<script src="../vendors/raphael/raphael.min.js"></script>
<script src="../vendors/morris.js/morris.min.js"></script>
<!-- bootstrap-progressbar -->
<script src="../vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
<!-- bootstrap-daterangepicker -->
<script src="../vendors/moment/min/moment.min.js"></script>
<script src="../vendors/bootstrap-daterangepicker/daterangepicker.js"></script>

<!-- Custom Theme Scripts -->
<script src="../build/js/custom.min.js"></script>

</body>
</html>