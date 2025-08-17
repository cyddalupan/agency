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

<div class="clients-page">

<div class="col-sm-2 pull-right" align="center">
</div>


<div class="row" style="margin:30px">
<div class="col-md-12"  style="floar:left;background:white;padding:10px;min-height:150px;border:1px solid #DCDCDC;border:1px solid #DCDCDC;width:140%">


<?php
include'db.php';
include'tracking.php';


?>


<style>
#example-checkbox table{
width:100%;	
}
#example-checkbox td {
font-size:12px;
text-align:center;	
}
#example-checkbox a{
font-size:12px;
text-align:center;	
}
#example-checkbox th{
font-size:12px;
text-align:center;	
}

table {
  text-align: left;
  position: relative;
  border-collapse: collapse; 
}
th, td {
  padding: 0.25rem;
}
tr.red th {
  background: red;
  color: white;
}
tr.green th {
  background: green;
  color: white;
}
tr.purple th {
  background: purple;
  color: white;
}
th {
  background: lightgray;
  position: sticky;
  top: 0; /* Don't forget this, required for the stickiness */
  box-shadow: 0 4px 4px -1px rgba(0, 0, 0, 0.6);
}
multiple_lineups
</style>

<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h3 style="color:black"><span>All DH Applicants</span></h3>
<!-- para -->
<?php include'menu.php';?>
<ul id="menu">
<li><a   href="dashboard.php">Dashboard</a></li>
<li><a href="preselected.php"> Pre Applicants/Available</a></li>
<li><a href="passporting.php">Passporting</a></li>
<li><a href="line.php">CV Sent</a></li>
<li><a href="med.php">Medical</a></li>
<li><a   href="all-app.php">Under Process</a></li>
<li><a   href="tesdanew.php">TESDA</a></li>
<li><a   href="musaned.php">MUSANED</a></li>
<li><a   href="owwanew.php">OWWA</a></li>
<li><a   href="stampnew.php">Visa Stamp</a></li>
<li><a   href="oecnew.php">OEC</a></li>
<li><a href="booking.php">Booking</a></li>
<li><a href="deployednew.php">Deployed</a></li>
<li><a href="repatnew.php">Repat</a></li>
<li><a href="atoa.php">A TO A</a></li>
<li><a href="backouts.php">Backout</a></li>
<li style="float:left">

</li>
</ul>
<div style="clear:both;height:10px"></div>

<div style="clear:both;height:10px"></div>
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

 
</br>

<table  cellspacing="0"
id="example-checkbox" class="table table-hover table-striped table-bordered" style="width:70%">
<thead>
<tr>

<th></th>

<th>DATE</th>
<th># DAYS APPLIED</th>
<th>APPLICANT NAME</th>
<th>AGE</th>
<th>POSITION</th>
<th>PASSPORT</th>
<th>COUNTRY</th>
<th>AGENT</th>
<th>BRANCH</th>								
<th>TRANSFER</th>
<th >MEDICAL </th>	
<th >CLINIC</th>	
<th >RESULT</th>
<th >DATE FTW</th>
<th>REMARKS</th>
<th>FRA</th>


<th>Status</th>

<th>ACTION</th>
</tr>
</thead>
<tbody>
<?php




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
where  applicant_position_type='Household'
and applicant_status not in (15,9)

group by applicant_id ORDER BY applicant.applicant_date_applied desc");



while($row=mysql_fetch_array($app))
{



$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);

$position = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);

if($row['typess']==0){$branchme="N/A";}
if($row['typess']==1){$branchme=" MAIN OFFICE";}
if($row['typess']==2){$branchme="Aklan BRANCH";}
if($row['typess']==3){$branchme="Calapan BRANCH";}
if($row['typess']==4){$branchme="Cagayan BRANCH";}
if($row['typess']==5){$branchme="Davao BRANCH";}
if($row['typess']==6){$branchme="Leyte BRANCH";}
if($row['typess']==7){$branchme="Lucena BRANCH ";}
if($row['typess']==8){$branchme="Pangasinan BRANCH";}



if($row['typess1']==0){$branchme1="N/A";}
if($row['typess1']==1){$branchme1=" MAIN OFFICE";}
if($row['typess1']==2){$branchme1="Aklan BRANCH";}
if($row['typess1']==3){$branchme1="Calapan BRANCH";}
if($row['typess1']==4){$branchme1="Cagayan BRANCH";}
if($row['typess1']==5){$branchme1="Davao BRANCH";}
if($row['typess1']==6){$branchme1="Leyte BRANCH";}
if($row['typess1']==7){$branchme1="Lucena BRANCH ";}
if($row['typess1']==8){$branchme1="Pangasinan BRANCH";}

echo'<tr>';


echo'<td>'.$count.'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['applicant_date_applied']));echo'</td>';

$datecountapplied=date_create("".$row['applicant_date_applied']."");
$currentdate=date_create("".$date_now."");
$dateaddedfinal=date_diff($datecountapplied,$currentdate);
echo"<td style='color:black;width:100px'></div><u style='padding:3px;color:black;font-size:12px;background:orange';> ";
echo $dateaddedfinal->format("%r%a days");
echo "</u>";
echo"</td>";

echo'<td><b>'.$row["applicant_first"].' '.$row["applicant_middle"].' '.$row["applicant_last"].'</b></td>';

echo"<td>".$row['applicant_age']."</td>";

echo"<td>".$position1['position_name']."</td>";
echo'<td>'.$row["passport_number"].'</td>';
echo'<td>'.$countryrow["country_name"].'</td>';
echo'<td>'.$row["agent_first"].' '.$row["agent_last"].'</td>';
echo'<td>'.$branchme.'</td>';
echo'<td>'.$branchme1.'</td>';
echo"<td>".$row['certificate_medical_exam_date']."</td>";
echo"<td>".$row['certificate_medical_clinic']."</td>";
echo"<td>".$row['certificate_medical_result']."</td>";

IF($row['medical_fit']=='1970-01-01' || $row['medical_fit']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo"<td>".$row['medical_fit']."</td>"; }
 echo"<td>".$row['applicant_remarks']."</td>";
echo'<td>'.$row["employer_name"].'</td>';


echo'<td>
<a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">
<button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:9px">'.$row["status"].'</button>  - '.$row["sub_status"].'</a></td>';


echo"<td style='white-space:normal'><a target='_blank'  href=".$review."".$row['applicant_id']."><button type='button' class='btn  pmd-btn-raised btn-danger btn-xs'  >Review</td> </a> ";
echo'</tr>';
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
order: [0, 'asc' ],
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
