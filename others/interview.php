<?php include'review.php';?>
<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<!-- Head -->
<head>
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 

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
<h3 STYLE="margin-left:40px"><span> <?=$_GET['nm']?></span></h3>
<!-- para -->


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
table th{
font-size:14px;
}
table td{
font-size:14px;
}
</style>


<a href="interview.php?fil=1&&nm=For Interview">For Interview</a> |
<a href="interview.php?fil=2&&nm=Approved">Approved</a> |
<a href="interview.php?fil=3&&nm=Reject">Reject</a> |
<a href="interview.php?fil=4&&nm=Did not Appear">Did not Appear</a> |

<table class="table table-hover table-striped table-bordered"  id="example-checkbox" style="width:110%" >
<thead>
<tr>

<th STYLE="WIDTH:10px">Action</th>
<th>#</th>
<th>NAME</th>
<th>MOBILE</th>	
<th>BRANCH</th>								
<th>TRANSFER</th>
<th>SOURCE</th>
<th STYLE="WIDTH:1px;color:white;FONT-SIZE:1PX">.</th>
<th STYLE="WIDTH:1px;color:white;FONT-SIZE:1PX">.</th>					
</tr>
</thead>
<tbody>

<?php
include'db.php';

if($_GET['fil']==1){$data="AND localflight2=''";}
if($_GET['fil']==2){$data="AND localflight2='approved'";}
if($_GET['fil']==3){$data="AND localflight2='reject'";}
if($_GET['fil']==4){$data="AND localflight2='Did not appear'";}


$app = mysql_query("SELECT applicant_certificate.*,applicant.*,statuses.*,recruitment_agent.*
FROM applicant
LEFT JOIN applicant_certificate ON 
applicant.applicant_id = applicant_certificate.certificate_applicant
LEFT JOIN  	recruitment_agent
ON applicant.applicant_source =recruitment_agent.agent_id
LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number
where  localflight1='ARRIVED'

order by applicant_certificate.certificate_medical_exam_date desc
");
?>   		
<?php

while($row=mysql_fetch_array($app))
{			
$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
$employer=mysql_fetch_array($emp);


$cert = mysql_query("SELECT * FROM applicant_certificate where certificate_applicant=".$row['applicant_id']." ");
$row1=mysql_fetch_array($cert);


$agent = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$agentrow1=mysql_fetch_array($agent);
$number=str_pad($row['applicant_id'], 10, '0', STR_PAD_LEFT);
IF($row['certificate_medical_exam_date']=='1970-01-01' || $row['certificate_medical_exam_date']=='0000-00-00' || $row['certificate_medical_exam_date']=='' ) {
$effectiveDate = '';
}
else{$effectiveDate=strtotime("+3 months", strtotime($row['certificate_medical_exam_date']));}



if($row['typess']==0){$branchme="N/A";}
if($row['typess']==1){$branchme="PARANAQUE MAIN OFFICE";}
if($row['typess']==2){$branchme="BACOLOD BRANCH";}
if($row['typess']==3){$branchme="BUTUAN BRANCH";}
if($row['typess']==4){$branchme="CEBU BRANCH";}
if($row['typess']==5){$branchme="CAGAYAN DE ORO BRANCH";}
if($row['typess']==6){$branchme="DAVAO BRANCH";}
if($row['typess']==7){$branchme="DIPOLOG BRANCH ";}
if($row['typess']==8){$branchme="DUMAGUETE BRANCH";}
if($row['typess']==9){$branchme="GENSAN BRANCH";}
if($row['typess']==10){$branchme="IPIL BRANCH";}
if($row['typess']==11){$branchme="MAASIN BRANCH";}
if($row['typess']==12){$branchme="OZAMIZ BRANCH";}
if($row['typess']==13){$branchme="PAGADIAN BRANCH";}
if($row['typess']==14){$branchme="SINDANGAN BRANCH";}
if($row['typess']==15){$branchme="TACLOBAN BRANCH";}
if($row['typess']==16){$branchme="TAGUM BRANCH";}
if($row['typess']==17){$branchme="VALENCIA BRANCH";}
if($row['typess']==18){$branchme="ZAMBOANGA BRANCH";}
if($row['typess']==19){$branchme="ROXAS BRANCH";}


if($row['typess1']==0){$branchme1="N/A";}
if($row['typess1']==1){$branchme1="PARANAQUE MAIN OFFICE";}
if($row['typess1']==2){$branchme1="BACOLOD BRANCH";}
if($row['typess1']==3){$branchme1="BUTUAN BRANCH";}
if($row['typess1']==4){$branchme1="CEBU BRANCH";}
if($row['typess1']==5){$branchme1="CAGAYAN DE ORO BRANCH";}
if($row['typess1']==6){$branchme1="DAVAO BRANCH";}
if($row['typess1']==7){$branchme1="DIPOLOG BRANCH ";}
if($row['typess1']==8){$branchme1="DUMAGUETE BRANCH";}
if($row['typess1']==9){$branchme1="GENSAN BRANCH";}
if($row['typess1']==10){$branchme1="IPIL BRANCH";}
if($row['typess1']==11){$branchme1="MAASIN BRANCH";}
if($row['typess1']==12){$branchme1="OZAMIZ BRANCH";}
if($row['typess1']==13){$branchme1="PAGADIAN BRANCH";}
if($row['typess1']==14){$branchme1="SINDANGAN BRANCH";}
if($row['typess1']==15){$branchme1="TACLOBAN BRANCH";}
if($row['typess1']==16){$branchme1="TAGUM BRANCH";}
if($row['typess1']==17){$branchme1="VALENCIA BRANCH";}
if($row['typess1']==18){$branchme1="ZAMBOANGA BRANCH";}
if($row['typess1']==19){$branchme1="ROXAS BRANCH";}



echo"<tr>";	


echo"<td style='white-space:normal'><a target='_blank'  href=".$review."".$row['applicant_id']."><button type='button' class='btn  pmd-btn-raised btn-info btn-XS'  >Review</td> </a> ";
echo"<td>0".$row['applicant_id']."</td>";
echo'<td STYLE="FONT-SIZE:14PX;width:250px"><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';
echo"<td>".$row['applicant_contacts']."</td>";
echo'<td>'.$branchme.'</td>';
echo'<td>'.$branchme1.'</td>';
echo'<td>'.$row["agent_first"].' '.$row["agent_last"].'</td>';

echo'<td></td>  ';
echo'<td></td>  ';	
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

