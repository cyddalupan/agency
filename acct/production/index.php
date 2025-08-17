<?php include'inc/header.php';
include'inc/expenses.php';
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
 

         
              



<div class="row">



<div class="row">
<div class="col-md-12 col-xs-12">
<div class="x_panel">
<div class="x_content">
</br>


<style>
h3{
font-size:16px;	
}
</style>
<style>
  .allSides
    {
      width:230px;height:150px;
      border: solid 1px #555;
      background-color: #F5F5F5;
      box-shadow:  0 0 10px  rgba(0,0,0,0.6);
      -moz-box-shadow: 0 0 10px  rgba(0,0,0,0.6);
      -webkit-box-shadow: 0 0 10px  rgba(0,0,0,0.6);
      -o-box-shadow: 0 0 10px  rgba(0,0,0,0.6);
	  float:left;
	  margin:15px;
	  
    }
	  .allSides1
    {
      width:230px;height:120px;
      border: solid 1px #555;
      background-color: #F0F8FF;
      box-shadow:  0 0 10px  rgba(0,0,0,0.6);
      -moz-box-shadow: 0 0 10px  rgba(0,0,0,0.6);
      -webkit-box-shadow: 0 0 10px  rgba(0,0,0,0.6);
      -o-box-shadow: 0 0 10px  rgba(0,0,0,0.6);
	  float:left;
	  margin:5px;
	  
    }
	  .allSides2
    {
      width:200px;height:120px;
      border: solid 1px #555;
      background-color: #F0F8FF;
      box-shadow:  0 0 10px  rgba(0,0,0,0.6);
      -moz-box-shadow: 0 0 10px  rgba(0,0,0,0.6);
      -webkit-box-shadow: 0 0 10px  rgba(0,0,0,0.6);
      -o-box-shadow: 0 0 10px  rgba(0,0,0,0.6);
	  float:left;
	  margin:5px;
	  
    }
</style>

<div class="clearfix"></div>



<?PHP

$office = mysql_query("SELECT 
SUM(amount)  as alls
FROM email_address

where  account NOT IN ('SALARY','Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND account_expense=0
and request_payment!=999
AND currency='PHP'  
AND request_payment=3
");
$office1 = mysql_fetch_array($office);


$officemy = mysql_query("SELECT 
SUM(amount)  as alls
FROM email_address

where  account NOT IN ('SALARY','Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND account_expense=0
and request_payment!=999
AND currency='USD'  
AND request_payment=3
");
$officemy1 = mysql_fetch_array($officemy);


$usdoffice=$officemy['alls']*50;
$finalusd=$office1['alls']+$usdoffice;


$salary = mysql_query("SELECT 
SUM(amount)  as alls
FROM email_address

where account  IN ('SALARY')
AND account_expense=0
and request_payment!=999
AND currency='PHP'  
AND request_payment=3
");
$salary1 = mysql_fetch_array($salary);


$agnet = mysql_query("SELECT 
SUM(amount)  as alls
FROM email_address

where account  IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND account_expense=0
and request_payment!=999
AND currency='PHP'  
AND request_payment=3
");
$agnet1 = mysql_fetch_array($agnet);

$agnetmy = mysql_query("SELECT 
SUM(amount)  as alls
FROM email_address

where account  IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND account_expense=0
and request_payment!=999
AND currency='USD'  
AND request_payment=3
");
$agnetmy1 = mysql_fetch_array($agnetmy);

$phpagent=$agnetmy1['alls']*50;
$myagent=$agnet1['alls']+$phpagent;


$totalme=$finalusd+$salary1['alls']+$myagent;
?>

<div class="allSides1" style="width:20.9%;background:#FFA07A">
</br>
<H2 STYLE="text-align:center;color:black;font-weight:bold;font-size:22px"> OFFICE EXPENSE</H2>
<P STYLE="text-align:center;color:black;font-weight:bold;font-size:22px;color:lightgray"><a href="soa7.php?mysoa=4" style=";color:black" target="_blank">₱<?php echo' '.number_format($finalusd, 2).''; ?></a></P>
</div>

<div class="allSides1" style="width:20.9%;background:#DC143C">
</br>
<H2 STYLE="text-align:center;color:WHITE;font-weight:bold;font-size:22px"> SALARY EXPENSE</H2>
<P STYLE="text-align:center;color:WHITE;font-weight:bold;font-size:22px;color:lightgray"><a href="soa-salary.php?mysoa=4" style=";color:WHITE" target="_blank">₱<?php echo' '.number_format($salary1['alls'], 2).''; ?></a></P>
</div>



<div class="allSides1" style="width:20.9%;background:#00FF7F">
</br>
<H2 STYLE="text-align:center;color:black;font-weight:bold;font-size:22px"> AGENT EXPENSE</H2>
<P STYLE="text-align:center;color:black;font-weight:bold;font-size:22px;color:lightgray"><a href="soa-agent.php?mysoa=4" style=";color:black" target="_blank">₱<?php echo' '.number_format($myagent, 2).''; ?></a></P>
</div>

<div class="allSides2" style="background:black;width:300px">
</br>
<H2 STYLE="text-align:center;color:white;font-weight:bold;font-size:22px">GRAND TOTAL</H2>
<P STYLE="text-align:center;color:white;font-weight:bold;font-size:22px;color:white"><a href="#" style=";color:white">₱<?php echo' '.number_format($totalme, 2).''; ?></a></P>
</div>







</br></br>
<div class="clearfix"></div>
<h1 style="color:black;background:#ADFF2F;padding:5px;width:300px;text-align:center;font-size:22px">Receivables Reports</h1>

<form action="cashflow.php" method="post" target="_blank" >

<table style="width:500px">
<tr>
<td><input type="date" class="form-control" name="start"  value="<?=$date2;?>"></td>
<td><input type="date" class="form-control" name="end"  value="<?=$date2;?>"></td>
<td>
<?php
echo'<select class="form-control" name="emp_id"  required STYLE="width:150px">';
echo'<option value="99999">ALL FRA</option>';
$emp1 = mysql_query("SELECT * FROM employer ORDER BY employer_name asc");
while($emp111=mysql_fetch_array($emp1))
{
echo'<option value="'.$emp111["employer_id"].'">  '.$emp111["employer_name"].'</option>';
}
ECHO'</select>';
?>
</td>







<td>
<input type="submit" name="submitsearch" value="Generate Reports" style="float:right;background:#1E90FF;color:white;padding:5px" >
</td>



</tr>
</table>

</form>


<div class="clearfix"></div>


</br></br></br>

<h1 style="color:black;background:#FFA07A;padding:5px;width:300px;text-align:center;font-size:22px">Expenses Reports</h1>

<form action="printdaily.php" method="post" target="_blank" >

<table style="width:500px">
<tr>
<td><input type="date" class="form-control" name="start"  value="<?=$date2;?>"></td>
<td><input type="date" class="form-control" name="end"  value="<?=$date2;?>"></td>
<td>
<select class="form-control" required name="currency" style="display:none">
<option>PHP</option>
</select>
</td>

<td>
<select class="form-control" required name="request" STYLE="Width:150px">
<option value="3">Released</option>
<option value="0">For Approval</option>


</select>
</td>




<td>
<select class="form-control" required name="account" STYLE="Width:150px">
<option value="3">All</option>
<option value="1">Agent/Source Commission</option>
<option value="2">Office Expense</option>
<option value="4">Personal Expense</option>
</select>
</td>


<td>
<input type="submit" name="submitsearch" value="Generate Reports" style="float:right;background:#1E90FF;color:white;padding:5px" >
</td>



</tr>
</table>

</form>

</br></br>

</br>





<h1 style="color:black;background:#191970;padding:5px;width:300px;text-align:center;font-size:22px;color:white">Branch Petty Cash </h1>


<table  cellspacing="0" id="example-checkbox" style="border:1px solid black;width:1100px">
<thead>
	<tr>
<th >Branch Name</th>
<th >PETTY CASH</th>
<th >LIQUIDATION</th>
<th >REFUND</th>
<th >CHANGE</th>

<th >BALANCE</th>
</tr>
</thead>
<tbody>
<?php	

$app = mysql_query("SELECT * FROM training_branches 
order by id asc");

if(isset($_POST['Search']))
{
$app = mysql_query("SELECT * FROM recruitment_agent where
agent_first LIKE '%".$_POST['agent_name']."%' OR agent_last LIKE '%".$_POST['agent_name']."%'
OR agent_id LIKE '%".$_POST['agent_name']."%'
");
}


while($row=mysql_fetch_array($app))
{
    
    
$expense = mysql_query("SELECT SUM(amount) as amount_total ,email_address.* FROM email_address
WHERE  request_payment IN (3) AND branch_type=".$row['id']." 
AND account IN ('PETTY CASH','PETTY CASH BALANCE')");
$expense1=mysql_fetch_array($expense);



$change = mysql_query("SELECT SUM(amount) as amount_total ,email_address.* FROM email_address
WHERE  request_payment IN (1,3) AND branch_type=".$row['id']." 
and e_liq='CHANGE'");
$change1=mysql_fetch_array($change);


$refund = mysql_query("SELECT SUM(amount) as amount_total ,email_address.* FROM email_address
WHERE  request_payment IN (1,3) AND branch_type=".$row['id']." 
and e_liq='REFUND'");
$refund1=mysql_fetch_array($refund);

$liq = mysql_query("SELECT SUM(amount) as amount_total ,email_address.* FROM email_address
WHERE   e_liq='YES' AND branch_type=".$row['id']." AND request_payment IN (3,1)");
$liq1=mysql_fetch_array($liq);

$liqrefund=$liq1["amount_total"]+$refund1["amount_total"];
$balancechange=($expense1["amount_total"]-$liqrefund)+$change1["amount_total"];    
$all=$balancechange-$refund1["amount_total"];



echo'<tr>';
echo'<td STYle="text-align:left;TEXT-TRANSFORM:uppercase">  '.$row['branch_name'].'</td>';


echo'<td style="color:black"> '.number_format($expense1["amount_total"],2).'</td>';
echo'<td style="color:black"> '.number_format($liq1["amount_total"],2).'</td>';

echo'<td style="color:black"> '.number_format($refund1["amount_total"],2).'</td>';
echo'<td style="color:black"> '.number_format($change1["amount_total"],2).'</td>';



echo'<td style="color:black;font-weight:bold"> '.number_format($balancechange,2).'</td>';



echo'</tr>';
}
?>
</tbody>
</table>
</br></br>






</br></br>








</br></br></br></br>

<style>
#example-checkbox table{
width:100%;	
}
#example-checkbox td{
font-size:12px;
text-align:center;	
}
</style>



























</div>
</div>
</div>  
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


