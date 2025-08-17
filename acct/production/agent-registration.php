<?php include'inc/header.php';
include'inc/agent.php';
include'styles.php';
?>



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
<?php
if($_GET['status']==1){$tite="Clients";}
if($_GET['status']==2){$tite="Suppliers";}
?>

<h3>Agents Registration</h3>


<form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post">

<a href="<?=$actual_link;?>" style="color:Red;text-align:right">Refresh</a>
<button type="button" class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#myModal">Register New </button>

</form>  


<div class="row">

<div class="col-md-12 col-xs-12">
<div class="x_panel">

<div class="x_content">



<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>
<th STYLE="width:1%"></th>
<th STYLE="">ACTION</th>
<th>Status</th>
<th>Code #</th>
<th >Name</th>
<th >agent_contacts</th>
<th >agent_email</th>
<th >agent_remarks</th>
<th>type of payment</th>
<th>mode_payment</th>
<th>bank_name</th>
<th>bank_number</th>
<th>bank_remarks</th>


</tr>
</thead>
<tbody>
<?php	

$app = mysql_query("SELECT * FROM recruitment_agent
order by agent_id desc");


while($row=mysql_fetch_array($app))
{


if($row["agentstatus"]==0){$request='For Approval';$rcolor='black';}
if($row["agentstatus"]==1){$request='Approved';$rcolor='blue';}


echo'<tr>';
echo'<td></td>';
echo'<td><button type="button" class="btn btn-danger" btn-xs" style="font-size:10px;margin:3px;">
<a href="agent-edit.php?e_id='.$row["agent_id"].'" target="_blank" style="color:white">Review</button></a>
</td>';
echo'<td style="font-size:12px;color:white;">
<i style="background:'.$rcolor.';padding:2px;margin:2px">'.$request.'</i>
</td>';
echo'<td>000'.$row["agent_id"].'</td>';
echo'<td>'.$row["agent_first"].' '.$row["agent_last"].'</td>';
echo'<td>'.$row["agent_contacts"].'</td>';
echo'<td>'.$row["agent_email"].'</td>';
echo'<td>'.$row["agent_remarks"].'</td>';
echo'<td>'.$row["terms_payment"].'</td>';
echo'<td>'.$row["mode_payment"].'</td>';
echo'<td>'.$row["bank_number"].'</td>';
echo'<td>'.$row["bank_name"].'</td>';
echo'<td>'.$row["bank_remarks"].'</td>';


echo'</tr>';
}
?>
</tbody>
</table>



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
order: [ 1, 'desc' ],
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



<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog">
<div class="modal-dialog">

<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal">&times;</button>
<h4 class="modal-title">New  Agent</h4>
</div>
<div class="modal-body">

<form class="form-horizontal form-label-left input_mask" method="post" action="">	



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">first name</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="agent_first" REQUIRED>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">LAST name</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="agent_last" REQUIRED>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">agent_contacts</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="agent_contacts" REQUIRED>
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">agent_email</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="agent_email" REQUIRED>
</div>
</div>






<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">agent_remarks</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="agent_remarks" REQUIRED>
</div>
</div>










<h5 style="color:red;text-align:center"><u>Bank Details</u></h5>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12" style="color:blue">Bank Name</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="bank_name"   required>
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Bank Account</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="bank_number" required>
</div>
</div>

<h5 style="color:red;text-align:center"><u>Agreement</u></h5>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Type of Payment</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control" name="terms_payment" required>
<option></option>
<option>Ful Payment</option>
<option>Cash Advance</option>
<option>Partial</option>
<option>Others</option>
</select>
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Mode of Payment</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control" name="mode_payment" required>
<option></option>
<option>Fund Transfer</option>
<option>Cash</option>
<option>Cheque</option>
<option>Others</option>
</select>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Agreement Details</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<textarea name="bank_remarks" rowspan="12" colspan="120" style="width:100%;height:150px"></textarea>
</div>
</div>



<div class="form-group">
<div class="col-md-12 col-sm-9 col-xs-12">	
<button type="submit" class="btn btn-danger pull-right"  name="add_client">Save</button>
</div>
</div>


</form>		  



</div>

</div>

</div>
</div>
