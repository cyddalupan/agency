<?php include'inc/header.php';
include'inc/expenses.php';
include'styles.php';
include'tracking.php';
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

<h3>Office Expenses</h3>



<form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post">

<a href="<?=$actual_link;?>" style="color:Red;text-align:right">Refresh</a>

<button type="button" class="btn btn-danger btn-sm pull-right" data-toggle="modal" data-target="#myModal" style="border:BORDER 2PX SOLID black;color:WHITE">Create SOA</button>

</form>  


<div class="row">

<div class="col-md-12 col-xs-12">
<div class="x_panel">

<div class="x_content">

<p style="color:black">" Note: THIS PAGE <i style="color:red">DEBIT</i> to <i style="color:red">Supplier</i> "</p>




<form action="" method="post">
  	
<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>
<th STYLE="width:1%"></th>
<th>#</th>
<th>Encoded</th>
<th STYLE="">ACTION</th>
<th style="text-align:right">Request</th>
<th > Date</th>
<th >CURRENCY</th>
<th>AMOUNT</th>
<th>LIQUIDATION</th>
<th>Suppier/Client</th>

<th>Type</th>
<th >Account</th>
<th >DESCRIPTION</th>
<th >Due Date</th>
<th>SOA NO.</th>
<th >Liquidation</th>


</tr>
</thead>
<tbody>
<?php	


if(isset($_POST['updateapproval'])){
foreach ($_POST['delall'] as $delall) {
$app11 = mysql_query("UPDATE email_address
SET  request_payment =".$_POST['mystatus']." where e_id= ".$delall)

or die ("cannot Update data");
}
echo"<p style='color:white;BACKGROUND:#DC143C;padding:10px;FONT-SIZE:14PX;WIDTH:400px'>Succesfully UPDATE..</p>";
}


$app = mysql_query("SELECT * FROM email_address
where account NOT IN ('Cash Advance agent','Commission')
AND account_expense=0
and request_payment=999
order by date_ad,e_id desc");



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
if($row["request_payment"]==999){$request='SOA';$rcolor='red';}


$bank = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$row['fund_source_id']." ");
$bank1=mysql_fetch_array($bank);
$bank2 = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$row['account_expense']." ");
$bank22=mysql_fetch_array($bank2);

$source = mysql_query("SELECT * FROM  source_payment where s_id=".$row['fund_source_id']." ");
$source1=mysql_fetch_array($source);



$expense = mysql_query("SELECT SUM(amount) as amount_total ,email_address.* FROM email_address
WHERE account='".$row["account"]."' and currency='PHP' AND request_paymenT IN (3)");
$expense1=mysql_fetch_array($expense);


if	($expense1["amount_total"]==0) {$expnes88="";}
if	($expense1["amount_total"]!=0) {$expnes88=$expense1["amount_total"];}	


echo'<tr>';
echo'<td><input type="checkbox" name="delall[]" value="'.$row['e_id'].'" ></td>';
echo'<td>000'.$row["e_id"].'</td>';
echo'<td style="color:">'.$row["requestby"].' </td>';
echo'<td><button type="button" class="btn btn-danger" btn-xs" style="font-size:11px">
<a href="expenses-soa.php?e_id='.$row["e_id"].'" target="_blank"  STYLE="color:white">Review</button></a>
</td>';



echo'<td style="font-size:12px;color:white;">
<i style="background:'.$rcolor.';padding:2px;margin:2px">'.$request.'</i>
</td>';


echo'<td>'; echo date('Y-M-d', strtotime($row['date_ad']));echo'</td>';


echo'<td>'.$row["currency"].'</td>';
echo'<td  style="color:red"> '.number_format($total_reim, 2).'</td>';
echo'<td  style="color:black"> '.number_format($total_liqq, 2).'</td>';
echo'<td>'.$sup1["codes"].'-'.$sup1["name"].' <br><i style="color:RED;backgroun:lightgray;FONT-SIZE:10PX">'.$sup1["sup_type"].'</si></td>';


echo'<td>'.$row["method"].'</td>';
echo'<td>'.$row["account"].'</td>';

echo'<td>'.$row["description"].'</td>';

echo'<td>'; echo date('Y-M-d', strtotime($row['duedate']));echo'</td>';
echo'<td>'.$row["ref"].'</td>';
echo'<td>';
echo'<style>
input[value="1'.$row["e_id"].'"]:checked ~ div[id="1'.$row["e_id"].'"]{
display:none;
}
</style>';

echo'<input type="checkbox" name="hider" value="1'.$row["e_id"].'" checked> <u style="color:black">Show </u>
<div id="1'.$row["e_id"].'">';


$listrrrr = mysql_query("SELECT * FROM e_liq where l_status=0
AND l_e_d=".$row['e_id']."
order by l_id desc");
while($select2222=mysql_fetch_array($listrrrr))

{
$app1222 = mysql_query("SELECT * FROM applicant where applicant_id=".$select2222['liq_app']." ");
$row122=mysql_fetch_array($app1222);
echo'<p><div  style="width:210px;border:0px solid black;float:left" id="1'.$row["e_id"].'">'.$row122["applicant_first"].' '.$row122["applicant_last"].' </div>
<div style="width:100px;float:left;color:red;FONT-SIZE:11PX" id="1'.$row["e_id"].'">'.$select2222["l_currency"].''.$select2222["l_amount"].'  <small id="1'.$row["e_id"].'">('.$select2222["type_applicant"].') </small></p></div>
';
}
echo'<div class="clearfix"></div>';
echo'</div>';
echo'</td>';




echo'</tr>';
}
?>
</tbody>
</table>





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
<h4 class="modal-title">New  Transaction</h4>
</div>
<div class="modal-body">

<form class="form-horizontal form-label-left input_mask" method="post" action="">	


<input type="HIDDEN" class="form-control" name="request_payment" value="999" required <?=$mydisable?>>
<input type="HIDDEN" class="form-control" name="account" value="SOA" required <?=$mydisable?>>
<?php
if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){
$mydisable='';
}
else{
 $mydisable='readonly';   
}
?>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">SOA DATE Received</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="date" class="form-control" name="date_ad" value="<?=$date1?>" required <?=$mydisable?>>
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Due DATE </label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="date" class="form-control" name="duedate" value="" >
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12"  style="color:red">SOA #</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="ref">
</div>
</div>






<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12" style="color:red">Cient / Suppliers</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<?php
echo'<select class="form-control" name="sup_id" required>

<option value="">--</option>';

$usertype = mysql_query("SELECT * FROM  suppliers where sup_type='Clients' ORDER BY name asc");
echo'<optgroup label="Clients">';
while($rows=mysql_fetch_array($usertype))
{
echo'<option value="'.$rows["sup_id"].'"> '.$rows["name"].'</option>';
}
echo'</optgroup>';

$sup = mysql_query("SELECT * FROM  suppliers where sup_type='Suppliers' ORDER BY name asc");
echo'<optgroup label="Suppliers">';
while($sup1=mysql_fetch_array($sup))
{
echo'<option value="'.$sup1["sup_id"].'"> '.$sup1["name"].'</option>';
}
echo'</optgroup>';

ECHO'</select>';
?>

</div>
</div>




<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Currency</label>
<div class="col-md-9 col-sm-9 col-xs-12" required>
<select class="form-control" name="currency"   >
<option>PHP</option>
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
