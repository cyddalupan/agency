<?php include'inc/header.php';
include'inc/or.php';
include'tracking.php';
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


<button type="button" class="btn btn-danger btn-sm pull-right" data-toggle="modal" data-target="#myModal">New Transaction</button>

<h3 class="pull-left">Payables TO  <small>(FRA)</small></h3>
<div class="row">

<div class="col-md-12 col-xs-12">


<div class="x_panel">




<div class="x_content">


<?php $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>
<a href="<?=$actual_link;?>" style="color:Red;text-align:right">Refresh</a>

<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>

<th STYLE="width:2%">Code #</th>
<th STYLE="width:10%">DATE</th>

<th>Reference Number:</th>
<th>Applicant</th>
<th>FRA</th>
<th>Account</th>
<th>Description</th>
<th>Amount</th>

<th>ACTION</th>
</tr>
</thead>
<tbody>
<?php	

$app = mysql_query("SELECT * FROM email_address6
Where collect_type=1 and status_collect=1
ORDER BY tr_id desc");

if(isset($_POST['Search']))
{
$app = mysql_query("SELECT * FROM email_address6 where
or_num LIKE '%".$_POST['search']."%'
");
}


while($row=mysql_fetch_array($app))
{
//$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
//$row1=mysql_fetch_array($app1);
$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$row['app_id']." ");
$rowp=mysql_fetch_array($applicant);

$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['emp_id']." ");
$emprow=mysql_fetch_array($emp);


$bank = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$row['collect_bank_id']." ");
$bank1=mysql_fetch_array($bank);

if($row["collect_status"]==0){$request='Pending';$rcolor='black';}
if($row["collect_status"]==1){$request='Approved';$rcolor='green';}
if($row["collect_status"]==2){$request='Return to maker';$rcolor='red';}

echo'<tr>';

echo'<td style="font-size:10px">000'.$row["tr_id"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['date']));echo'</td>';
echo'<td>'.$row["or_num"].'</td>';

echo'<td>
'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td>'.$emprow["employer_name"].'</td>';
echo'<td>'.$row["account"].'</td>';
echo'<td>'.$row["particular"].'</td>';
echo'<td>'.$row["currency"].' '.$row["amount"].'</td>';

echo'<td>
<a href="collection-fra.php?trid='.$row['tr_id'].'&&apid='.$row['app_id'].'&&name='.$rowp["applicant_first"].''.$rowp["applicant_last"].'" STYLE="COLOR:BLUE" target="_blank">
<button type="button" class="btn btn-danger" btn-xs" style="font-size:11px">Review</button>
</A>
</td>';

echo'</tr>';
}
?>
</tbody>
</table>

</div>
</div>

<div class="ln_solid"></div>

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
order: [ 3, 'desc' ],
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






<script>
function showapplicant(str) {

var xhttp;    
if (str == "") {
document.getElementById("showapp").innerHTML = "";
return;
}
xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
if (this.readyState == 4 && this.status == 200) {
document.getElementById("showapp").innerHTML = this.responseText;
}
};
xhttp.open("GET", "showapp-employer.php?q="+str, true);
xhttp.send();
}

</script>




<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog">
<div class="modal-dialog">

<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal">&times;</button>
<h4 class="modal-title">New Transaction</h4>
</div>
<div class="modal-body">

<form class="form-horizontal form-label-left input_mask" method="post" action="">	
<input type="hidden" class="form-control" name="user" value="<?=$_SESSION['user_fullname']?>" required>
<input type="hidden" class="form-control" name="status_collect" value="1" required>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Date of Transaction</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="date" class="form-control" name="date" value="<?=$date1?>" required>
</div>
</div>


<?php
$old_date = date('l, F d y h:i:s');           // works
$old_date_timestamp = strtotime($old_date);
?>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Reference Number:</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="or_num" value="<?php echo $old_date_timestamp; ?>" readonly>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">FRA</label>
<div class="col-md-9 col-sm-9 col-xs-12">

<?php
echo'<select class="form-control" name="emp_id" onchange="showapplicant(this.value)" required>';
echo'<option value="">  --</option>';
$emp1 = mysql_query("SELECT * FROM employer ORDER BY employer_name asc");
while($emp111=mysql_fetch_array($emp1))
{
echo'<option value="'.$emp111["employer_id"].'">  '.$emp111["employer_name"].'</option>';
}
ECHO'</select>';
?>

</div>
</div>



<div class="form-group" STYLE="">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Payment from: Applicant</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<?php
echo'<select class="form-control" name="app_id"  id="showapp" >';

//$applicant = mysql_query("SELECT * FROM applicant ORDER BY applicant_date_applied asc");
//while($rows1=mysql_fetch_array($applicant))
//{
//echo'<option value="'.$rows1["applicant_id"].'">  '.$rows1["applicant_last"].' '.$rows1["applicant_first"].'-'.$rows1["applicant_id"].'</option>';
//}
ECHO'</select>';
?>
</div>
</div>






<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="amount" step="any" required>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">CURRENCY</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control" required  name="currency" required>
<option VALUE=""></option>
<option>USD</option>
<option>PHP</option>

</select>
</div>
</div>



















 








<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Description</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<textarea name="particular" class="col-md-9 col-sm-9 col-xs-12"></textarea>	
</div>
</div>


<input type="hidden" class="form-control" name="collect_type"  value="1">
<input type="hidden" class="form-control" name="account"  value="others">

<div class="form-group">
<div class="col-md-12 col-sm-9 col-xs-12">	
<button type="submit" class="btn btn-danger pull-right"  name="add_applicants">Save</button>
</div>
</div>


</form>		  



</div>

</div>

</div>
</div>
