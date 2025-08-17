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

$app = mysql_query("UPDATE applicant_certificate
SET certificate_medical_clinic='".$_POST['certificate_medical_clinic']."'
, certificate_medical_exam_date='".$_POST['certificate_medical_exam_date']."'
, medical_fit='".$_POST['medical_fit']."'
, certificate_medical_result='".$_POST['certificate_medical_result']."'
, certificate_medical_remarks='".$_POST['certificate_medical_remarks']."'
, medical_cert='".$_POST['medical_cert']."'
, medical_certdate='".$_POST['medical_certdate']."'
, medicaltype='".$_POST['medicaltype']."'
, certificate_medical_expiration='".$_POST['certificate_medical_expiration']."'
WHERE certificate_applicant=".$_GET['idme']." ");


$app2 = mysql_query("UPDATE recruitment_agent
SET agent_contacts='".$_POST['agent_contacts']."'
, agent_contacts1='".$_POST['agent_contacts1']."'

WHERE agent_id=".$_POST['agent_id']." ");


$app3 = mysql_query("UPDATE applicant
SET applicant_contacts='".$_POST['applicant_contacts']."'
, applicant_contacts_2='".$_POST['applicant_contacts_2']."'

WHERE applicant_id=".$_GET['idme']." ");


$app1 = mysql_query("INSERT INTO medicalhistory (certificate_medical_clinic, certificate_medical_exam_date,medical_fit,
certificate_medical_result, certificate_medical_remarks,m_app,m_user,medical_cert,medical_certdate,medicaltype)
VALUES ('".$_POST['certificate_medical_clinic']."', '".$_POST['certificate_medical_exam_date']."',
'".$_POST['medical_fit']."', '".$_POST['certificate_medical_result']."', '".$_POST['certificate_medical_remarks']."', ".$_POST['applicant_id']."
, '".$_GET['user']."' , '".$_POST['medical_cert']."','".$_POST['medical_certdate']."','".$_POST['medicaltype']."'); ");

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
<label class="control-label col-md-3 col-sm-3 col-xs-12">Medical Type</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<select name="medicaltype" style="width:100%;border:2px solid red" REQUIRED>
<option><?=$row["medicaltype"]?></option>
<option>PRE MED</option>
<option>FULL MED</option>
<option>FULL MEDPHASE 1</option>
<option>FULL MED PHASE 2</option>
</select>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Medical Clinic </label>
<div class="col-md-4 col-sm-6 col-xs-12">



<input name="certificate_medical_clinic" type="text" value="<?php echo $row['certificate_medical_clinic']; ?>">


</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Exam Date</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input name="certificate_medical_exam_date" type="date" placeholder="yyyy-mm-dd" value="<?php echo $row['certificate_medical_exam_date']; ?>">

</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Date FTW</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input name="medical_fit" type="date" placeholder="yyyy-mm-dd" value="<?php echo $row['medical_fit']; ?>">
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Date Expiration</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input name="certificate_medical_expiration" type="date" placeholder="yyyy-mm-dd" value="<?php echo $row['certificate_medical_expiration']; ?>">
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">RESULT</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<select name="certificate_medical_result" style="width:100%;border:2px solid red">

<option value="">-- Select --</option>
<option value="FOR MEDICAL" <?php echo $row['certificate_medical_result'] == 'FOR MEDICAL' ? 'selected' : ''; ?>>FOR MEDICAL</option>

<option value="FIT TO WORK" <?php echo $row['certificate_medical_result'] == 'FIT TO WORK' ? 'selected' : ''; ?>>FIT TO WORK</option>
<option value="PENDING" <?php echo $row['certificate_medical_result'] == 'TREATMENT' ? 'selected' : ''; ?>>TREATMENT</option>

<option value="PENDING" <?php echo $row['certificate_medical_result'] == 'PENDING' ? 'selected' : ''; ?>>PENDING</option>

<option value="UNFIT" <?php echo $row['certificate_medical_result'] == 'UNFIT' ? 'selected' : ''; ?>>UNFIT</option>

</select>
</div>
</div>








<div class="form-group">
<label class="control-label col-md-2 col-sm-2 col-xs-12">Remarks</label>
<div class="col-md-8 col-sm-9 col-xs-12">
<textarea name="certificate_medical_remarks" rows="1" class="form-control" placeholder=""  style="width:700px;height:250px"><?php echo $row['certificate_medical_remarks']; ?></textarea>
	
</div>
</div>










<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">MEDICAL CERT</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<select name="medical_cert" style="width:100%;border:2px solid red">

<option value="">-- Select --</option>
<option value="" <?php echo $row['medical_cert'] == '' ? 'selected' : ''; ?>></option>

<option value="FOR FOLLOW UP" <?php echo $row['medical_cert'] == 'FOR FOLLOW UP' ? 'selected' : ''; ?>>FOR FOLLOW UP</option>
<option value="RECEIVED" <?php echo $row['medical_cert'] == 'RECEIVED' ? 'selected' : ''; ?>>RECEIVED</option>

</select>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Date CERT RECEIVED</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input name="medical_certdate" type="date" placeholder="yyyy-mm-dd" value="<?php echo $row['medical_certdate']; ?>">
</div>
</div>

<h2 style="margin-left:100px">NUMBER FORMAT: <b style="color:red">639953269012</b></h2>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Applicant Contact 1 </label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input name="applicant_contacts" type="text"  value="<?php echo $row['applicant_contacts']; ?>">
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Applicant Contact 2</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input name="applicant_contacts_2" type="text"  value="<?php echo $row['applicant_contacts_2']; ?>">
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Agent Contact 1</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input name="agent_contacts" type="text"  value="<?php echo $row['agent_contacts']; ?>">
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Agent Contact 2</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input name="agent_contacts1" type="text"  value="<?php echo $row['agent_contacts1']; ?>">
</div>
</div>
<input name="nameme" type="hidden"  value="<?php echo $row['applicant_first']; ?> <?php echo $row['applicant_last']; ?>">
<input name="agent_id" type="hidden"  value="<?php echo $row['agent_id']; ?>">







<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Send Sms?</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<select name="sendme" style="width:100%;border:2px solid red" REQUIRED>

<option value="1">NO</option>

</select>
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





<h3>History </h3>

  <form action="" method="post">
<table  cellspacing="1" id="customers">
<thead>
<tr>
<th>#</th>
<th>Clinic Name</th>
<th>Type</th>
<th>Date Exam</th>
<th>Date FTW</th>
<th>Result</th>
<th>Remarks</th>
<th>CERT</th>
<th>CERT DATE</th>
<th>Date Updated</th>
<th>User</th>
</tr>
</thead>
<tbody>
<?php	
$agreerow = mysql_query("SELECT medicalhistory.*,applicant.* FROM medicalhistory 
LEFT JOIN applicant
ON medicalhistory.m_app = applicant.applicant_id
where m_app=".$_GET['idme']." order by m_id desc");
while($agreerow1=mysql_fetch_array($agreerow))
{
echo'<tr>';
echo'<td>00'.$agreerow1['applicant_id'].'</td>';
echo'<td>'.$agreerow1['certificate_medical_clinic'].'</td>';
echo'<td>'.$agreerow1['medicaltype'].'</td>';
echo'<td>'.$agreerow1['certificate_medical_exam_date'].'</td>';
echo'<td>'.$agreerow1['medical_fit'].'</td>';
echo'<td>'.$agreerow1['certificate_medical_result'].'</td>';
echo'<td>'.$agreerow1['certificate_medical_remarks'].'</td>';
echo'<td>'.$agreerow1['medical_cert'].'</td>';
echo'<td>'.$agreerow1['medical_certdate'].'</td>';
echo'<td>'.$agreerow1['m_date'].'</td>';
echo'<td>'.$agreerow1['m_user'].'</td>';
echo'</tr>';
}

?>

</tbody>
</table>





















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