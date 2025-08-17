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



<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h3 style="color:black"><span> Household</span></h3>
<!-- para -->
<?php include'menu.php';?>
<ul id="menu">
<li><a   href="dashboard.php">Dashboard</a></li>
<li><a href="preselected.php"  > Pre Applicants/Available</a></li>
<li><a href="passporting.php">Passporting</a></li>
<li><a href="line.php"  class="active"> CV Sent</a></li>
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
<input type='submit'class="btn btn-sm btn-danger" name='updatedata' value='Update Data' style="height:28px;float:left;margin:10px;border:2px solid white">
</li>
</ul>

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

<a href="delete_methode_link" onclick="return confirm('Are you sure you want Proceed?');">		
<button type="submit" class="btn btn-success btn-sm"  name="sentcv" style="border:BORDER 2PX SOLID black;color:white;border:1px solid black">Back to Avaiable</button>
</a>
</br></br>
<p style="color:Red">Count down starts once line up (14 days countdown)</p>

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

</style>
<table  cellspacing="0"
id="example-checkbox" class="" style="width:60%">
<thead>
<tr>

<th></th>
<th>#</th>
<th>Date Applied</th>
<th>CV Send</th>
<th>Extension</th>
<th>Days Sent</th>
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
SET  applicant_status =0
,applicant_employer =0
,applicant_updatedby =10013
,applicant_updated= '".$date3."'
,numberone =".$_POST['numberone']."
where applicant_id= ".$delall)
or die ("cannot Update data");



$app3 = mysql_query("DELETE FROM multiple_lineups where applicant_id= ".$delall)
or die ("cannot Update data");


}
echo"<p style='color:white;BACKGROUND:#DC143C;padding:5px;FONT-SIZE:1qPX;WIDTH:400px'>Back to Avaiable!</p>";
}

if(isset($_POST['extendme'])){
foreach ($_POST['delall'] as $delall) {
$app = mysql_query("UPDATE applicant
SET numberone =".$_POST['numberone']."
where applicant_id= ".$delall)
or die ("cannot Update data");
}
echo"<p style='color:white;BACKGROUND:#DC143C;padding:5px;FONT-SIZE:1qPX;WIDTH:400px'>Back to Avaiable!</p>";
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

where applicant.applicant_status IN (5)
ORDER BY applicant.applicant_id desc");

if(isset($_POST['Search']))
{
$app = mysql_query("SELECT  * FROM applicant
where
applicant_last LIKE '%".$_POST['search']."%'
OR applicant_first LIKE '%".$_POST['search']."%'
OR emoloyer_name LIKE '%".$_POST['search']."%'
limit 20
");
}

while($row=mysql_fetch_array($app))
{



$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);
$position = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);


$line = mysql_query("SELECT * FROM applicant_log where log_applicant=".$row['applicant_id']." and log_status=5 order by log_id desc");
$lineup=mysql_fetch_array($line);


$datecountapplied=date_create("".$lineup['log_created']."");
$currentdate=date_create("".$date_now."");
$dateaddedfinal=date_diff($datecountapplied,$currentdate);
$dayone=$dateaddedfinal->format("%r%a");
$coundown=($row["numberone"])+$dayone;

if(5<$coundown) {$colorme="";}
if(6>=$coundown) {$colorme="";}




if(5<$coundown)
{
$color111a="#FFB6C1";
$color21a="white";
}

if(6>=$coundown)
{
$color111a="white";
$color21a="black";
}


echo'<tr style="background:'.$color111a.';color:'.$color21.'">';
echo'<td><input type="checkbox" name="delall[]" value="'.$row['applicant_id'].'" ></td>';
echo'<td>'.$count.'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['applicant_date_applied']));echo'</td>';


echo"<td>";
echo "" .$lineup['log_created'];
echo"</td>";

if($row["numberone"]==0){echo'<td>--</td>';};
if($row["numberone"]!=0){echo'<td>'.$row["numberone"].'</td>';};
echo"<td style='color:white;width:100px'></div><u style='padding:1px;color:black;font-size:16px;background:".$colorme.";font-weight:bold';> ";
echo $coundown;
echo "</u>";
echo"</td>";


echo'<td><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';
echo"<td>".$row['applicant_age']."</td>";
echo"<td>".$position1['position_name']."</td>";
echo'<td>'.$row["passport_number"].'</td>';
echo'<td>'.$countryrow["country_name"].'</td>';

echo'<td style="font-size:12px">'.$row["agent_first"].' '.$row["agent_last"].'</td>';
echo"<td>".$row['certificate_medical_exam_date']."</td>";
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
</br></br>
<div class="col-md-4 col-xs-4">
Extension Days: <input type="number" class="form-control" name="numberone" required style="width:200px" value="0"> 
 
<a href="delete_methode_link" onclick="return confirm('Are you sure you want to Extend CV?');">		
<button type="submit" class="btn btn-danger btn-sm"  name="extendme" style="border:BORDER 2PX SOLID black;color:white;border:1px solid black;float:left">Extend Cv Sent</button>
</a>
</div>
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
order: [ 5, 'desc' ],
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
