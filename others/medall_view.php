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
<h3 STYLE="margin-left:40px"><span> ALL APPLICANTS</span></h3>
<!-- para -->
<?php include'menu1.php';?>


<ul id="menu">
<li><a  class="active" href="medical_view.php">Medical</a></li>
<li><a    href="tesda_view.php">TESDA</a></li>
<li><a    href="forline.php">FTW</a></li>
<li><a    href="medall_view.php">ALL</a></li>
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
table th{
	font-size:14px;
}
table td{
	font-size:14px;
}
</style>


<table class="table table-hover table-striped table-bordered"  id="example-checkbox" style="width:95%" >
<thead>
<tr>
<th STYLE="WIDTH:10px;color:white">.</th>	
<th STYLE="WIDTH:10px">Action</th>
<th>Code</th>	
<th>Applicant</th>	
<th>Contact</th>
<th>Position</th>	
<th>Destination</th>
<th>Agent</th>
<th>Clinic</th> 	
<th>Date Medical</th>	
<th>Result</th>
<th>-Date Expired</th>	
<th>Remarks <SMALL STYLE="font-size:10px">when to GO BACK</SMALL></th>
<th>Status</th>
<th>-Remarks</th>				
</tr>
</thead>
<tbody>

<?php
include'db.php';
include'functions.php';

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

where applicant_status NOT IN (99999)
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


echo"<tr>";	
echo'<td></td>  ';	
echo"<td style='white-space:normal'><a target='_blank'  href=".$review."".$row['applicant_id']."><button type='button' class='btn  pmd-btn-raised btn-danger btn-xs'  >Review</td> </a> ";

echo"<td>0".$row['applicant_id']."</td>";
echo"<td style='font-size:14px;color:black;font-weight:bold'>".$row['applicant_first']."  ".$row['applicant_last']."</td>";
echo"<td>".$row['applicant_contacts']."</td>";
echo"<td>".$position1['position_name']."</td>";
echo'<td>'.$countryrow["country_name"].'</td>';
echo"<td>".$agentrow1['agent_first']." ".$agentrow1['agent_last']."</td>";
echo"<td>".$row['certificate_medical_clinic']."</td>";

IF($row['certificate_medical_exam_date']=='1970-01-01' || $row['certificate_medical_exam_date']=='0000-00-00' || $row['certificate_medical_exam_date']=='' ) {
echo"<td>--</td>";
}
else{  echo'<td>'; echo date('M-d-Y', strtotime($row1['certificate_medical_exam_date']));echo'</td>'; }

if($row['certificate_medical_result']!=''){
echo"<td>".$row['certificate_medical_result']."</td>";
}
if($row['certificate_medical_result']==''){
echo"<td STYLE='color:black;font-size:12px'></td>";
}

echo"<td>".date("d-F-Y",$effectiveDate)."</td>";

echo"<td><input type='TEXT'  name='certificate_goback[]' value='".$row['certificate_goback']."' style='width:120px'></td>";

echo'<td><button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:12px">'.$row["status"].'</button></td>';
echo"<td>".$row['certificate_medical_remarks']."</td>";

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

