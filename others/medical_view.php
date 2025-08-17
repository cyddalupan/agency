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

</head>
<!-- /Head -->
<!-- Body -->
<body style="background:white">

<div class="col-md-14 col-sm-14" style="margin-top:-1px">
<div class="row">
<div class="col-md-12">

<div class="clients-page">

<div class="row" style="margin:30px">
<div class="col-md-12"  style="floar:left;background:white;padding:10px;min-height:150px;border:1px solid #DCDCDC;border:1px solid #DCDCDC;width:100%">

<form action="" method="post">
<?php
if($_GET['id']==0) { $stat="Medical Under Processs";}
if($_GET['id']==5) { $stat="Line Up";}
if($_GET['id']==2) { $stat="Reserved";}
if($_GET['id']==4) { $stat="Selected";}
if($_GET['id']==12){ $stat="For Booking";}
if($_GET['id']==9) {$stat="Deployed";}
if($_GET['id']==15) { $stat="Passporting";}
?>

<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h1 style="color:black"	><span>  Medical </span></h1>
<!-- para -->
<?php

?>

<div style="clear:both;height:20px"></div>




</div> 

<div class="row">
<div class="col-md-12"  style=" padding-bottom:20px;">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">
<div class="table-responsive">


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
<a id="downloadLink" onclick="exportF(this)" style="color:blue;float:right">Export to Reports</a>



<style>
TABLE th{
font-size:14px;	
}
TABLE tD{
font-size:12px;	
}
</style>

<?php
include'db.php';
include'functions_med.php';



if($_GET['medical']==1){
$app = mysql_query("SELECT * FROM applicant
LEFT JOIN applicant_certificate ON 
applicant.applicant_id = applicant_certificate.certificate_applicant

LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number
where applicant_status IN (0,10)
AND certificate_medical_result IN ('FIT TO WORK')
AND applicant_position_type='Household'
order by applicant_certificate.certificate_medical_exam_date desc");
$title='FIT TO WORK';
}

if($_GET['medical']==2){
$app = mysql_query("SELECT * FROM applicant
LEFT JOIN applicant_certificate ON 
applicant.applicant_id = applicant_certificate.certificate_applicant

LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number
where applicant_status IN (0,10,7)
AND certificate_medical_result IN ('PENDING','FOR APPROVAL WAIVER','2ND OPINION','PENDING','PENDING')
AND applicant_position_type='Household'
order by applicant_certificate.certificate_medical_exam_date desc");
$title='PENDING';
}


if($_GET['medical']==3){
$app = mysql_query("SELECT * FROM applicant
LEFT JOIN applicant_certificate ON 
applicant.applicant_id = applicant_certificate.certificate_applicant

LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number
where applicant_status IN (0,10)
AND certificate_medical_result IN ('UNFIT')
AND applicant_position_type='Household'
order by applicant_certificate.certificate_medical_exam_date desc");
$title='UNFIT';
}


if($_GET['medical']==4){
$app = mysql_query("SELECT * FROM applicant
LEFT JOIN applicant_certificate ON 
applicant.applicant_id = applicant_certificate.certificate_applicant

LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number
where applicant_status IN (0,10,5)
AND certificate_medical_result IN ('')
AND applicant_position_type='Household'
order by applicant.applicant_date_applied asc");
$title='NO RESULT';

}


if($_GET['medical']==5){
$app = mysql_query("SELECT * FROM applicant
LEFT JOIN applicant_certificate ON 
applicant.applicant_id = applicant_certificate.certificate_applicant

LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number
where applicant_status IN (0,10)
AND certificate_medical_result IN ('FOR MEDICAL')
AND applicant_position_type='Household'
order by applicant_certificate.certificate_medical_exam_date desc");
$title='FOR MEDICAL';
}


if($_GET['medical']==6){
$app = mysql_query("SELECT * FROM applicant
LEFT JOIN applicant_certificate ON 
applicant.applicant_id = applicant_certificate.certificate_applicant

LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number
where applicant_status IN   (1,25,21)
AND applicant_position_type='Household'
AND applicant_employer=0
order by applicant_certificate.certificate_medical_exam_date desc");
$title='BACKOUT (NOT YET MARKET)';
}
?> 
</br></br>

<a href="medical_view.php?medical=5"  STYLE="background:#191970;padding:10px;color:white;font-size:16px;margin:5px">FOR MEDICAL</a> |
<a href="medical_view.php?medical=1" STYLE="background:#191970;padding:10px;color:white;font-size:16px;margin:5px">FIT TO WORK</a> | 
<a href="medical_view.php?medical=2"  STYLE="background:red;padding:10px;color:white;font-size:16px;margin:5px">PEDING</a> | 
<a href="medical_view.php?medical=3"  STYLE="background:red;padding:10px;color:white;font-size:16px;margin:5px">UNFIT</a> | 
<a href="medical_view.php?medical=6"  STYLE="background:red;padding:10px;color:white;font-size:16px;margin:5px">BACKOUT</a> |
<a href="medical_view.php?medical=4"  STYLE="background:red;padding:10px;color:white;font-size:16px;margin:5px"><b>NO UPDATE</b></a> 

<h3 style="font-weight:bold;COLOR:BLACK"><b><?=$title?></b></h3>



<a href="pagelie.php?pos=HOUSEHOLD"  target="_blank" 
style="border:3px solid black;padding:5px;color:white;margin:10px;FONT-size:1px;position:absolute;float:left;BACKGROUND:GREEN;margin-left:500px"></a>




<table  cellspacing="0"
id="example-checkbox"  style="width:100%">
<thead>
<thead>
<tr>
    <th>#</th>
<th>Status</th>
<th>Date Applied</th>
<th>Days </th>
<th>Applicant</th>		
<th>Contact</th>
<th>Position</th>	
<th>Destination</th>
<th>Branch</th>
<th>Agent</th>

<th>Branch</th>
<th>Clinic</th> 
<th>Date FTW</th>
<th>Date Medical</th>	
<th>Result</th>
<th>-Date Expired</th>	
<th>Remarks </th>
<th># Days Med</th>				
</tr>
</thead>
<tbody>




<?php
$count=1;
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

$position = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);

$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);


$number=str_pad($row['applicant_id'], 10, '0', STR_PAD_LEFT);
IF($row['certificate_medical_exam_date']=='1970-01-01' || $row['certificate_medical_exam_date']=='0000-00-00' || $row['certificate_medical_exam_date']=='' ) {
$effectiveDate = '';
}
else{$effectiveDate=strtotime("+3 months", strtotime($row['certificate_medical_exam_date']));}

if($row['typess']==1){$branchme="MAIN MANILA";}
if($row['typess']==2){$branchme="ILOILO";}
if($row['typess']==3){$branchme="BACOLOD";}
if($row['typess']==4){$branchme="DAVAO";}
if($row['typess']==5){$branchme="GENSAN";}
if($row['typess']==6){$branchme="TUGUEGARAO";}
if($row['typess']==7){$branchme="PAGADIAN";}
if($row['typess']==8){$branchme="KORONADAL";}


$line = mysql_query("SELECT * FROM applicant
LEFT JOIN applicant_certificate ON 
applicant.applicant_id = applicant_certificate.certificate_applicant

LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number
where applicant_status IN (0,10)

AND applicant_position_type='Household'
AND certificate_applicant=".$row['applicant_id']."
order by applicant_certificate.certificate_medical_exam_date desc");
$lineup=mysql_fetch_array($line);


$datecountapplied=date_create("".$lineup['certificate_medical_exam_date']."");
$currentdate=date_create("".$date_now."");
$dateaddedfinal=date_diff($datecountapplied,$currentdate);
$dayone=$dateaddedfinal->format("%r%a");
$coundown=($row["numberone"])+$dayone;

if(15<$coundown) {$colorme="";}
if(16>=$coundown) {$colorme="";}

if(15<$coundown && $row['certificate_medical_result']!='FIT TO WORK')
{
$color111a="#FFB6C1";
$color21a="white";
}

if(16>=$coundown && $row['certificate_medical_result']=='FIT TO WORK')
{
$color111a="white";
$color21a="black";
}

echo'<tr style="background:'.$color111a.';color:'.$color21.'">';	
echo"<td>".$count."</td>";
echo'<td>
<a href='.$review.''.$row["applicant_id"].'  target="_blank">
<button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:12px">'.$row["status"].'</button></a></td>';

echo'<td>'; echo date('M-d-Y', strtotime($row['applicant_date_applied']));echo'</td>';

$datecountapplied=date_create("".$row['applicant_date_applied']."");
$currentdate=date_create("".$date_now."");
$dateaddedfinal=date_diff($datecountapplied,$currentdate);
echo"<td style='color:black;width:100px'></div><u style='padding:5px;color:black;font-size:18px;background:orange';> ";
echo $dateaddedfinal->format("%r%a ");
echo "</u>";
echo"</td>";


echo'<td STYLE="FONT-SIZE:14PX;width:250px"><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';
echo"<td>".$row['applicant_contacts']."</td>";
echo"<td>".$position1['position_name']."</td>";
echo'<td>'.$countryrow["country_name"].'</td>';
echo"<td>".$branchme."</td>";
echo"<td>".$agentrow1['agent_first']." ".$agentrow1['agent_last']."</td>";
echo'<td>'.$branchme.'</td>';
echo"<td >".$row['certificate_medical_clinic']."</td>";


IF($row['medical_fit']=='1970-01-01' || $row['medical_fit']=='0000-00-00' || $row['medical_fit']=='' ) {
echo"<td>--</td>";
}
else{ 
echo'<td>'; echo date('M-d-Y', strtotime($row['medical_fit']));echo'</td>';
}



IF($row['certificate_medical_exam_date']=='1970-01-01' || $row['certificate_medical_exam_date']=='0000-00-00' || $row['certificate_medical_exam_date']=='' ) {
echo"<td>--</td>";
}
else{ 
echo'<td>'; echo date('M-d-Y', strtotime($row['certificate_medical_exam_date']));echo'</td>';
}

if($row['certificate_medical_result']!=''){
echo"<td  style='color:Red'>".$row['certificate_medical_result']."</td>";
}
if($row['certificate_medical_result']==''){
echo"<td STYLE='color:black;font-size:9px'>--</td>";
}

echo"<td>".date("M-d-Y",$effectiveDate)."</td>";


echo"<td>".$row['certificate_medical_remarks']."-".$row['applicant_employer']."</td>";

echo"<td style='color:white;width:100px'></div><u style='padding:1px;color:black;font-size:16px;background:".$colorme.";font-weight:bold';> ";
echo $coundown;
echo "</u>";
echo"</td>";
echo"</tr>";
$count++;
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

