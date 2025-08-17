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
font-size:8px;
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

include'db.php';





if($_POST['visacontract']==1){
        
    if($_POST['start']=='' || $_POST['start']=='0000-00-00'){

        $visame="";
        $visaonly="requirement_visa_stamp NOT IN ('1970-01-01','0000-00-00')";
        $order="order by requirement_visa_stamp desc";

    }
    
     if($_POST['start']!=''){

        $visame="requirement_visa_stamp between '".$_POST['start']."' AND '".$_POST['end']."' AND";
         $visaonly="requirement_visa_stamp NOT IN ('1970-01-01','0000-00-00')";
        $order="order by requirement_visa_stamp desc";

    }
    $data="Visa Stamp";

}


if($_POST['visacontract']==2){
        
    if($_POST['start']==''){

        $visame="";
        $visaonly="requirement_contract NOT IN ('1970-01-01','0000-00-00')";
        $order="order by requirement_contract desc";

    }
    
     if($_POST['start']!=''){

        $visame="requirement_contract between '".$_POST['start']."' AND '".$_POST['end']."' AND";
         $visaonly="requirement_contract NOT IN ('1970-01-01','0000-00-00')";
        $order="order by requirement_contract desc";

    }
   $data="Contract Received";
}



if($_POST['visacontract']==3){
        
  
        $visame="";
        $visaonly="requirement_contract_sign  IN ('1970-01-01','0000-00-00')  ";
        $order="order by requirement_contract desc";

    
   $data="Signing Contract";
}






if($_POST['employer']=='99999'){

	$employer="applicant_employer!=0 AND";	
}
if($_POST['employer']!='99999'){
$employer="applicant_employer=".$_POST['employer']." AND";	
}





?>




<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h3><span> <?=$data?> Applicants </span></h3>
<!-- para -->
</div> 

<div class="row">
<div class="col-md-12"  style=" padding-bottom:20px;">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">
<div class="table-responsive">
<?php
$counter=1;
?>

<p class="date-filter">Date: <?php echo $_POST['start']; ?> &minus; <?php echo $_POST['end']; ?></p>
<a id="downloadLink" onclick="exportF(this)" style="color:Red">SAVE MY WORK</a>

<style>
table td,th{
font-size:12px;	
}
</style>


<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="FONT-SIZE:9px;width:98%" >
<thead>
<tr>
<th STYLE="WIDTH:10px;color:black">#</th>
<th>Date Applied </th>
<th>Days </th>
<th >Name</th>
<th >CONTACT</th>	
<th >FRA</th>
<th >RESULT</th>		
<th >TESDA</th>
<th>AGENT</th>	
<th>C. RECEIVED</th>
<th>C. SIGNED</th>	
<th>OWWA</th>	
<th>VISA</th>	
<th >OEC</th>	
<th>STATUS</th>
<th>STATUS DATE</th>
<th style=""></th>																	
</tr>
</thead>
<tbody>

<?php


$app = mysql_query("SELECT applicant.*,
applicant_requirement.*,statuses.*
 FROM applicant

LEFT JOIN  applicant_requirement
ON applicant.applicant_id = applicant_requirement.requirement_applicant

LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number


where
$employer
$visame
$visaonly
and applicant_status    IN (26,27)
$order

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

$datecountapplied=date_create("".$row['applicant_date_applied']."");
$currentdate=date_create("".$date_now."");
$dateaddedfinal=date_diff($datecountapplied,$currentdate);
echo'<td>'; echo date('M-d-Y', strtotime($row['applicant_date_applied']));echo'</td>';
echo"<td style='color:black;width:100px'></div><u style='padding:5px;color:black;font-size:18px;background:orange';> ";
echo $dateaddedfinal->format("%r%a ");
echo "</u>";
echo"</td>";


echo"<td><a target='_blank'  href='".$review."".$row['applicant_id']."'>
".$row['applicant_first']."  ".$row['applicant_last']."</a></td>";




echo"<td>".$row['applicant_contacts']."</td>";
echo"<td>".$employer['employer_name']."</td>";

echo"<td>".$row1['certificate_medical_result']."</td>";

echo"<td>".$row1['certificate_tesda_date']."</td>";

echo"<td>".$agentrow1['agent_first']." ".$agentrow1['agent_last']."</td>";
IF($row2['requirement_contract']=='1970-01-01' || $row2['requirement_contract']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row2['requirement_contract']));echo'</td>'; }

IF($row2['requirement_contract_sign']=='1970-01-01' || $row2['requirement_contract_sign']=='0000-00-00' || $row2['requirement_contract_sign']=='0001-01-01'  || $row2['requirement_contract_sign']=='1969-12-31') { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row2['requirement_contract_sign']));echo'</td>'; }

echo"<td>".$row1['owwa_number']."</td>";
IF($row2['requirement_visa_stamp']=='1970-01-01' || $row2['requirement_visa_stamp']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row2['requirement_visa_stamp']));echo'</td>'; }


IF($row2['requirement_oec_release_date']=='1970-01-01' || $row2['requirement_oec_release_date']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row2['requirement_oec_release_date']));echo'</td>'; }

echo"<td>".$row['statusText']."</td>";
 echo'<td>'; echo date('M-d-Y', strtotime($new_date));echo'</td>'; 
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

