<?php
session_start();
if(!isset($_SESSION['admin']['user'])){
header("location:noaccess.php");
}
include'db1.php';
?>
<?php

if(isset($_POST['delete_orders'])){
foreach ($_POST['delall'] as $delall) {
$app11 = mysql_query("delete  FROM hearings where hid= ".$delall)

or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted</p>";
}

			if(isset($_POST['add_agree']))
			{




	$insert=mysql_query("insert into  hearings (htype,hdate,htime,href,hloc,hremarks,happ,hattend) values
	('".$_POST['htype']."','".$_POST['hdate']."','".$_POST['htime']."'
	,'".$_POST['href']."'
	,'".$_POST['hloc']."'
	,'".$_POST['hremarks']."'
	
	,".$_GET['idme']."
	,'".$_POST['hattend']."')");
	echo"<P STYLE='COLOR:white;FONT-SIZE:22PX;padding:10px;background:lightblue'>Succesfully Save</P>";




			}

$app = mysql_query("SELECT * FROM subject where SUBJ_ID=".$_GET['idme']." ");
$row=mysql_fetch_array($app);





?>


<style>
body{
font-family:calibri;	
font-size:12px;
}
input[type=text], select {
  width: 100%;
  padding: 8px;
  margin: 2px 0;
  display: inline-block;
  border: 1px solid #ccc;
  border-radius: 4px;
  width:300px;
  box-sizing: border-box;
}
select{
  width: 100%;
  padding: 8px;
  margin: 2px 0;
  display: inline-block;
  border: 1px solid #ccc;
  border-radius: 4px;
  width:280px;
  box-sizing: border-box;
  flot:right;
}

input[type=submit] {
  width: 100%;
  background-color: #4CAF50;
  color: white;
  padding: 4px;
  margin: 1px 0;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

input[type=submit]:hover {
  background-color: #45a049;
}

form {
  border-radius: 5px;
  background-color: #f2f2f2;
  padding: 20px;
}
</style>

<div style="width:100%;min-height:800px;padding:10px">



<form action="" method="post"   style="width:45%;float:Left;min-height:700px;margin:5px">

<H2 style="color:#DC143C"> APPLCIANT DETAILS</H2>


 
 <script>
function showapplicant(str) {

var xhttp;    
if (str == "") {
document.getElementById("showapp").innerHTML = "";
return;
}
xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
if (this.readyState == 4 && this.status == 200) {
document.getElementById("showapp").innerHTML = this.responseText;
}
};
xhttp.open("GET", "showapp.php?q="+str, true);
xhttp.send();
}

</script>
 
 

    
	<table>

	<tr>
	<td><label for="fname"> APPLICANT NAME</label>
	<input type="text" id="app_name" name="app_name" value="<?=$row['app_name']?>" disabled>
	</td>

	<td><label for="fname"> FRA</label>

	<?php
	echo'<select class="form-control" name="fra_name"  disabled>
	<option vaue="'.$row["fra_name"].'">'.$row["fra_name"].'</option>
	<option vaue="0">N/A</option>
	';

	$emp = mysql_query("SELECT * FROM employer  
	ORDER BY employer_name asc");
	while($emp1s=mysql_fetch_array($emp))
	{
	echo'<option value="'.$emp1s["employer_name"].'"> '.$emp1s["employer_name"].'</option>';
	}
	ECHO'</select>';
	?>	

	</td>
	</tr>





	<tr>
	<td><label for="fname">CONTACT</label>
	<input type="text" id="app_contact" name="app_contact" value="<?=$row['app_contact']?>"  disabled>
	</td>

	<td><label for="fname"> FACEBOOK NAME</label>
	<input type="text" id="app_fb" name="app_fb" value="<?=$row['app_fb']?>" disabled>
	</td>

	</tr>

	</table>
  


<H2 style="color:#DC143C">HEARING  DETAILS</H2>
<table>


<tr>
		<td>
		<label for="fname">SOURCE</label>
		<select class="form-control"   name="htype" required>
		<option value="<?=$row['htype']?>"><?=$row['htype']?></option>
		<option value="">----</option>
		<option>POEA</option>
		<option>OWWA</option>
		<option>DOLE</option>
		<option>NLRC</option>
		<option>REPAT</option>
		<option>SENA</option>
		<option>OTHERS</option>

		</select>
		</td>
		
		<td><label for="fname"> REF</label>
		<input type="text" id="href" name="href" value="<?=$row['href']?>" REQUIRED>
		</td>

	
</tr>


<tr>
<td><label for="fname"> DATE</label>
<input type="DATE" id="hdate" name="hdate" value="<?=$row['hdate']?>" REQUIRED>
</td>

<td><label for="fname"> TIME</label>
<input type="TIME" id="htime" name="htime" value="<?=$row['htime']?>" REQUIRED>	<td>
</tr>



<tr>
<td><label for="fname"> LOCATION</label>
<input type="TEXT" id="hloc" name="hloc" value="<?=$row['hloc']?>" REQUIRED>
</td>

<td><label for="fname"> ATTENDEES</label>
<input type="TEXT" id="hattend" name="hattend" value="<?=$row['hattend']?>" REQUIRED>	<td>
</tr>






<td> <label for="fname">   CLAIMS/REMARKS</label>
<div style="clea:both"></div>
<textarea name="hremarks" style="width:280px;height:50px" ><?=$row['hremarks']?></textarea>

</td>

</tr>



</table>	
	
	
	
  
  
  <div style="clea:both"></div>
   <a href="delete_methode_link" onclick="return confirm('Are you sure you want to SAVE  (OWMS)?');"> 
<button type="submit" class="btn btn-danger pull-right"  name="add_agree" style="padding:8px;font-size:18px;background:lightblue">Save</button>
</a>
  </form>
  
  
  
  
  <form action="" method="post"   style="width:45%;float:Left;min-height:700px;margin:5px">
 <H2>STATUS HISTORY</H2>






<style>
#customers {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
  font-size:11px;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 2px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #04AA6D;
  color: white;
}
</style>


</br></br><h2>HISTORY</h2>

<form action="" method="post" style="width:500px;FLOat:left">	




<table  cellspacing="0" id="customers">
<thead>
<tr>
<th></th>
<th>ATTENDEES</th>
<th>TYPE</th>
<th>DATE</th>
<th>TIME</th>
<th>REF</th>
<th>LOCATION</th>
<th>REMARKS</th>	




</tr>
</thead>
<tbody>
<?php	



$myrow = mysql_query("SELECT * FROM  hearings 
where happ=".$_GET['idme']." order by hid desc");


while($myrow1=mysql_fetch_array($myrow))
{


echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$myrow1['hid']."' ></td> ";
echo'<td>'.$myrow1["hattend"].'</td>';
echo'<td>'.$myrow1["htype"].'</td>';
echo'<td>'.$myrow1["hdate"].'</td>';
echo'<td>'.$myrow1["htime"].'</td>';
echo'<td>'.$myrow1["href"].'</td>';
echo'<td>'.$myrow1["hloc"].'</td>';
echo'<td>'.$myrow1["hremarks"].'</td>';

echo'</tr>';
}
?>
</tbody>
</table>

</br>
<div class="row">
<div class="col-5" style="width:100%,float:right">		
<input type="submit" value="Delete" name="delete_orders" style="width:130px;background:Red;padding:5px;margin-left:20px;margin-top:-1px;color:white">

</div>
</div>

</form>	









<?php
@set_time_limit(3600); //1 hour
@ini_set('max_execution_time', 3600); //1 hour
?>
  
    <div style="clear:both;height:10px"></div>


  <div style="clear:both;height:10px"></div>

<?php

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




$insert=mysql_query("INSERT INTO resibo1 (resibo_connect,resibo_path,file_type)
VALUES (".$_GET['idme'].",'".$filename1."','".$_POST['file_type']."' )");
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
<form name="frmupload" method="post" action="" enctype="multipart/form-data" id='imagesForm'>
<label style="color:red">Upload Receipt (JPG,PNG ONLY)</label>
<input type="hidden" name="p_id"  value="<?=$datenow?>" style="width:300px;">


<DIV style="clear:both;height:30px"></DIV>

<input type="text" name="file_type"   style="width:300px;" placeholder="File Type">



<input type="file" name="file[]" id="input1"><input type="submit" name="uploadphoto" value="Upload Photo" style="padding:5px;background:orange;color:black;margin-top:25px;width:300px">
	
 </form>
 </br>
 
<?php

if(isset($_POST['delete_orders111'])){
foreach ($_POST['delall'] as $delall) {
$app11 = mysql_query("delete  FROM resibo1 where resibo_id= ".$delall)

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








$app11111 = mysql_query("SELECT * FROM resibo1 where resibo_connect=".$_GET['idme']."
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









<div style="clea:both"></div>

</div>






