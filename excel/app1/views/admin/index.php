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
      table { border: 1px solid black; border-collapse: collapse; width:110%;text-transform:uppercase}

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
			<div class="span6" id="form-login">
			
			

			
			
			
			
			
			
			</div>
			<div class="span3 hidden-phone"></div>
		</div>
		
<ul>
  <li><a class="active" href="index.php">Home</a></li>
  <li><a href="all.php">Hearings</a></li>
   <li><a href="prio.php">Complaint</a></li>
   <li><a href="done.php">All</a></li>
 <li><a href="search.php">Search</a></li>
</ul>
</br>







<form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post" ACTION="search.php">

<div class="form-group">
<div class="col-md-4 col-sm-4 col-xs-4 form-group">
<input type="text" placeholder="first name , last name " class="form-control" name="search1" required style="padding:8px;border:2px solid gray;width:400px">
<button type="submit" class="btn btn-info"  name="SearchME" >Search Applicant</button>

</div>
</br>

</form>  






<form action="" method="post" style="width:500px;FLOat:left">	



<section id="body">
dasdadasadad
<div class="blink"><span>Schedule. . . </span></div>
	<iframe src="https://workasia.agency/system/excel/index2.php" style="width:100%;margin:auto 0 ;height:1000px"></iframe>
		
		
	</section>	

</br>






	</div>
	</div>

	</body>
</html>