<?php include'review.php';?>
<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<!-- Head -->
<head>
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
<title></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../assets/styles/admin/bootstrap.min.css" rel="stylesheet" />
<link href="../assets/styles/admin/font-awesome.min.css" rel="stylesheet" />


<!--Beyond styles-->
<link id="beyond-link" href="../assets/styles/admin/beyond.min.css" rel="stylesheet" type="text/css" />
<script>
function exportF(elem) {
  var table = document.getElementById("example-checkbox");
  var html = table.outerHTML;
  var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url 
  elem.setAttribute("href", url);
  elem.setAttribute("download", "export.xls"); // Choose the file name
  return false;
}
</script>

 
</head>
<style>
th{
font-size:12px;
font-weight:none;
color:blue;	
padding:1px;
}
tD{
padding:1px;
}
</style>

<!-- /Head -->
<!-- Body -->
<body style="background:white">

<div class="col-md-14 col-sm-14" style="margin-top:-1px">
<div class="row">
<div class="col-md-12">

<div class="clients-page">

<div class="col-sm-2 pull-right" align="center">
<a href="javascript:window.close()" class="btn btn-md btn-danger">Close</a>
</div>

<div class="row">
<div class="col-md-12" style="margin:20px">
<?php





if($_POST['status_app']=='99999'){
$stt="applicant_status NOT IN (999999999999)";
$titi='ALL ';
$filtme="";		
}



if($_POST['status_app']=='1'){
$stt="applicant_status IN (5)";
$titi='CV SENT';	
		   //if($_POST['start']=='' || $_POST['start']=='0000-00-00'){
			 $filtme="";
		   //}
		  // if($_POST['start']!=''){
			  
			//$filtme="AND applicant_updated between '".$_POST['start']."' AND '".$_POST['end']."'";			   
		   //}	
}





if($_POST['status_app']=='2'){
$stt="applicant_status   NOT IN (9,21,22,1,14,25,5,8,12,0,15)";
$titi='GENERAL REPORTSs';	
$filtme="";

			if($_POST['start']=='' || $_POST['start']=='0000-00-00'){
			 $filtme="";
		   }
		   if($_POST['start']!=''){
			  
			$filtme="AND applicant.applicant_updated between '".$_POST['start']."' AND '".$_POST['end']."'";			   
		   }	
	
}

if($_POST['status_app']=='3'){
$stt="applicant_status IN (8,12)";	
$titi='TICKET';
$filtme="";	
}

if($_POST['status_app']=='26'){
$stt="applicant_status IN (26)";
$titi='w/ Transmittal';			
$filtme="";	
}

if($_POST['status_app']=='27'){
$stt="applicant_status IN (27)";
$titi='For Contract Signing';		
$filtme="";		
}

if($_POST['status_app']=='28'){
$stt="applicant_status IN (28)";
$titi='Owwa';			
$filtme="";	
}

if($_POST['status_app']=='29'){
$stt="applicant_status IN (29)";
$titi='w/ Owwa Schedule';		
$filtme="";		
}

if($_POST['status_app']=='32'){
$stt="applicant_status IN (32)";
$titi='Visa Stamped';		
$filtme="";		
}




if($_POST['status_app']=='21'){
$stt="applicant_status IN (21)";
$titi='UNFIT';			
$filtme="";	
}


if($_POST['status_app']=='114'){
$stt="applicant_status IN (1,25)";
$titi='BACKOUT';		
$filtme="";		
}

if($_POST['status_app']=='30'){
$stt="applicant_status IN (30)";
$titi='OEC Filled';			
$filtme="";	
}

if($_POST['status_app']=='33'){
$stt="applicant_status IN (33)";
$titi='OEC Filling';		
$filtme="";		
}

if($_POST['status_app']=='31'){
$stt="applicant_status IN (31)";
$titi='OEC Released';		
		if($_POST['start']=='' || $_POST['start']=='0000-00-00'){
			 $filtme="";
		   }
		   if($_POST['start']!=''){
			  
			$filtme="AND requirement_oec_release_date between '".$_POST['start']."' AND '".$_POST['end']."'";			   
		   }		
}

if($_POST['status_app']=='35'){
$stt="applicant_status IN (35)";
$titi='VISA Released';		
$filtme="";		
}



include'db.php';
if($_POST['employer']=='99999'){

	$employer="";	 

}

if($_POST['employer']!='99999'){
$employer="applicant_employer=".$_POST['employer']." AND";	
}


if($_POST['sub_status']=='99999'){

	$sub_status="";	
}
if($_POST['sub_status']!='99999'){
$sub_status="sub_status='".$_POST['sub_status']."' AND";	
}


if($_POST['agent']=='99999'){

	$agent="";	
}
if($_POST['agent']!='99999'){
$agent="applicant_source=".$_POST['agent']." AND";	
}



?>

<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h3 STYLE="text-align:center"><span> <?=$titi?>  </span></h3>
<!-- para -->
</div> 

<div class="row">
<div class="col-md-12"  style=" padding-bottom:20px;">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">
<div class="table-responsive">
<?php
$counter=1;
?>
<a id="downloadLink" onclick="exportF(this)" style="color:Red">SAVE MY WORK</a>



<style>
table td,th{
font-size:12px;	
}
</style>

<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="FONT-SIZE:9px;width:99%" >
<thead>
<tr>
<th STYLE="WIDTH:10px;color:black">#</th>
<th>Date Applied </th>

<th >Name</th>
<th>AGENT</th>	
<th >FRA</th>

<th>CONTRACT ARRIVED</th>
<th>CONTRACT SIGNED</th>	
<th>OWWA</th>
<th>SPONSOR NAME</th>	
<th>VISA #</th>	
<th>SPONSOR ID</th>		
<th>VISA</th>	
<th >OEC</th>	
<th>STATUS</th>

<th style=""></th>																	
</tr>
</thead>
<tbody>

<?php
$counter=1;




$app = mysql_query("SELECT * FROM applicant


		LEFT JOIN  statuses
				ON applicant.applicant_status = statuses.number

				LEFT JOIN  	applicant_certificate
				ON applicant.applicant_id = applicant_certificate.certificate_applicant


				LEFT JOIN  	applicant_requirement
				ON applicant.applicant_id =applicant_requirement.requirement_applicant

				LEFT JOIN  	employer
				ON applicant.applicant_employer =employer.employer_id

				LEFT JOIN  	recruitment_agent
				ON applicant.applicant_source =recruitment_agent.agent_id


			

where
$employer
$sub_status
$agent
$stt
$filtme
order by applicant_status asc
 ");
?>   		
<?php

while($row=mysql_fetch_array($app))
{			
$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
$employer=mysql_fetch_array($emp);

$cert = mysql_query("SELECT * FROM applicant_certificate where certificate_applicant=".$row['applicant_id']." ");
$row1=mysql_fetch_array($cert);

$req = mysql_query("SELECT * FROM applicant_requirement where requirement_applicant=".$row['applicant_id']." ");
$row2=mysql_fetch_array($req);

$agent = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$agentrow1=mysql_fetch_array($agent);

if($row["applicant_status"]==0 || $row["applicant_status"]==5 ) { $new_date = date('Y-m-d', strtotime($row['applicant_date_applied'])); }
else { $new_date = date('Y-m-d', strtotime($row['applicant_updated'])); }


$number=str_pad($row['applicant_id'], 10, '0', STR_PAD_LEFT);
echo"<td>".$counter."</td>";

echo'<td>'; echo date('M-d-Y', strtotime($row['applicant_date_applied']));echo'</td>';

echo"<td><a target='_blank'  href='".$review."".$row['applicant_id']."'>
".$row['applicant_first']."  ".$row['applicant_last']."</a></td>";
echo"<td>".$agentrow1['agent_first']." ".$agentrow1['agent_last']."</td>";


$line = mysql_query("SELECT * FROM multiple_lineups where applicant_id=".$row['applicant_id']." order by id desc");
$lineup=mysql_fetch_array($line);


$datecountapplied=date_create("".$lineup['created_at']."");
$currentdate=date_create("".$date_now."");
$dateaddedfinal=date_diff($datecountapplied,$currentdate);
$dayone=$dateaddedfinal->format("%r%a");
$coundown=(14+$row["numberone"])-$dayone;

if(5<$coundown) {$colorme="lightblue";}
if(6>=$coundown) {$colorme="red";}

echo"<td>".$employer['employer_name']."</td>";

IF($row2['requirement_contract']=='1970-01-01' || $row2['requirement_contract']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row2['requirement_contract']));echo'</td>'; }

IF($row2['requirement_contract_sign']=='1970-01-01' || $row2['requirement_contract_sign']=='0000-00-00'  
|| $row2['requirement_contract_sign']=='0001-01-01'  || $row2['requirement_contract_sign']==null ) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row2['requirement_contract_sign']));echo'</td>'; }

IF($row['applicant_ex']=="EX ABROAD"){ echo"<td>EX ABROAD</td>";}
else { echo"<td>";

IF($row1['certificate_owwa_from']=='1970-01-01' || $row1['certificate_owwa_from']=='0000-00-00' || $row1['certificate_owwa_from']==null ) {}
		else{ 
		echo date('M-d-Y', strtotime($row1['certificate_owwa_from'])); 
		}
ECho"</td>"; }

echo"<td>".$row['sub_employer']."  </td>";
echo"<td>".$row2['requirement_visa_no']."  </td>";
echo"<td>".$row['applicant_employer_idno']."  </td>";

IF($row['requirement_visa_stamp']=='1970-01-01' || $row['requirement_visa_stamp']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row['requirement_visa_stamp']));echo'</td>'; }


IF($row2['requirement_oec_release_date']=='1970-01-01' || $row2['requirement_oec_release_date']=='0000-00-00' || $row2['requirement_oec_release_date']==null) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row2['requirement_oec_release_date']));echo'</td>'; }


echo"<td>".$row['statusText']."</td>";

echo"<td style='color:Red'>".$row['sub_status']."</td>";
$counter++;
echo"</tr>";
}				

?>	

</tbody>
</table>
<div style="clear:both;height:40px"></div>
</div>

</div>
</div>

</div>
</div>
</div>


</div>
</div>
</div>
</div>



</body>
<!--  /Body -->
</html>

<script src="assets/js/jquery-1.12.2.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/propeller.min.js"></script>


