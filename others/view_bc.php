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
FONT-size:12px;
}
table th{
FONT-size:12px;
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



<div class="row" style="padding:10px;margin:15px;border:0px solid gray;margin-top:-10px" id="shadow">
<div class="col-md-12" style="">

<?php
include'db.php';

include'functions.php';

$employer = mysql_query("SELECT * FROM   assign1
where a_user=".$_GET['user']." 
ORDER BY a_id desc");

$nameArray = array();
while($rowemployer = mysql_fetch_array($employer)) {
    // Append to the array
     $nameArray[] = $rowemployer['a_employer'];  
}
$employerr=implode(",",$nameArray);



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

where
typess IN(".implode(",",$nameArray).")
or typess1 IN(".implode(",",$nameArray).")
group by applicant_id
order by applicant_status ASC ");
?>



<form action="" method="post">



<div class="hero">
<!-- Title. Don't forget the <span> tag -->

<ul id="menu">
<li><a href="view_bc.php?user=<?=$_GET['user']?>">My applicant </a></li>

<li style="float:right">

</li>
</ul>
<h3><span>All Applicants</span></h3>

</div> 
<div style="clear:both;height:20px"></div>
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


<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="" >
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
<th>BRANCH</th>								
<th>TRANSFER</th>										
</tr>
</thead>
<tbody>
   		
<?php
$count=1;
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


if($row['typess']==0){$branchme="N/A";}
if($row['typess']==1){$branchme="PARANAQUE MAIN OFFICE";}
if($row['typess']==2){$branchme="BACOLOD BRANCH";}
if($row['typess']==3){$branchme="BUTUAN BRANCH";}
if($row['typess']==4){$branchme="CEBU BRANCH";}
if($row['typess']==5){$branchme="CAGAYAN DE ORO BRANCH";}
if($row['typess']==6){$branchme="DAVAO BRANCH";}
if($row['typess']==7){$branchme="DIPOLOG BRANCH ";}
if($row['typess']==8){$branchme="DUMAGUETE BRANCH";}
if($row['typess']==9){$branchme="GENSAN BRANCH";}
if($row['typess']==10){$branchme="IPIL BRANCH";}
if($row['typess']==11){$branchme="MAASIN BRANCH";}
if($row['typess']==12){$branchme="OZAMIZ BRANCH";}
if($row['typess']==13){$branchme="PAGADIAN BRANCH";}
if($row['typess']==14){$branchme="SINDANGAN BRANCH";}
if($row['typess']==15){$branchme="TACLOBAN BRANCH";}
if($row['typess']==16){$branchme="TAGUM BRANCH";}
if($row['typess']==17){$branchme="VALENCIA BRANCH";}
if($row['typess']==18){$branchme="ZAMBOANGA BRANCH";}
if($row['typess']==19){$branchme="ROXAS BRANCH";}


if($row['typess1']==0){$branchme1="N/A";}
if($row['typess1']==1){$branchme1="PARANAQUE MAIN OFFICE";}
if($row['typess1']==2){$branchme1="BACOLOD BRANCH";}
if($row['typess1']==3){$branchme1="BUTUAN BRANCH";}
if($row['typess1']==4){$branchme1="CEBU BRANCH";}
if($row['typess1']==5){$branchme1="CAGAYAN DE ORO BRANCH";}
if($row['typess1']==6){$branchme1="DAVAO BRANCH";}
if($row['typess1']==7){$branchme1="DIPOLOG BRANCH ";}
if($row['typess1']==8){$branchme1="DUMAGUETE BRANCH";}
if($row['typess1']==9){$branchme1="GENSAN BRANCH";}
if($row['typess1']==10){$branchme1="IPIL BRANCH";}
if($row['typess1']==11){$branchme1="MAASIN BRANCH";}
if($row['typess1']==12){$branchme1="OZAMIZ BRANCH";}
if($row['typess1']==13){$branchme1="PAGADIAN BRANCH";}
if($row['typess1']==14){$branchme1="SINDANGAN BRANCH";}
if($row['typess1']==15){$branchme1="TACLOBAN BRANCH";}
if($row['typess1']==16){$branchme1="TAGUM BRANCH";}
if($row['typess1']==17){$branchme1="VALENCIA BRANCH";}
if($row['typess1']==18){$branchme1="ZAMBOANGA BRANCH";}
if($row['typess1']==19){$branchme1="ROXAS BRANCH";}
 
echo"<tr>";


echo"<td style='white-space:normal'></td> ";
echo'<td>'; echo date('M-d-Y', strtotime($row['applicant_date_applied']));echo'</td>';
echo"<td><a target='_blank'  href=".$review."".$row['applicant_id'].">".$row['applicant_first']."  ".$row['applicant_middle']." ".$row['applicant_last']."</a></td>";

echo"<td>".$row['agent_first']." ".$row['agent_last']."</td>";	



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
echo'<td>'.$branchme.'</td>';
echo'<td>'.$branchme1.'</td>';
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
