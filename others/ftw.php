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


<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h3><span> Line up Applicants</span></h3>
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
<a id="downloadLink" onclick="exportF(this)" style="color:Red">Export to excel</a>

<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="FONT-SIZE:10px;width:95%;padding:1px" >
<thead>
<tr>
<th STYLE="WIDTH:10px;color:white">.</th>
<th STYLE="WIDTH:10px;color:white">.</th>
<th style="font-size:10px">Name</th>
<th style="font-size:10px">CONTACT #</th>	
<th style="font-size:10px">FRA</th>
<th style="font-size:10px">PASSPORT</th>
<th  style="font-size:10px">MEDICAL </th>	
<th  style="font-size:10px">CLINIC</th>	
<th  style="font-size:10px">RESULT</th>		
<th style="font-size:10px">AGENT</th>	
<th style="font-size:10px">Date Line Up</th>										
</tr>
</thead>
<tbody>

<?php
include'db.php';
$app = mysql_query("SELECT * FROM applicants_logs_view

LEFT JOIN applicant
ON applicants_logs_view.log_applicant = applicant.applicant_id
LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number



where
log_date between '".$_POST['start']."' AND '".$_POST['end']."'
AND log_status=5
group by log_applicant
order by log_date desc


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


$number=str_pad($row['applicant_id'], 10, '0', STR_PAD_LEFT);
echo"<td></td>  ";
		
echo"<td></td>  ";
echo"<td><a target='_blank'  href='http://stepupmanpower.com/admin/admin/applicants/review_single/".$row['applicant_id']."'>
".$row['applicant_first']."  ".$row['applicant_last']."</a></td>";
echo"<td>".$row['applicant_contacts']."</td>";
echo"<td>".$employer['employer_name']."</td>";
echo"<td>".$row['passport_number']."</td>";
echo"<td>".$row1['certificate_medical_exam_date']."</td>";
echo"<td>".$row1['certificate_medical_clinic']."</td>";
echo"<td>".$row1['certificate_medical_result']."</td>";
echo"<td>".$agentrow1['agent_first']." ".$agentrow1['agent_last']."</td>";
echo"<td>".$row['log_date']."</td>";
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
