<?php include'inc/header.php';
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






<div class="row">



<div class="col-md-12 col-xs-12">


<div class="x_panel">


<div class="x_content" style="margin-top:-20px;border:none">




</div>
<h4>Branch</h4>
<table  cellspacing="0" id="example-checkbox" style="border:1px solid black">
<thead>
	<tr>

	<th STYLE="width:2%"></th>
		<th STYLE="width:2%"></th>
	<th  STYLE="width:20%">PRO</th>
	<th style="font-weight:bold">Agreement</th>
	<th>APPLICANTS</th>
	<th >AVAILABLE</th>
	<th>ON PROCESS</th>
	<th>CANCELLED/UNFIT</th>
	<th >REPAT</th>
	<th >DEPLOYED</th>
	<th >DEBIT</th>
	<th >CREDIT</th>
	<th >BALANCE</th>
	</tr>
	</thead>
	<tbody>
<?php	
include'inc/functions.php';
$app = mysql_query("SELECT * FROM recruitment_agent where agent_type='Branch'
order by agent_first asc");

if(isset($_POST['Search']))
{
$app = mysql_query("SELECT * FROM recruitment_agent where
agent_first LIKE '%".$_POST['agent_name']."%' OR agent_last LIKE '%".$_POST['agent_name']."%'
OR agent_id LIKE '%".$_POST['agent_name']."%'
");
}


while($row=mysql_fetch_array($app))
{

// DEBIT    
$old = mysql_query("SELECT SUM(receivable) as totalamount,  email_address4.* FROM email_address4 
where  status_me=1 AND agent_id=".$row["agent_id"]." ");    
$old1=mysql_fetch_array($old);   

$release = mysql_query("SELECT SUM(amount) as totalamount,  email_address.* FROM email_address where  agent_id=".$row['agent_id']."
AND request_payment=3
AND account='Commission'
order by date_ad desc");
$release1=mysql_fetch_array($release);



$deduction88 = mysql_query("SELECT SUM(deduction_amount) as totalamount,  deduction.* FROM deduction
where agent_id=".$row['agent_id']."
ORDER BY deduction_id desc");
$deduction881=mysql_fetch_array($deduction88); 

$cashadvance = mysql_query("SELECT SUM(amount) as totalamount,  email_address.* FROM email_address
where account  IN ('Cash Advance agent')
AND agent_id=".$row['agent_id']."
order by date_ad,e_id asc");
$cashadvance1=mysql_fetch_array($cashadvance);  


$debitpayable = mysql_query("SELECT SUM(receivable) as totalamount,  email_address4.* FROM email_address4 where  agent_id=".$row['agent_id']."
 AND status_me=0
order by date_transaction desc");
$debitpayable1=mysql_fetch_array($debitpayable); 



$totaldebit=$debitpayable1['totalamount']+$cashadvance1['totalamount']+$release1['totalamount']+$old1['totalamount']+$deduction881['totalamount'];



//CREDIT
$applicantftw = mysql_query("SELECT SUM(agent_ftw+agent_contract+agent_deployed) as totalamount,  applicant.* FROM applicant
WHERE applicant_source=".$row['agent_id']." AND ( agent_ftw!=0 OR agent_contract!=0 OR agent_deployed!=0)");
$applicantftw1=mysql_fetch_array($applicantftw); 

$colec = mysql_query("SELECT UM(amount) as totalamount,  email_address6.* FROM email_address6
where agent_id=".$row['agent_id']."
ORDER BY tr_id desc");
$colec1=mysql_fetch_array($colec); 


$totalcredit=$colec1['totalamount']+$applicantftw1['totalamount'];
    
    
    
    
    
$app1 = mysql_query("SELECT SUM(amount) as USD ,agent_id,currency FROM email_address
WHERE agent_id=".$row["agent_id"]." AND currency='USD'");
$usd=mysql_fetch_array($app1);

$app2 = mysql_query("SELECT SUM(amount) as PHP2 ,agent_id,currency FROM email_address
WHERE agent_id=".$row["agent_id"]." AND currency='PHP'");
$php=mysql_fetch_array($app2);


$unfit = mysql_query("SELECT SUM(receivable) as receive ,agent_id,receivable FROM email_address4
WHERE agent_id='".$row["agent_id"]."'  AND currency='PHP' and type_status='charge'");
$unfitrow=mysql_fetch_array($unfit);

$CON = mysql_query("SELECT SUM(receivable) as receiveusd ,agent_id,receivable FROM email_address4
WHERE agent_id='".$row["agent_id"]."'  AND  currency='USD' and type_status='charge'");
$contract=mysql_fetch_array($CON);

$col = mysql_query("SELECT SUM(amount) as amountusd ,agent_id,amount FROM email_address6
WHERE agent_id='".$row["agent_id"]."'  AND  currency='USD' ");
$colusd=mysql_fetch_array($col);


$colS = mysql_query("SELECT SUM(amount) as amounPHP ,agent_id,amount FROM email_address6
WHERE agent_id='".$row["agent_id"]."'  AND  currency='PHP' ");
$colphp=mysql_fetch_array($colS);

$app11 = mysql_query("SELECT  SUM(receivable) as tot FROM email_address4 where  agent_id=".$row['agent_id']."
AND type_status='Agent'
AND status_paid=0");
$totalcharge=mysql_fetch_array($app11);


$result1 = mysql_query("SELECT count(applicant_source) as alls FROM  applicant where applicant_source='".$row['agent_id']."'");
$all = mysql_fetch_array($result1);


$onprocess = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$row['agent_id']."' 
AND applicant_status IN (8,12,4,3,2,5)
");
$onprocess1 = mysql_fetch_array($onprocess);

$deployed = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$row['agent_id']."' 
AND applicant_status IN (9)
");
$deployed1 = mysql_fetch_array($deployed);


$repat = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$row['agent_id']."' 
AND applicant_status IN (22)
");
$repat1 = mysql_fetch_array($repat);


$pass = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$row['agent_id']."' 
AND applicant_status IN (15,0,10)
");
$pass1 = mysql_fetch_array($pass);


$not = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$row['agent_id']."' 
AND applicant_status IN (1,14,21,7)
");
$not1 = mysql_fetch_array($not);




$agree = mysql_query("SELECT count(agent_id) as totalamount,  agent_agreement.* FROM agent_agreement
where agent_id=".$row['agent_id']."");
$agree1=mysql_fetch_array($agree);


echo'<tr>';
echo'<td></td>';
if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){
echo'<td>
<a href="profile1.php?agent='.$row["agent_id"].'" target="_blank">
<button type="button" class="btn btn-danger btn-xs" style="font-size:11px;margin:3px">Debit/Credit</button></a>
<a href="agent_agreement.php?agent='.$row["agent_id"].'" target="_blank">
<button type="button" class="btn btn-default btn-xs" style="font-size:11px;margin:3px">Add Agreement</button></a>
</td>';
}
Else{
echo'<td>
<a href="profile1.php?agent='.$row["agent_id"].'" target="_blank">
<button type="button" class="btn btn-danger btn-xs" style="font-size:11px;margin:3px">Debit/Credit</button></a>
</td>';	
}


echo'<td> '.$row["agent_first"].' '.$row["agent_last"].'</td>';

if($agree1['totalamount']!=0){ echo'<td style="color:blue">'.$agree1["totalamount"].'</td>'; }
if($agree1['totalamount']==0){ echo'<td style="color:blue">--</td>'; }

echo'<td style="color:black;font-weight:bold;background:">'.$all["alls"].'</td>';
echo'<td style="color:blue">'.$pass1["countme"].'</td>';
echo'<td style="color:blue">'.$onprocess1["countme"].'</td>';
echo'<td style="color:red">'.$not1["countme"].'</td>';
echo'<td style="color:red">'.$repat1["countme"].'</td>';

//mybalanbce
$mybalance=$totaldebit-$totalcredit;


echo'<td style="color:green">'.$deployed1["countme"].'</td>';
echo'<td style="color:black">  '.number_format($totaldebit,2).'</td>';
echo'<td style="color:black">  '.number_format($totalcredit,2).'</td>';
echo'<td style="color:black">  '.number_format($mybalance,2).'</td>';


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




