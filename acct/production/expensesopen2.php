<?php include'inc/header.php';
include'inc/expenses.php';
include'styles.php';
include'tracking.php';
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.8.1/css/bootstrap-select.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.8.1/js/bootstrap-select.js"></script>


<style>
input[type="checkbox"] {
   width: 20px;
            height: 20px;
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

<h3>Pending Request</h3>





<form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post"> 
<div class="form-group">
<div class="col-md-3 col-sm-4 col-xs-4 form-group">
<input type="date" class="form-control" name="date_ad"  value="<?=$date2;?>">

</div>

<div class="col-md-3 col-sm-4 col-xs-4 form-group">


<?php echo'
<select class="form-control" name="currency" required>
<option>PHP</option>
<option>USD</option>';
echo'</select>';
?>

</div>

<div class="col-md-6 col-sm-12 col-xs-12 form-group">
<button type="submit" class="btn btn-primary"  name="searchme" >Search</button>
</div>
</div>
</form> 







<form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post" action="multiple/index.php?me=1">
<button type="submit" class="btn btn-info btn-sm pull-right"  style="border:BORDER 2PX SOLID white;color:black">Multiple REQUEST</button>
 
</form> 

<div class="row">

<div class="col-md-12 col-xs-12">
<div class="x_panel">

<div class="x_content">



<form action="" method="post">
<?php
if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){
?>
 <select name="mystatus" style="padding:5px" required>
     <option value="">-Select-</option>
     <option value="0">For Approval</option>
     <option value="1">Approved</option>
      <option value="3">Released</option>
      <option value="4">Cancelled</option>
 </select>
 
<a href="delete_methode_link" onclick="return confirm('Are you sure you want to Save?');">		
<button type="submit" class="btn btn-warning btn-sm"  name="updateapproval" style="border:BORDER 2PX SOLID black;color:white">Save "Approved"</button>
</a>

<?PHP } ?>   	

<style>
#example-checkbox table td,th{
font-size:11px;	
}
</style>

<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>
<th STYLE="width:1%"></th>
<th>#</th>


<th STYLE="">ACTION</th>
<th > DATE </th>
<th>REF NO.</th>
<th></th>
<th > Branch</th>
<th style="text-align:right">Request</th>
<th > Date</th>
<th >Currency</th>
<th>Amount</th>
<th>#Request</th>
<th>Agent</th>
<th>Applicant</th>
<th >Account</th>
<th >Description</th>
<th >Charge</th>

</tr>
</thead>
<tbody>
<?php	


if(isset($_POST['updateapproval'])){
foreach ($_POST['delall'] as $delall) {
$app = mysql_query("UPDATE email_address
SET  request_payment =".$_POST['mystatus']." 

where e_id= ".$delall)

or die ("cannot Update data");
}
echo"<p style='color:white;BACKGROUND:#DC143C;padding:10px;FONT-SIZE:14PX;WIDTH:400px'>Succesfully UPDATE..</p>";
}


$app = mysql_query("SELECT email_address.*
,applicant.applicant_first
,applicant.applicant_middle
,applicant.applicant_last
FROM email_address
LEFT JOIN  	applicant
ON email_address.app_id = applicant.applicant_id

	
where  account_expense=0
AND request_payment=1

");


if(isset($_POST['searchme']))
{
$app = mysql_query("SELECT email_address.*
,applicant.applicant_first
,applicant.applicant_middle
,applicant.applicant_last
,applicant.applicant_id
FROM email_address


LEFT JOIN  	applicant
ON email_address.app_id = applicant.applicant_id

where account_expense=0
and request_payment=1
and email_address.currency='".$_POST['currency']."'
and date_ad='".$_POST['date_ad']."'
order by date_ad,e_id desc");
}

while($row=mysql_fetch_array($app))
{

$countmein = mysql_query("SELECT count(app_id) as alls,email_address.* FROM email_address
where  account='".$row['account']."'
AND app_id=".$row['app_id']."
AND request_payment!=4
AND app_id!=0");
$countmein1 = mysql_fetch_array($countmein);

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
$total_reim=$row['amount'];


if($row["request_payment"]==0){$request='Pending';$rcolor='black';}
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



$expense = mysql_query("SELECT SUM(amount) as amount_total ,email_address.* FROM email_address
WHERE account='".$row["account"]."' and currency='PHP' AND request_paymenT IN (3)");
$expense1=mysql_fetch_array($expense);


if	($expense1["amount_total"]==0) {$expnes88="";}
if	($expense1["amount_total"]!=0) {$expnes88=$expense1["amount_total"];}

if($row['branch_type']==0){$branchme="N/A";}
if($row['branch_type']==1){$branchme=" MAIN OFFICE";}
if($row['branch_type']==2){$branchme="Aklan BRANCH";}
if($row['branch_type']==3){$branchme="Calapan BRANCH";}
if($row['branch_type']==4){$branchme="Cagayan BRANCH";}
if($row['branch_type']==5){$branchme="Davao BRANCH";}
if($row['branch_type']==6){$branchme="Leyte BRANCH";}
if($row['branch_type']==7){$branchme="Lucena BRANCH ";}
if($row['branch_type']==8){$branchme="Pangasinan BRANCH";}
if($row['branch_type']==9){$branchme="Enrile BRANCH";}



if($row['tm1']==1){$approved1="Approved Officer";}
if($row['tm2']==1){$approved2="Superadmin";}


if($row['tm2']==2){$approved2="Declined";}
if($row['tm1']==2){$approved1="Declined";}
if($row['tm1']==0){$approved1="Not Approve";}
if($row['tm1']==0){$approved2="";}	

if($row["currency"]=="USD"){$mypeso=$row['amount']*50;}
if($row["currency"]=="PHP"){$mypeso=$row['amount'];}

if($countmein1["alls"]==1){$bgcolor="";}
if($countmein1["alls"]==2){$bgcolor="#FFFF00";}
if($countmein1["alls"]==3){$bgcolor="#FFFF00";}
if($countmein1["alls"]==4){$bgcolor="#FFFF00";}
if($countmein1["alls"]==5){$bgcolor="#FFFF00";}
if($countmein1["alls"]==6){$bgcolor="#FFFF00";}
if($countmein1["alls"]==7){$bgcolor="#FFFF00";}

echo'<tr style="background:'.$bgcolor.'">';
echo'<td><input type="checkbox" name="delall[]" value="'.$row['e_id'].'" ></td>';
echo'<td>'.$row["e_id"].'</td>';

echo'<td><a href="expenses_edit.php?e_id='.$row["e_id"].'" target="_blank"  STYLE="color:white;background:Red;padding:4px">Review</a>
</td>';

IF($row['date_ad']=='1970-01-01' || $row['date_ad']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo"<td>".$row['date_ad']."</td>"; }
echo'<td>#'.$row["ref"].'</td>';
echo'<td><p style="border:1px solid lightgray;padding:2px">'.$approved1.'<p>
<p style="border:1px solid lightblue;padding:2px">'.$approved2.'<p></td>';

echo'<td>'.$branchme.'</td>';

echo'<td style="font-size:12px;color:white;">
<i style="background:'.$rcolor.';padding:2px;margin:2px">'.$request.'</i>
</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['date_ad']));echo'</td>';
echo'<td>'.$row["currency"].'</td>';
echo'<td  style="color:"> '.number_format($total_reim, 2).'</td>';
echo'<td ><a href="soacopy.php?appid='.$row['app_id'].'"  target="_blank" style="color:black;font-size:16px;font-weight:bold">
'.$countmein1["alls"].'</a></td>';


echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';

echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$row["applicant_first"].' '.$row["applicant_last"].'</td>';

echo'<td>'.$row["account"].'</td>';

echo'<td>'.$row["description"].'</td>';
echo'<td>'.$row["chargeto"].'</td>';

echo'</tr>';

$totalme=$total_reim+$totalme;
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
order: [ 11, 'desc' ],
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
<label class="control-label col-md-3 col-sm-3 col-xs-12" STYLE="COLOR:RED">Status</label>
<div class="col-md-9 col-sm-9 col-xs-12">

<select class="form-control" name="request_payment" required>
<option value="0">Pending</option>
</select>
</div>
</div>



<?php
if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){
$mydisable='';
}
else{
 $mydisable='';   
}
?>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">DATE Transaction</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="date" class="form-control" name="date_ad" value="<?=$date1?>" required <?=$mydisable?>>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">REF #</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="TEXT" class="form-control" name="ref"   >
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Due DATE </label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="date" class="form-control" name="duedate" value="" >
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Charge To</label>
<div class="col-md-9 col-sm-9 col-xs-12" required>
<select class="form-control" name="chargeto"   >
<option value="">--</option>
<option>Agent</option>
<option>Office</option>
</select>
</div>
</div>






<div class="form-group" STYLE="">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Agent</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<?php
echo'<select class="form-control" name="agent_id" required>

<option vaue="0">N/A</option>
';

$usertype = mysql_query("SELECT * FROM recruitment_agent   ORDER BY agent_first asc");
while($rows=mysql_fetch_array($usertype))
{
echo'<option value="'.$rows["agent_id"].'"> '.$rows["agent_first"].' '.$rows["agent_last"].'</option>';
}
ECHO'</select>';
?>
</div>
</div>



<script>
function showprice(str) {

var xhttp;    
if (str == "") {
document.getElementById("showamount").innerHTML = "";
return;
}
xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
if (this.readyState == 4 && this.status == 200) {
document.getElementById("showamount").innerHTML = this.responseText;
}
};
xhttp.open("GET", "showamount.php?q="+str, true);
xhttp.send();
}

</script>





<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Account</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<?php echo'
<select class="form-control" name="account" required  >
<option VALUE=""></option>';

$set_ = mysql_query("SELECT * FROM set_   ORDER BY account desc");
while($setr=mysql_fetch_array($set_))
{
echo'<option value="'.$setr["account"].'">'.$setr["account"].'   - '.$setr["a_amount"].'</option>';
}


echo'</select>';
?>
</div>
</div>





<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>
<div class="col-md-9 col-sm-9 col-xs-12" required >

<input type="number" class="form-control" name="amount"  step="any">
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Currency</label>
<div class="col-md-9 col-sm-9 col-xs-12" required>
<select class="form-control" name="currency"   >
<option>PHP</option>
<option>USD</option>
</select>
</div>
</div>






<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Particular(s)</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="description" required>
</div>
</div>



<div class="form-group" STYLE="">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Applciant</label>
<div class="col-md-9 col-sm-9 col-xs-12">

<select class="selectpicker" single data-live-search="true" name="app_id" required>
<?php
echo'<option></option>
<option value="0"> OTHERS</option>
';

$applicant = mysql_query("SELECT * FROM applicant 
LEFT JOIN  	recruitment_agent
ON applicant.applicant_source =recruitment_agent.agent_id
ORDER BY applicant_last asc");
while($rows12222=mysql_fetch_array($applicant))
{
echo'<option value="'.$rows12222["applicant_id"].'">'.$rows12222["applicant_last"].'  '.$rows12222["applicant_middle"].'  '.$rows12222["applicant_first"].' '.$rows12222["applicant_first"].'</option>';
}
?>
</select>

</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Branch</label>
<div class="col-md-9 col-sm-9 col-xs-12" required>
<select class="form-control" name="branch_type" >
<option></option>
<option VALUE="7">URDANETA</option>
<option VALUE="14">MAIN  MANILA  BRANCH</option>

</select>
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Country</label>
<div class="col-md-9 col-sm-9 col-xs-12" >
<select class="form-control" name="country" >
<option></option>
<option VALUE="0">N/A</option>
<option VALUE="5">KUW</option>
<option VALUE="7">KSA</option>
<option VALUE="11">QAT</option>
<option VALUE="12">DUBAI</option>
</select>
</div>
</div>


<input type="hidden" class="form-control" name="requestby" VALUE="<?=$_SESSION['admin']['user']['user_name']?>">
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
