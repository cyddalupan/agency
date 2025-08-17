<?php include'inc/header.php';
include'inc/expenses.php';
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





<form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post">
<div class="form-group">
<div class="col-md-3 col-sm-4 col-xs-4 form-group">
<input type="date" placeholder="" class="form-control" name="search">
</div>

<div class="form-group">
<div class="col-md-3 col-sm-4 col-xs-4 form-group">
<?php
echo'<select class="form-control" name="Currency" >
<option value="">--Currency--</option>
<option>USD</option>
<option>PHP</option>
';

ECHO'</select>';
?></div>

<div class="col-md-6 col-sm-12 col-xs-12 form-group">
<button type="submit" class="btn btn-info"  name="Search" >Search</button>

</div>

<a href="<?=$actual_link;?>" style="color:Red;text-align:right">Refresh</a>

<button type="button" class="btn btn-danger btn-sm pull-right" data-toggle="modal" data-target="#myModal">Add New Expenses</button>

</form>  


<div class="row">

<div class="col-md-12 col-xs-12">
<div class="x_panel">

<div class="x_content">



<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>
<th></th>
<th>#</th>
<th>DATE</th>
<th>VOUCHER NO.</th>
<th>Applicant</th>
<th>Type</th>
<th>Type of Expenses</th>

<th>Requesting Party</th>
<th>Description</th>
<th>CURRENCY</th>
<th>AMOUNT</th>
<th>Request</th>
<th>ACTION</th>
</tr>
</thead>
<tbody>
<?php	

$app = mysql_query("SELECT * FROM email_address
where account  IN ('BIR payment')
order by date_ad,e_id asc");
if(isset($_POST['Search']))
{
if($_POST['Currency']==''){
$app = mysql_query("SELECT * FROM email_address 
where  date_ad= '".$_POST['search']."'
");
}

if($_POST['Currency']!=''){
$app = mysql_query("SELECT * FROM email_address 
where  currency= '".$_POST['Currency']."'
");
}
}

while($row=mysql_fetch_array($app))
{


$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);

$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$row['app_id']." ");
$rowp=mysql_fetch_array($applicant);

$USER = mysql_query("SELECT * FROM  user where user_id=".$row['staff_id']." ");
$rowu=mysql_fetch_array($USER);


$LIQ = mysql_query("SELECT sum(l_amount) as cashfund FROM  e_liq 
where l_status=0 AND l_e_d=".$row['e_id']."");
$cashliq=mysql_fetch_array($LIQ);


if($row["request_payment"]==0){$request='For Approval';$rcolor='black';}
if($row["request_payment"]==1){$request='Approved';$rcolor='blue';}
if($row["request_payment"]==2){$request='For Release';$rcolor='orange';}
if($row["request_payment"]==3){$request='Released';$rcolor='green';}
if($row["request_payment"]==4){$request='Decline';$rcolor='red';}




echo'<tr>';
echo'<td></td>';
echo'<td>000'.$row["e_id"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['date_ad']));echo'</td>';

echo'<td>'.$row["ref"].'</td>';
echo'<td>
'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td>'.$row["method"].'</td>';
echo'<td>'.$row["account"].'</td>';
echo'<td><a href="view_liq.php?e_id='.$row["e_id"].' && name='.$rowu["user_fullname"].'" target="_blank" ">'.$rowu["user_fullname"].'</a></td>';
echo'<td>'.$row["description"].'</td>';
echo'<td>'.$row["currency"].'</td>';
echo'<td>'.$row['amount'].'</td>';
echo'<td style="text-align:right;font-size:10px;color:white;background:'.$rcolor.'">'.$request.'</td>';


echo'<td><button type="button" class="btn btn-default" btn-xs" style="font-size:9px;margin:3px">
<a href="expenses_edit.php?e_id='.$row["e_id"].'&&date_ad='.$row["date_ad"].'" target="_blank"  STYLE="COLOR:BLUE">Edit</button></a>
</td>';

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



<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog">
<div class="modal-dialog">

<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal">&times;</button>
<h4 class="modal-title">New  Transaction</h4>
</div>
<div class="modal-body">

<form class="form-horizontal form-label-left input_mask" method="post" action="">	

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">DATE Transaction</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="date" class="form-control" name="date_ad" value="<?=$date1?>" required>
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">VOUCHER #</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="ref">
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Applicant</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<?php
echo'<select class="form-control" name="app_id" required>
<option value="9999999">Others</option>
';

$applicant = mysql_query("SELECT * FROM applicant ORDER BY applicant_date_applied asc");
while($rows1=mysql_fetch_array($applicant))
{
echo'<option value="'.$rows1["applicant_id"].'">'.$rows1["applicant_last"].'   '.$rows1["applicant_first"].' -'.$rows1["applicant_id"].'</option>';
}
ECHO'</select>';
?>

</div>
</div>


<div class="form-group" STYLE="display:none">
<label class="control-label col-md-3 col-sm-3 col-xs-12">PAYEE</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<?php
echo'<select class="form-control" name="agent_id" required>
<option>4</option>
';

$usertype = mysql_query("SELECT * FROM recruitment_agent ORDER BY agent_first asc");
while($rows=mysql_fetch_array($usertype))
{
echo'<option value="'.$rows["agent_id"].'"> '.$rows["agent_first"].' '.$rows["agent_last"].'</option>';
}
ECHO'</select>';
?>

</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Requesting Party</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<?php
echo'<select class="form-control" name="staff_id" required>
<option value=0>N/A</option>
';

$usertype1 = mysql_query("SELECT * FROM user ORDER BY user_fullname asc");
while($rows1=mysql_fetch_array($usertype1))
{
echo'<option value="'.$rows1["user_id"].'">  '.$rows1["user_fullname"].'</option>';
}
ECHO'</select>';
?>

</div>
</div>






<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Type Payment</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control" name="method" required>
<option></option>
<option>Fund Transfer</option>
<option>Cash</option>
<option>Cheque</option>
<option>Credit Card</option>
<option>Direct Debit</option>
</select>
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Type Of Expenses</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<?php echo'
<select class="form-control" name="account" required>';
echo'<option>BIR Payment</option>';
echo'</select>';
?>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Currency</label>
<div class="col-md-9 col-sm-9 col-xs-12" required>
<select class="form-control" name="currency"   >
<option></option>
<option>PHP</option>
<option>USD</option>
</select>
</div>
</div>




<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="amount" step="any"  required>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12" style="color:blue">Payment to</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="payment_to"   required>
</div>
</div>

<div class="form-group" STYLE="">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Description</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="description" >
</div>
</div>



<div class="form-group">
<div class="col-md-12 col-sm-9 col-xs-12">	
<button type="submit" class="btn btn-danger pull-right"  name="add_exp">Save</button>
</div>
</div>


</form>		  



</div>

</div>

</div>
</div>
