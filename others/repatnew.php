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
<div class="col-md-12"  style="floar:left;background:white;padding:10px;min-height:150px;border:1px solid #DCDCDC;border:1px solid #DCDCDC;width:120%">


<?php
include'db.php';


?>



<form action="" method="post">



<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h1 style="color:black"><span>REPAT</span></h3>
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
<li><a  class="active" href="repatnew.php"><?=$repat?></a></li>
<li><a href="atoa.php"><?=$atoa?></a></li>
<li><a href="backouts.php"><?=$backoutmenu?></a></li>
<li style="float:right">

</li>
</ul>
<div style="clear:both;height:10px"></div>


</div> 

<div class="row">
<div class="col-md-12"  style=" padding-bottom:20px;">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">
<div class="table-responsive">


<style>
TABLE th{
font-size:14px;	
}
TABLE tD{
font-size:12px;	
}
</style>

<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="FONT-SIZE:9px;width:98%" >
<thead>
<tr>

<th STYLE="WIDTH:10px">Action</th>
<th STYLE="WIDTH:10px">Applicant</th>
<th STYLE="WIDTH:10px">Applicant</th>
<th style="">FRA</th>
<th style="">Sponsor</th>
<th style="">Contact</th>
<th style="">Incase of Emergency</th>
<th style="">Complaint</th>
<th style="">Case #</th>
<th style="">Received From</th>
<th style="">Date Received</th>
<th style="">Agent</th>
<th style="">Flight</th>
</tr>
</thead>
<tbody>
<?php
include'db.php';	
$app = mysql_query("SELECT * FROM applicant
LEFT JOIN applicant_passport
ON applicant.applicant_id = applicant_passport.passport_applicant
LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number

where
applicant_status IN (22)

order by applicant_status ASC");

while($row=mysql_fetch_array($app))
{
$color='';
if($row['status']=="New"){
$but='default';
}
if($row['status']=="CLOSED"){
$but='success';
}	
if($row['status']=="FOR REVIEW"){
$but='warning';
}
if($row['status']=="FOLLOW UP"){
$but='danger';
}
if($row['status']=="NOTIFY"){
$but='primary';
}	
$date_addedded=date_create("".$row['date_added']."");
$datecur=date_create("".$date2."");
$diff=date_diff($date_addedded,$datecur);

$app1 = mysql_query("SELECT * FROM applicant 
where applicant_id=".$row['app_id']." ");
$approw=mysql_fetch_array($app1);

$applicant = mysql_query("SELECT * FROM applicant_requirement 
where requirement_applicant=".$row['app_id']." ");
$applicantrow=mysql_fetch_array($applicant);


$emp = mysql_query("SELECT * FROM employer where employer_id=".$approw['applicant_employer']." ");
$employer=mysql_fetch_array($emp);

$agent = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$approw['applicant_source']." ");
$agentrow1=mysql_fetch_array($agent);

$req = mysql_query("SELECT * FROM applicant_requirement where requirement_applicant=".$row['applicant_id']." ");
$row2=mysql_fetch_array($req);

$position = mysql_query("SELECT * FROM position where position_id=".$approw['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);

$country = mysql_query("SELECT * FROM country where country_id=".$approw['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);

$case = mysql_query("SELECT * FROM history where case_id=".$row['case_id']." order by hid desc");
$caser=mysql_fetch_array($case);


$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);
$detail="".$row['others']."".$row['c1']."".$row['c2']." ".$row['c3']." ".$row['c4']." ".$row['c5']." ".$row['c6']." ".$row['c7']."
".$row['c8']." ".$row['c9']." ".$row['c10']." ".$row['c11']." ".$row['c12']."";

echo'<tr>';
echo"<td style='white-space:normal'><a target='_blank'  href=".$review."".$row['applicant_id']."><button type='button' class='btn  pmd-btn-raised btn-DANGER btn-XS'  >Review</td> </a> ";
echo"<td>0".$row['applicant_id']."</td>";

echo'<td STYLE="FONT-SIZE:14PX;width:250px"><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';

echo"<td style='width:180px'>".$employer['employer_name']."</td>";
echo'<td > '.$approw["sub_employer"].'</td>';
echo'<td > '.$approw["applicant_contacts"].'</td>';

echo'<td >'.$approw["applicant_incase_name"].' - '.$approw["applicant_incase_contact"].' 
</br>'.$row["contact"].'</td>';
echo'<td style="font-size:9px;width:16%;letter-spacing:.02em" >
<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" 
href="#collapseThree'.$row["case_id"].'" aria-expanded="false" aria-controls="collapseThree'.$row["case_id"].'" style="color:gray;padding:2px;font-size:9px;
">
<button type="button" class="btn pmd-btn-raised btn-default btn-xs" style="border:1px solid green">

Click
</a>
</button>
<div id="collapseThree'.$row["case_id"].'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
<div class="panel-body">';
if($row['others']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["others"].'</button>';}
if($row['c1']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c1"].'</button>';}
if($row['c2']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c2"].'</button>';}
if($row['c3']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c3"].'</button>';}
if($row['c4']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c4"].'</button>';}
if($row['c5']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c5"].'</button>';}
if($row['c6']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c6"].'</button>';}
if($row['c7']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c7"].'</button>';}
if($row['c8']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c8"].'</button>';}
if($row['c9']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c9"].'</button>';}
if($row['c10']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c10"].'</button>';}
if($row['c11']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c11"].'</button>';}
if($row['c12']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c12"].'</button>';}


echo'
</div>
</div>
</td>';

echo'<td  > '.$caser["caseid"].' </td>';
echo'<td>'.$row["case_type"].'</td>';
echo'<td  > '.$row["date_added"].' </td>';
echo"<td>".$agentrow1['agent_first']." ".$agentrow1['agent_last']."</td>";
echo'<td>'.$row2["flight_date"].'</td>';
echo'</tr>';
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
