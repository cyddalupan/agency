<?php 
session_start();
if(!isset($_SESSION['admin']['user'])){
header("location:noaccess.php");
}
	include 'db.php';
	


?>	
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>manumoti</title>
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
popupWindow = window.open(url,'popUpWindow','height=600,width=1200,left=50,top=-150,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes')
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
            background: #dee;
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
				<a class="brand" href="#">Imporst Excel Manumoti</a>
				
			</div>
		</div>
	</div>



	

	<div id="wrap">
	<div class="container">
		<div class="row">
			<div class="span3 hidden-phone"></div>
			
			<div class="span3 hidden-phone"></div>
		</div>
		
<ul>
  <li><a class="active" href="index.php">Home</a></li>
  <li><a href="all.php">All</a></li>
  <li><a href="nc.php">Follow up</a></li>
   <li><a href="prio.php">Priority</a></li>
   <li><a href="done.php">Done / Closed</a></li>
    <li><a href="reports.php">Reports</a></li>
</ul>
</br>
















<section id="body">

<h1>Latest Data</h1>
<br>

		<table style="margin-left:-100px" class="js-sort-table" id="demo1">
			<thead>
			<tr>
			<th></th>		  	    
			<th></th>			  	    
			<th>ID</th>

			<th>Applicant Name</th>
			<th>Days</th>
			<th>Country</th>
			<th>Position</th>
			<th>Date Applied</th>
			<th>Date Deployed</th>
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
			<th>Uploaded</th>

			</tr>	
		</thead>
		<?php
		$SQLSELECT = "SELECT * FROM subject 

LEFT JOIN subject_type
ON subject.w1 = subject_type.wel_id

LEFT JOIN subject_name
ON subject.w2 = subject_name.well_id

LEFT JOIN recruitment_agent
ON subject.agent_id = recruitment_agent.agent_id
	
		WHERE   app_update <= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
		order by app_update asc limit 100";
		$result_set =  mysqli_query($conn, $SQLSELECT);
		
		
		
	

		
		
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
                    
                      <td> 
                    <a href="edit.php?idme=<?=$row["SUBJ_ID"]?>" onclick="basicPopup(this.href);return false"><button>Edit</button></a> 
                    </td>
                     <td> 
                    <a href="update.php?idme=<?=$row["SUBJ_ID"]?>"   onclick="basicPopup(this.href);return false"><button style="background:Red">Update</button></a> 
                    </td>
                    
                    <td><?php echo $count; ?></td>
                    <td  style="width:400px"><b><?php echo $row['app_name']; ?></b></td>
                    <?php
                    echo"<td style='color:white;width:100px'></div><u style='padding:1px;color:black;font-size:16px;background:".$colorme.";font-weight:bold';> ";
                    echo $coundown;
                    echo "</u>";
                    echo"</td>";
                    ?>
                    <td><b><?php echo $row['app_country']; ?></b></td>
                    <td><?php echo $row['app_position']; ?></td>
                    <td><?php echo $row['app_applied']; ?></td>
                    <td><?php echo $row['app_deployed']; ?></td>
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
                    <td><?php echo $row['app_update']; ?></td>
                  
                    
                    </tr>
                    <?php
                    $count++;
                    }
			?>
		</table>
		
		
	</section>	
</br>
<a  href="index.php" style="color:red;text-align:left;font-size:18px;padding:10px;font-weight:bold">>> BACK </a>		








	</div>
	</div>

	</body>
</html>