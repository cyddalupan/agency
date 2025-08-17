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
AND applicant_position_type='Skilled'

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
<h3 style="color:black"><span> Skilled</span></h3>
<!-- para -->
<?php
include'menuchange.php';
include'menu.php';
?>
<ul id="menu">
<li><a  href="dashboard.php"><?=$dashboard?></a></li>
<li><a  href="preselected.php"  ><?=$available?></a></li>
<li><a href="passporting.php"><?=$passporting?></a></li>
<li><a class="active"  href="line.php"> <?=$cv?></a></li>
<li><a href="med.php"><?=$medical?></a></li>
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
<li><a href="backouts.php"><?=$backoutmenu?></a></li>
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


<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="FONT-SIZE:9px;width:72%" >
<thead>
<tr>
<th></th>
<th STYLE="WIDTH:10px;color:white">Action</th>
<th>Applicant #</th>
<th>Date Applied</th>
<th># Days Process</th>
<th>CV Send</th>
<th style="">Name</th>
<th style="">Age</th>
<th style="">Religion</th>
<th style="">Marital Status</th>
<th style="">SOURCE</th>	
<th style="">FRA</th>
<th style="">PASSPORT</th>
<th style="">-Position</th>
<th style="">Interview Date</th>
<th style="">NBI</th>
<th style="">E-Reg</th>
<th style="">Peos</th>
<th style="">Remarks</th>

	
										
										
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
$effectiveDate = strtotime("+30 days", strtotime($date_now));
 $datenow1 = strtotime($date_now);
 $datenow= date("Y-m-d",$datenow1);
$date2=date("Y-m-d",$row1['certificate_medical_expiration']);
 $date3 = strtotime($date2);
echo"<tr>";	
echo "<td></td> ";	
echo"<td style='white-space:normal'><a target='_blank'  href=".$review."".$row['applicant_id']."><button type='button' class='btn  pmd-btn-raised btn-danger btn-xs'  >Review</td> </a> ";
echo"<td>000000".$row['applicant_id']."</td>";

$datecountapplied=date_create("".$row['applicant_date_applied']."");
$currentdate=date_create("".$date_now."");
$dateaddedfinal=date_diff($datecountapplied,$currentdate);
echo"<td>".$row['applicant_date_applied']."</td>";
echo"<td style='color:black;width:100px'></div><u style='padding:3px;color:black;font-size:12px;background:orange';> ";
echo $dateaddedfinal->format("%r%a days");
echo "</u>";
echo"</td>";

$line = mysql_query("SELECT * FROM applicants_logs_view where log_applicant=".$row['applicant_id']." AND log_status=5 Group by log_status desc");
$lineup=mysql_fetch_array($line);

echo"<td>";
echo "" .$lineup['log_created'];
echo"</td>";

echo"<td>".$row['applicant_first']."  ".$row['applicant_last']."</td>";
echo"<td>".$row['applicant_age']."</td>";
echo"<td>".$row['applicant_religion']."</td>";
echo"<td>".$row['applicant_civil_status']."</td>";
echo"<td>".$agentrow1['agent_first']." ".$agentrow1['agent_last']."</td>";
echo"<td>".$employer['employer_name']."</td>";
echo"<td>".$row['passport_number']."</td>";
echo"<td>".$position1['position_name']."</td>";
echo"<td>".$row['applicant_date_interview']."</td>";
echo"<td>".$row1['nbi_expired_date']."</td>";
echo"<td>".$row2['applicant_requirement_ereg']."</td>";
echo"<td>".$row2['applicant_requirement_peosd']."</td>";
echo"<td><input type='hidden' name='applicant_id[]' value='".$row['applicant_id']."' checked>
<input type='TEXT'  name='certificate_lineup_remarks[]' value='".$row1['certificate_lineup_remarks']."'  style='width:120px'></td>";


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
