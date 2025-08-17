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

</head>
<style>
table{
font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
border-collapse: collapse;
font-size:11px;
}

td,th {
border: 1px solid #ddd;
padding: 4px;
}

tr:nth-child(even){background-color: #f2f2f2;}

tr:hover {background-color: #ddd;}

th {
padding-top: 12px;
padding-bottom: 12px;
text-align: left;
background-color: lightblue;
color: white;
padding: 8px;
}


th{
font-size:12px;
font-weight:none;
color:blue;	
padding:8px;
}
tD{
padding:1px;
font-size:11px;
}
input{
width:110px;
padding:1px;
font-size:11px;
height:20px;	
}


</style>

<!-- /Head -->
<!-- Body -->
<body style="background:white">

<div class="col-md-14 col-sm-14" style="margin-top:-1px">
<div class="row">
<div class="col-md-12">
<form action="" method="post">

<div class="clients-page">

<div class="col-sm-2 pull-right" align="center">
</div>


<div class="row">
<div class="col-md-12" style="margin:20px">

<?php
include'db.php';

include'functions.php';

$app = mysql_query("SELECT * FROM applicant
LEFT JOIN applicant_passport
ON applicant.applicant_id = applicant_passport.passport_applicant
LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number

where
applicant_status  IN (14)

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
<h3><span> Blacklist Applicants</span>  </br><small style="text-align:right"><a id="downloadLink" onclick="exportF(this)" style="color:Red;font-size:11px">Export to excel</a>
</small></h3>
<!-- para -->
<?php include'menu.php';?>
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





<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="FONT-SIZE:9px;width:98%" >
<thead>
<tr>
<th style="width:2px"></th>
<th style="font-size:12px;width:155">Name</th>
<th style="font-size:12px">CONTACT #</th>
<th style="font-size:12px">TOR Evaluated</th>
<th style="font-size:12px">Course</th>
<th style="font-size:12px">Number of units</th>
<th style="font-size:12px">Years of Clasroom Experience Teaching</th>
<th style="font-size:12px">STATUS</th>
<th style="font-size:12px">School District</th>
<th style="font-size:12px">Status Date</th>
<th style="font-size:12px;width:300px">Remarks</th>										
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

if($row['applicant_status']=='0') {$color='lightgray';}
if($row['applicant_status']=='4') {$color='green';}
if($row['applicant_status']=='3') {$color='lightgray';}
if($row['applicant_status']=='2') {$color='blue';}
if($row['applicant_status']=='12') {$color='orange';}
if($row['applicant_status']=='8') {$color='red';}
if($row['applicant_status']=='5') {$color='lightblue';}
$number=str_pad($row['applicant_id'], 10, '0', STR_PAD_LEFT);
$effectiveDate = strtotime("+30 days", strtotime($date_now));
$datenow1 = strtotime($date_now);
$datenow= date("Y-m-d",$datenow1);
$date2=date("Y-m-d",$row1['certificate_medical_expiration']);
$date3 = strtotime($date2);
echo"<tr>";	
echo "<td><input type='hidden' name='applicant_id[]' value='".$row['applicant_id']."' checked></td> ";	
echo"<td><a target='_blank'  href='https://fcec-inc.com/system/admin/applicants/review_single/".$row['applicant_id']."' style='color:gray'>
".$row['applicant_first']."  ".$row['applicant_last']."</a></td>";
echo"<td>".$row['applicant_contacts']."</td>";
echo"<td>".$row2['requirement_tore']."</td>";
echo"<td>".$row2['ec']."</td>";
echo"<td>".$row2['eunits']."</td>";
echo"<td>".$row2['eclass']."</td>";
echo"<td><div style='background:".$color.";padding:4px;color:white;MARGIN:3PX'>".$row['statusText']."</div></td>";
echo"<td>".$employer['employer_name']."</td>";
echo"<td>".$row['applicant_updated']."</td>";
echo"<td>".$row['applicant_remarks']."</td>";

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
