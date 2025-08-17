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
  <form action="" method="post">
  
<div class="clients-page">

<div class="col-sm-2 pull-right" align="center">
</div>


<div class="row" style="margin:30px">
<div class="col-md-12"  style="floar:left;background:white;padding:10px;min-height:150px;border:1px solid #DCDCDC;border:1px solid #DCDCDC;width:140%">


<?php
include'db.php';
include'functions_line.php';

$app = mysql_query("SELECT * FROM applicant
LEFT JOIN applicant_passport
ON applicant.applicant_id = applicant_passport.passport_applicant
LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number

where
applicant_status IN (5)
AND applicant_position_type='Household'

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
<h1 STYLE="COLOR:BLACK"><span> BACKOUT</span></h1>
<!-- para -->
<?php
include'menuchange.php';
include'menu.php';
?>
<ul id="menu">
<li><a  href="dashboard.php"><?=$dashboard?></a></li>
<li><a  href="preselected.php"  ><?=$available?></a></li>
<li><a href="passporting.php"><?=$passporting?></a></li>
<li><a  href="line.php"> <?=$cv?></a></li>
<li><a  href="med.php"><?=$medical?></a></li>
<li><a   href="all-app.php"><?=$selected?></a></li>
<li><a   href="tesdanew.php"><?=$tesda?></a></li>
<li><a   href="musaned.php"><?=$musaned?></a></li>
<li><a   href="owwanew.php"><?=$owwa?></a></li>
<li><a   href="stampnew.php"><?=$visamenu?></a></li>
<li><a   href="oecnew.php"><?=$oecmenu?></a></li>
<li><a href="booking.php"><?=$bookingmenu?></a></li>
<li><a href="deployednew.php"><?=$deployed?></a></li>
<li><a href="repatnew.php"><?=$repat?></a></li>
<li><a href="atoa.php"><?=$atoa?></a></li>
<li><a  class="active" href="backouts.php"><?=$backoutmenu?></a></li>
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



<style>
TABLE th{
font-size:14px;	
}
TABLE tD{
font-size:12px;	
}
</style>


<table  cellspacing="0" id="example-checkbox" class="table table-hover table-striped table-bordered" style="width:70%">
<thead>
<tr>

<th>#</th>
<th Style="width:6%">Status</th>
<th  Style="width:6%">Date Applied</th>
<th>Applicant Name</th>
<th>Agent</th>

<th>FRA</th>
<th>Age</th>
<th>Position</th>
<th>Passport</th>
<th>Country</th>


</tr>
</thead>
<tbody>
<?php	

if(isset($_POST['sentcv'])){
foreach ($_POST['delall'] as $delall) {
$app = mysql_query("UPDATE applicant
SET  applicant_status =0
,applicant_employer =0
,applicant_updatedby =".$_SESSION['user_id']."
,applicant_updated= '".$date3."'
,numberone =".$_POST['numberone']."
where applicant_id= ".$delall)
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

where applicant.applicant_status IN (1,25)
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


echo'<tr>';

echo'<td>'.$count.'</td>';
echo'<td>
<a href='.$review.''.$row["applicant_id"].'  target="_blank">
<button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:12px">'.$row["status"].'</button></a></td>';
echo'<td>'; echo date('M-d-Y', strtotime($row['applicant_date_applied']));echo'</td>';




echo'<td STYLE="FONT-SIZE:14PX;width:250px"><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';


echo'<td >'.$row["agent_first"].' '.$row["agent_last"].'</td>';
echo'<td>'.$row["employer_name"].'</td>';
echo"<td>".$row['applicant_age']."</td>";
echo"<td>".$position1['position_name']."</td>";
echo'<td>'.$row["passport_number"].'</td>';
echo'<td>'.$countryrow["country_name"].'</td>';

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
order: [ 5, 'asc' ],
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
