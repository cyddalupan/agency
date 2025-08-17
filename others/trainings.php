<html>
<head>
<title>API Samples</title>
<meta http-equiv="Content-Type" content="utf-8">
<?php
session_start();
include'db.php';
include'tracking.php';
include'styles.php';
	
$app = mysql_query("SELECT * FROM applicant 
LEFT JOIN  	applicant_certificate
ON applicant.applicant_id = applicant_certificate.certificate_applicant

LEFT JOIN  	recruitment_agent
ON applicant.applicant_source = recruitment_agent.agent_id

where applicant_id=".$_GET['idme']." ");
$row=mysql_fetch_array($app);

if(isset($_POST['add_agreeme']))
{


$app1 = mysql_query("INSERT INTO appliocant_train (t_provider,t_place,t_issue,t_expired,t_name,m_app)
VALUES ('".$_POST['t_provider']."'
,'".$_POST['t_place']."'
,'".$_POST['t_issue']."'
,'".$_POST['t_expired']."'
,'".$_POST['t_name']."'
,".$_GET['idme']."
); ");

echo"Updated..";

}











?>

<title>Medical</title>

<!-- Bootstrap -->
<link href="../acct/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->















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
input{
width:500px;
padding:3px;	
}


</style>

<body style="background:white">
<div id="wrapper">

<div>
<h1 style="font-size:20px;red:black;text-align:left;color:black"> APPLICANT NAME: <?=$row['applicant_first']?> <?=$row['applicant_last']?></h1>
<h1 style="font-size:20px;red:black;text-align:left;color:black">  AGENT NAME: <?=$row['agent_first']?> <?=$row['agent_last']?></h1>
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




<div class="row" >
<div class="col-md-12 col-xs-12" >
<div class="x_panel" style="background:#F5F5F5">
<div class="x_content">



<form class="form-horizontal form-label-left input_mask" method="post" action="">	



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Name</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input name="t_name" type="text"  value="<?php echo $row['t_name']; ?>">

</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Provider</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input name="t_provider" type="text"  value="<?php echo $row['t_provider']; ?>">

</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Place</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input name="t_place" type="text"  value="<?php echo $row['t_place']; ?>">

</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Issue Date</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input name="t_issue" type="date" placeholder="yyyy-mm-dd" value="<?php echo $row['t_issue']; ?>">

</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Expired Date </label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input name="t_expired" type="date" placeholder="yyyy-mm-dd" value="<?php echo $row['t_expired']; ?>">
</div>
</div>


<div class="form-group">
<div class="col-md-4 col-sm-6 col-xs-12">
<input type="HIDDEN" class="form-control" name="applicant_id" value="<?=$_GET['idme']?>">
<button type="submit" class="btn btn-danger pull-right"  name="add_agreeme">Save</button>
</div>
</div>
</form>	





<style>
#customers {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #4CAF50;
  color: white;
}


#customers1 {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers1 td, #customers1 th {
  border: 1px solid #ddd;
  padding: 8px;
}

#customers1 tr:nth-child(even){background-color: #f2f2f2;}

#customers1 tr:hover {background-color: #ddd;}

#customers1 th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #87CEFA;
  color: white;
}

</style>





<h3>Trainings </h3>

  <form action="" method="post">
<table  cellspacing="1" id="customers" style="width:60%">
<thead>
<tr>
<th STYLE="width:2%"></th>
<th> Name</th>
<th>Provider</th>
<th>Place</th>
<th>Issue Date</th>
<th>Expired Date</th>

</tr>
</thead>
<tbody>
<?php	
if(isset($_POST['delete_orders'])){
foreach ($_POST['delall'] as $delall) {
mysql_query("delete from appliocant_train

WHERE t_id = ".$delall)


or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted.</p>";
}


$agreerow = mysql_query("SELECT * FROM  appliocant_train 
where m_app=".$_GET['idme']." order by t_id desc");
while($agreerow1=mysql_fetch_array($agreerow))
{
echo'<tr>';
echo "<td  STYLE='width:2%'><input type='checkbox' name='delall[]' value='".$agreerow1['t_id']."' ></td> ";
echo'<td>'.$agreerow1['t_name'].'</td>';
echo'<td>'.$agreerow1['t_provider'].'</td>';
echo'<td>'.$agreerow1['t_place'].'</td>';
echo'<td>'.$agreerow1['t_issue'].'</td>';
echo'<td>'.$agreerow1['t_expired'].'</td>';

echo'</tr>';
}

?>



</tbody>
</table>

</br>

<div class="row">
<div class="col-6" style="width:100%,float:right">		
<input type="submit" value="Delete" name="delete_orders" style="width:130px;background:blue;padding:5px;margin-left:20px;margin-top:-1px;color:white">

</div>
</div>


</form>	

















</form>	











</div>
</div>
</div>  
<div style="clear:both"></div>			
</div>			









</div>
</div>
<!-- /.col -->
</div>
<!-- /.row -->


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
</html>