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



<div class="row">
<div class="col-md-12" style="margin:20px">

<?php
include'menuchange.php';
?>

<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h3 STYLE="margin-left:40px"><span> Passporting Applicants</span></h3>
<!-- para -->

<ul id="menu" STYLE="WIDTH:600PX">
<li><a href="view_branch_ro.php?imy=<?=$_GET['imy']?>">All </a></li>
<li><a    class="active"  href="passporting2.php?imy=<?=$_GET['imy']?>">Passporting </a></li>

<li><a   href="view_coordinator22.php?imy=<?=$_GET['imy']?>">Cancelled </a></li>
</ul>
<div style="clear:both;height:10px"></div>


</div> 

<div class="row">
<div class="col-md-11"  style=" padding-bottom:20px;">


	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">
<div class="table-responsive">
<table class="table table-hover table-striped table-bordered"  id="example-checkbox" style="width:80%" >
<thead>
<tr>

<th STYLE="WIDTH:10px">Action</th>
<th></th>
<th>Name</th>
<th>Date Applied</th>	
<th>Contact #</th>
<th>Passport#</th>
<th>Branch</th>
<th>Agent</th>
<th>Country</th>
<th> Medical Type</th>			
<th>Date Medical</th>	
<th>Clinic</th> 
<th>Result</th>	
<th>Remarks Medical</th>
<th>Schedule Date/Time</th> 
<th>Location</th>
<th>Release</th>	
<th>Status</th>
	<th>Remarks</th>
</tr>
</thead>
<tbody>

<?php
include'db.php';
if(isset($_POST['passportsave'])){
$insert=mysql_query("UPDATE applicant
SET 
timesched = '".$_POST['timesched']."',
passsched = '".$_POST['passsched']."',
releases = '".$_POST['releases']."',
remarkspas = '".$_POST['remarkspas']."',
locsched = '".$_POST['locsched']."'
WHERE applicant_id=".$_POST['applicant_id']."");



$insert1=mysql_query("UPDATE applicant_passport
SET 
passport_number = '".$_POST['passport_number']."',
passport_issue_place = '".$_POST['passport_issue_place']."',
passport_expiration = '".$_POST['passport_expiration']."',
passport_issue = '".$_POST['passport_issue']."'
WHERE pass=".$_POST['passport_applicant']."");

echo '<div style="width:100%;padding:5px;background:green;color:white;">';			
echo "Passport Successfully Updated";
echo "</div>";

}

$app = mysql_query("SELECT applicant_certificate.*,applicant.*,applicant_passport.*,statuses.*,recruitment_agent.*

 FROM applicant
LEFT JOIN applicant_certificate ON 
applicant.applicant_id = applicant_certificate.certificate_applicant

LEFT JOIN applicant_passport ON 
applicant.applicant_id = applicant_passport.passport_applicant

LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number

LEFT JOIN  	recruitment_agent
ON applicant.applicant_source =recruitment_agent.agent_id

where passport_number=''
AND applicant_status IN  (15)
AND applicant_source=".$_GET['imy']."

order by applicant_date_applied ASC
");
?>   		
<?php

while($row=mysql_fetch_array($app))
{			
$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
$employer=mysql_fetch_array($emp);

$agent = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$agentrow1=mysql_fetch_array($agent);


$file = mysql_query("SELECT * FROM applicant_files
where file_applicant = ".$row['applicant_id']."");

$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);


if($row['branch_type']==0){$branchme="N/A";}
if($row['branch_type']==7){$branchme="ILO ILO BRANCH";}
if($row['branch_type']==8){$branchme="CDO BRANCH";}
if($row['branch_type']==9){$branchme="DAVAO BRANCH";}
if($row['branch_type']==10){$branchme="BINANGON BRANCH";}
if($row['branch_type']==11){$branchme="CAVITE BRANCH";}
if($row['branch_type']==14){$branchme="MAIN MANILA BRANCH";}
if($row['branch_type']==15){$branchme="TACLOBAN BRANCH";}
if($row['branch_type']==13){$branchme="CEBU BRANCH";}




$number=str_pad($row['applicant_id'], 10, '0', STR_PAD_LEFT);
echo"<tr>";	
echo"<td style='white-space:normal'><a target='_blank'  href=".$review."".$row['applicant_id']."><button type='button' class='btn  pmd-btn-raised btn-danger btn-xs'  >Review</td> </a> ";
echo'<td style="white-space:normal">
<button type="button" class="btn  pmd-btn-raised btn-default btn-sm" data-toggle="modal" 
data-target="#myModal'.$row["applicant_id"].'" >Schedule</td>  ';	

echo"<td>".$row['applicant_first']." ".$row['applicant_middle']."  ".$row['applicant_last']."</td>";

echo"<td>".$row['applicant_date_applied']."</td>";
echo"<td>".$row['applicant_contacts']."</td>";
echo"<td>".$row['passport_number']."</td>";
echo'<td>'.$branchme.'</td>';
echo"<td>".$agentrow1['agent_first']." ".$agentrow1['agent_last']."</td>";
echo'<td>'.$countryrow["country_name"].'</td>';
echo"<td>".$row['medicaltype']."</td>";
echo"<td>".$row['certificate_medical_exam_date']."</td>";
echo"<td>".$row['certificate_medical_clinic']."</td>";
echo"<td>".$row['certificate_medical_result']."</td>";
echo"<td>".$row['certificate_medical_remarks']."</td>";
if($row['passsched']!='0000-00-00'){echo"<td>".$row['passsched']." ".$row['timesched']."</td>";}
if($row['passsched']=='0000-00-00'){echo"<td></td>";}

echo"<td>".$row['locsched']."</td>";
if($row['releases']!='0000-00-00'){echo"<td>".$row['releases']."</td>";}
if($row['releases']=='0000-00-00'){echo"<td></td>";}



echo'<td><button type="button" class="btn btn-warning btn-xs" style="font-size:9px">'.$row["status"].'</button></td>';

echo"<td>".$row['remarkspas']." </td>";
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

<?php




$app1 = mysql_query("SELECT applicant_certificate.*,applicant.*,applicant_passport.*,statuses.*

 FROM applicant
LEFT JOIN applicant_certificate ON 
applicant.applicant_id = applicant_certificate.certificate_applicant

LEFT JOIN applicant_passport ON 
applicant.applicant_id = applicant_passport.passport_applicant

LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number

where passport_number=''
AND applicant_status IN  (15)
");

while($pas=mysql_fetch_array($app1))
{
?>	
<!-- Modal -->
<div class="modal fade" id="myModal<?=$pas['applicant_id']?>" role="dialog">
<div class="modal-dialog" STYle="width:70%">

<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">

<form class="form-horizontal form-label-left input_mask" method="post" action="" style="font-size:12px">	


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">DFA Location</label>
<div class="col-md-3 col-sm-3 col-xs-12">
<input type="text" class="form-control" name="locsched" value="<?=$pas['locsched']?>" STyle="text-transform: uppercase">
</div>

<label class="control-label col-md-3 col-sm-3 col-xs-12"></label>
<div class="col-md-3 col-sm-3 col-xs-12">
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Date Schedule</label>
<div class="col-md-3 col-sm-3 col-xs-12">
<input type="date" class="form-control" name="passsched" value="<?=$pas['passsched']?>" STyle="text-transform: uppercase">
</div>

<label class="control-label col-md-3 col-sm-3 col-xs-12"></label>
<div class="col-md-3 col-sm-3 col-xs-12">
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Time Schedule</label>
<div class="col-md-3 col-sm-3 col-xs-12">
<input type="text" class="form-control" name="timesched" value="<?=$pas['timesched']?>" STyle="text-transform: uppercase">
</div>
<label class="control-label col-md-3 col-sm-3 col-xs-12"></label>
<div class="col-md-3 col-sm-3 col-xs-12">
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Release Schedule</label>
<div class="col-md-3 col-sm-3 col-xs-12">
<input type="date" class="form-control" name="releases" value="<?=$pas['releases']?>" STyle="text-transform: uppercase">
</div>
<label class="control-label col-md-3 col-sm-3 col-xs-12"></label>
<div class="col-md-3 col-sm-3 col-xs-12">
</div>
</div>





<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Remarks</label>
<div class="col-md-6 col-sm-6 col-xs-12">
<input type="text" class="form-control" name="remarkspas" value="<?=$pas['remarkspas']?>" STyle="text-transform: uppercase">
</div>
<label class="control-label col-md-3 col-sm-3 col-xs-12"></label>
<div class="col-md-3 col-sm-3 col-xs-12">
</div>
</div>
</br></br>


<h3 style="color:Red">Passport Information</h2>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">passport number</label>
<div class="col-md-3 col-sm-3 col-xs-12">
<input type="text" class="form-control" name="passport_number" value="<?=$pas['passport_number']?>" STyle="text-transform: uppercase">
</div>
<label class="control-label col-md-3 col-sm-3 col-xs-12"></label>
<div class="col-md-3 col-sm-3 col-xs-12">
</div>
</div>





<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">passport issue</label>
<div class="col-md-3 col-sm-3 col-xs-12">
<input type="date" class="form-control" name="passport_issue" value="<?=$pas['passport_issue']?>" STyle="text-transform: uppercase">
</div>
<label class="control-label col-md-3 col-sm-3 col-xs-12"></label>
<div class="col-md-3 col-sm-3 col-xs-12">
</div>
</div>





<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">passport expiration</label>
<div class="col-md-3 col-sm-3 col-xs-12">
<input type="date" class="form-control" name="passport_expiration" value="<?=$pas['passport_expiration']?>" STyle="text-transform: uppercase">
</div>
<label class="control-label col-md-3 col-sm-3 col-xs-12"></label>
<div class="col-md-3 col-sm-3 col-xs-12">
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">passport issue place</label>
<div class="col-md-3 col-sm-3 col-xs-12">
<input type="text" class="form-control" name="passport_issue_place" value="<?=$pas['passport_issue_place']?>" STyle="text-transform: uppercase">
</div>
<label class="control-label col-md-3 col-sm-3 col-xs-12"></label>
<div class="col-md-3 col-sm-3 col-xs-12">
</div>
</div>

           
<input type="hidden" class="form-control" name="applicant_id" value="<?=$pas['applicant_id']?>">






<div class="form-group">
<div class="col-md-12 col-sm-9 col-xs-12">	
</br>
<button type="submit" class="btn btn-danger pull-right"  name="passportsave" style="padding:10px;background:green;color:white">Save Data</button>
</div>
</div>

</form>		  
</div>
</div>
</div>
</div>
<?php } ?>