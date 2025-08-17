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
<form action="" method="post">
<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h1 style="color:black"><span>  OEC </span></h1>
<!-- para -->

<?php
include'db.php';
include'functions2.php';

$app = mysql_query("SELECT applicant_certificate.*,applicant.*,statuses.*

 FROM applicant
LEFT JOIN applicant_certificate ON 
applicant.applicant_id = applicant_certificate.certificate_applicant


LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number

where applicant_status IN (30,31)
order by applicant_certificate.certificate_medical_exam_date desc
");
?> 

<?php
include'menuchange.php';
include'menu.php';
?>
<ul id="menu">
<li><a  href="dashboard.php"><?=$dashboard?></a></li>
<li><a  href="preselected.php"  ><?=$available?></a></li>
<li><a href="passporting.php"><?=$passporting?></a></li>
<li><a  href="line.php"> <?=$cv?></a></li>
<li><a href="med.php"><?=$medical?></a></li>
<li><a   href="all-app.php"><?=$selected?></a></li>
<li><a   href="tesdanew.php"><?=$tesda?></a></li>
<li><a   href="musaned.php"><?=$musaned?></a></li>
<li><a   href="owwanew.php"><?=$owwa?></a></li>
<li><a   href="stampnew.php"><?=$visamenu?></a></li>
<li><a   class="active"   href="oecnew.php"><?=$oecmenu?></a></li>
<li><a href="booking.php"><?=$bookingmenu?></a></li>
<li><a href="deployednew.php"><?=$deployed?></a></li>
<li><a href="repatnew.php"><?=$repat?></a></li>
<li><a href="atoa.php"><?=$atoa?></a></li>
<li><a href="backouts.php"><?=$backoutmenu?></a></li>
<li style="float:left">
<input type='submit'class="btn btn-sm btn-danger" name='updatedata' value='Update Data' style="height:28px;float:left;margin:10px;border:2px solid white">
</li>
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

<style>
TABLE th{
font-size:14px;	
}
TABLE tD{
font-size:12px;	
}
</style>


<table class="table table-hover table-striped table-bordered"  id="example-checkbox" style="width:120%" >
<thead>
<tr>
<th STYLE="WIDTH:10px;color:white">.</th>	
<th STYLE="WIDTH:10px">Action</th>
<th STYLE="WIDTH:10px">Applicant</th>
<th  STYLE="WIDTH:120px">Applicant Name</th>	
<th STYLE="WIDTH:120px">SPONSOR</th>

<th>Date Encoded</th>
<th>Insurance</th>
<th>Date Filing</th>	
<th>Date Release</th>
<th>OEC  certificate</th>
<th>Attachment</th>
<th>Remarks</th>
<th STYLE="WIDTH:220px">FRA</th>								
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

$position = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);

$number=str_pad($row['applicant_id'], 10, '0', STR_PAD_LEFT);
IF($row['certificate_medical_exam_date']=='1970-01-01' || $row['certificate_medical_exam_date']=='0000-00-00' || $row['certificate_medical_exam_date']=='' ) {
$effectiveDate = '';
}
else{$effectiveDate=strtotime("+3 months", strtotime($row['certificate_medical_exam_date']));}


echo"<tr>";	
echo'<td></td>  ';	
echo"<td style='white-space:normal'><a target='_blank'  href=".$review."".$row['applicant_id']."><button type='button' class='btn  pmd-btn-raised btn-danger btn-xs'  >Review</td> </a> ";
echo"<td>0".$row['applicant_id']."</td>";
echo'<td STYLE="FONT-SIZE:14PX;width:250px"><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';

echo"<td>".$row['sub_employer']."</td>";
echo"<td>".$row2['applicant_date_applied']."</td>";
echo"<td>".$row1['insurance_no']."</td>";

 
IF($row2['requirement_oec_submission_date']=='1970-01-01' || $row2['requirement_oec_submission_date']=='0000-00-00' || $row2['requirement_oec_submission_date']=='' ) {
echo"<td></td>";
}
else{ 
echo'<td>'; echo date('M-d-Y', strtotime($row2['requirement_oec_submission_date']));echo'</td>';
}

IF($row2['requirement_oec_release_date']=='1970-01-01' || $row2['requirement_oec_release_date']=='0000-00-00' || $row2['requirement_oec_release_date']=='' ) {
echo"<td></td>";
}
else{ 
echo'<td>'; echo date('M-d-Y', strtotime($row2['requirement_oec_release_date']));echo'</td>';
}


echo"<td>".$row['requirement_oec_number']."</td>";
echo"<td></td>";
echo"<td><input type='hidden' name='applicant_id[]' value='".$row['applicant_id']."' checked>
<input type='TEXT'  name='certificate_oecremarks[]' value='".$row1['certificate_oecremarks']."'></td>";
echo"<td>".$employer['employer_name']."</td>";
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

