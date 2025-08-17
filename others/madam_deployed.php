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

$app = mysql_query("SELECT deployed.*,
statuses.*,
applicant_passport.*,
applicant.applicant_id,
applicant.applicant_first,
applicant.applicant_contacts,
applicant.applicant_employer,
applicant.applicant_source,
applicant.m_agent,
applicant.applicant_last
 FROM deployed
LEFT JOIN applicant
ON deployed.deployed_applicant = applicant.applicant_id


LEFT JOIN applicant_passport
ON deployed.deployed_applicant = applicant_passport.passport_applicant


LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number


where
applicant_status IN (9)
and applicant_source=17

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

<a href="reportsnew.php" style="border:3px solid black;padding:5px;color:red;margin:10px;FONT-size:14px;float:left;margin-left:950px">GENERATE REPORTS</a>

<ul id="menu">
<li><a   href="madam_all.php">All Applicant </a></li>
<li><a     href="madam_line.php">CV Sent</a></li>
<li><a href="madam_process.php">On Process</a></li>
<li><a  class="active"  href="madam_deployed.php">Deployed</a></li>
<li><a    href="madam_cancelled.php">Cancelled</a></li>
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
#example-checkbox{
font-size:14px;	
}
</style>


<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="FONT-SIZE:12px;width:85%" >
<thead>
<tr>
<th></th>
<th style="font-size:10px;width:55"></th>
<th style="font-size:10px;width:155">Name</th>
<th style="font-size:10px">CONTACT #</th>	
<th style="font-size:10px">FRA</th>
<th style="font-size:10px">PASSPORT</th>
<th style="font-size:10px">STATUS</th>

<th style="font-size:10px">TESDA</th>
<th style="font-size:10px">AGENT</th>
<th>SUB AGENT</th>
<th style="font-size:10px;width:30px">DATE DEPLOYED</th>
										
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







IF($row1['certificate_tesda_date']=='1970-01-01' || $row1['certificate_tesda_date']=='0000-00-00' || $row1['certificate_tesda_date']=='' ) {
echo"<td></td>";
}
else{ 
echo"<td >".$row1['certificate_tesda_date']."</td>";
}
echo"<td>".$agentrow1['agent_first']." ".$agentrow1['agent_last']." <b>".$agentrow1['agent_contacts']."</b></td>";
echo'<td>'.$sub1["agent_m"].'</td>';
echo"<td>".$row['deployed_date']."</td>";





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
