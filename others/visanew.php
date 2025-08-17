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
<h3 STYLE="margin-left:40px"><span>  Medical Status</span></h3>
<!-- para -->
<?php include'menu.php';?>



<ul id="menu">
<li><a    href="dashboard.php">Dashboard</a></li>
<li><a href="med.php">Medical</a></li>
<li><a href="passporting.php">Passporting</a></li>
<li><a href="line.php">Line Up</a></li>
<li><a href="preselected.php">Pre-Selected</a></li>
<li><a   href="all-app.php">On Process</a></li>
<li><a   class="active"  href="tesdanew.php">TESDA</a></li>
<li><a   href="stampnew.php">Visa Stamp</a></li>
<li><a   href="owwanew.php">OWWA</a></li>
<li><a   href="oecnew.php">OEC</a></li>
<li><a   href="musaned.php">MUSANED</a></li>
<li><a href="booking.php">For Deployment</a></li>
<li><a href="deployednew.php">Deployed</a></li>
<li><a href="repatnew.php">Repat</a></li>
</ul>

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

<table class="table table-hover table-striped table-bordered"  id="example-checkbox" style="width:110%" >
<thead>
<tr>
<th STYLE="WIDTH:10px;color:white">.</th>	
<th STYLE="WIDTH:10px">Action</th>
<th>Applicant Name</th>	
<th>Training Center</th>
<th>Training Date</th>
<th>Release Date of Cert</th>				
</tr>
</thead>
<tbody>

<?php
include'db.php';
if(isset($_POST['passportsave'])){
$insert=mysql_query("UPDATE applicant
SET 
timesched = ".$_POST['timesched'].",
passsched = ".$_POST['passsched'].",
locsched = ".$_POST['locsched']."
WHERE applicant_id=".$_POST['applicant_id']."");

echo '<div style="width:100%;padding:5px;background:green;color:white;">';			
echo "Passport Successfully Updated";
echo "</div>";

}


$app = mysql_query("SELECT applicant_certificate.*,applicant.*,statuses.*


 FROM applicant
LEFT JOIN applicant_certificate ON 
applicant.applicant_id = applicant_certificate.certificate_applicant


LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number

where applicant_status NOT IN (21,22,14,8,12,9,7,15)
order by applicant_certificate.certificate_medical_exam_date desc
");
?>   		
<?php

while($row=mysql_fetch_array($app))
{			
$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
$employer=mysql_fetch_array($emp);

$agent = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$agentrow1=mysql_fetch_array($agent);
$number=str_pad($row['applicant_id'], 10, '0', STR_PAD_LEFT);
IF($row['certificate_medical_exam_date']=='1970-01-01' || $row['certificate_medical_exam_date']=='0000-00-00' || $row['certificate_medical_exam_date']=='' ) {
$effectiveDate = '';
}
else{$effectiveDate=strtotime("+3 months", strtotime($row['certificate_medical_exam_date']));}


echo"<tr>";	
echo'<td></td>  ';	
echo"<td style='white-space:normal'><a target='_blank'  href=".$review."".$row['applicant_id']."><button type='button' class='btn  pmd-btn-raised btn-danger btn-sm'  >Update</td> </a> ";

echo"<td>".$row['applicant_first']."  ".$row['applicant_last']."</td>";
echo"<td>".$row['applicant_contacts']."</td>";
echo"<td>".$row['applicant_contacts']."</td>";
echo"<td>".$row['applicant_contacts']."</td>";


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

