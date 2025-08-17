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
<style>
* {
  box-sizing: border-box;
}

#myInput {
  background-position: 10px 10px;
  background-repeat: no-repeat;
  width: 100%;
  font-size: 16px;
  padding: 12px;
  border: 1px solid #ddd;
  margin-bottom: 12px;
  width:300px;
  margin-left:200px;
}

#myTable {
  border-collapse: collapse;
  width: 100%;
  border: 1px solid #ddd;
  font-size: 12px;
}

#myTable th, #myTable td {
  text-align: left;
  padding: 4px;
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
    padding: 8px 12px;
    text-decoration: none;
  margin:3px;
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
				<a class="brand" href="#">Imporst Excel Manumoti</a>
				
			</div>
		</div>
	</div>



	

	<div id="wrap">
	<div class="container">
	</br></br></br></br>	
		
<ul>
  <li><a  href="index.php">Home</a></li>
  <li><ahref="all.php">All</a></li>
  <li><a href="nc.php">Follow up</a></li>
   <li><a   href="prio.php">Priority</a></li>
   <li><a href="done.php">Done / Closed</a></li>
     <li><a class="active"   href="reports.php">Reports</a></li>
</ul>
</br>


<h2>All Applicants</h2>




<form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post"> 
<table>

<tr>
<td>
			<?php echo'
			<select class="form-control" name="country" required>';

			echo'<option value="111">-All Country-</option>';

			$SQLSELECT1 = "SELECT * FROM subject group by app_country ";
			$result_set1=  mysqli_query($conn, $SQLSELECT1);

			while($country1=mysqli_fetch_array($result_set1))
			{
			echo'<option value="'.$country1["app_country"].'">'.$country1["app_country"].'</option>';
			}
			echo'</select>';
			?>
</td>


<td>
			<?php echo'
			<select class="form-control" name="fra" required>';

			echo'<option value="111">-All FRA-</option>';

			$fra = "SELECT * FROM subject group by fra_name ";
			$result_setfra=  mysqli_query($conn, $fra);

			while($fra1=mysqli_fetch_array($result_setfra))
			{
			echo'<option value="'.$fra1["fra_name"].'">'.$fra1["fra_name"].'</option>';
			}
			echo'</select>';
			?>
</td>
			
<td><button type="submit" class="btn btn-primary"  name="searchme" >Search</button></td>
<td></td>			
</tr>
</table>



</form> 

<input type="text" id="myInput" onkeyup="myFunction()" placeholder="Search for names.." title="Type in a name">

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

<H2>Reports</H2>

	
	
	
	
	
	</div>
	</div>

	</body>
</html>


<script>
function myFunction() {
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById("myInput");
  filter = input.value.toUpperCase();
  table = document.getElementById("myTable");
  tr = table.getElementsByTagName("tr");
  for (i = 0; i < tr.length; i++) {
    td = tr[i].getElementsByTagName("td")[1];
    if (td) {
      txtValue = td.textContent || td.innerText;
      if (txtValue.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
    }       
  }
}
</script>