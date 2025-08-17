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

<h3>Payment Expenses (Personal)</h3>
<p style="color:black">" Note: This payment to a person account <i style="color:red">Under banks & Accounts tab(Settings)</i> "</p>


<form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post">

<a href="<?=$actual_link;?>" style="color:Red;text-align:right">Refresh</a>

<button type="button" class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#myModal" style="border:BORDER 2PX SOLID black;color:black">Create SOA/Expenses</button>

</form>  


<div class="row">

<div class="col-md-12 col-xs-12">
<div class="x_panel">

<div class="x_content">



<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>
<th STYLE="width:1%"></th>
<th>Source Fund</th>
<th STYLE="">ACTION</th>
<th style="text-align:right">Request</th>
<th > Date</th>
<th >Due Date</th>
<th>SOA NO.</th>
<th>Branch</th>
<th>Suppier/Client</th>
<th>Bank (CASHOUT)</th>
<th>Bank (CASH IN)</th>
<th>Applicant</th>
<th>Type</th>
<th>Payment To</th>
<th >Account</th>
<th>PRO</th>
<th >STAFF</th>
<th >DESCRIPTION</th>
<th >CURRENCY</th>
<th>AMOUNT</th>
<th>LIQUIDATION</th>


</tr>
</thead>
<tbody>
<?php	

$app = mysql_query("SELECT * FROM email_address
where account_expense!=0 order by date_ad,e_id desc");
if(isset($_POST['Search']))
{
if($_POST['agent_id']==''){
$app = mysql_query("SELECT * FROM email_address 
where  date_ad= '".$_POST['search']."'
");
}

if($_POST['agent_id']!=''){
$app = mysql_query("SELECT * FROM email_address 
where  agent_id= '".$_POST['agent_id']."'
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



$sup = mysql_query("SELECT * FROM  suppliers where sup_id=".$row['sup_id']." ");
$sup1=mysql_fetch_array($sup);


$LIQ = mysql_query("SELECT sum(l_amount) as cashfund FROM  e_liq 
where l_status=0 AND l_e_d=".$row['e_id']."");
$cashliq=mysql_fetch_array($LIQ);
$total_liqq=$cashliq['cashfund']+$row['cash_refund'];
$total_reim=$row['amount']+$row['reimbursement'];


if($row["request_payment"]==0){$request='For Approval';$rcolor='black';}
if($row["request_payment"]==1){$request='Approved';$rcolor='blue';}
if($row["request_payment"]==2){$request='For Release';$rcolor='orange';}
if($row["request_payment"]==3){$request='Released';$rcolor='green';}
if($row["request_payment"]==4){$request='Decline';$rcolor='red';}
if($row["request_payment"]==5){$request='Return to maker';$rcolor='red';}


$bank = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$row['fund_source_id']." ");
$bank1=mysql_fetch_array($bank);
$bank2 = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$row['account_expense']." ");
$bank22=mysql_fetch_array($bank2);

$source = mysql_query("SELECT * FROM  source_payment where s_id=".$row['fund_source_id']." ");
$source1=mysql_fetch_array($source);
echo'<tr>';
echo'<td></td>';

echo'<td style="color:">'.$source1["s_name"].' </td>';
echo'<td><button type="button" class="btn btn-danger" btn-xs" style="font-size:11px">
<a href="expenses_edit.php?e_id='.$row["e_id"].'" target="_blank"  STYLE="color:white">Review</button></a>
</td>';



echo'<td style="font-size:12px;color:white;">
<i style="background:'.$rcolor.';padding:2px;margin:2px">'.$request.'</i>
</td>';


echo'<td>'; echo date('Y-M-d', strtotime($row['date_ad']));echo'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['duedate']));echo'</td>';

echo'<td>'.$row["ref"].'</td>';
echo'<td style="font-size:10px">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$sup1["codes"].'-'.$sup1["name"].' <br><i style="color:RED;backgroun:lightgray;FONT-SIZE:10PX">'.$sup1["sup_type"].'</si></td>';
echo'<td>BankName:'.$bank1["bank_name"].' <br> BankAccount:'.$bank1["bank_number"].' </td>';
echo'<td>BankName:'.$bank22["bank_name"].' <br> BankAccount:'.$bank22["bank_number"].' </td>';
echo'<td>
'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td>'.$row["method"].'</td>';
echo'<td>'.$row["account"].'</td>';
echo'<td>'.$row["payment_to"].'</td>';
echo'<td>'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td><a href="view_liq.php?e_id='.$row["e_id"].' && name='.$rowu["user_fullname"].'" target="_blank" ">'.$rowu["user_fullname"].'</a></td>';
echo'<td>'.$row["description"].'</td>';

echo'<td>'.$row["currency"].'</td>';
echo'<td ><B style="color:Red">'.$total_reim.'</B></td>';
echo'<td ><B style="color:Red">'.$total_liqq.'</B></td>';




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
order: [ 4, 'desc' ],
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

<form class="form-horizontal form-label-left input_mask" method="post" action="expenses.php">	

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12" STYLE="COLOR:RED">Status</label>
<div class="col-md-9 col-sm-9 col-xs-12">

<select class="form-control" name="request_payment" required>
<option value="">--</option></option>
<option value="0">Pending</option>
<option value="1">Approved</option>
<option value="2">For Release</option>
<option value="3">Released</option>
<option value="5">Returnt to Maker</option>
</select>

</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12" STYLE="COLOR:RED">Bank & accounts (CASH OUT)</label>
<div class="col-md-9 col-sm-9 col-xs-12">

<?php
echo'<select class="form-control" name="fund_source_id"  required>';
echo'<option value="">  --</option>';
$bank = mysql_query("SELECT * FROM bank_accounts ORDER BY bank_name asc");
while($bank1=mysql_fetch_array($bank))
{
echo'<option value="'.$bank1["bank_id"].'">  '.$bank1["bank_name"].' - Account #: '.$bank1["bank_num"].' ('.$bank1["bank_currency"].') </option>';
}
ECHO'</select>';
?>

</div>
</div>




<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12" STYLE="COLOR:blue">PAID TO </label>
<div class="col-md-9 col-sm-9 col-xs-12">

<?php
echo'<select class="form-control" name="account_expense" >';
echo'<option value="">  --</option>';
$bank = mysql_query("SELECT * FROM bank_accounts where bank_user_type=1 ORDER BY bank_name asc");
while($bank1=mysql_fetch_array($bank))
{
echo'<option value="'.$bank1["bank_id"].'">  '.$bank1["bank_name"].' - Account #: '.$bank1["bank_num"].' ('.$bank1["bank_currency"].') </option>';
}
ECHO'</select>';
?>

</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">DATE Transaction</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="date" class="form-control" name="date_ad" value="<?=$date1?>" required>
</div>
</div>






<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Mode of Payment</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control" name="method" required>
<option>Cash</option>
<option>Fund Transfer</option>

<option>Cheque</option>
<option>Others</option>
<option>Credit Card</option>
<option>Direct Debit</option>
</select>
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Account</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<?php echo'
<select class="form-control" name="account" required>
<option>'.$row["account"].'</option>';

echo'<optgroup label="Office Expense">';
$set_ = mysql_query("SELECT * FROM set_ WHERE set_type IN('OFFICE EXPENSE')ORDER BY account asc");
while($setr=mysql_fetch_array($set_))
{
echo'<option value="'.$setr["account"].'">'.$setr["account"].'</option>';
}
echo'</optgroup>';




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
<label class="control-label col-md-3 col-sm-3 col-xs-12">Particular(s)</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="description" required>
</div>
</div>


<input type="hidden" class="form-control" name="requestby" VALUE="<?=$_SESSION['user_name']?>">
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
