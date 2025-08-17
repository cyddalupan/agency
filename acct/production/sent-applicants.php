<?php include'inc/header.php';
include'db.php';
include'tracking.php';
include'styles.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
      <meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
 
  </head>

<body class="nav-md">
<div class="container body">
<div class="main_container">
<div class="col-md-3 left_col">
<div class="left_col scroll-view">
<div class="navbar nav_title" style="border: 0;">
<a href="index.html" class="site_title"> <span><?php include'title.php';?></span></a>
</div>

<div class="clearfix"></div>

<!-- menu profile quick info -->
<div class="profile clearfix">
<div class="profile_pic">
<img src="images/img.jpg" alt="..." class="img-circle profile_img">
</div>
<div class="profile_info">
<span>Welcome,</span>
<h2><?=$_SESSION['user_fullname']?></h2>
</div>
</div>
<!-- /menu profile quick info -->

<br />

<!-- sidebar menu -->
<div id="sidebar-menu" class="main_menu_side hidden-print main_menu">

<div class="menu_section">
<?php include'inc/menu.php';?>	
</div>
</div>
<!-- /sidebar menu -->

<!-- /menu footer buttons -->
<div class="sidebar-footer hidden-small">
<a data-toggle="tooltip" data-placement="top" title="Settings">
<span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
</a>
<a data-toggle="tooltip" data-placement="top" title="FullScreen">
<span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
</a>
<a data-toggle="tooltip" data-placement="top" title="Lock">
<span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
</a>
<a data-toggle="tooltip" data-placement="top" title="Logout" href="login.html">
<span class="glyphicon glyphicon-off" aria-hidden="true"></span>
</a>
</div>
<!-- /menu footer buttons -->
</div>
</div>

<!-- top navigation -->
<div class="top_nav">
<div class="nav_menu">
<nav>
<div class="nav toggle">
<a id="menu_toggle"><i class="fa fa-bars"></i></a>
</div>
<ul class="nav navbar-nav navbar-right">
<li class="">
<a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
<img src="images/img.jpg" alt="">
<span class=" fa fa-angle-down"></span>
</a>
<ul class="dropdown-menu dropdown-usermenu pull-right">

</li>
<li><a href="logout.php"><i class="fa fa-sign-out pull-right"></i> Log Out</a></li>
</ul>
</li>


</ul>
</li>
</ul>
</nav>
</div>
</div>
<!-- /top navigation -->

<!-- page content -->
<div class="right_col" role="main">




<div class="row">




<div class="col-md-12 col-xs-12">


<div class="x_panel">

<h2>CV SENT Applicants</h2>


<div class="x_content">

<form action="" method="post"">

<a href="delete_methode_link" onclick="return confirm('Are you sure you want to Sent CV?');">		
<button type="submit" class="btn btn-success btn-sm"  name="sentcv" style="border:BORDER 2PX SOLID black;color:white;border:1px solid black">Back to Avaiable</button>
</a>


</br></br>



<p style="color:Red">Count down starts once line up (14 days countdown)</p>


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

<a id="downloadLink" onclick="exportF(this)" style="color:Red;FLOAT:right;font-size:14px;padding:5px">Export to excel</a>


<table  cellspacing="0"
id="example-checkbox">
<thead>
<tr>

<th></th>
<th>#</th>
<th>Date Applied</th>
<th>CV Send</th>
<th>Extension</th>
<th>Count Down</th>
<th>Applicant Name</th>
<th>Age</th>
<th>Position</th>
<th>Passport</th>
<th>Country</th>
<th>Agent</th>
<th >MEDICAL </th>	
<th >CLINIC</th>	
<th >RESULT</th>
<th>Employer/Prinicipal</th>
<th>Status</th>

<th>ACTION</th>
</tr>
</thead>
<tbody>
<?php	

if(isset($_POST['sentcv'])){
foreach ($_POST['delall'] as $delall) {
$app = mysql_query("UPDATE applicant
SET  applicant_status =0
,applicant_employer =0
,applicant_updatedby =".$_SESSION['user_id']."
,applicant_updated= '".$date3."'
,numberone =".$_POST['numberone']."
where applicant_id= ".$delall)
or die ("cannot Update data");
}
echo"<p style='color:white;BACKGROUND:#DC143C;padding:5px;FONT-SIZE:1qPX;WIDTH:400px'>Back to Avaiable!</p>";
}

if(isset($_POST['extendme'])){
foreach ($_POST['delall'] as $delall) {
$app = mysql_query("UPDATE applicant
SET numberone =".$_POST['numberone']."
where applicant_id= ".$delall)
or die ("cannot Update data");
}
echo"<p style='color:white;BACKGROUND:#DC143C;padding:5px;FONT-SIZE:1qPX;WIDTH:400px'>Back to Avaiable!</p>";
}



$count=1;	
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

where applicant.applicant_status IN (5)
 group by applicant.applicant_id");

if(isset($_POST['Search']))
{
$app = mysql_query("SELECT  * FROM applicant
where
applicant_last LIKE '%".$_POST['search']."%'
OR applicant_first LIKE '%".$_POST['search']."%'
OR emoloyer_name LIKE '%".$_POST['search']."%'
limit 20
");
}

while($row=mysql_fetch_array($app))
{



$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);
$position = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);


echo'<tr>';
echo'<td><input type="checkbox" name="delall[]" value="'.$row['applicant_id'].'" ></td>';
echo'<td>'.$count.'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['applicant_date_applied']));echo'</td>';

$line = mysql_query("SELECT * FROM multiple_lineups where applicant_id=".$row['applicant_id']." order by id desc");
$lineup=mysql_fetch_array($line);


$datecountapplied=date_create("".$lineup['created_at']."");
$currentdate=date_create("".$date_now."");
$dateaddedfinal=date_diff($datecountapplied,$currentdate);
$dayone=$dateaddedfinal->format("%r%a");
$coundown=(14+$row["numberone"])-$dayone;

if(5<$coundown) {$colorme="lightblue";}
if(6>=$coundown) {$colorme="red";}

echo"<td>";
echo "" .$lineup['created_at'];
echo"</td>";

if($row["numberone"]==0){echo'<td>--</td>';};
if($row["numberone"]!=0){echo'<td>'.$row["numberone"].'</td>';};
echo"<td style='color:white;width:100px'></div><u style='padding:4px;color:white;font-size:14px;background:".$colorme."';> ";
echo $coundown;
echo "</u>";
echo"</td>";


echo'<td><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';
echo"<td>".$row['applicant_age']."</td>";
echo"<td>".$position1['position_name']."</td>";
echo'<td>'.$row["passport_number"].'</td>';
echo'<td>'.$countryrow["country_name"].'</td>';

echo'<td style="color:orange;font-size:10px">'.$row["agent_first"].' '.$row["agent_last"].'</td>';
echo"<td>".$row['certificate_medical_exam_date']."</td>";
echo"<td>".$row['certificate_medical_clinic']."</td>";
echo"<td>".$row['certificate_medical_result']."</td>";
echo'<td>'.$row["employer_name"].'</td>';
echo'<td>
<a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">
<button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:9px">'.$row["status"].'</button></a></td>';


echo'<td><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">View </A></td>';
echo'</tr>';
$count++;
}
?>
</tbody>
</table>
</br></br>
<div class="col-md-4 col-xs-4">
Extension Days: <input type="number" class="form-control" name="numberone" required style="width:200px" value="0"> 
 
<a href="delete_methode_link" onclick="return confirm('Are you sure you want to Extend CV?');">		
<button type="submit" class="btn btn-danger btn-sm"  name="extendme" style="border:BORDER 2PX SOLID black;color:white;border:1px solid black;float:left">Extend Cv Sent</button>
</a>
</div>
</form>	


<div class="ln_solid"></div>

</div>
</div>
</div>  




</div>			











<!-- /footer content -->
</div>
</div>

<!-- jQuery -->
<script src="../vendors/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Skycons -->
<script src="../vendors/skycons/skycons.js"></script>

<!-- Custom Theme Scripts -->
<script src="../build/js/custom.min.js"></script>

</body>





<!-- Propeller Data table js-->
<script type="text/javascript" language="javascript" src="jsdatatables.js"></script>
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
order: [ 5, 'asc' ],
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
"<'row'<'col-sm-12'><'search-paper pmd-textfield'f>>" +
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

</body>
</html>


</html>