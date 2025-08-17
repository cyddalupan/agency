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
$app11 = mysql_query("delete  FROM subject1 where SUBJ_ID= ".$delall)

or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted</p>";
}

if(isset($_POST['add_agree']))
{
    
 



 $app = mysql_query("UPDATE subject
SET app_name='".$_POST['app_name']."'

,app_status1='".$_POST['app_status1']."'
,app_remarks='".$_POST['app_remarks']."'
,app_country='".$_POST['app_country']."'
,app_contact='".$_POST['app_contact']."'


,app_deployed='".$_POST['app_deployed']."'
,app_action='".$_POST['app_action']."'
,app_fb='".$_POST['app_fb']."'
,fra_name='".$_POST['fra_name']."'
,app_position='".$_POST['app_position']."'

,agent_id='".$_POST['agent_id']."'
,app_remarks1='".$_POST['app_remarks1']."'

,app_source='".$_POST['app_source']."'
,app_arrival='".$_POST['app_arrival']."'
,app_arrival1='".$_POST['app_arrival1']."'
,w1=".$_POST['subjecttype']."
,w2=".$_POST['subjectname']."
,sponsor='".$_POST['sponsor']."'
,reqname='".$_POST['reqname']."'
,reqcontact='".$_POST['reqcontact']."'
,reqrel='".$_POST['reqrel']."'
,reqadd='".$_POST['reqadd']."'



,passport='".$_POST['passport']."'
,oec='".$_POST['oec']."'
,arrival='".$_POST['arrival']."'
,agent='".$_POST['agent']."'
,sub='".$_POST['sub']."'
,other1='".$_POST['other1']."'
,app_fb1='".$_POST['app_fb1']."'
,app_fb2='".$_POST['app_fb2']."'
,app_fb3='".$_POST['app_fb3']."'

,app_source='".$_POST['app_source']."'
WHERE SUBJ_ID=".$_GET['idme']." ");  






$insert=mysql_query("insert into subject1 (app_action,app_deployed,app_status,ids,agent_id,app_source,w1,w2,app_remarks1) values
('".$_POST['app_action']."','".$_POST['app_deployed']."','".$_POST['app_status1']."',".$_GET['idme']."
,'".$_POST['agent_id']."'
,'".$_POST['app_source']."'
,".$_POST['subjecttype']."
,".$_POST['subjectname']."
,'".$_POST['app_remarks1']."')");




    
echo"<P STYLE='COLOR:RED;FONT-SIZE:18PX'>DATA Updated....'</P>";

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



<form action="" method="post"   style="width:45%;float:Left;min-height:700px;margin:5px;BORDER:5PX solid #DC143C">

<H2 style="color:#DC143C"> Welfare APPLCIANT</H2>


 
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
		<input type="text" id="app_name" name="app_name" value="<?=$row['app_name']?>" >
		</td>
		
			<td><label for="fname"> FRA</label>

			<?php
			echo'<select class="form-control" name="fra_name"  >
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
<input type="text" id="app_contact" name="app_contact" value="<?=$row['app_contact']?>"  >
		</td>
		
		<td> <label for="fname"> COUNTRY</label>
<input type="text" id="app_country" name="app_country" value="<?=$row['app_country']?>" >
 </td>
</tr>


<tr>
		<td><label for="fname"> FACEBOOK NAME</label>
    <input type="text" id="app_fb" name="app_fb" value="<?=$row['app_fb']?>" >
		</td>
		<td><label for="fname"> FACEBOOK LINK 1</label>	
    <input type="text" id="app_fb1" name="app_fb1" value="<?=$row['app_fb1']?>" >
</tr>



<tr>
		<td><label for="fname"> FACEBOOK LINK 2</label>
    <input type="text" id="app_fb2" name="app_fb2" value="<?=$row['app_fb2']?>" >
		</td>
		<td><label for="fname"> FACEBOOK LINK 3</label>	
    <input type="text" id="app_fb3" name="app_fb3" value="<?=$row['app_fb3']?>" >
</tr>



<tr>
	
		<td><label for="fname"> CATEGORY</label>
<input type="text" id="app_position" name="app_position" value="<?=$row['app_position']?>" >

		<td>

<td></td>

</tr>





<tr>
			<td> <label for="fname"> DEPLOYED DATE</label>
			<input type="DATE" id="app_deployed" name="app_deployed" value="<?=$row['app_deployed']?>">
			</td>


		<td  style="display:none"> <label for="fname"> ARRIVAL DATE</label>
			<input type="DATE" id="arrival" name="arrival" value="<?=$row['arrival']?>">
			</td>
	
</tr>			
			
			
			
<tr>			
		
		
		
        	<td> <label for="fname"> Other Contact</label>
			<input type="text" id="other1" name="other1" value="<?=$row['other1']?>">
			</td>	
			
	
        	<td  style="display:none"> <label for="fname"> Person in Charge</label>
			<input type="text" id="agent_id" name="agent_id" value="<?=$row['agent_id']?>">
			</td>
 
	
</tr>

<tr>
		<td><br><br><label for="fname"> SPONSOR</label>
		<input type="text" id="sponsor" name="sponsor" value="<?=$row['sponsor']?>" >
		</td>
		
		<td><br><br><label for="fname">REQUESTING PARTY</label>
		<input type="text" id="reqname" name="reqname" value="<?=$row['reqname']?>" >
		</td>
<td>
</tr>

<tr>
		<td><label for="fname"> Contact Details</label>
		<input type="text" id="reqcontact" name="reqcontact" value="<?=$row['reqcontact']?>" >
		</td>
		
		<td><label for="fname">RELATIONSHIP</label>
		<input type="text" id="reqrel" name="reqrel" value="<?=$row['reqrel']?>" >
		</td>
<td>
  </tr>  
  
  
  <tr>
		<td><label for="fname"> OEC</label>
		<input type="text" id="oec" name="oec" value="<?=$row['oec']?>" >
		</td>
		
		<td><label for="fname">Passport</label>
		<input type="text" id="passport" name="passport" value="<?=$row['passport']?>" >
		</td>
<td>
</tr>

  
  <tr>
		<td><label for="fname"> agent</label>
		<input type="text" id="agent" name="agent" value="<?=$row['agent']?>" >
		</td>
		
		<td><label for="fname">sub</label>
		<input type="text" id="sub" name="sub" value="<?=$row['sub']?>" >
		</td>
<td>
</tr>



<tr>
		<td COLSPAN=""  style="display:none"><label for="fname">ADDRESS</label>
		<input type="text" id="reqadd" name="reqadd" value="<?=$row['reqadd']?>" >
		</td>
		
		<td>
		<label for="fname">SOURCE</label>
		<select class="form-control"   name="app_source">
		<option value="<?=$row['app_source']?>"><?=$row['app_source']?></option>
		<option value="">----</option>
		<?php include'optctype.php';?>
		</select>
		</td>

		
</tr>



</table>




<br><br><br>


<table>	

<tr>
<td STYLE="DISPLAY:NONE"> <label for="fname">  REMARKS</label>
<div style="clea:both"></div>
<textarea name="app_remarks" style="width:280px;height:50px" ><?=$row['app_remarks']?></textarea>

</td>

<td>
<label for="fname">      <B STYLE="font-size:16px;color:#DC143C;">ACTION TAKEN / COMPLAINTS /ACTION<B></label>
<div style="clea:both"></div>
<textarea name="app_remarks1" style="width:280px;height:70px;:border:2px solid red;" ><?=$row['app_remarks1']?></textarea>

</td>
</tr>

<br>
<br>










	


<tr  style="display:none">

		<td>
		<label for="fname">Type</label>
		<?php
		echo'<select class="form-control" name="subjecttype" required onchange="showapplicant(this.value)" >
		<option vaue="'.$row["w1"].'">'.$row["w1"].'</option>
        <option  value="0">N/A</option>
		';

		$usertype = mysql_query("SELECT * FROM subject_type  
		ORDER BY wel_id asc");
		while($rows=mysql_fetch_array($usertype))
		{
		echo'<option value="'.$rows["wel_id"].'">  '.$rows["wel_type"].'</option>';
		}
		ECHO'</select>';
		?>
		</td>

		<td><label for="fname">Type Name</label>
		<?php
		echo'<select class="form-control" name="subjectname"  id="showapp" >
		<option vaue="'.$row["w2"].'">'.$row["w2"].'</option>
		<option  value="0">-----</option>';

		//$applicant = mysql_query("SELECT * FROM applicant ORDER BY applicant_date_applied asc");
		//while($rows1=mysql_fetch_array($applicant))
		//{
		//echo'<option value="'.$rows1["applicant_id"].'">  '.$rows1["applicant_last"].' '.$rows1["applicant_first"].'-'.$rows1["applicant_id"].'</option>';
		//}
		ECHO'</select>';
		?>


		</td>
</tr>












<tr>
		<td> <label for="fname">ARRIVAL DATE</label>
<input type="date" id="app_arrival" name="app_arrival" value="<?=$row['app_arrival']?>" style="padding:4px;font-size:14px;border:1px solid red" >
		</td>

		<td  style="display:none">
    <label for="fname"> FLIGHT DETAILS </label>
	 <div style="clea:both"></div>
 <textarea name="app_arrival1" style="width:280px;height:50px" ><?=$row['app_arrival1']?></textarea>
  
		</td>
</tr>



<tr>
		<td colspan="2"><B STYLE="font-size:18px;color:#DC143C;">ACTION DATE INFORMATION<B></td>
</tr>

<tr>
		<td><label for="fname">STATUS</label>
        <select id="app_status1" name="app_status1">
        ``````<option value="<?=$row['app_status1']?>"><?=$row['app_status1']?></option>
                <option value="N/A">N/A</option>
                 <option value="TRANSFERRED">TRANSFERRED</option>
                <option value="RESOLVED">RESOLVED</option>
                 <option value="UNDER CUSTODY">UNDER CUSTODY</option>
                   <option value="UNDER LOCAL AUTORITIES">UNDER LOCAL AUTORITIES</option>
                <option value="ACTIVE">ACTIVE COMPLAINT</option>
                <option value="ACTIVE CASE">ACTIVE CASE </option>
                <option value="NO RESPONSE">NO RESPONSE</option>
                <option value="ARRIVED">ARRIVED</option>
                <option value="CASE DETAILS">CASE DETAILS</option>
                 <option value="CONTINUE WORKING">CONTINUE WORKING</option>
		<option value="">N/A </option>
		
		<option value="DELETE">DELETE </option>
        </select>
		</td>

		<td>  <label for="fname">ACTION DATE </label>
<input type="date" id="app_action" name="app_action" value="<?=$row['app_action']?>" style="padding:4px;font-size:14px;border:1px solid red" required>
  </td>
</tr>







</table>	
	
	
	






  
  
  <div style="clea:both"></div>
  <a href="delete_methode_link" onclick="return confirm('Are you sure you want to SAVE  (WELFARE/COMPLAINT)?');">
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
<th>Status</th>
<th>Source</th>
<th>Type</th>
<th>Type Name</th>

<th>Welfare R</th>
<th>Office R</th>
<th>Date Deployed</th>
<th>Agent</th>

<th>Action Date</th>




</tr>
</thead>
<tbody>
<?php	



$myrow = mysql_query("SELECT * FROM subject1 

LEFT JOIN subject_type
ON subject1.w1 = subject_type.wel_id

LEFT JOIN subject_name
ON subject1.w2 = subject_name.well_id

LEFT JOIN recruitment_agent
ON subject1.agent_id = recruitment_agent.agent_id

where ids=".$_GET['idme']." order by SUBJ_ID desc");


while($myrow1=mysql_fetch_array($myrow))
{


echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$myrow1['SUBJ_ID']."' ></td> ";
echo'<td>'.$myrow1["app_status"].'</td>';
echo'<td>'.$myrow1["app_source"].'</td>';
echo'<td>'.$myrow1["wel_type"].'</td>';
echo'<td>'.$myrow1["well_name"].'</td>';
echo'<td>'.$myrow1["app_remarks"].'</td>';
echo'<td>'.$myrow1["app_remarks1"].'</td>';
echo'<td>'.$myrow1["app_deployed"].'</td>';
echo'<td>'.$myrow1["agent_first"].' '.$myrow1["agent_last"].'</td>';

echo'<td>'.$myrow1["app_action"].'</td>';
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
<form name="frmupload" method="post" action="" enctype="multipart/form-data" id='imagesForm' style="display:none">
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
 










<div style="clea:both"></div>

</div>






