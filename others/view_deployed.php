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

$employer = mysql_query("SELECT * FROM   assign
where a_user=".$_GET['user']." 
ORDER BY a_id desc");

$nameArray = array();
while($rowemployer = mysql_fetch_array($employer)) {
    // Append to the array
     $nameArray[] = $rowemployer['a_employer'];  
}

$employerr=implode(",",$nameArray);

$app = mysql_query("SELECT * FROM applicant
LEFT JOIN applicant_passport
ON applicant.applicant_id = applicant_passport.passport_applicant
LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number

where
applicant_status IN (9)
and applicant_employer IN(".implode(",",$nameArray).")
group by applicant_id 
order by applicant_status ASC");
?>



<form action="" method="post">
<input type='submit'class="btn btn-lg btn-danger" name='updatedata' value='Update Data' style="width:140px;height:40px;float:right;margin-right:150px;border:2px solid white">


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
<h3><span>Deployed Applicants</span></h3>


<ul id="menu">
<li><a   href="view_coordinator.php?user=<?=$_GET['user']?>">Dashboard </a></li>
<li><a  href="view_coordinator-all.php?user=<?=$_GET['user']?>">My Applicant </a></li>
<li><a href="view_line.php?user=<?=$_GET['user']?>">CV Sent</a></li>
<li><a href="view_coordinator1.php?user=<?=$_GET['user']?>">On Process</a></li>
<li><a  class="active" href="view_deployed.php?user=<?=$_GET['user']?>">Deployed</a></li>
<li><a   href="view_cancelled.php?user=<?=$_GET['user']?>">Cancelled</a></li>
<li><a href="view_reports.php?user=<?=$_GET['user']?>">Reports </a></li>
<li style="float:right">

</li>
</ul>

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
TABLE th{
font-size:14px;	
}
TABLE tD{
font-size:12px;	
}
</style>


<table class="table table-hover table-striped table-bordered"  id="example-checkbox"  STYLE="Width:90%" >
<thead>
<tr>
<th  style="width:1px"></th>
<th>Date Applied</th>

<th >Applicant Name</th>
<th>AGENT</th>
<th >CONTACT #</th>	
<th>FRA</th>
<th>PASSPORT</th>
<th>STATUS</th>
<th>SUB_STATUS</th>

<th>CLINIC</th>	
<th>RESULT</th>		
<th>VACCINE</th>
<th>TESDA</th>
<th>PDOS</th>	
<th>C. RECEIVED</th>
<th>C. SIGNED</th>	
<th>OWWA</th>	
<th>OEC</th>	
<th>VISA</th>
									
										
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

$number=str_pad($row['applicant_id'], 10, '0', STR_PAD_LEFT);
$effectiveDate = strtotime("+30 days", strtotime($date_now));
 $datenow1 = strtotime($date_now);
 $datenow= date("Y-m-d",$datenow1);
$date2=date("Y-m-d",$row1['certificate_medical_expiration']);
 $date3 = strtotime($date2);
 
$line = mysql_query("SELECT * FROM applicants_logs_view where log_applicant=".$row['applicant_id']." AND log_status=5 Group by log_status desc");
$lineup=mysql_fetch_array($line);



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


echo"<td style='white-space:normal'></td> ";
echo'<td>'; echo date('M-d-Y', strtotime($row['applicant_date_applied']));echo'</td>';
echo"<td><a target='_blank'  href=".$review."".$row['applicant_id']." STYLE='FONT-SIZE:14px'>".$row['applicant_first']."  ".$row['applicant_last']."</a></td>";

echo"<td>".$agentrow1['agent_first']." ".$agentrow1['agent_last']."</td>";	


echo"<td>".$row['applicant_contacts']."</td>";
echo"<td>".$employer['employer_name']."</td>";
echo"<td>".$row['passport_number']."</td>";


echo'<td><button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:12px">'.$row["status"].'</button></td>';
if ($row['applicant_status']!=9 || $row['applicant_status']!=8 || $row['applicant_status']!=12) { echo"<td>".$row['sub_status']."</td>"; }
else { echo"<td></td>"; }






echo"<td>".$row1['certificate_medical_clinic']."</td>";
echo"<td>".$row1['certificate_medical_result']."</td>";

echo"<td >".$row1['certificate_mmr']."</td>";

if($row1['certificate_tesda_date']=='1970-01-01' || $row1['certificate_tesda_date']=='0000-00-00' || $row1['certificate_tesda_date']==''){
echo"<td ></td>";
}
else{
 echo'<td>'; echo date('M-d-Y', strtotime($row1['certificate_tesda_date']));echo'</td>';
}



if($row1['certificate_pdos_date']=='1970-01-01' || $row1['certificate_pdos_date']=='0000-00-00' || $row1['certificate_pdos_date']==''){
echo"<td ></td>";
}
else{
echo'<td>'; echo date('M-d-Y', strtotime($row1['certificate_pdos_date']));echo'</td>';
}





IF($row2['requirement_contract']=='1970-01-01' || $row2['requirement_contract']=='0000-00-00' || $row2['requirement_contract']=='' ) {
echo"<td style='color:Red'>";
echo"</td>";
}
else{ 
echo"<td style='color:Red'>"; echo date('M-d-Y', strtotime($row2['requirement_contract']));
echo"&nbsp;<u style='color:black'></br>";
echo $diffcr->format("%r%a days");
echo"</u></td>";
}

IF($row2['requirement_contract_sign']=='1970-01-01' || $row2['requirement_contract_sign']=='0000-00-00' || $row2['requirement_contract_sign']=='') { echo"<td></td>"; }
else{  echo'<td>'; echo date('M-d-Y', strtotime($row2['requirement_contract_sign']));echo'</td>'; }


IF($row1['certificate_owwa_to']=='1970-01-01' || $row1['certificate_owwa_to']=='0000-00-00' || $row1['certificate_owwa_to']=='' ) { echo"<td></td>"; }
else{  echo'<td>'; echo date('M-d-Y', strtotime($row1['certificate_owwa_to']));echo'</td>'; }


if($row2['requirement_oec_release_date']=='1970-01-01' || $row2['requirement_oec_release_date']=='0000-00-00' || $row2['requirement_oec_release_date']==''){
echo"<td ></td>";
}
else{
echo'<td>'; echo date('M-d-Y', strtotime($row2['requirement_oec_release_date']));echo'</td>';
}



$date_addedded122=date_create("".$row2['requirement_contract']."");
$visacr=date_create("".$row2['requirement_visa_stamp']."");
$diffvisacr=date_diff($date_addedded122,$visacr);
$d2=$diffvisacr;


IF($row2['requirement_visa_stamp']=='1970-01-01' || $row2['requirement_visa_stamp']=='0000-00-00' || $row2['requirement_visa_stamp']=='' ) {
echo"<td style='color:Red'>
 ";

echo"</td>";
}

else{ 
echo"<td style='color:Red'>"; echo date('M-d-Y', strtotime($row2['requirement_visa_stamp'])); 
echo"</br><u style='color:black'> ";
echo $diffvisacr->format("%r%a days");
echo"</u></td>";
}
echo"</tr>";


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
