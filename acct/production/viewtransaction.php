<?php include'inc/header.php';
include'inc/expenses.php';
include'styles.php';
?>








<div class="row">

<div class="col-md-12 col-xs-12">
<div class="x_panel">

<div class="x_content">


<h3>Accounts Transaction History</h3>
<button onclick="goBack()">Go Back</button>

<script>
function goBack() {
  window.history.back();
}
</script></p>



<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>
<th STYLE="width:1%"></th>
<th>#</th>

<th style="text-align:right">Request</th>
<th >DATE</th>
<th>SOA NO.</th>
<th>Suppier/Client</th>
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
where account NOT IN ('SALARY','Cash Advance agent','Commission')
AND sup_id=".$_GET['client']."
order by date_ad,e_id desc");


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



echo'<tr>';
echo'<td></td>';
echo'<td style="color:White">'.$row["e_id"].'</td>';




echo'<td style="font-size:12px;color:white;">
<i style="background:'.$rcolor.';padding:2px;margin:2px">'.$request.'</i>
</td>';


echo'<td>'; echo date('Y-M-d', strtotime($row['date_ad']));echo'</td>';

echo'<td>'.$row["ref"].'</td>';
echo'<td>'.$sup1["codes"].'-'.$sup1["name"].' <br><i style="color:RED;backgroun:lightgray;FONT-SIZE:10PX">'.$sup1["sup_type"].'</si></td>';
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






</div>

</div>

</div>
</div>
