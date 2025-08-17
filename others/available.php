<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<!-- Head -->
<head>
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
<title>Available</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../assets/styles/admin/bootstrap.min.css" rel="stylesheet" />
<link href="../assets/styles/admin/font-awesome.min.css" rel="stylesheet" />


<!--Beyond styles-->
<link id="beyond-link" href="../assets/styles/admin/beyond.min.css" rel="stylesheet" type="text/css" />

</head>
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
<h3><span>Available Applicants</span></h3>
<!-- para -->
<a href="available.php">Available</a> | 
<a href="available.php">Interview</a> | 
<a href="available.php">Hired</a> | 
<a href="available.php">On process</a> | 
<a href="available.php">For Booking</a> | 
<a href="deployed2.php">Deployed</a>
</div> 

<div class="row">
<div class="col-md-11"  style=" padding-bottom:20px;">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">
<div class="table-responsive">
<table class="table table-hover table-striped table-bordered"  id="example-checkbox" >
<thead>
<tr>
<th></th>
<th>Name</th>	
<th>School</th>
<th>Sponsor</th>
<th>Files</th>	
<th></th>
<th></th>	
	<th></th>
<th></th>							
</tr>
</thead>
<tbody>

<?php
include'db.php';
$app = mysql_query("SELECT * FROM applicant
where applicant_status IN (10,0)
order by applicant_status ASC");
?>   		
<?php

while($row=mysql_fetch_array($app))
{			
$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
$employer=mysql_fetch_array($emp);

$file = mysql_query("SELECT * FROM applicant_files
where file_applicant = ".$row['applicant_id']."
");

$number=str_pad($row['applicant_id'], 10, '0', STR_PAD_LEFT);
echo"<tr>";	
echo'<td style="white-space:normal">
<a target="_blank"  href="http://fcec-inc.com/system/employer/applicants/resume/';?><?=$number?>/<?=$row['applicant_first']?>+<?=$row['applicant_last']?>

<?php	
echo'<td></td>';
echo'" class="btn btn-xs btn-info">resume</a>
</td>  ';
echo"<td>".$row['applicant_first']."  ".$row['applicant_last']."</td>";
echo"<td>".$employer['employer_name']."</td>";
echo"<td></td>";
echo"<td>";?>
<?php
while($filerow=mysql_fetch_array($file)){	
 echo $files="<i style='background:#90EE90;color:black;font-size:10px;padding:4px;margin:4px;width:100px'>
 <a href='http://fcec-inc.com/system/".$filerow['file_path']."'target='_blank;' style='color:black'>
 ".$filerow["file_type"]."</i></a>";
}

echo"</td>";
echo'<td></td>';
echo'<td></td>';
echo'<td></td>';
echo'<td></td>';
echo"</tr>";

}				
?>	

</tbody>
</table>
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
