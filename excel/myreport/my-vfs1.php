<?php include'../review.php';?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {background:#F5F5F5;
FONT-FAMILY:arial;
font-size:12px;
}
#customers {
font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
border-collapse: collapse;
width: 100%;
}

#customers td, #customers th {
border: 1px solid #ddd;
padding: 4px;
}

#customers tr:nth-child(even){background-color: white;}

#customers tr:hover {background-color: white;}

#customers th {
padding-top: 10px;
padding-bottom: 10px;
text-align: left;
background-color: #DCDCDC;
color: black;
}
</style>
</head>
<body>
<?PHP
include'../tracking.php';
include'../db.php';
?>




<script>
function exportF(elem) {
  var table = document.getElementById("customers");
  var html = table.outerHTML;
  var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url 
  elem.setAttribute("href", url);
  elem.setAttribute("download", "export.xls"); // Choose the file name
  return false;
}
</script>






<h1 style="text-align:center"> HEARINGS Schedule  </h1>
<h3 style="text-align:center">As of <?php echo date('M-d-Y', strtotime($_GET["dateme"])); ?></p></h3>
<p>
<button onclick="goBack()">Go Back</button>

<script>
function goBack() {
  window.history.back();
}
</script></p>
<div class="row">




<div class="col-md-12 col-sm-12" style="margin-top:-1px;margin:10px;">
<div class="row" style="floar:left;background:white;padding:10px;min-height:100px">


<script>
function exportF(elem) {
  var table = document.getElementById("customers");
  var html = table.outerHTML;
  var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url 
  elem.setAttribute("href", url);
  elem.setAttribute("download", "export.xls"); // Choose the file name
  return false;
}
</script>


<a id="downloadLink" onclick="exportF(this)" style="color:Red">SAVE MY WORK</a>
<style>
table td{
font-size:12px;
}
table th{
font-size:14px;
}
</style>

<table id="customers" >
<tr>
<td colspan="18"><h2 style="text-align:center"></h2></td>
<tr>

<tr>

<th style="width:3%">#</th>
<th></th>
<th></th>
<th>NAME</th>
<th>TYPE</th>
<th>REF</th>
<th>DATE</th>
<th>TIME</th>

<th>LOCATION</th>
<th>ATTENDEES</th>		
<th>REMARKS</th>	
</tr>
<tr>
<?php
$con=mysql_connect('localhost','property_888web','Eclipse888!')or die ('cannot connect');
mysql_select_db('property_yl2',$con);
?>
<?php
$count=1;



$fit =mysql_query("SELECT * FROM  hearings  
LEFT JOIN subject
ON hearings.happ = subject.SUBJ_ID
where hdate='".$_GET["dateme"]."' ");

while($row=mysql_fetch_array($fit))
{

echo'<tr>';
echo'<td>'.$count.'</td>';
?>

<td  style="width:400px"><b><?php echo $row['app_name']; ?>, <?php echo $row['app_name1']; ?> <?php echo $row['app_name2']; ?></b></td>
 <td> 
<a href="../edit.php?idme=<?=$row["happ"]?>" target="_blank"><button>Hearings</button></a> 
</td>
<td> 
<a href="../update.php?idme=<?=$row["happ"]?>" target="_blank"><button style="background:Red">Complaint</button></a> 
</td>
<td  style="width:400px"><b><?php echo $row['app_name']; ?>, <?php echo $row['app_name1']; ?> <?php echo $row['app_name2']; ?></b></td>
          
<?php

echo'<td>'.$row["htype"].'</td>';
echo'<td>'.$row["href"].'</td>';
echo'<td>'.$row["hdate"].'</td>';
echo'<td>'.$row["htime"].'</td>';

echo'<td>'.$row["hloc"].'</td>';
echo'<td>'.$row["hattend"].'</td>';
echo'<td>'.$row["hremarks"].'</td>';
echo'</tr>';
$count++;

}
ECHO'</table>';
	
?>		

  
</div>

</body>
</html>
