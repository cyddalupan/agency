<?php include'inc/header.php';
include'inc/functions.php';
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
<h2>John Doe</h2>
</div>
</div>
<!-- /menu profile quick info -->

<br />

<!-- sidebar menu -->
<div id="sidebar-menu" class="main_menu_side hidden-print main_menu">

<div class="menu_section">
<h3>Live On</h3>

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

<h2 STYLE="color:black;font-weight:bold">Liquidation Expenses Report</h2>


<div class="x_content">



<form action="soa3.php" method="post" target="_blank" >

<div class="form-group" >

<label class="control-label col-md-3 col-sm-3 col-xs-12">Start Date</label>
<div class="col-md-4 col-sm-4 col-xs-12">
<input type="date" class="form-control" name="start" ></div>
</div>
<div class="clearfix"></div>
<div class="clearfix"></div>

<div class="form-group" >
<label class="control-label col-md-3 col-sm-3 col-xs-12">End Date</label>
<div class="col-md-4 col-sm-4 col-xs-12">
<input type="date" class="form-control" name="end" ></div>
</div>

<div class="clearfix"></div>
<div class="clearfix"></div>


<div class="clearfix"></div>
<div class="form-group" >
<label class="control-label col-md-3 col-sm-3 col-xs-12">Account</label>
<div class="col-md-4 col-sm-4 col-xs-12 form-group has-feedback">
<select class="form-control" required name="account">
<option value="">-Select Type-</option>
<option value="1">Agent/Source Commission</option>
</select>
</div>
</div>


<div class="clearfix"></div>
<div class="form-group" >
<label class="control-label col-md-3 col-sm-3 col-xs-12">Category</label>
<div class="col-md-4 col-sm-4 col-xs-12 form-group has-feedback">
<select class="form-control" required name="type_applicant">
<option value="0">ALL</option>
<option value="1">PPT</option>
<option value="2">FTW</option>
<option value="3">CS</option>
<option value="4">DEPLOYED</option>
<option value="5">BONUS</option>
</select>
</div>
</div>


<div class="clearfix"></div>



<div class="form-group" >
<label class="control-label col-md-3 col-sm-3 col-xs-12">Account</label>
<div class="col-md-4 col-sm-4 col-xs-12 form-group has-feedback">
<select class="form-control" required name="currency">
<option value="">-SELECT Currency-</option>
<option>USD</option>
<option>PHP</option>
</select>

</div>


  




<div class="clearfix"></div>




<div class="clearfix"></div>
<div class="form-group" >
<label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
<div class="col-md-4 col-sm-4 col-xs-12 form-group has-feedback">
<select class="form-control" required name="request" >
<option value="0">For Approval</option>
<option value="1">Approved</option>
<option value="2">For Release</option>
<option value="3">Released</option>
</select>
</div>
</div>



<div class="clearfix"></div></br>

<div class="col-md-7 col-sm-7 col-xs-12">
<input type="submit" name="submitsearch" value="Search" style="width:80px;float:right" >
</div>
</form>
</div>
</div>


<DIV style="clear:both;height:20px"></DIV>
<h2 STYLE="color:black;font-weight:bold">Projected Income  Report</h2>

<style>
#example-checkbox td,th{
font-size:14px;	
text-align:center;
color:black;
}
</style>



<table  cellspacing="0" id="example-checkbox" style="border:1px solid black">
<thead>
<tr>
<th  STYLE="width:5%">View DEBIT/CREDIT</th>
<th STYLE="width:2%">Code #</th>
<th >Principal</th>
<th >Country</th>
<th >Visa Stamp</th>
<th >Contract Received</th>
<th  style="display:none">No Contract</th>
<th  style="display:none">Available</th>
<th >Deployed</th>
<th  style="display:none">Selected</th>
<th style="display:none">Total</th>
<th>%</th>


<th>BALANCE</th>

</tr>
</thead>
<tbody>
<?php	

$app = mysql_query("SELECT * FROM employer
LEFT JOIN country
ON employer.employer_country = country.country_id

order by employer_name desc");



if(isset($_POST['Search']))
{
$app = mysql_query("SELECT * FROM employer
where
employer_name LIKE '%".$_POST['search']."%' limit 20
");
}


while($row=mysql_fetch_array($app))
{

$aff = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed+fra_sent+fra_before) as singils ,applicant.* FROM applicant
WHERE applicant_employer=".$row["employer_id"]."

");
$rowre=mysql_fetch_array($aff);	

$app1 = mysql_query("SELECT SUM(amount) as USD ,emp_id,currency FROM email_address
WHERE emp_id=".$row["employer_id"]." AND currency='USD'");
$usd=mysql_fetch_array($app1);


$unfit = mysql_query("SELECT SUM(receivable) as receive ,emp_id,receivable FROM email_address4
WHERE emp_id='".$row["employer_id"]."'  AND currency='PHP'");
$unfitrow=mysql_fetch_array($unfit);

$CON = mysql_query("SELECT SUM(receivable) as receiveusd ,emp_id,receivable FROM email_address4
WHERE emp_id='".$row["employer_id"]."'  AND  currency='USD' ");
$contract=mysql_fetch_array($CON);

//$result1 = mysql_query("SELECT count(emp_id) as alls FROM  email_address2 where emp_id='".$row['employer_id']."'");
//$all = mysql_fetch_array($result1);


$col = mysql_query("SELECT SUM(amount) as amountusd ,emp_id,amount FROM email_address6
WHERE emp_id='".$row["employer_id"]."'  AND  currency='USD' and status_collect=0");
$colusd=mysql_fetch_array($col);


$deduction = mysql_query("SELECT SUM(amount) as amountusd ,emp_id,amount FROM email_address6
WHERE emp_id='".$row["employer_id"]."'  AND  currency='USD' and status_collect=1");
$deduction1=mysql_fetch_array($deduction);

$oldcollect = mysql_query("SELECT SUM(amount) as amountusd ,emp_id,amount FROM email_address6
WHERE emp_id='".$row["employer_id"]."'  AND  currency='USD' and status_collect=2");
$oldcollect1=mysql_fetch_array($oldcollect);


$expnese88= mysql_query("SELECT SUM(amount) as totalamount ,email_address.* FROM email_address
WHERE emp_id='".$row["employer_id"]."'  AND  currency='PHP' AND request_payment=3");
$expnese881=mysql_fetch_array($expnese88);




$oldme8 = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed+fra_sent+fra_before) AS amount,applicant.* FROM applicant
WHERE  applicant_employer=".$row['employer_id']." AND   applicant_status NOT IN (25,1,21,14,22)   ");
$oldme81=mysql_fetch_array($oldme8);


//$colS = mysql_query("SELECT SUM(amount) as amounPHP ,emp_id,amount FROM email_address6
//WHERE emp_id='".$row["employer_id"]."'  AND  currency='PHP' ");
//$colphp=mysql_fetch_array($colS);

$backout = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed+fra_sent+fra_before) AS amount,applicant.*  FROM applicant
WHERE applicant_status IN (25,1,21,14,22) AND applicant_employer=".$row['employer_id']." ");
$backout1=mysql_fetch_array($backout);


$repatresult = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed+fra_sent+fra_before) AS amount,applicant.*,deployed.* FROM applicant
LEFT JOIN deployed
ON applicant.applicant_id = deployed.deployed_applicant
WHERE applicant_status  IN (22) AND applicant_employer=".$_GET['emp_id']."
AND deployed_date between date_sub(now(), interval 91 day)  AND date_add(now(), interval 1 day)");
$repatresult1=mysql_fetch_array($repatresult);

$chargejayven=$expnese881["totalamount"]/50;
$singil=$chargejayven+$oldcollect1["amountusd"]+$oldme81["amount"]+$repatresult1["amount"]+$backout1["amount"];
$alldeduct=$colusd["amountusd"]+$deduction1["amountusd"];
$balancesheet=$singil-$alldeduct;





$deployed1 =
mysql_query("SELECT count(DISTINCT deployed.deployed_applicant) as alls,
applicant.applicant_id as adonis,deployed.*,
applicant.*
FROM deployed
LEFT JOIN applicant
ON deployed.deployed_applicant = applicant.applicant_id
where
applicant.applicant_status IN (9)
AND applicant_employer=".$row["employer_id"]." 
order by deployed_date desc");
$deployedw = mysql_fetch_array($deployed1);







$visas =  mysql_query("SELECT 
count(requirement_visa_stamp) as alls,
applicant.applicant_id as adonis, 
applicant_requirement.*,
applicant.* 
FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status  NOT IN (9,21,22,1,14,25)
AND applicant_employer=".$row["employer_id"]." 
AND requirement_visa_stamp NOT IN ('1970-01-01','0000-00-00')
");
$visa = mysql_fetch_array($visas);




$sign = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status  NOT IN (9,21,22,1,14,25,5)
AND applicant_employer=".$row["employer_id"]." 
AND  requirement_visa_stamp  IN ('0000-00-00','1970-01-01','')
AND requirement_contract NOT IN ('1970-01-01','0000-00-00') ");
$signrow = mysql_fetch_array($sign);


$nocontract = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status  NOT IN (9,21,22,1,14,25,5)
AND applicant_employer=".$row["employer_id"]." 
AND requirement_contract  IN ('1970-01-01','0000-00-00')
 ");
$nocontract1 = mysql_fetch_array($nocontract);



$sign = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status  NOT IN (9,21,22,1,14,25,5)
AND applicant_employer=".$row["employer_id"]." 
AND  requirement_visa_stamp  IN ('0000-00-00','1970-01-01','')
AND requirement_contract NOT IN ('1970-01-01','0000-00-00') ");
$signrow = mysql_fetch_array($sign);


$onprocess = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status    NOT IN (9,21,22,1,14,25,5)
AND applicant_employer=".$row["employer_id"]." ");
$onprocess1 = mysql_fetch_array($onprocess);



$oec = mysql_query("SELECT 
count(	requirement_oec_release_date) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status   NOT IN (9,21,22,1,14,25,5)
AND applicant_employer=".$row["employer_id"]." 
AND requirement_oec_release_date NOT IN ('1970-01-01','0000-00-00') ");
$oec1 = mysql_fetch_array($oec);



$fit2 = mysql_query("SELECT 
count(applicant_id) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status     IN (5)
AND applicant_employer=".$row["employer_id"]." ");
$fitt = mysql_fetch_array($fit2);


$percent=($deployedw['alls']+$onprocess1['alls']);
$percent1=($deployedw['alls']/$percent)*100;
$totalsaudi=($deployedw['alls']+$onprocess1['alls']);
echo'<tr>';


if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){
echo'<td>
<a href="employer_reports.php?emp_id='.$row["employer_id"].'" target="_blank">
<button type="button" class="btn btn-danger btn-xs" style="font-size:11px;margin:3px">Debit/Credit</button></a>
</td>';

}
Else{
echo'<td>
<a href="employer_reports.php?emp_id='.$row["employer_id"].'" target="_blank">
<button type="button" class="btn btn-danger btn-xs" style="font-size:11px;margin:3px">Debit/Credit</button></a>
</td>';	
}



echo'<td>000'.$row["employer_id"].'</td>';
echo'<td style="text-align:center;font-size:16px;color:black"><b>'.$row["employer_name"].'</b></td>';
echo'<td>'.$row["country_name"].'</td>';

echo'<td><b>'.$visa['alls'].'</b></td>';
echo'<td><b>'.$signrow['alls'].'</b></td>';
echo'<td  style="background:#D3D3D3;display:none"><b>'.$nocontract1['alls'].'</b></td>';
echo'<td style="background:#D3D3D3;display:none"><b>'.$fitt['alls'].'</b></td>';
echo'<td  style="text-align:center;color:black;font-weight:bold;background:yellow"><b>'.$deployedw['alls'].'</b></td>';
echo'<td style="text-align:center;color:black;font-weight:bold;background:yellow;display:none"><b>'.$onprocess1['alls'].'</b></td>';
echo'<td style="background:yellow;display:none">'.$totalsaudi.'</td>';


if($percent1!=0){ echo'<td style="color:black;background:#F08080">'.number_format($percent1,0).' %</td>'; }
if($percent1==0){ echo'<td style="color:blue;font-weight:bold">--</td>'; }

echo'<td style="color:black;background:#F08080">'.number_format($balancesheet,0).' </td>';

//echo'<td style="color:orange">'.number_format($colusd["amountusd"],2).'</td>';
//echo'<td style="color:orange">'.number_format($colphp["amounPHP"],2).'</td>';
/*
echo'<td>
<a href="summary_employer.php?emp_id='.$row["employer_id"].'" target="_blank" style="color:black;font-size:11px">

<button type="button" class="btn btn-danger" btn-xs" style="font-size:11px">Summary Reports</button>
</A>
</td>';
*/
$deployedall=$deployedw['alls']+$deployedall;
$oecall=$oec1['alls']+$oecall;
$visaall=$visa['alls']+$visaall;
$signall=$signrow['alls']+$signall;
$fitall=$fitt['alls']+$fitall;
$onprocessall=$onprocess1['alls']+$onprocessall;
$compute=$onprocessall+$deployedall;
$percentall=($deployedall/$compute)*100;
$grandbalance=$balancesheet+$grandbalance;
$totalsaudi1=$totalsaudi+$totalsaudi1;
$nocontractsaudi=$nocontract1['alls']+$nocontractsaudi;

echo'</tr>';
}
?>
</tbody>
<tr>
<td colspan="4" style="text-align:right;color:black;font-size:16px;font-weight:bold">Total: </td>

<td style="text-align:center;color:black;font-size:16px;font-weight:bold"> <?=$visaall?></td>
<td style="text-align:center;color:black;font-size:16px;font-weight:bold"> <?=$signall?></td>
<td style="text-align:center;color:black;display:none;font-weight:bold"> <?=$nocontractsaudi?></td>
<td style="text-align:center;color:black;background:#D3D3D3;display:none;font-weight:bold"> <?=$fitall?></td>
<td style="text-align:center;color:black;background:yellow;font-size:16px;font-weight:bold"> <?=$deployedall?></td>
<td style="text-align:center;color:black;font-weight:bold;background:yellow;display:none;font-weight:bold"> <?=$onprocessall?></td>
<td style="text-align:center;color:black;font-weight:bold;background:yellow;display:none;font-weight:bold"> <?=$totalsaudi1?></td>
<td  style="text-align:center;color:black;background:#F08080;font-size:16px;font-weight:bold"> <?=number_format($percentall,0)?>%</td>
<td  style="text-align:center;color:black;background:#F08080;font-size:16px;font-weight:bold"> <?=number_format($grandbalance,0)?></td>
</tr>
</table>









<DIV style="clear:both;height:10px"></DIV>
</div>  
</div>			

<!-- /footer content -->
</div>
</div>

<script src="../vendors/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Skycons -->
<script src="../vendors/skycons/skycons.js"></script>

<!-- Custom Theme Scripts -->
<script src="../build/js/custom.min.js"></script>
	
  </body>
</html>




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
		order: [ 8, 'desc' ],
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

