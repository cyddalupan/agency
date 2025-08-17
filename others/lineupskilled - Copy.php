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

</head>


<!-- /Head -->
<!-- Body -->
<body style="background:#F5F5F5">

<div class="col-md-14 col-sm-14" style="margin-top:-1px">
<div class="row">
<div class="col-md-12">
  <form action="" method="post">
  
<div class="clients-page">

<div class="col-sm-2 pull-right" align="center">
</div>


<div class="row" style="margin:30px">
<div class="col-md-12"  style="floar:left;background:white;padding:10px;min-height:150px;border:1px solid #DCDCDC;border:1px solid #DCDCDC;width:140%">


<?php
include'db.php';

if(isset($_POST['updatedata'])){

foreach ($_POST['applicant_id'] as $delall) {
 
$insert1=mysql_query("UPDATE applicant
SET 
applicant_status =1
WHERE applicant_id=".$delall."");

 }
 
 echo '<div style="width:100%;padding:5px;color:red;">';			
echo " Successfully Cancelled";
echo "</div>";

}



$app = mysql_query("SELECT * FROM applicant
LEFT JOIN applicant_passport
ON applicant.applicant_id = applicant_passport.passport_applicant
LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number

where
applicant_status IN (5)

order by applicant_status ASC");
?>



<form action="" method="post">


<?php
if($_GET['id']==0) { $stat="available";}
if($_GET['id']==5) { $stat="Line Up";}
if($_GET['id']==2) { $stat="Reserved";}
if($_GET['id']==4) { $stat="On Process";}
if($_GET['id']==12){ $stat="For Booking";}
if($_GET['id']==9) {$stat="Deployed";}
if($_GET['id']==15) { $stat="Passporting";}
?>

<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h3><span>Line UP Applicants</span></h3>
<!-- para -->
<?php include'menu.php';?>
<ul id="menu">
<li><a    href="dashboard.php">Dashboard</a></li>
<li><a   href="med.php">Medical</a></li>
<li><a href="passporting.php">Passporting</a></li>
<li><a class="active" href="line.php">Line Up</a></li>
<li><a href="preselected.php">Pre-Selected</a></li>
<li><a   href="all-app.php">On Process</a></li>
<li><a   href="tesdanew.php">TESDA</a></li>
<li><a   href="owwanew.php">OWWA</a></li>
<li><a   href="oecnew.php">OEC</a></li>
<li><a   href="musaned.php">MUSANED</a></li>
<li><a href="booking.php">For Deployment</a></li>
<li><a href="deployednew.php">Deployed</a></li>
<li><a href="repatnew.php">Repat</a></li>

</ul>
<div style="clear:both;height:10px"></div>

<div style="clear:both;height:10px"></div>
</div> 

<div class="row">
<div class="col-md-12"  style=" padding-bottom:20px;">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">
<div class="table-responsive">

<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="FONT-SIZE:9px;width:98%" >
<thead>
<tr>
<th></th>
<th STYLE="WIDTH:10px;color:white">Action</th>
<th style="font-size:10px;width:155">Name</th>
<th style="font-size:10px">CONTACT #</th>	
<th style="font-size:10px">FRA</th>
<th style="font-size:10px">PASSPORT</th>
<th style="font-size:10px">STATUS</th>
<th  style="font-size:10px">MEDICAL </th>	
<th  style="font-size:10px">CLINIC</th>	
<th  style="font-size:10px">RESULT</th>	
<th style="font-size:10px">TESDA</th>
<th style="font-size:10px">AGENT</th>	
<th style="font-size:10px">C. RECEIVED</th>
<th style="font-size:10px">C. SIGNED</th>	
<th style="font-size:10px">VFS</th>	
<th style="font-size:10px">OWWA</th>	
<th style="font-size:10px">STAMP</th>	
<th style="font-size:10px">OEC</th>	
										
										
</tr>
</thead>
<tbody>
   		
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



$number=str_pad($row['applicant_id'], 10, '0', STR_PAD_LEFT);
$effectiveDate = strtotime("+30 days", strtotime($date_now));
 $datenow1 = strtotime($date_now);
 $datenow= date("Y-m-d",$datenow1);
$date2=date("Y-m-d",$row1['certificate_medical_expiration']);
 $date3 = strtotime($date2);
echo"<tr>";	
echo "<td><input type='hidden' name='applicant_id[]' value='".$row['applicant_id']."' ></td> ";	
echo"<td style='white-space:normal'><a target='_blank'  href=".$review."".$row['applicant_id']."><button type='button' class='btn  pmd-btn-raised btn-default btn-sm'  >Review</td> </a> ";
echo"<td>".$row['applicant_first']."  ".$row['applicant_last']."</td>";
echo"<td>".$row['applicant_contacts']."</td>";
echo"<td>".$employer['employer_name']."</td>";
echo"<td>".$row['passport_number']."</td>";
echo'<td><button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:9px">'.$row["status"].'</button></td>';

if ($row1['certificate_medical_expiration'] <= $datenow) {
echo"<td style='color:black;background:red'>".$row1['certificate_medical_expiration']."</td>";
}
if ($row1['certificate_medical_expiration'] > $datenow) {
echo"<td style='color:blue'>".$row1['certificate_medical_expiration']."</td>";
}


echo"<td>".$row1['certificate_medical_clinic']."</td>";
echo"<td>".$row1['certificate_medical_result']."</td>";

IF($row1['certificate_tesda_date']=='1970-01-01' || $row1['certificate_tesda_date']=='0000-00-00' || $row1['certificate_tesda_date']=='' ) {
echo"<td><input type='date' value='".$row1['certificate_tesda_date']."' name='certificate_tesda_date[]'><i style='font-size:1px'>".$row1['certificate_tesda_date']."<i></td>";
}
else{ 
echo"<td ><input type='date' value='".$row1['certificate_tesda_date']."' name='certificate_tesda_date[]' style='background:#9ACD32'><i style='font-size:1px'>".$row1['certificate_tesda_date']."<i></td>";
}
echo"<td>".$agentrow1['agent_first']." ".$agentrow1['agent_last']."</td>";



IF($row2['requirement_contract']=='1970-01-01' || $row2['requirement_contract']=='0000-00-00' || $row2['requirement_contract']=='' ) {
echo"<td><input type='date' value='".$row2['requirement_contract']."' name='requirement_contract[]'><i style='font-size:1px'>".$row2['requirement_contract']."<i></td>";
}
else{ 
echo"<td ><input type='date' value='".$row2['requirement_contract']."' name='requirement_contract[]' style='background:#9ACD32'><i style='font-size:1px'>".$row2['requirement_contract']."<i></td>";
}



IF($row2['requirement_contract_sign']=='1970-01-01' || $row2['requirement_contract_sign']=='0000-00-00' || $row2['requirement_contract_sign']=='' ) {
echo"<td><input type='date' value='".$row2['requirement_contract_sign']."' name='requirement_contract_sign[]'><i style='font-size:1px'>".$row2['requirement_contract_sign']."<i></td>";
}
else{ 
echo"<td ><input type='date' value='".$row2['requirement_contract_sign']."' name='requirement_contract_sign[]' style='background:#9ACD32'><i style='font-size:1px'>".$row2['requirement_contract_sign']."<i></td>";
}



 
echo"<td><input type='date' value='".$row2['vfs']."'  name='vfs[]'><i style='font-size:1px'>".$row2['vfs']."<i></td>";
 
 
IF($row1['certificate_owwa_to']=='1970-01-01' || $row1['certificate_owwa_to']=='0000-00-00' || $row1['certificate_owwa_to']=='' ) {
echo"<td><input type='date' value='".$row1['certificate_owwa_to']."' name='certificate_owwa_to[]'><i style='font-size:1px'>".$row1['certificate_owwa_to']."<i></td>";
}
else{ 
echo"<td ><input type='date' value='".$row1['certificate_owwa_to']."' name='certificate_owwa_to[]' style='background:#9ACD32'><i style='font-size:1px'>".$row1['certificate_owwa_to']."<i></td>";
}



IF($row2['requirement_visa_stamp']=='1970-01-01' || $row2['requirement_visa_stamp']=='0000-00-00' || $row2['requirement_visa_stamp']=='' ) {
echo"<td><input type='date' value='".$row2['requirement_visa_stamp']."' name='requirement_visa_stamp[]'><i style='font-size:1px'>".$row2['requirement_visa_stamp']."<i></td>";
}
else{ 
echo"<td ><input type='date' value='".$row2['requirement_visa_stamp']."' name='requirement_visa_stamp[]' style='background:#9ACD32'><i style='font-size:1px'>".$row2['requirement_visa_stamp']."<i></td>";
}


IF($row2['requirement_oec_release_date']=='1970-01-01' || $row2['requirement_oec_release_date']=='0000-00-00' || $row2['requirement_oec_release_date']=='' ) {
echo"<td><input type='date' value='".$row2['requirement_oec_release_date']."' name='requirement_oec_release_date[]'><i style='font-size:1px'>".$row2['requirement_oec_release_date']."<i></td>";
}
else{ 
echo"<td ><input type='date' value='".$row2['requirement_oec_release_date']."' name='requirement_oec_release_date[]' style='background:#9ACD32'><i style='font-size:1px'>".$row2['requirement_oec_release_date']."<i></td>";
}
echo"</tr>";

}				
?>	

</tbody>
</table>

  </form>
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


<!-- Datatable js -->
<script type="text/javascript" language="javascript" src="jsdatatables.js"></script>



<!-- Propeller Data table js-->
<script>
//Propeller Customised Javascript code 
$(document).ready(function() {
$('#example-checkbox').DataTable({
responsive: false,
columnDefs: [ {
orderable: false,
className: 'select-checkbox',
targets:0,
} ],
select: {
style: 'multi',
selector: 'td:first-child'
},
order: [ 7, 'desc' ],
bFilter: true,
bLengthChange: true,
pagingType: "simple",
"paging": true,
"searching": true,
"language": {
"info": " _START_ - _END_ of _TOTAL_ ",
"sLengthMenu": "<span class='custom-select-title'>Rows per page:</span> <span class='custom-select'> _MENU_ </span>",
"sSearch": "",
"sSearchPlaceholder": "Search",
"paginate": {
"sNext": " ",
"sPrevious": " "
},
},
dom:
"<'pmd-card-title'<'data-table-title'><'search-paper pmd-textfield'f>>" +
"<'custom-select-info'<'custom-select-item'><'custom-select-action'>>" +
"<'row'<'col-sm-12'tr>>" +
"<'pmd-card-footer' <'pmd-datatable-pagination' l i p>>",
});

/// Select value
$('.custom-select-info').hide();

$('#example-checkbox tbody').on( 'click', 'tr', function () {
if ( $(this).hasClass('selected') ) {
var rowinfo = $(this).closest('.dataTables_wrapper').find('.select-info').text();
$(this).closest('.dataTables_wrapper').find('.custom-select-info .custom-select-item').text(rowinfo);
if ($(this).closest('.dataTables_wrapper').find('.custom-select-info .custom-select-item').text() != null){
$(this).closest('.dataTables_wrapper').find('.custom-select-info').show();
//show delet button
} else{
$(this).closest('.dataTables_wrapper').find('.custom-select-info').hide();
}
}
else {
var rowinfo = $(this).closest('.dataTables_wrapper').find('.select-info').text();
$(this).closest('.dataTables_wrapper').find('.custom-select-info .custom-select-item').text(rowinfo);
}
if($('#example-checkbox').find('.selected').length == 0){
$(this).closest('.dataTables_wrapper').find('.custom-select-info').hide();
}
} );
$("div.data-table-title").html('');
$(".custom-select-action").html('<button class="btn btn-sm pmd-btn-fab pmd-btn-flat pmd-ripple-effect btn-primary" type="button"><i class="material-icons pmd-sm">delete</i></button><button class="btn btn-sm pmd-btn-fab pmd-btn-flat pmd-ripple-effect btn-primary" type="button"><i class="material-icons pmd-sm">more_vert</button>');

} );
</script>
