<?php
session_start();
include'inc/functions.php';
include'tracking.php';
include'styles.php';
	

if(isset($_POST['delete_orders'])){
foreach ($_POST['delall'] as $delall) {
mysql_query("delete  FROM agent_agreement
WHERE agree_id = ".$delall)
or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted.</p>";
}



$app = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$_GET['agent']." ");
$row=mysql_fetch_array($app);




?>

<title>Accounting System</title>

<!-- Bootstrap -->
<link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
<!-- NProgress -->
<link href="../vendors/nprogress/nprogress.css" rel="stylesheet">
<!-- iCheck -->
<link href="../vendors/iCheck/skins/flat/green.css" rel="stylesheet">

<!-- bootstrap-progressbar -->
<link href="../vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
<!-- JQVMap -->
<link href="../vendors/jqvmap/dist/jqvmap.min.css" rel="stylesheet"/>
<!-- bootstrap-daterangepicker -->
<link href="../vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">

<!-- Custom Theme Style -->
<link href="../build/css/custom.min.css" rel="stylesheet">
<style>
#wrapper{
border:0px solid black;
width:90%;
min-height:630px;
height:!important;
margin: 0 auto;	
padding:10px;
font-size:12px;
font-family:calibri;
}
.table-fixed td,th{
font-size:13px;
padding:2px;
}


</style>

<body style="background:white">
<div id="wrapper">

<div>

</div>
<a href="<?=$actual_link;?>" style="color:Red;text-align:right">Refresh</a>

<!-- page content -->
<div class="right_col" role="main">
<div class="">


<div class="clearfix"></div>

<div class="row">
<div class="col-md-12">
<div class="x_panel">

<div class="x_content">

<section class="content invoice">



</div>

</BR>

<!-- Table row -->
<div class="row">
<div class="col-xs-12 ">












</div>
</div>
<!-- /.col -->
</div>
<!-- /.row -->




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




$insert=mysql_query("INSERT INTO suppliers_docs (jobid,docs_fie,docs_name,file_status)
VALUES (".$_GET['metime'].",'".$filename1."','".$_POST['docs_name']."','jobs' )");
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
  
  </hr>
  
<form name="frmupload" method="post" action="<?=$actual_link?>" enctype="multipart/form-data" id='imagesForm'>
<h2>AGENT FILE</h2>

<DIV style="clear:both;height:30px"></DIV>
<select class="form-control" name="docs_name"  style="width:300px;border:1px solid gray;margin:5px" required>
<option value="">-Select file Type-</option>
<option>JOB ORDER FILE 1</option>
<option>JOB ORDER FILE 2</option>
<option>JOB ORDER FILE 3</option>
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
 
 
 <h1><?=$_GET['myname']?></h2>
 
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








$app11111 = mysql_query("SELECT * FROM suppliers_docs where jobid=".$_GET['metime']."
AND file_status='jobs' order by id desc");

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



</section>
</div>
</div>
</div>
</div>
</div>
</div>
<!-- /page content -->


</div>

</body>