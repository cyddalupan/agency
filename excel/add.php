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
    
 


$insert=mysql_query("insert into subject (app_name,fra_name,app_contact,app_country,app_deployed) values
('".$_POST['app_name']."','".$_POST['fra_name']."','".$_POST['app_contact']."','".$_POST['app_country']."','".$_POST['app_deployed']."')");




    
echo"<P STYLE='COLOR:RED;FONT-SIZE:18PX'>Added</P>";

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

<H2 style="color:#DC143C">ADD APPLCIANT</H2>


 
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
<input type="text" id="app_country" name="app_country" value="<?=$row['app_country']?>">
 </td>
 
 
 
 
</tr>






	<td><label for="fname">Deployed Date</label>
<input type="date" id="app_deployed" name="app_deployed" value="<?=$row['app_deployed']?>"  >
		</td>









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












<div style="clea:both"></div>

</div>






