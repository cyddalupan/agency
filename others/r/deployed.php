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


<!--Beyond styles-->
<link id="beyond-link" href="../assets/styles/admin/beyond.min.css" rel="stylesheet" type="text/css" />
<script>
function exportF(elem) {
  var table = document.getElementById("customers");
  var html = table.outerHTML;
  var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url 
  elem.setAttribute("href", url);
  elem.setAttribute("download", "export.xls"); // Choose the file name
  return false;
}
</script>

 
</head>
<style>
#customers {
  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #4CAF50;
  color: white;
}
</style>

<!-- /Head -->
<!-- Body -->
<body style="background:white">

<div class="col-md-14 col-sm-14" style="margin-top:-1px">
<div class="row">
<div class="col-md-12">

<div class="clients-page">

<div class="col-sm-2 pull-right" align="center">
<a href="javascript:window.close()" class="btn btn-md btn-danger">Close</a>
</div>

<div class="row">
<div class="col-md-12" style="margin:20px">


<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h3><span> Deployed Applicants</span></h3>
<!-- para -->
</div> 

<div class="row">
<div class="col-md-12"  style=" padding-bottom:20px;">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">
<div class="table-responsive">
<?php
$counter=1;
?>

<p class="date-filter">Date: <?php echo $_POST['start']; ?> &minus; <?php echo $_POST['end']; ?></p>
<a id="downloadLink" onclick="exportF(this)" style="color:Red">Export to excel</a>

<?php
include'db.php';
include'functions.php';
?>



<form action="" method="post">
<input type='submit'class="btn btn-lg btn-danger" name='updatedata' value='Update Data' style="width:140px;height:40px;float:right;margin-right:150px;border:2px solid white">
</br>



<table class="table table-hover table-striped table-bordered"  id="customers" STYLE="FONT-SIZE:11px;width:88%" >
<thead>
<tr>
<th STYLE="WIDTH:11px;color:white">#</th>

<th style="font-size:11px">Name</th>
<th style="font-size:11px">CONTACT #</th>	
<th style="font-size:11px">FRA</th>

<th style="font-size:11px">Source</th>	

<th style="font-size:11px">STATUS</th>
<th style="font-size:11px;">DATE DEPLOYED</th>
<th style="font-size:11px">Amount</th>									
</tr>
</thead>
<tbody>

<?php

if($_POST['agent_id']=='0'){
$agent="";	
}
if($_POST['agent_id']!='0'){
$agent="applicant_source=".$_POST['agent_id']." AND";	
}

$app = mysql_query("SELECT deployed.*,
statuses.*,
applicant_passport.*,
recruitment_agent.*,
applicant.applicant_id,
applicant.applicant_first,
applicant.applicant_contacts,
applicant.applicant_employer,
applicant.applicant_source,
applicant.incc,
applicant.applicant_last
 FROM deployed
LEFT JOIN applicant
ON deployed.deployed_applicant = applicant.applicant_id


LEFT JOIN applicant_passport
ON deployed.deployed_applicant = applicant_passport.passport_applicant

LEFT JOIN recruitment_agent
ON applicant.applicant_source = recruitment_agent.agent_id


LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number


where
$agent
deployed_date between '".$_POST['start']."' AND '".$_POST['end']."'
AND applicant_status=9
group by applicant_id
order by deployed_date desc


 ");
?>   		
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
$totalamountUSD=$row['incc']+$totalamountUSD;

$number=str_pad($row['applicant_id'], 10, '0', STR_PAD_LEFT);
echo "<td><input type='hidden' name='applicant_id[]' value='".$row['applicant_id']."' checked></td> ";

echo"<td><a target='_blank'  href='https://www.recruitmentph.com/marinduquena/admin/applicants/review_single/".$row['applicant_id']."'>
".$row['applicant_first']."  ".$row['applicant_last']."</a></td>";
echo"<td>".$row['applicant_contacts']."</td>";
echo"<td>".$employer['employer_name']."</td>";

echo"<td>".$agentrow1['agent_first']." ".$agentrow1['agent_last']."</td>";

echo"<td>".$row['statusText']."</td>";
echo"<td>".$row['deployed_date']."</td>";
echo"<td><input type='number' value='".$row['incc']."'  name='incc[]' step'any'><i style='font-size:1px'>".$row['incc']."<i></td>";
 
$counter++;
echo"</tr>";
}				
$usd = number_format($totalamountUSD, 2)
?>	

</tbody>

<tr>
<td  colspan="7"  style="font-weight:bold;text-align:right;font-size:14px;color:red">Total</td>
<td  style="font-weight:bold;text-align:right;font-size:14px;color:red"> <?=$usd;?> </td>
</TR>


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
