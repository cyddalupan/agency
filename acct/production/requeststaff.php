<?php include'inc/header2.php';
include'styles.php';
?>
<style>
table td{
color:black;	
}
</style>
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
<span>Staff Request</span>
<h2></h2>
</div>
</div>
<!-- /menu profile quick info -->

<br />

<!-- sidebar menu -->
<div id="sidebar-menu" class="main_menu_side hidden-print main_menu">

<div class="menu_section">

<a href="http://promesa.recruitmensys.com/promesa/acct/"><button type="submit" class="btn btn-success"  name="delete">Login as Accounting</button></a>
<div class="clearfix"></div>


<ul class="nav side-menu" STYLE="font-size:11px">
<?php

echo'<li><a href="requeststaff.php?request=0&&textme=Payment Request&&imy='.$_GET["imy"].'"><i class="fa fa-folder-open"></i>Request</a></li>';
echo'<li><a href="requeststaff.php?request=1&&textme=Approved Request&&imy='.$_GET["imy"].'"><i class="fa fa-check"></i>Approved Request</a></li>';
echo'<li><a href="requeststaff.php?request=3&&textme=Released Request&&imy='.$_GET["imy"].'"><i class="fa fa-money"></i>Released Request</a></li>';
echo'<li><a href="requeststaff.php?request=5&&textme=Return to Maker&&imy='.$_GET["imy"].'"><i class="fa fa-exclamation-circle"></i>Return to Maker</a></li>';
echo'<li><a href="requeststaff.php?request=4&&textme=Declined Request&&imy='.$_GET["imy"].'"><i class="fa fa-exclamation-circle"></i>Declined Request</a></li>';
?>

</ul>



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


<div class="x_content" style="margin-top:-20px;border:none">




</div>
<H2 style="color:black;text-decoration:underline"><?=$_GET['textme']?></H2>
<a href="requestadd.php?request=0&&textme=Payment Request&&imy=<?=$_GET["imy"]?>"><button type="submit" class="btn btn-danger pull-right"  name="delete">Add Request</button></a>

<table  cellspacing="0" id="example-checkbox" style="fonts-size:11px">
<thead>

<tr>
<th style="width: 15%">Applicant</th>
<th style="width: 10%">Date</th>
<th style="width: 15%">Agent</th>
<th style="">Type</th>
<th style="width: 16%">Description</th>
<th style="text-align:right">Currency</th>
<th style="text-align:right">Amount</th>
<th style="text-align:right">othe charges</th>
<th style="text-align:right;">Charge To</th>
<th style="text-align:right">Status</th>
<th style="text-align:right">Request</th>
<th style="text-align:right">by</th>
<th STYLE="">ACTION</th>
</tr>

</thead>
<tbody>
<?PHP
include'inc/functions.php';
$app = mysql_query("SELECT * FROM email_address4  where emp_id=0 
AND request_payment=".$_GET['request']." AND requestby='".$_GET['imy']."'
order by exp_id desc ");

if(isset($_POST['Search']))
{
$app = mysql_query("SELECT * FROM email_address4 where
exp_type LIKE '%".$_POST['agent_first']."%' OR findings LIKE '%".$_POST['agent_first']."%'
");
}

while($row=mysql_fetch_array($app))
{

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);

$app12 = mysql_query("SELECT * FROM applicant where applicant_id=".$row['app_id']." ");
$row12=mysql_fetch_array($app12);
$total=$row['receivable']+$total;

if ($row["type_status"]=='Agency'){
	if($row["status_paid"]==0){$status1='';}
	if($row["status_paid"]==1){$status1='';}
}
if ($row["type_status"]=='Agent'){
	if($row["status_paid"]==0){$status1='Not Paid';}
	if($row["status_paid"]==1){$status1='Paid';}
}


if($row["request_payment"]==0){$request='For Approval';$rcolor='black';}
if($row["request_payment"]==1){$request='Approved';$rcolor='blue';}
if($row["request_payment"]==2){$request='For Release';$rcolor='orange';}
if($row["request_payment"]==3){$request='Released';$rcolor='green';}
if($row["request_payment"]==4){$request='Decline';$rcolor='red';}

if($_GET['request']!=0){$activate="disabled";}
if($_GET['request']==0){$activate="";}

echo'<tr>';
echo'<td><a href="edit_unfit.php?exp='.$row["exp_id"].'" target="_blank" style="color:blue">'.$row12["applicant_first"].' '.$row12["applicant_last"].'</a></td>';			
echo'<td>'.$row["date_transaction"].'</td>';
echo'<td>'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$row["exp_type"].'</td>';
echo'<td>'.$row["findings"].'</td>';
echo'<td style="text-align:right">'.$row["currency"].'</td>';
echo'<td style="text-align:right">'.$row["receivable"].'</td>';
echo'<td style="text-align:right">'.$row["othercharge"].'</td>';
echo'<td style="text-align:right">'.$row["type_status"].'</td>';
//echo'<td  style="text-align:right">'.$row["amount_paid"].'</td>';
//echo'<td style="color:orange;font-size:10px">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td style="text-align:right;font-size:10px;color:Red">'.$status1.'</td>';
echo'<td style="text-align:right;font-size:10px;color:white;background:'.$rcolor.'">'.$request.'</td>';
echo'<td style="text-align:right">'.$row["requestby"].'</td>';
if($_GET['request']==0){
echo'<td>
<button type="button" class="btn btn-default" btn-xs" style="font-size:9px;margin:3px" >
<a href="edit_request.php?exp='.$row["exp_id"].'" target="_blank"  STYLE="COLOR:BLUE">Edit Request</button></a>
</td>';
}

if($_GET['request']!=0){
echo'<td>Not editable</td>';}
}
echo'</tr>';
?>	
</tbody>
</table>

</div>
</div>

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
		order: [ 2, 'desc' ],
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




