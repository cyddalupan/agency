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


   <style type="text/css">
      table { border: 1px solid black; border-collapse: collapse; width:110%}

        th, td { padding: 1px; border: 1px solid black;font-size:12px }

        thead { background: #ddd; }

        table#demo2.js-sort-0 tbody tr td:nth-child(1),
        table#demo2.js-sort-1 tbody tr td:nth-child(2),
        table#demo2.js-sort-2 tbody tr td:nth-child(3),
        table#demo2.js-sort-3 tbody tr td:nth-child(4),
        table#demo2.js-sort-4 tbody tr td:nth-child(5),
        table#demo2.js-sort-5 tbody tr td:nth-child(6),
        table#demo2.js-sort-6 tbody tr td:nth-child(7),
        table#demo2.js-sort-7 tbody tr td:nth-child(8),
        table#demo2.js-sort-8 tbody tr td:nth-child(9),
        table#demo2.js-sort-9 tbody tr td:nth-child(10) {
           
        }
		
		.js-sort-table {
    border-collapse: collapse;
    margin: 25px 0;
    font-size: 0.9em;
    font-family: sans-serif;
    min-width: 400px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
}

	.js-sort-table thead tr {
    background-color: #009879;
    color: #ffffff;
    text-align: left;
}

	.js-sort-table th,
.styled-table td {
    padding: 12px 15px;
}

	.js-sort-table tbody tr {
    border-bottom: 1px solid #dddddd;
}

	.js-sort-table tbody tr:nth-of-type(even) {
    background-color: #f3f3f3;
}

	.js-sort-table tbody tr:last-of-type {
    border-bottom: 2px solid #009879;
}

	.js-sort-table tbody tr.active-row {
    font-weight: bold;
    color: #009879;
}

    </style>
	<!-- Navbar	
	
	

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




	

	<div id="wrap">
	<div class="container">
		<div class="row">
			<div class="span3 hidden-phone"></div>
		
			<div class="span3 hidden-phone"></div>
		</div>
	<br>	<br>
	
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

	
<ul>
	<li><a  href="index.php">Home</a></li>
<li><a  href="all.php">All</a></li>

<li><a href="prio.php">Complaint</a></li>
<li><a href="done.php">Done / Closed</a></li>
<li><a class="active" href="search.php">Search</a></li>
  <li><a href="hearings.php" style="color:yellow">Hearing Schedule</a></li>
</ul>
</br>







<form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post" ACTION="search.php">

<div class="form-group">
<div class="col-md-4 col-sm-4 col-xs-4 form-group">
<input type="text" placeholder="first name , last name ,remarks " class="form-control" name="search1" required style="padding:8px;border:2px solid gray;width:400px">
<button type="submit" class="btn btn-info"  name="SearchME" >Search Applicant</button>

</div>
</br>

</form>  







<form action="" method="post" style="width:500px;FLOat:left">	



<section id="body">



<table style="margin-left:10px" class="js-sort-table" id="demo1">
<thead>
<tr>
<th></th>
<th></th>		  	    
<th></th>			  	    
<th>ID</th>

<th>Applicant Name</th>
<th>Days</th>

<th>Facebook</th>
<th>Contact</th>
<th>Status</th>
<th>Type</th>
<th>Type Name</th>
<th>Welfare R</th>
<th>Office R</th>
<th>Agent</th>
<th>FRA</th>						

<th>FRA Contact</th>	
<th>Date Update</th>

<th>Country</th>
<th>Position</th>

<th>Date Deployed</th>		
<th STYLE="background:red">Updated_at</th>

			</tr>	
		</thead>
		<?php
		
		if(isset($_POST['delete_orders'])){
				foreach ($_POST['delall'] as $delall) {
						
			

				$SQLSELECT = "delete  FROM subject where SUBJ_ID= ".$delall."";
				$result_set =  mysqli_query($conn, $SQLSELECT);

				
						
				}
				echo"<p style='color:Red'>Succesfully Deleted</p>";
		}	
		

if(isset($_POST['SearchME']))
{

$SQLSELECT ="SELECT * FROM subject 

LEFT JOIN subject_type
ON subject.w1 = subject_type.wel_id

LEFT JOIN subject_name
ON subject.w2 = subject_name.well_id

LEFT JOIN recruitment_agent
ON subject.agent_id = recruitment_agent.agent_id
where
app_name LIKE '%".$_POST['search1']."%'
or app_name1 LIKE '%".$_POST['search1']."%'
or app_name2 LIKE '%".$_POST['search1']."%'
or app_remarks LIKE '%".$_POST['search1']."%'
LIMIT 200 ";
$result_set =  mysqli_query($conn, $SQLSELECT);

ECHO $_POST['search1'];
}


		
		
	

		
		
$count=1;
while($row = mysqli_fetch_array($result_set))
{
	$datecountapplied=date_create("".$row['app_last']."");
$currentdate=date_create("".$date_now."");
$dateaddedfinal=date_diff($datecountapplied,$currentdate);
$dayone=$dateaddedfinal->format("%r%a");
$coundown=($row["numberone"])+$dayone;

if(80<$coundown)
{
$color111a="#FFB6C1";
$color21a="white";
}

if(81>=$coundown)
{
$color111a="white";
$color21a="black";
}

                    ?>
                    
                    <?php echo'<tr style="background:'.$color111a.';color:'.$color21.'">'; ?>
                    <?php echo "<td><input type='checkbox' name='delall[]' value='".$row['SUBJ_ID']."' ></td> "; ?>
                      <td> 
                    <a href="edit.php?idme=<?=$row["SUBJ_ID"]?>" onclick="basicPopup(this.href);return false"><button>Hearings</button></a> 
                    </td>
                     <td> 
                    <a href="update.php?idme=<?=$row["SUBJ_ID"]?>"   onclick="basicPopup(this.href);return false"><button style="background:Red">Complaint</button></a> 
                    </td>
                    
                    <td><?php echo $count; ?></td>
                    <td  style="width:400px"><b><?php echo $row['app_name']; ?> <?php echo $row['app_name1']; ?> <?php echo $row['app_name2']; ?></b></td>
                    <?php
                    echo"<td style='color:white;width:100px'></div><u style='padding:1px;color:black;font-size:16px;background:".$colorme.";font-weight:bold';> ";
                    echo $coundown;
                    echo "</u>";
                    echo"</td>";
                    ?>
                   
                    <td><?php echo $row['app_fb']; ?></td>
                    <td><?php echo $row['app_contact']; ?></td>
                    <td><?php echo $row['app_status']; ?></td>
                   <td><?php echo $row['wel_type']; ?></td>
					<td><?php echo $row['well_name']; ?></td>
					  <td><?php echo $row['app_remarks']; ?></td>
					<td><?php echo $row['app_remarks1']; ?></td>
					<td><?php echo $row['agent_first']; ?> <?php echo $row['agent_last']; ?></td>
                    <td><?php echo $row['fra_name']; ?></td>
                    <td><?php echo $row['fra_contact']; ?></td>
                    <td><?php echo $row['app_last']; ?></td>
                    
					 <td><b><?php echo $row['app_country']; ?></b></td>
                    <td><?php echo $row['app_position']; ?></td>
                    <td><?php echo $row['app_deployed']; ?></td>
					<td><?php echo $row['app_update']; ?></td>
					
                  
                    
                    </tr>
                    <?php
                    $count++;
                    }
			?>
		</table>
		
		
	</section>	

</br>
<div class="row">
<div class="col-5" style="width:100%,float:right">		
<input type="submit" value="Delete" name="delete_orders" style="width:130px;background:Red;padding:5px;margin-left:20px;margin-top:-1px;color:white">

</div>
</div>


</form>	




	</div>
	</div>

	</body>
</html>