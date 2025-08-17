<?php 
session_start();
if(!isset($_SESSION['admin']['user'])){
header("location:noaccess.php");
}
	include 'db.php';
	include 'tracking.php';


?>	
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="Import Excel File To MySql Database Using php">

		<link rel="stylesheet" href="css/bootstrap.min.css">
		<link rel="stylesheet" href="css/bootstrap-responsive.min.css">
		<link rel="stylesheet" href="css/bootstrap-custom.css">


	</head>
	<body>  



<style>
ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
  overflow: hidden;
  background-color: #333;
}

li {
  float: left;
  border-right:1px solid #bbb;
}

li:last-child {
  border-right: none;
}

li a {
  display: block;
  color: white;
  text-align: center;
  padding: 14px 16px;
  text-decoration: none;
}

li a:hover:not(.active) {
  background-color: #111;
}

.active {
  background-color: #04AA6D;
}
</style>
	
	
	
<style>
ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
  overflow: hidden;
  background-color: #333;
}

li {
  float: left;
  border-right:1px solid #bbb;
}

li:last-child {
  border-right: none;
}

li a {
  display: block;
  color: white;
  text-align: center;
  padding: 14px 16px;
  text-decoration: none;
}

li a:hover:not(.active) {
  background-color: #111;
}

.active {
  background-color: #04AA6D;
}
table th{
background:#87CEFA;	
}
button {
 -webkit-border-radius: 4px;
    -moz-border-radius: 4px;
    border-radius: 4px;
    border: solid 1px #20538D;
    text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.4);
    -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 1px 1px rgba(0, 0, 0, 0.2);
    -moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 1px 1px rgba(0, 0, 0, 0.2);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 1px 1px rgba(0, 0, 0, 0.2);
    background: #4479BA;
    color: #FFF;
    padding: 2px;
    text-decoration: none;
  margin:1px;
}
	.blink{
		width:600px;
		height: 20px;

		padding: 2px;	
		text-align: center;

	}
	span{
		font-size: 20px;
		font-family: cursive;
		color: red;
		text-align:center;
		animation: blink 1s linear infinite;
	}
@keyframes blink{
0%{opacity: 0;}
50%{opacity: .7;}
100%{opacity: 2;}
}

</style>
	
<script>
function exportF(elem) {
  var table = document.getElementById("myTable");
  var html = table.outerHTML;
  var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url 
  elem.setAttribute("href", url);
  elem.setAttribute("download", "export.xls"); // Choose the file name
  return false;
}
</script>

<a id="downloadLink" onclick="exportF(this)" style="color:Red;FLOAT:right;font-size:14px;padding:5px">Export to excel</a>
<script>
// JavaScript popup window function
	function basicPopup(url) {
popupWindow = window.open(url,'popUpWindow','height=800,width=1400,left=50,top=-150,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes')
	}

</script>
 <script type="text/javascript" src="sort-table.js"></script>


  <style>
* {
box-sizing: border-box;
}

#myInput {
  background-image: url('/css/searchicon.png');
  background-position: 10px 10px;
  background-repeat: no-repeat;
  width: 100%;
  font-size: 16px;
  padding: 12px 20px 12px 40px;
  border: 1px solid #ddd;
  margin-bottom: 12px;
}

#myTable {
  border-collapse: collapse;
  width: 100%;
  border: 1px solid #ddd;
  font-size: 11px;
  text-transform:uppercase;
}

#myTable th, #myTable td {
  text-align: left;
  padding:4px;
}

#myTable tr {
  border-bottom: 1px solid #ddd;
}

#myTable tr.header, #myTable tr:hover {
  background-color: #f1f1f1;
}
</style>

</head>
<body>  



<style>
ul {
list-style-type: none;
margin: 0;
padding: 0;
overflow: hidden;
background-color: #333;
}

li {
float: left;
border-right:1px solid #bbb;
}

li:last-child {
border-right: none;
}

li a {
display: block;
color: white;
text-align: center;
padding: 14px 16px;
text-decoration: none;
}

li a:hover:not(.active) {
background-color: #111;
}

.active {
background-color: #04AA6D;
}
table th{
background:#87CEFA;	
}
button {
-webkit-border-radius: 4px;
-moz-border-radius: 4px;
border-radius: 4px;
border: solid 1px #20538D;
text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.4);
-webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 1px 1px rgba(0, 0, 0, 0.2);
-moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 1px 1px rgba(0, 0, 0, 0.2);
box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 1px 1px rgba(0, 0, 0, 0.2);
background: #4479BA;
color: #FFF;
padding: 2px;
text-decoration: none;
margin:1px;
}
</style>
	
	

	<!-- Navbar
    ================================================== -->

	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container"> 
				<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>
				<a class="brand" href="#">Imporst Excel </a>
				
			
			</div>
		</div>
	</div>


<div style="clear:both;height:40px"></div>

	

	<div id="wrap">
	<div class="container">

<?php



$deployed = "SELECT count(DISTINCT deployed.deployed_applicant) as alls,applicant.*,deployed.* FROM deployed
LEFT JOIN applicant
ON deployed.deployed_applicant = applicant.applicant_id

where
applicant_status=9
AND mystatus=0
AND deployed_date between '2020-06-01' AND '".$date3."'

order by deployed_date desc";
$deployed1 =  mysqli_query($conn, $deployed);
$row1=mysqli_fetch_array($deployed1,MYSQLI_ASSOC);

?>
	
	<a href="deployed.php" target="_blank" style="color:black;background:yellow;padding:12px;margin-bottom:30px;border:1px solid black">
Deployed List <b style="color:black">(<?=$row1['alls']?>)</a>
</br>	</br>	


<?php include'menu.php';?>



</br>







<form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post" ACTION="search.php">

<div class="form-group">
<div class="col-md-4 col-sm-4 col-xs-4 form-group">
<input type="text" placeholder="first name , last name " class="form-control" name="search1" required style="padding:8px;border:2px solid gray;width:400px">
<button type="submit" class="btn btn-info"  name="SearchME" >Search Applicant</button>

</div>
</br>

</form>  



<p style="font-size:16px"> 
<a href="add.php?idme=<?=$row["SUBJ_ID"]?>"   onclick="basicPopup(this.href);return false"><button style="background:green;padding:8px;">ADD NEW</button></a> 
</p>


<div class="blink"><span>Applicant's For Update! 7 DAYS . . . </span></div>


<p style="color:white"><?=$_SESSION['admin']['user']['user_type']?></p>



<form action="" method="post" style="width:1100px;FLOat:left">	


<a href="index.php?type=<?=$_GET['type']?>" style="color:blue;font-size:18px;font-weight:bold">TODAY</a> |
<a href="index1.php?type=<?=$_GET['type']?>" style="color:blue;font-size:18px;font-weight:bold">7 DAYS</a> |
<a href="index2.php?type=<?=$_GET['type']?>" style="color:blue;font-size:18px;font-weight:bold">30 DAYS</a> 
</br></br>



<table class="js-sort-table" style="width:150%;margin-left:10px" id="myTable">
<thead>
<tr>
<th></th>		  	    
<th></th>			  	    
<th>ID</th>

<th>Applicant Name</th>
<th>Days</th>
<th>Category</th>

<th>Status</th>


<th>Action Taken</th>

<th>FRA</th>						


<th>Last Update</th>

<th>Date Deployed</th>			
<th>Date Arrival</th>			
<th>Passport#</th>
<th>OEC</th>
<th>AGENT</th>
<th>SUB AGENT</th>
<th>FB LINK</th>
</tr>	
</thead>
<?php
$SQLSELECT = "SELECT * FROM subject 


where  app_status NOT IN ('DONE','NO RESPONSE','ARRIVED','CASE DETAILS','ACTIVE')
AND app_status1!='DELETE'
AND   app_action == DATE_SUB(CURDATE(), INTERVAL 1 DAY)
group by SUBJ_ID order by SUBJ_ID desc";
$result_set =  mysqli_query($conn, $SQLSELECT);




if(isset($_POST['searchme']))
{
if($_POST['fra']!=111){    
$SQLSELECT = "SELECT * FROM subject 
LEFT JOIN subject_type
ON subject.w1 = subject_type.wel_id

LEFT JOIN subject_name
ON subject.w2 = subject_name.well_id

LEFT JOIN recruitment_agent
ON subject.agent_id = recruitment_agent.agent_id

where  fra_name='".$_POST['fra']."'

 order by SUBJ_ID desc ";

}
if($_POST['country']!=111){    
$SQLSELECT = "SELECT * FROM subject 

LEFT JOIN subject_type
ON subject.w1 = subject_type.wel_id

LEFT JOIN subject_name
ON subject.w2 = subject_name.well_id

LEFT JOIN recruitment_agent
ON subject.agent_id = recruitment_agent.agent_id
where  app_country='".$_POST['country']."' order by SUBJ_ID desc ";

}


echo'test';     
$result_set =  mysqli_query($conn, $SQLSELECT);      
}	



$count=1;
while($row = mysqli_fetch_array($result_set))
{
$datecountapplied=date_create("".$row['app_action']."");
$currentdate=date_create("".$date_now."");
$dateaddedfinal=date_diff($datecountapplied,$currentdate);
$dayone=$dateaddedfinal->format("%r%a");
$coundown=($row["numberone"])+$dayone;

if(6<$coundown)
{
$color111a="#FFB6C1";
$color21a="white";
}

if(7>=$coundown)
{
$color111a="white";
$color21a="black";
}

?>
<tbody>                   
<?php echo'<tr style="background:'.$color111a.';color:'.$color21.'">'; ?>
<td> 
</td>
<td> 
<a href="update.php?idme=<?=$row["SUBJ_ID"]?>&&imy=<?=$_SESSION['admin']['user']['user_name']?>"   onclick="basicPopup(this.href);return false"><button style="background:Red">Complaint</button></a> 
</td>

<td><?php echo $count; ?></td>
<td  style="width:400px"><b><?php echo $row['app_name']; ?></b></td>
<?php
echo"<td style='color:white;width:100px'></div>
<u style='padding:1px;color:black;font-size:16px;background:".$colorme.";font-weight:bold';> ";
echo $coundown;
echo "</u>";
echo"</td>";
?>

<td><?php echo $row['app_position']; ?></td>
<td><?php echo $row['app_status1']; ?></td>



<td><?php echo $row['app_remarks1']; ?></td>
<td><?php echo $row['fra_name']; ?></td>


			
  <?php
 IF($row['app_action']=='1970-01-01' || $row['app_action']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo"<td>".$row['app_action']."</td>"; }
 
 ?>
 
  <?php
 IF($row['app_deployed']=='1970-01-01' || $row['app_deployed']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo"<td>".$row['app_deployed']."</td>"; }
 
 ?>
 
 
 
 <?php
 IF($row['arrival']=='1970-01-01' || $row['arrival']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo"<td>".$row['arrival']."</td>"; }
 
 ?>
 


<td><?php echo $row['passport']; ?></td>
<td><?php echo $row['oec']; ?></td>
<td><?php echo $row['agent']; ?></td>
<td><?php echo $row['sub']; ?></td>
<td><?php echo $row['app_fb1']; ?></td>


</tr>
<?php
$count++;
}
?>

</tbody>		
</table>
		
		


</br>


</br></br>
<a  href="latest.php" style="color:red;text-align:left;font-size:18px;padding:10px;font-weight:bold">>> Latest Update </a>		

</form>	




	</div>
	</div>

	</body>
</html>