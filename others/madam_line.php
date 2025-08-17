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
<style>
body{
font-family:calibri;    
}

#shadow {
  -webkit-box-shadow: 3px 3px 5px 6px #ccc;  /* Safari 3-4, iOS 4.0.2 - 4.2, Android 2.3+ */
  -moz-box-shadow:    3px 3px 5px 6px #ccc;  /* Firefox 3.5 - 3.6 */
  box-shadow:         3px 3px 5px 6px #ccc;  /* Opera 10.5, IE 9, Firefox 4+, Chrome 6+, iOS 5 */
}
table td input{
color:black;
height:20px;
width:100px;
}

ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
  overflow: hidden;
  background-color: #000080;
  width:65%;
}

li {
  float: left;
  border-right:1px solid #bbb;
}

li:last-child {
  border-right: none;
}

li a {
  display: block;
  color: white;
  text-align: center;
  padding: 14px 16px;
  text-decoration: none;
}

li a:hover:not(.active) {
  color:white;
}

.active {
  background-color: #1E90FF;
}

</style>


<!-- /Head -->
<!-- Body -->
<body style="background:white">

<div class="col-md-14 col-sm-14" style="margin-top:-1px" >
<div class="row">
<div class="col-md-12">
  <form action="" method="post">
  
<div class="clients-page">

<div class="col-sm-2 pull-right" align="center">
</div>

<div style="clear:both;height:30px"></div>



<div class="row" style="padding:15px;margin:25px;border:1px solid gray;width:110%" id="shadow">
<div class="col-md-12" style="">

<?php
include'db.php';

include'functions.php';


$app = mysql_query("SELECT * FROM applicant
LEFT JOIN applicant_passport
ON applicant.applicant_id = applicant_passport.passport_applicant
LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number

where
applicant_status IN (5)
AND applicant_source=17
 group by applicant_id
order by applicant_status ASC");
?>



<form action="" method="post">


<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h3><span>CV Send </span></h3>

<style>
#example-checkbox{
font-size:14px;	
}
</style>


<ul id="menu">
<li><a   href="madam_all.php">All Applicant </a></li>
<li><a     class="active" href="madam_line.php">CV Sent</a></li>
<li><a href="madam_process.php">On Process</a></li>
<li><a  href="madam_deployed.php">Deployed</a></li>
<li><a   href="madam_cancelled.php">Cancelled</a></li>
<li style="float:right">

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
<a id="downloadLink" onclick="exportF(this)" style="color:Red">SAVE MY WORK</a>
<style>
#example-checkbox{
font-size:14px;	
}
</style>

<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="FONT-SIZE:12px;width:85%" >
<thead>
<tr>
<th></th>
<th></th>
<th style="font-size:10px;width:155">Name</th>
<th>CONTACT #</th>	
<th>FRA</th>
<th>PASSPORT</th>
<th>STATUS</th>

<th>CLINIC</th>	

<th>RESULT</th>	
<th>Expiration</th>
<th>CV Sent </th>
<th>VACCINE</th>	
<th>TESDA</th>
<th>AGENT</th>
<th>SUB AGENT</th>
<th>PDOS</th>	
<th>Contract RECEIVED</th>
<th>Contract SIGNED</th>	
<th>VFS</th>	
<th>OWWA</th>	
<th>OEC</th>	
<th>STAMP</th>	
									
										
</tr>
</thead>
<tbody>
   		
<?php

while($row=mysql_fetch_array($app))
{			
$sub = mysql_query("SELECT * FROM m_agents where m_id=".$row['m_agent']." ");
$sub1=mysql_fetch_array($sub);

$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
$employer=mysql_fetch_array($emp);

$cert = mysql_query("SELECT * FROM applicant_certificate where certificate_applicant=".$row['applicant_id']." ");
$row1=mysql_fetch_array($cert);

$req = mysql_query("SELECT * FROM applicant_requirement where requirement_applicant=".$row['applicant_id']." ");
$row2=mysql_fetch_array($req);

$agent = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$agentrow1=mysql_fetch_array($agent);


if($row['applicant_status']=='4') {$color='orange';}
if($row['applicant_status']=='3') {$color='lightgray';}
if($row['applicant_status']=='2') {$color='blue';}
if($row['applicant_status']=='12') {$color='orange';}
if($row['applicant_status']=='8') {$color='orange';}
if($row['applicant_status']=='1') {$color='red';}
else {$color='gray';}

$number=str_pad($row['applicant_id'], 10, '0', STR_PAD_LEFT);
$effectiveDate = strtotime("+30 days", strtotime($date_now));
 $datenow1 = strtotime($date_now);
 $datenow= date("Y-m-d",$datenow1);
$date2=date("Y-m-d",$row1['certificate_medical_expiration']);
 $date3 = strtotime($date2);
 
 



if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);
$ip2= gethostbyaddr($_SERVER['REMOTE_ADDR']);
$timezone = "Asia/Kuala_Lumpur";
$date12=date('Y-m-d');

$date_addedded=date_create("".$lineup['log_created']."");




$datecur=date_create("".$date12."");
$diff=date_diff($date_addedded,$datecur);
$d=$lineup['log_created'];

$cr=date_create("".$row2['requirement_contract']."");
$diffcr=date_diff($date_addedded,$cr);
$d1=$diffcr;

 
 
echo"<tr>";	
echo "<td><input type='hidden' name='applicant_id[]' value='".$row['applicant_id']."' checked></td> ";
echo"<td style='white-space:normal'><a target='_blank'  href=".$review."".$row['applicant_id']."><button type='button' class='btn  pmd-btn-raised btn-default btn-sm'  >Review</td> </a> ";


	
echo"<td><a target='_blank'  href='".$review."".$row['applicant_id']."'>
".$row['applicant_first']."  ".$row['applicant_last']."</a></td>";

echo"<td>".$row['applicant_contacts']."</td>";
echo"<td>".$employer['employer_name']."</td>";
echo"<td>".$row['passport_number']."</td>";
echo'<td><button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:9px">'.$row["status"].'</button>-<i style="color:Red">'.$row['sub_status'].'</td>';





$line = mysql_query("SELECT * FROM multiple_lineups where applicant_id=".$row['applicant_id']." order by id desc");
$lineup=mysql_fetch_array($line);


echo"<td>".$row1['certificate_medical_clinic']."</td>";
echo"<td>".$row1['certificate_medical_result']."</td>";


echo"<td style='color:blue'>".$row1['certificate_medical_expiration']."</td>";



echo"<td>";
echo "" .$lineup['created_at'];echo"</td>";



echo"</td>";IF($row1['certificate_mmr']=='1970-01-01' || $row1['certificate_mmr']=='0000-00-00' || $row1['certificate_mmr']=='' ) {
echo"<td></td>";
}
else{ 
echo"<td >".$row1['certificate_mmr']."</td>";
}



IF($row1['certificate_tesda_date']=='1970-01-01' || $row1['certificate_tesda_date']=='0000-00-00' || $row1['certificate_tesda_date']=='' ) {
echo"<td></td>";
}
else{ 
echo"<td >".$row1['certificate_tesda_date']."</td>";
}
echo"<td>".$agentrow1['agent_first']." ".$agentrow1['agent_last']." <b>".$agentrow1['agent_contacts']."</b></td>";
echo'<td>'.$sub1["agent_m"].'</td>';

IF($row1['certificate_pdos_date']=='1970-01-01' || $row1['certificate_pdos_date']=='0000-00-00' || $row1['certificate_pdos_date']=='' ) {
echo"<td></td>";
}
else{ 
echo"<td >".$row1['certificate_pdos_date']."</td>";
}

IF($row2['requirement_contract']=='1970-01-01' || $row2['requirement_contract']=='0000-00-00' || $row2['requirement_contract']=='' ) {
echo"<td></td>";
}
else{ 
echo"<td style='color:Red'>".$row2['requirement_contract']." ";

echo"</td>";
}



IF($row2['requirement_contract_sign']=='1970-01-01' || $row2['requirement_contract_sign']=='0000-00-00' || $row2['requirement_contract_sign']=='' ) {
echo"<td></td>";
}
else{ 
echo"<td >".$row2['requirement_contract_sign']."</td>";
}



 
echo"<td>".$row2['vfs']."</td>";
 
 
IF($row1['certificate_owwa_to']=='1970-01-01' || $row1['certificate_owwa_to']=='0000-00-00' || $row1['certificate_owwa_to']=='' ) {
echo"<td></td>";
}
else{ 
echo"<td >".$row1['certificate_owwa_to']."</td>";
}



IF($row2['requirement_oec_release_date']=='1970-01-01' || $row2['requirement_oec_release_date']=='0000-00-00' || $row2['requirement_oec_release_date']=='' ) {
echo"<td></td>";
}
else{ 
echo"<td >".$row2['requirement_oec_release_date']."</td>";
}

$date_addedded122=date_create("".$row2['requirement_contract']."");
$visacr=date_create("".$row2['requirement_visa_stamp']."");
$diffvisacr=date_diff($date_addedded122,$visacr);
$d2=$diffvisacr;



if($row2['requirement_contract']=='1970-01-01' || $row2['requirement_contract']=='0000-00-00' || $row2['requirement_contract']==''){
$date_addedded12=date_create("".$lineup['log_created']."");
$visacr1=date_create("".$date12."");
$diffvisacr1=date_diff($date_addedded12,$visacr1);
}
else{
$date_addedded12=date_create("".$row2['requirement_contract']."");
$visacr1=date_create("".$date12."");
$diffvisacr1=date_diff($date_addedded12,$visacr1);
}



IF($row2['requirement_visa_stamp']=='1970-01-01' || $row2['requirement_visa_stamp']=='0000-00-00' || $row2['requirement_visa_stamp']=='' ) {
echo"<td style='color:Red'>";

}
else{ 
echo"<td style='color:Red'>".$row2['requirement_visa_stamp']."";
}






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
