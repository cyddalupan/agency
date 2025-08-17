<html>
<head>
<title>API Samples</title>
<meta http-equiv="Content-Type" content="utf-8">
<h2 style="margin-left:100px">NUMBER FORMAT: <b style="color:red">639953269012 CHANGE ZERO TO 63</b> </h2>

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



$app2 = mysql_query("UPDATE recruitment_agent
SET agent_contacts='".$_POST['agent_contacts']."'
, agent_contacts1='".$_POST['agent_contacts1']."'

WHERE agent_id=".$_POST['agent_id']." ");


$app3 = mysql_query("UPDATE applicant
SET applicant_contacts='".$_POST['applicant_contacts']."'
, applicant_contacts_2='".$_POST['applicant_contacts_2']."'

WHERE applicant_id=".$_GET['idme']." ");





$app4 = mysql_query("UPDATE applicant_certificate
SET certificate_tesda_date='".$_POST['certificate_tesda_date']."'
,certificate_tesda_release='".$_POST['certificate_tesda_release']."'
WHERE certificate_applicant=".$_GET['idme']." ");




echo"Updated..";

}




// message  me

 if(isset($_POST["add_agreeme"])){

					$username = 'brittle007';
			$password = 'adonis13288';
			$message = "Good day Ms. ".""."".$row['applicant_first']."  ".$row['applicant_last']."".",please be informed of your TESDA schedule dated ".date('M-d-Y', strtotime($_POST['certificate_tesda_date']))."  To ".date('M-d-Y', strtotime($_POST['certificate_tesda_date']))." 8:00am.".""."Please be at Sky Lines OFFICE 30 minutes before your schedule."."\n\n".$_POST["staff"];
			$destination = urlencode($_POST["applicant_contacts"]);
		
			$data = array (
			'sendid' => 'Sky Lines',
			'recipient' => array
			(
			array('dstno' => $destination, 'msg' => $message, 'type' => '1')
			),
			'agreedterm' =>  'YES',
			'method' => 'isms_send_all_id'
			);
			$payload = json_encode($data);
			$ch = curl_init('https://www.isms.com.my/RESTAPI.php');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			$headers = array(
			'Content-Type: application/json',
			'Authorization: Basic '. base64_encode("$username:$password"),
			'Content-Length: ' . strlen($payload)
			);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$result = curl_exec($ch);
			echo $result;
			curl_close($ch);
$message1 = mysql_query("INSERT INTO applicant_message (me_app, me_agent,message,me_user,me_contact)
VALUES (".$_GET['idme'].",".$_POST['agent_id'].",'".$message."', '".$_GET['user']."', '".$destination."' ); ");
 }
 
 
  if(isset($_POST["add_agreeme"])){

			$username = 'brittle007';
			$password = 'adonis13288';
			$message = "Good day Ms. ".""."".$row['applicant_first']."  ".$row['applicant_last']."".",please be informed of your TESDA schedule dated ".date('M-d-Y', strtotime($_POST['certificate_tesda_date']))."  To ".date('M-d-Y', strtotime($_POST['certificate_tesda_date']))." 8:00am.".""."Please be at Sky Lines OFFICE 30 minutes before your schedule."."\n\n".$_POST["staff"];
				$destination = urlencode($_POST["applicant_contacts_2"]);
		
			$data = array (
			'sendid' => 'Sky Lines',
			'recipient' => array
			(
			array('dstno' => $destination, 'msg' => $message, 'type' => '1')
			),
			'agreedterm' =>  'YES',
			'method' => 'isms_send_all_id'
			);
			$payload = json_encode($data);
			$ch = curl_init('https://www.isms.com.my/RESTAPI.php');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			$headers = array(
			'Content-Type: application/json',
			'Authorization: Basic '. base64_encode("$username:$password"),
			'Content-Length: ' . strlen($payload)
			);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$result = curl_exec($ch);
			echo $result;
			curl_close($ch);
$message1 = mysql_query("INSERT INTO applicant_message (me_app, me_agent,message,me_user,me_contact)
VALUES (".$_GET['idme'].",".$_POST['agent_id'].",'".$message."', '".$_GET['user']."', '".$destination."' ); ");




 }
 
 
 
  if(isset($_POST["add_agreeme"])){

			$username = 'brittle007';
			$password = 'adonis13288';
			$message = "Good day Ms. ".""."".$row['applicant_first']."  ".$row['applicant_last']."".",please be informed of your TESDA schedule dated ".date('M-d-Y', strtotime($_POST['certificate_tesda_date']))."  To ".date('M-d-Y', strtotime($_POST['certificate_tesda_date']))." 8:00am.".""."Please be at Sky Lines OFFICE 30 minutes before your schedule."."\n\n".$_POST["staff"];
				$destination = urlencode($_POST["agent_contacts"]);
		
			$data = array (
			'sendid' => 'Sky Lines',
			'recipient' => array
			(
			array('dstno' => $destination, 'msg' => $message, 'type' => '1')
			),
			'agreedterm' =>  'YES',
			'method' => 'isms_send_all_id'
			);
			$payload = json_encode($data);
			$ch = curl_init('https://www.isms.com.my/RESTAPI.php');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			$headers = array(
			'Content-Type: application/json',
			'Authorization: Basic '. base64_encode("$username:$password"),
			'Content-Length: ' . strlen($payload)
			);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$result = curl_exec($ch);
			echo $result;
			curl_close($ch);
$message1 = mysql_query("INSERT INTO applicant_message (me_app, me_agent,message,me_user,me_contact)
VALUES (".$_GET['idme'].",".$_POST['agent_id'].",'".$message."', '".$_GET['user']."', '".$destination."' ); ");



 }
 
 
 
  if(isset($_POST["add_agreeme"])){

				$username = 'brittle007';
			$password = 'adonis13288';
				$message = "Good day Ms. ".""."".$row['applicant_first']."  ".$row['applicant_last']."".",please be informed of your TESDA schedule dated ".date('M-d-Y', strtotime($_POST['certificate_tesda_date']))."  To ".date('M-d-Y', strtotime($_POST['certificate_tesda_date']))." 8:00am.".""."Please be at Sky Lines OFFICE 30 minutes before your schedule."."\n\n".$_POST["staff"];
			$destination = urlencode($_POST["agent_contacts1"]);
		
			$data = array (
			'sendid' => 'Sky Lines',
			'recipient' => array
			(
			array('dstno' => $destination, 'msg' => $message, 'type' => '1')
			),
			'agreedterm' =>  'YES',
			'method' => 'isms_send_all_id'
			);
			$payload = json_encode($data);
			$ch = curl_init('https://www.isms.com.my/RESTAPI.php');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			$headers = array(
			'Content-Type: application/json',
			'Authorization: Basic '. base64_encode("$username:$password"),
			'Content-Length: ' . strlen($payload)
			);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$result = curl_exec($ch);
			echo $result;
			curl_close($ch);
$message1 = mysql_query("INSERT INTO applicant_message (me_app, me_agent,message,me_user,me_contact)
VALUES (".$_GET['idme'].",".$_POST['agent_id'].",'".$message."', '".$_GET['user']."', '".$destination."' ); ");


 }

  if(isset($_POST["add_agreeme"])){

				$username = 'brittle007';
			$password = 'adonis13288';
			$message = "Good day Ms. ".""."".$row['applicant_first']."  ".$row['applicant_last']."".",please be informed of your TESDA schedule dated ".date('M-d-Y', strtotime($_POST['certificate_tesda_date']))."  To ".date('M-d-Y', strtotime($_POST['certificate_tesda_date']))." 8:00am.".""."Please be at Sky Lines OFFICE 30 minutes before your schedule."."\n\n".$_POST["staff"];
			$destination = urlencode($_GET["numberme"]);
		
			$data = array (
			'sendid' => 'Sky Lines',
			'recipient' => array
			(
			array('dstno' => $destination, 'msg' => $message, 'type' => '1')
			),
			'agreedterm' =>  'YES',
			'method' => 'isms_send_all_id'
			);
			$payload = json_encode($data);
			$ch = curl_init('https://www.isms.com.my/RESTAPI.php');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			$headers = array(
			'Content-Type: application/json',
			'Authorization: Basic '. base64_encode("$username:$password"),
			'Content-Length: ' . strlen($payload)
			);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$result = curl_exec($ch);
			echo $result;
			curl_close($ch);
$message1 = mysql_query("INSERT INTO applicant_message (me_app, me_agent,message,me_user,me_contact)
VALUES (".$_GET['idme'].",".$_POST['agent_id'].",'".$message."', '".$_GET['user']."', '".$destination."' ); ");


 }








?>

<title>Medical</title>

<!-- Bootstrap -->
<link href="../acct/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->


<link href="../acct/build/css/custom.min.css" rel="stylesheet">
</head>
<body>


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
a{
font-size:16px;
font-weight:bold;
border:2px solid black;
padding:5px;	
}

</style>

<body style="background:white">
<div id="wrapper">



<a href="sms_app.php?idme=<?=$_GET['idme']?>&&user=<?=$_GET['user']?>&&numberme=<?=$_GET['numberme']?>" style="color:blue">SMS Manual</a>
| <a href="sms_tesda.php?idme=<?=$_GET['idme']?>&&user=<?=$_GET['user']?>&&numberme=<?=$_GET['numberme']?>"  style="color:blue">SMS TESDA</a>
| <a href="sms_con.php?idme=<?=$_GET['idme']?>&&user=<?=$_GET['user']?>&&numberme=<?=$_GET['numberme']?>"  style="color:blue">SMS CONTRACT</a>

| <a href="sms_owwa.php?idme=<?=$_GET['idme']?>&&user=<?=$_GET['user']?>&&numberme=<?=$_GET['numberme']?>"  style="color:blue">SMS OWWA</a>
| <a href="sms_bio.php?idme=<?=$_GET['idme']?>&&user=<?=$_GET['user']?>&&numberme=<?=$_GET['numberme']?>"  style="color:blue">SMS BIOMETRIC </a>
| <a href="sms_swab.php?idme=<?=$_GET['idme']?>&&user=<?=$_GET['user']?>&&numberme=<?=$_GET['numberme']?>"  style="color:blue">SMS Swab Test</a>
| <a href="sms_flight.php?idme=<?=$_GET['idme']?>&&user=<?=$_GET['user']?>&&numberme=<?=$_GET['numberme']?>"  style="color:blue">SMS FLIGHT </a>


<h1 style="color:black">SMS TESDA</h1>

</br>

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
<label class="control-label col-md-3 col-sm-3 col-xs-12">TESDA FROM </label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input name="certificate_tesda_date" type="date" value="<?php echo $row['certificate_tesda_date']; ?>">
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">TESDA TO </label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input name="certificate_tesda_release" type="date" value="<?php echo $row['certificate_tesda_release']; ?>">
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Staff Information</label>
<div class="col-md-4 col-sm-9 col-xs-12">

<textarea name="staff" rows="1" class="form-control" placeholder=""  required style="width:400px;height:100px" readonly>
Please reply to this number YOUR NAME ,CONTACT # for confirmation: <?=$_GET['user']?> +<?=$_GET['numberme']?></textarea>

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
  background-color: #87CEFA;
  color: white;
}
</style>







</br></br>



<h3 style="color:blue">SMS History </h3>

  <form action="" method="post">
<table  cellspacing="1" id="customers">
<thead>
<tr>
<th>APPLICANT NAME</th>
<th>AGENT Name</th>
<th>MESSAGE</th>
<th>DATE</th>
<th>NUMBER</th>
<th>USER</th>
</tr>
</thead>
<tbody>
<?php	
$messages = mysql_query("SELECT * FROM applicant_message 
LEFT JOIN applicant
ON applicant_message.me_app = applicant.applicant_id

LEFT JOIN  	recruitment_agent
ON applicant.applicant_source = recruitment_agent.agent_id

where me_app=".$_GET['idme']." 
AND me_contact!=''
order by message_id desc");
while($messages1=mysql_fetch_array($messages))
{
$countme=strlen($messages1['me_contact']);	

if($countme==12) {$hide='';};	
if($countme!=12) {$hide='style="display:none"';};
echo'<tr '.$hide.'>';
echo'<td>'.$messages1['applicant_first'].' '.$messages1['applicant_last'].'</td>';
echo'<td>'.$messages1['agent_first'].' '.$messages1['agent_last'].'</td>';
echo'<td>'.$messages1['message'].'</td>';
echo'<td>'.$messages1['me_date'].'</td>';
echo'<td>'.$messages1['me_contact'].'</td>';
echo'<td>'.$messages1['me_user'].'</td>';
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