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
<style>
#example-checkbox{
font-size:14px;	
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
applicant_status  NOT IN (9,21,22,1,14,25,5)
and applicant_source=17
 group by applicant_id
order by applicant_status ASC");
?>



<form action="" method="post">


<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h3><span>On Process Applicants</span></h3>


<ul id="menu">
<li><a   href="madam_all.php">All Applicant </a></li>
<li><a     href="madam_line.php">CV Sent</a></li>
<li><a   class="active" href="madam_process.php">On Process</a></li>
<li><a  href="madam_deployed.php">Deployed</a></li>
<li><a   href="madam_cancelled.php">Cancelled</a></li>

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

</br></br>


<a href="view_coordinator1.php?user=<?=$_GET['user']?>">All </a> |  <a href="view_selected.php?user=<?=$_GET['user']?>">Selected</a>

</br></br>


<a id="downloadLink" onclick="exportF(this)" style="color:Red">SAVE MY WORK</a>
<style>
#example-checkbox{
font-size:14px;	
}
#example-checkbox td{
padding:3px
}
</style>

<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="FONT-SIZE:12px;width:85%" >
<thead>
<tr>
<th STYLE="WIDTH:10px;color:black">#</th>
<th>Date Applied </th>
<th>Days </th>
<th >Name</th>
<th>CV Send</th>
<th >CONTACT</th>

<th >FRA</th>
<th >Medical RESULT</th>
<th >Medical Cert</th>	
<th >Cert Date</th>		
<th >TESDA</th>
<th>AGENT</th>
<th>SUB AGENT</th>
<th>Country</th>
<th>C. RECEIVED</th>
<th>C. SIGNED</th>	
<th>OWWA</th>
<th>BIO</th>
<th>VISA</th>	
<th >OEC</th>	
<th>STATUS</th>
<th>STATUS DATE</th>
<th style=""></th>	
<th style="">Remarks</th>																	
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

$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);


if($row["applicant_status"]==0 || $row["applicant_status"]==5 ) { $new_date = date('Y-m-d', strtotime($row['applicant_date_applied'])); }
else { $new_date = date('Y-m-d', strtotime($row['applicant_updated'])); }


$number=str_pad($row['applicant_id'], 10, '0', STR_PAD_LEFT);
echo"<td>".$counter."</td>";

$datecountapplied=date_create("".$row['applicant_date_applied']."");
$currentdate=date_create("".$date_now."");
$dateaddedfinal=date_diff($datecountapplied,$currentdate);
echo'<td>'; echo date('M-d-Y', strtotime($row['applicant_date_applied']));echo'</td>';
echo"<td style='color:black;width:100px'></div><u style='padding:5px;color:black;font-size:18px;background:orange';> ";
echo $dateaddedfinal->format("%r%a ");
echo "</u>";
echo"</td>";


echo"<td><a target='_blank'  href='".$review."".$row['applicant_id']."'>
".$row['applicant_first']."  ".$row['applicant_last']."</a></td>";

$line = mysql_query("SELECT * FROM multiple_lineups where applicant_id=".$row['applicant_id']." order by id desc");
$lineup=mysql_fetch_array($line);


$datecountapplied=date_create("".$lineup['created_at']."");
$currentdate=date_create("".$date_now."");
$dateaddedfinal=date_diff($datecountapplied,$currentdate);
$dayone=$dateaddedfinal->format("%r%a");
$coundown=(14+$row["numberone"])-$dayone;

if(5<$coundown) {$colorme="lightblue";}
if(6>=$coundown) {$colorme="red";}
echo'<td>'; echo date('M-d-Y', strtotime($lineup['created_at']));echo'</td>';




echo"<td>".$row['applicant_contacts']."</td>";
echo"<td>".$employer['employer_name']."</td>";

echo"<td>".$row1['certificate_medical_result']."</td>";
echo"<td>".$row1['medical_cert']."</td>";
IF($row1['medical_certdate']=='1970-01-01' || $row1['medical_certdate']=='0000-00-00'  ) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row1['medical_certdate']));echo'</td>'; }

echo"<td>".$row1['certificate_tesda_date']."</td>";

echo"<td>".$agentrow1['agent_first']." ".$agentrow1['agent_last']."</td>";
echo'<td>'.$sub1["agent_m"].'</td>';
echo'<td>'.$countryrow["country_name"].'</td>';

IF($row2['requirement_contract']=='1970-01-01' || $row2['requirement_contract']=='0000-00-00' || $row2['requirement_contract']==null ) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row2['requirement_contract']));echo'</td>'; }

IF($row2['requirement_contract_sign']=='1970-01-01' || $row2['requirement_contract_sign']=='0000-00-00'  
|| $row2['requirement_contract_sign']=='0001-01-01'  || $row2['requirement_contract_sign']=='1969-12-31'  || $row2['requirement_contract_sign']==null  ) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row2['requirement_contract_sign']));echo'</td>'; }

IF($row1['certificate_owwa_from']=='1970-01-01' || $row1['certificate_owwa_from']=='0000-00-00'  || $row1['certificate_owwa_from']==null) { echo"<td></td>"; }
else {
echo"<td>";
echo date('M-d-Y', strtotime($row1['certificate_owwa_from'])); 
ECho" -".$row['applicant_ex']."</td>"; 
}


IF($row2['vfs']=='1970-01-01' || $row2['vfs']=='0000-00-00' || $row2['vfs']=='1969-12-31' || $row2['vfs']==null) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row2['vfs']));echo'</td>'; }

IF($row2['requirement_visa_stamp']=='1970-01-01' || $row2['requirement_visa_stamp']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row2['requirement_visa_stamp']));echo'</td>'; }


IF($row2['requirement_oec_release_date']=='1970-01-01' || $row2['requirement_oec_release_date']=='0000-00-00' || $row2['requirement_oec_release_date']=='1969-12-31'   || $row2['requirement_oec_release_date']==NULL) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row2['requirement_oec_release_date']));echo'</td>'; }


echo"<td>".$row['statusText']."</td>";
 echo'<td>'; echo date('M-d-Y', strtotime($new_date));echo'</td>'; 
echo"<td style='color:Red'>".$row['sub_status']."</td>";
echo"<td>".$row['applicant_remarks']."</td>";
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
