<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<!-- Head -->
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
<title></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../assets/styles/admin/bootstrap.min.css" rel="stylesheet" />
<link href="../assets/styles/admin/font-awesome.min.css" rel="stylesheet" />



	<script>
	function popUp(strURL,strType,strHeight,strWidth) {
var strOptions="";
if (strType=="console") strOptions="resizable,height="+strHeight+",width="+strWidth;
if (strType=="fixed") strOptions="status,height="+strHeight+",width="+strWidth;
if (strType=="elastic") strOptions="toolbar,menubar,scrollbars,resizable,location,height="+strHeight+",width="+strWidth;
window.open(strURL, 'newWin', strOptions);
}
</script>

</head>
<?php
include'db.php';
include'tracking.php';

?>


</style>
<body>

<div style="margin:0 auto;margin-top:10px;text-align:center">
<a href="index.php" style="color:blue;font-size:16px ;text-decoration: none;">My Schedule</a> | 
<a href="request.php" style="color:red;font-size:16px ;text-decoration: underline;">Payment Request</a> | 
<a href="collection-data.php" style="color:blue;font-size:16px ;text-decoration: underline;">Collection</a> | 
<a href="b_account.php"  style="color:blue;font-size:16px; text-decoration: none;">Summary Reports</a>
</div>
<div style="clear:both;height:10px"></div>
<h2 style="font-size:22px;text-align:center">Return to Maker</h2>
<div style="clear:both;height:20px"></div>
<div class="tab">
<button class="tablinks" style="background:blue;color:white"><a href="request.php" style="color:white"> For Approval Office/Agent </button></a>
<button class="tablinks" style="background:blue;color:white"><a href="requestaapproved.php" style="color:white"> Approved</button></a>
<button class="tablinks" style="background:blue;color:white"><a href="requestreleased.php" style="color:white"> Released</button></a>
<button class="tablinks" style="background:#DC143C;color:white"><a href="requestreturn.php" style="color:white"> Return to Maker </button></a>
<button class="tablinks" style="background:blue;color:white"><a href="requestdeclined.php" style="color:white"> Decline </button></a>

</div>

<div  style="margin-top:-1px;margin:10px" id="London"  class="tabcontent col-md-12 col-sm-12">

<div class="row" style="floar:left;background:white;padding:10px;min-height:150px;border:1px solid #DCDCDC;border:1px solid #DCDCDC">

<h1 style="text-align:center;color:#000080;font-size:24px">Return to Maker </h1>
<h3 style="text-align:center;font-size:16px">As of <?php echo date('d-F-Y', strtotime($date1)); ?></p></h3>
<?php
include'r/db.php';
include'r/functions.php';

?>


<form action="" method="post">

<table id="customers">
<thead>
<tr>
<td colspan="18"><input type='submit'class="btn btn-lg btn-danger" 
name='updatedata1' value='Save Data Changes' style="float:right;color: white;background:blue;padding:6px"></td>
</tr>

<tr>
<th></th>
<th >#</th>
<th >Due DATE</th>
<th>SOA_NO.</th>
<th >Supplier/Client</th>
<th >Bank_Info</th>
<th >Liquidation <small>(applicant,type)</small></th>
<th >AMOUNT</th>
<th >Type of Payment</th>
<th >Mode of Payment</th>
<th >Type of Expenses</th>
<th >Payment To</th>
<th >Requesting Party</th>
<th >Description</th>
<th>Request</th>
<th>Staus</th>
<th >Remarks</th>

</tr>
</thead>
<tbody>
<?php	

$app = mysql_query("SELECT * FROM email_address
where request_payment=5
order by date_ad,e_id asc");


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

$salary = mysql_query("SELECT sum(l_amount) as cashfund FROM  e_liq 
where l_status=0 AND l_e_d=".$row['e_id']."
AND l_type  IN ('Loan','Absences','Deduction')

");
$salary1=mysql_fetch_array($salary);
$netpay=$row['amount']-$salary1['cashfund'];

if($row["request_payment"]==0){$request='For Approval';$rcolor='black';}
if($row["request_payment"]==1){$request='Approved';$rcolor='blue';}
if($row["request_payment"]==2){$request='For Release';$rcolor='orange';}
if($row["request_payment"]==3){$request='Released';$rcolor='green';}
if($row["request_payment"]==4){$request='Decline';$rcolor='red';}
if($row["request_payment"]==5){$request='Return to maker';$rcolor='red';}



echo'<tr>';
echo'<td></td>';
echo'<td style="font-SIZE:1px">'.$row["e_id"].'';?>

<a href="https://promesa.recruitmensys.com/promesa/acct/production/request-profile.php?e_id=<?=$row['e_id']?>" 
 onclick="popUp(this.href,'console',700,1200);return false;" target="newWin" style="font-size:12px;background:red;padding:4px;color:white;border:1px solid black">Review</a>
<?php
echo'</td>';
IF($row2['duedate']=='1970-01-01' || $row2['duedate']=='0000-00-00' || $row2['duedate']=='' ) {
echo'<td>--</td>';
}
else{ 
echo'<td>'; echo date('Y-M-d', strtotime($row['duedate']));echo'</td>';
}

echo'<td>'.$row["ref"].'</td>';
echo'<td>'.$sup1["codes"].'-'.$sup1["name"].' <br><i style="color:RED;backgroun:lightgray;FONT-SIZE:10PX">'.$sup1["sup_type"].'</si></td>';
echo'<td>BankName:'.$sup1["bank_name"].' <br> BankAccount:'.$sup1["bank_number"].'</td>';



echo'<td><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b>';
//if($row["account"]!='SALARY'){
$listrrrr = mysql_query("SELECT * FROM e_liq where l_status=0
AND l_e_d=".$row['e_id']."
order by l_id desc");
while($select2222=mysql_fetch_array($listrrrr))

{
	$app1222 = mysql_query("SELECT * FROM applicant where applicant_id=".$select2222['liq_app']." ");
	$row122=mysql_fetch_array($app1222);
	echo'<p><div  style="width:210px;border:0px solid black;float:left">'.$row122["applicant_first"].' '.$row122["applicant_last"].' </div>
	<div style="width:200px;float:left;color:black;FONT-SIZE:11PX;margin-top:-5px">'.$select2222["l_type"].': '.$select2222["l_currency"].''.$select2222["l_amount"].' </div><small>('.$select2222["type_applicant"].') </small></p>
	';
	}
//}

echo'</td>';

echo'<td> <b>'.$row["currency"].''.$netpay.'</b></td>';
echo'<td>'.$row["type_payment"].'</td>';
echo'<td>'.$row["method"].'</td>';
echo'<td>'.$row["account"].'</td>';
echo'<td>'.$row["payment_to"].'</td>';
echo'<td>'.$rowu["user_fullname"].'</td>';
echo'<td>'.$row["description"].'</td>';

echo'<td style="text-align:right;font-size:10px;color:white;background:'.$rcolor.'">'.$request.'</td>';


echo "<td><input type='hidden' name='e_id[]' value='".$row['e_id']."' >
<select name='request_payment[]'>
<option value='".$row["request_payment"]."'>".$request."</option>
<option value='1'>Approved</option>
<option value='3'>Released</option>
<option value='4'>Decline</option>
<option value='5'>Return to Maker</option>
</SELECT>

</td> ";
echo "<td>
<input type='hidden' name='e_dateupdate[]' value='".$date2."' >
<input type='text' name='e_remarks[]' value='".$row['e_remarks']."' placeholder='Remarks' style='border:1px sollid gray;padding:3px'></td> ";

echo'</tr>';
}
?>
</tbody>
</table>

</form>	


<div style="clear:both; height:5px;"></div>


</div>
</div>










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




</body>
<!--  /Body -->
</html>




