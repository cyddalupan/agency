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


<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h3 STYLE="margin-left:40px"><span>  Medical Status</span></h3>
<!-- para -->
<?php include'menu1.php';?>


<ul id="menu">
<li><a   href="medical_view.php">Medical</a></li>
<li><a    href="tesda_view.php">TESDA</a></li>
<li><a    class="active" href="forline.php">FTW</a></li>
<li><a    href="medall_view.php">ALL</a></l
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



<form action="" method="post"">
<?php
include'db.php';
echo'<select name="emp_id"  required style="padding:5px;width:150px;border:1px solid black">';
echo'<option VALUE="">--</option>
<option value="0">N/A</option>';
$emp1 = mysql_query("SELECT * FROM employer ORDER BY employer_name asc");
while($emp111=mysql_fetch_array($emp1))
{
echo'<option value="'.$emp111["employer_id"].'">  '.$emp111["employer_name"].'</option>';
}
ECHO'</select>';
?>
 
<a href="delete_methode_link" onclick="return confirm('Are you sure you want to Sent CV?');">		
<button type="submit" class="btn btn-success btn-sm"  name="sentcv" style="border:BORDER 2PX SOLID black;color:white;border:1px solid black">Sent CV</button>
</a>
 
<style>
table th{
	font-size:14px;
}
table td{
	font-size:14px;
}
</style>
<table  cellspacing="0"
id="example-checkbox" class="table table-hover table-striped table-bordered" style="width:95%">
<thead>
<tr>

<th></th>
<th>#</th>
<th></th>
<th>Date Applied</th>
<th>Days Applied</th>
<th>Applicant Name</th>
<th>Age</th>
<th>Position</th>
<th>Passport</th>
<th>Country</th>
<th>Agent</th>
<th >MEDICAL </th>	
<th >CLINIC</th>	
<th >RESULT</th>
<th>Employer/Prinicipal</th>
<th>Status</th>

<th>ACTION</th>
</tr>
</thead>
<tbody>
<?php

if(isset($_POST['sentcv'])){
foreach ($_POST['delall'] as $delall) {
$app = mysql_query("UPDATE applicant
SET  applicant_status =5
,applicant_employer =".$_POST['emp_id']."
,applicant_updatedby =1
,applicant_updated= '".$date3."'
where applicant_id= ".$delall)
or die ("cannot Update data");





$app2 = mysql_query("INSERT INTO multiple_lineups (applicant_id,applicant_employer, created_at,updated_at)
VALUES (".$delall." ,".$_POST['emp_id']." ,'".$date3."','".$date3."' )");



}
echo"<p style='color:white;BACKGROUND:#DC143C;padding:5px;FONT-SIZE:1qPX;WIDTH:400px'>Cv Sent!</p>";
}
$count=1;	
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


				LEFT JOIN  applicant_passport
				ON applicant.applicant_id =	applicant_passport.passport_applicant	

where applicant.applicant_status IN (0)
AND certificate_medical_result  IN ('FIT TO WORK')
AND applicant_position_type='Household'
ORDER BY applicant.applicant_date_applied desc");

if(isset($_POST['Search']))
{
$app = mysql_query("SELECT  * FROM applicant


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


				LEFT JOIN  applicant_passport
				ON applicant.applicant_id =	applicant_passport.passport_applicant	

where applicant.applicant_status IN (0)
AND applicant_last LIKE '%".$_POST['search']."%'
OR applicant_first LIKE '%".$_POST['search']."%'
OR emoloyer_name LIKE '%".$_POST['search']."%' 
OR certificate_medical_result LIKE '%".$_POST['search']."%'
");
}

while($row=mysql_fetch_array($app))
{



$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);

$position = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);

echo'<tr>';

echo'<td><input type="checkbox" name="delall[]" value="'.$row['applicant_id'].'" ></td>';
echo'<td>'.$count.'</td>';
echo"<td>".$row['applicant_ex']."</td>";
echo'<td>'; echo date('Y-M-d', strtotime($row['applicant_date_applied']));echo'</td>';

$datecountapplied=date_create("".$row['applicant_date_applied']."");
$currentdate=date_create("".$date_now."");
$dateaddedfinal=date_diff($datecountapplied,$currentdate);
echo"<td style='color:black;width:100px'></div><u style='padding:3px;color:black;font-size:12px;background:orange';> ";
echo $dateaddedfinal->format("%r%a days");
echo "</u>";
echo"</td>";

echo"<td style='font-size:14px;color:black;font-weight:bold'>".$row['applicant_first']."  ".$row['applicant_last']."</td>";

echo"<td>".$row['applicant_age']."</td>";

echo"<td>".$position1['position_name']."</td>";
echo'<td>'.$row["passport_number"].'</td>';
echo'<td>'.$countryrow["country_name"].'</td>';
echo'<td>'.$row["agent_first"].' '.$row["agent_last"].'</td>';
IF($row['certificate_medical_exam_date']=='1970-01-01' || $row['certificate_medical_exam_date']=='0000-00-00' || $row['certificate_medical_exam_date']=='' ) {
echo"<td>--</td>";
}
else{  echo'<td>'; echo date('M-d-Y', strtotime($row1['certificate_medical_exam_date']));echo'</td>'; }

echo"<td>".$row['certificate_medical_clinic']."</td>";
echo"<td>".$row['certificate_medical_result']."</td>";
echo'<td>'.$row["employer_name"].'</td>';
echo'<td>
<a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">
<button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:9px">'.$row["status"].'</button></a></td>';

echo"<td style='white-space:normal'><a target='_blank'  href=".$review."".$row['applicant_id']."><button type='button' class='btn  pmd-btn-raised btn-danger btn-xs'  >Review</td> </a> ";
echo'</tr>';
$count++;
}
?>
</tbody>
</table>
</form>	

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
order: [ 2, 'DESC' ],
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

