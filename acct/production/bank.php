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


<a href="<?=$actual_link;?>" style="color:Red;text-align:right">Refresh</a>

<div class="row">
<div class="col-md-12 col-xs-12">
<div class="x_panel">
<div class="x_content">
</br>


<?php if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){ ?>


<h2> "Banks $"</h2>
<button type="button" class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#myModal">Add New Accounts</button>
<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>
    <th STYLE="width:2%">#</th>
<th STYLE="width:10%">DATE</th>
<th>Type</th>
<th STYLE="width:15%">bank_name</th>
<th>bank_num</th>
<th>bank_remarks</th>
<th>bank_currency</th>
<th>Debit</th>
<th>Credit</th>
<th>Balance</th>
<th>Debit/Credit</th>

</tr>
</thead>
<tbody>
<?php



$bbb = mysql_query("SELECT * FROM  bank_accounts
where bank_user_type IN(0)
AND bank_currency='USD' order by bank_user_type desc  ");


while($bbb1=mysql_fetch_array($bbb))
{

$collection = mysql_query("SELECT SUM(amount) as amount_total ,email_address6.* FROM email_address6
WHERE collect_bank_id=".$bbb1["bank_id"]."");
$collection1=mysql_fetch_array($collection);

$cash_id = mysql_query("SELECT SUM(cash_amount*cash_convertion) as amount_total ,cash_transaction.* FROM cash_transaction
WHERE cash_bank_in=".$bbb1["bank_id"]."
");

$credit = mysql_query("SELECT SUM(fund_amount+fund_amount_other) as amount_total ,fund.* FROM fund
WHERE fund_bank_id=".$bbb1["bank_id"]."");
$credit1=mysql_fetch_array($credit);

$cash_out = mysql_query("SELECT SUM(cash_amount) as amount_total ,cash_transaction.* FROM cash_transaction
WHERE cash_bank_out=".$bbb1["bank_id"]."
");
$cash_out1=mysql_fetch_array($cash_out);

$expense = mysql_query("SELECT SUM(amount) as amount_total ,email_address.* FROM email_address
WHERE fund_source_id=".$bbb1["bank_id"]." and currency='USD' ");
$expense1=mysql_fetch_array($expense);


//cash in
$cash_id1=mysql_fetch_array($cash_id);
$totalcashin=$collection1['amount_total']+$cash_id1['amount_total'];

//cash out

$totalcashout=$cash_out1['amount_total']+$credit1["amount_total"]+$expense1["amount_total"];

//total balance
$balance=$totalcashin-$totalcashout;

if($bbb1["bank_user_type"]==0){$request='Bank Account';$rcolor='black';}
if($bbb1["bank_user_type"]==1){$request='Personal Account';$rcolor='blue';}
if($bbb1["bank_user_type"]==2){$request='Office Account';$rcolor='orange';}


echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$bbb1['bank_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($bbb1['bank_date']));echo'</td>';
echo'<td style="font-size:12px;color:BLACK;">'.$request.'</td>';
echo'<td>'.$bbb1["bank_name"].'</td>';
echo'<td>'.$bbb1["bank_num"].'</td>';

echo'<td>'.$bbb1["bank_remarks"].'</td>';
echo'<td>'.$bbb1["bank_currency"].'</td>';
echo'<td> '.number_format($totalcashin, 2).'</td>';
echo'<td> '.number_format($totalcashout, 2).'</td>';
echo'<td style="color:Red"> '.number_format($balance, 2).'</td>';
//echo'<td>'.$amt0.'</td>';


echo'<td>
<a href="bank_reports.php?accounts='.$bbb1["bank_id"].'&&cur='.$bbb1["bank_currency"].'" target="_blank">
<button type="button" class="btn btn-danger btn-xs" style="font-size:11px;margin:3px">Review</button></a>

<a href="bank_edit.php?accounts='.$bbb1["bank_id"].'" target="_blank">
<button type="button" class="btn btn-info btn-xs" style="font-size:11px;margin:3px">Edit</button></a>



</td>';


echo'</tr>';
$grandtotal=$balance+$grandtotal;
    
}
$tot = number_format($total, 2);
?>
</tbody>


</table>
</form>	

<?php } ?>


<?php// php peso?>


</BR></BR>

<?php if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){ ?>


<h2> "Banks Peso"</h2>
<button type="button" class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#myModal">Add New Accounts</button>
<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>
    <th STYLE="width:2%">#</th>
<th STYLE="width:10%">DATE</th>
<th>Type</th>
<th STYLE="width:15%">bank_name</th>
<th>bank_num</th>
<th>bank_remarks</th>
<th>bank_currency</th>
<th>Debit</th>
<th>Credit</th>
<th>Balance</th>
<th>Debit/Credit</th>

</tr>
</thead>
<tbody>
<?php



$bbbpeso = mysql_query("SELECT * FROM  bank_accounts
where bank_user_type IN(0)
and bank_currency='PHP'
order by bank_user_type desc  ");


while($bbbpeso1=mysql_fetch_array($bbbpeso))
{

$collection = mysql_query("SELECT SUM(amount) as amount_total ,email_address6.* FROM email_address6
WHERE collect_bank_id=".$bbbpeso1["bank_id"]."");
$collection1=mysql_fetch_array($collection);

$cash_id = mysql_query("SELECT SUM(cash_amount*cash_convertion) as amount_total ,cash_transaction.* FROM cash_transaction
WHERE cash_bank_in=".$bbbpeso1["bank_id"]."
");

$credit = mysql_query("SELECT SUM(fund_amount+fund_amount_other) as amount_total ,fund.* FROM fund
WHERE fund_bank_id=".$bbbpeso1["bank_id"]."");
$credit1=mysql_fetch_array($credit);

$cash_out = mysql_query("SELECT SUM(cash_amount) as amount_total ,cash_transaction.* FROM cash_transaction
WHERE cash_bank_out=".$bbbpeso1["bank_id"]."
");
$cash_out1=mysql_fetch_array($cash_out);

$expense = mysql_query("SELECT SUM(amount) as amount_total ,email_address.* FROM email_address
WHERE fund_source_id=".$bbbpeso1["bank_id"]." and currency='USD1111' ");
$expense1=mysql_fetch_array($expense);


//cash in
$cash_id1=mysql_fetch_array($cash_id);
$totalcashin=$collection1['amount_total'];

//cash out

$totalcashout=$cash_out1['amount_total']+$credit1["amount_total"]+$expense1["amount_total"];

//total balance
$balance=$totalcashin-$totalcashout;

if($bbbpeso1["bank_user_type"]==0){$request='Bank Account';$rcolor='black';}
if($bbbpeso1["bank_user_type"]==1){$request='Personal Account';$rcolor='blue';}
if($bbbpeso1["bank_user_type"]==2){$request='Office Account';$rcolor='orange';}


echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$bbbpeso1['bank_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($bbbpeso1['bank_date']));echo'</td>';
echo'<td style="font-size:12px;color:BLACK;">'.$request.'</td>';
echo'<td>'.$bbbpeso1["bank_name"].'</td>';
echo'<td>'.$bbbpeso1["bank_num"].'</td>';

echo'<td>'.$bbbpeso1["bank_remarks"].'</td>';
echo'<td>'.$bbbpeso1["bank_currency"].'</td>';
echo'<td> '.number_format($totalcashin, 2).'</td>';
echo'<td> '.number_format($totalcashout, 2).'</td>';
echo'<td style="color:Red"> '.number_format($balance, 2).'</td>';
//echo'<td>'.$amt0.'</td>';

if($bbbpeso1["bank_name"]=='AGENT PAYMENTS'){
echo'<td>
<a href="bank_reports.php?accounts='.$bbbpeso1["bank_id"].'&&cur='.$bbbpeso1["bank_currency"].'" target="_blank">
<button type="button" class="btn btn-danger btn-xs" style="font-size:11px;margin:3px">Review</button></a>

<a href="bank_edit.php?accounts='.$bbbpeso1["bank_id"].'" target="_blank">
<button type="button" class="btn btn-info btn-xs" style="font-size:11px;margin:3px">Edit</button></a>
</td>';
}

if($bbbpeso1["bank_name"]=='PNB PESO ACCOUNT'){
echo'<td>
<a href="bank_reports1.php?accounts='.$bbbpeso1["bank_id"].'&&cur='.$bbbpeso1["bank_currency"].'" target="_blank">
<button type="button" class="btn btn-danger btn-xs" style="font-size:11px;margin:3px">Review</button></a>

<a href="bank_edit.php?accounts='.$bbbpeso1["bank_id"].'" target="_blank">
<button type="button" class="btn btn-info btn-xs" style="font-size:11px;margin:3px">Edit</button></a>
</td>';
}




echo'</tr>';
$grandtotal=$balance+$grandtotal;
    
}
$tot = number_format($total, 2);
?>
</tbody>


</table>
</form>	

<?php } ?>






</BR></BR></BR></BR></BR></BR>







































<h4> "Accounts"</h4>
<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>
    <th STYLE="width:2%">#</th>
<th STYLE="width:10%">DATE</th>
<th>Type</th>
<th STYLE="width:15%">Acouunt Name</th>
<th>bank_num</th>
<th>bank_remarks</th>
<th>bank_currency</th>
<th>Debit</th>
<th>Credit</th>
<th>Balance</th>
<th>Debit/Credit</th>

</tr>
</thead>
<tbody>
<?php



$app = mysql_query("SELECT * FROM  bank_accounts
where bank_user_type IN(2)
order by bank_user_type desc  ");


while($row=mysql_fetch_array($app))
{

$collection = mysql_query("SELECT SUM(amount) as amount_total ,email_address6.* FROM email_address6
WHERE collect_bank_id=".$row["bank_id"]."");
$collection1=mysql_fetch_array($collection);

$cash_id = mysql_query("SELECT SUM(cash_amount*cash_convertion) as amount_total ,cash_transaction.* FROM cash_transaction
WHERE cash_bank_in=".$row["bank_id"]."
");

$credit = mysql_query("SELECT SUM(fund_amount+fund_amount_other) as amount_total ,fund.* FROM fund
WHERE fund_bank_id=".$row["bank_id"]."");
$credit1=mysql_fetch_array($credit);

$cash_out = mysql_query("SELECT SUM(cash_amount) as amount_total ,cash_transaction.* FROM cash_transaction
WHERE cash_bank_out=".$row["bank_id"]."
");


$expense = mysql_query("SELECT SUM(amount) as amount_total ,email_address.* FROM email_address
WHERE fund_source_id=".$row["bank_id"]." AND request_payment=3  and currency='PHP'");
$expense1=mysql_fetch_array($expense);


//cash in
$cash_id1=mysql_fetch_array($cash_id);
$totalcashin=$collection1['amount_total']+$cash_id1['amount_total'];

//cash out
$cash_out1=mysql_fetch_array($cash_out);
$totalcashout=$cash_out1['amount_total']+$credit1["amount_total"]+$expense1["amount_total"];

//total balance
$balance=$totalcashin-$totalcashout;

if($row["bank_user_type"]==0){$request='Bank Account';$rcolor='black';}
if($row["bank_user_type"]==1){$request='Personal Account';$rcolor='blue';}
if($row["bank_user_type"]==2){$request='Office Account';$rcolor='orange';}


echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$row['bank_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($row['bank_date']));echo'</td>';
echo'<td style="font-size:12px;color:BLACK;">'.$request.'</td>';
echo'<td>'.$row["bank_name"].'</td>';
echo'<td>'.$row["bank_num"].'</td>';

echo'<td>'.$row["bank_remarks"].'</td>';
echo'<td>'.$row["bank_currency"].'</td>';
echo'<td> '.number_format($totalcashin, 2).'</td>';
echo'<td> '.number_format($totalcashout, 2).'</td>';
echo'<td style="color:Red"> '.number_format($balance, 2).'</td>';

//echo'<td>'.$amt0.'</td>';

if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){
echo'<td>
<a href="bank_reports.php?accounts='.$row["bank_id"].'&&cur='.$row["bank_currency"].'" target="_blank">
<button type="button" class="btn btn-danger btn-xs" style="font-size:11px;margin:3px">Review</button></a>

<a href="bank_edit.php?accounts='.$row["bank_id"].'" target="_blank">
<button type="button" class="btn btn-info btn-xs" style="font-size:11px;margin:3px">Edit</button></a>
</td>';
}
else{
echo'<td>
<a href="bank_reports.php?accounts='.$row["bank_id"].'&&cur='.$row["bank_currency"].'" target="_blank">
<button type="button" class="btn btn-danger btn-xs" style="font-size:11px;margin:3px">Review</button></a>

</td>';
}


echo'</tr>';
$grandtotal111=$balance+$grandtotal111;
    
}
$tot = number_format($total, 2);
?>
</tbody>

<Tr>
    <td colspan="12" style="font-size:16px;font-weight:bold;color:black;text-align:right"> Cash on Hand: <?php  echo number_format($grandtotal111, 2); ?></td>    
</Tr>
</table>
</form>	













<?php if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){ ?>


<h4>"Personal Account"</h4>


<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>
    <th STYLE="width:2%">#</th>
<th STYLE="width:10%">DATE</th>

<th STYLE="width:15%">bank_name</th>
<th>bank_num</th>
<th>bank_remarks</th>
<th>bank_currency</th>
<th>Debit</th>
<th>Credit</th>
<th>Balance</th>
<th>Debit/Credit</th>

</tr>
</thead>
<tbody>
<?php


if(isset($_POST['delete_orders'])){
foreach ($_POST['delall'] as $delall) {
$app11 = mysql_query("delete  FROM bank_accounts where bank_id= ".$delall)

or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted</p>";
}



$personal = mysql_query("SELECT * FROM  bank_accounts
 where bank_user_type=1  order by bank_user_type desc  ");


while($personal1=mysql_fetch_array($personal))
{

$cashinrow = mysql_query("SELECT SUM(cash_amount*cash_convertion) as amount_total ,cash_transaction.* FROM  cash_transaction 
WHERE cash_bank_out=".$personal1["bank_id"]." ");
$cashinrow1=mysql_fetch_array($cashinrow);

$expenserow = mysql_query("SELECT SUM(amount) as amount_total ,email_address.* FROM email_address
WHERE account_expense=".$personal1["bank_id"]."");
$expenserow1=mysql_fetch_array($expenserow);

//Credit
$creditrow=$cashinrow1['amount_total'];
//Debit
$debitrow=$expenserow1['amount_total'];
//balance
$mybalance=$creditrow-$debitrow;

if($row["bank_user_type"]==0){$request='Bank Account';$rcolor='black';}
if($row["bank_user_type"]==1){$request='Personal Account';$rcolor='blue';}
if($row["bank_user_type"]==2){$request='Office Account';$rcolor='orange';}


echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$personal1['bank_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($personal1['bank_date']));echo'</td>';
echo'<td>'.$personal1["bank_name"].'</td>';
echo'<td>'.$personal1["bank_num"].'</td>';

echo'<td>'.$personal1["bank_remarks"].'</td>';
echo'<td>'.$personal1["bank_currency"].'</td>';
echo'<td> '.number_format($debitrow, 2).'</td>';
echo'<td> '.number_format($creditrow, 2).'</td>';
echo'<td style="color:Red">'.number_format($mybalance , 2).' </td>';
//echo'<td>'.$amt0.'</td>';
echo'<td>
<a href="personal_reports.php?accounts='.$personal1["bank_id"].'&&cur='.$personal1["bank_currency"].'" target="_blank">
<button type="button" class="btn btn-danger btn-xs" style="font-size:11px;margin:3px">Review</button></a>
</td>';
echo'</tr>';
}

?>
</tbody>
</table>





<div style="clear:both;height:50px"></div>
 
 
 
 
 <h4>"Office Deduction"</h4>


<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>
    <th STYLE="width:2%">#</th>
<th STYLE="width:10%">DATE</th>

<th STYLE="width:15%">bank_name</th>
<th>bank_num</th>
<th>bank_remarks</th>
<th>bank_currency</th>
<th>Debit</th>
<th>Credit</th>
<th>Balance</th>
<th>Debit/Credit</th>

</tr>
</thead>
<tbody>
<?php


$officededuction = mysql_query("SELECT * FROM  bank_accounts
 where bank_user_type=3  order by bank_user_type desc  ");


while($officededuction1=mysql_fetch_array($officededuction))
{

$cashinrow = mysql_query("SELECT SUM(cash_amount*cash_convertion) as amount_total ,cash_transaction.* FROM  cash_transaction 
WHERE cash_bank_out=".$officededuction["bank_id"]." ");
$cashinrow1=mysql_fetch_array($cashinrow);

$expenserow = mysql_query("SELECT SUM(amount) as amount_total ,email_address.* FROM email_address
WHERE account_expense=".$officededuction["bank_id"]."");
$expenserow1=mysql_fetch_array($expenserow);

//Credit
$creditrow=$cashinrow1['amount_total'];
//Debit
$debitrow=$expenserow1['amount_total'];
//balance
$mybalance=$creditrow-$debitrow;

if($row["bank_user_type"]==0){$request='Bank Account';$rcolor='black';}
if($row["bank_user_type"]==1){$request='Personal Account';$rcolor='blue';}
if($row["bank_user_type"]==2){$request='Office Account';$rcolor='orange';}
if($row["bank_user_type"]==2){$request='Office Deduction';$rcolor='orange';}

echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$officededuction['bank_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($officededuction['bank_date']));echo'</td>';
echo'<td>'.$officededuction["bank_name"].'</td>';
echo'<td>'.$officededuction["bank_num"].'</td>';

echo'<td>'.$officededuction["bank_remarks"].'</td>';
echo'<td>'.$officededuction["bank_currency"].'</td>';
echo'<td> '.number_format($debitrow, 2).'</td>';
echo'<td> '.number_format($creditrow, 2).'</td>';
echo'<td style="color:Red">'.number_format($mybalance , 2).' </td>';
//echo'<td>'.$amt0.'</td>';
echo'<td>
<a href="personal_reports.php?accounts='.$officededuction["bank_id"].'&&cur='.$officededuction["bank_currency"].'" target="_blank">
<button type="button" class="btn btn-danger btn-xs" style="font-size:11px;margin:3px">Review</button></a>
</td>';
echo'</tr>';
}

?>
</tbody>
</table>
 
 
 
 
 
 
 
 
 
 


<div style="clear:both;height:50px"></div>
<a href="<?=$actual_link;?>" style="color:Red;text-align:right">Refresh</a>

<h4>"Cash IN & Cash OUT"</h4>



<form action="" method="post">
<table>
<tr>
<td><small style="color:blue">Date</small><input type="date" class="form-control" name="cash_date" ></td>

<td><small style="color:blue">Cash OUT</small>
<?php
echo'<select class="form-control" name="cash_bank_out"  style="width:150px">';
echo'<option value="">  --</option>';
$bank = mysql_query("SELECT * FROM bank_accounts ORDER BY bank_name asc");
while($bank1=mysql_fetch_array($bank))
{
echo'<option value="'.$bank1["bank_id"].'">  '.$bank1["bank_name"].' - Account #: '.$bank1["bank_num"].' ('.$bank1["bank_currency"].') </option>';
}
ECHO'</select>';
?>
</td>

<td><small style="color:blue">Cash IN</small>
<?php
echo'<select class="form-control" name="cash_bank_in"  required style="width:150px">';
echo'<option value="">  --</option>';
$bank = mysql_query("SELECT * FROM bank_accounts ORDER BY bank_name asc");
while($bank1=mysql_fetch_array($bank))
{
echo'<option value="'.$bank1["bank_id"].'">  '.$bank1["bank_name"].' - Account #: '.$bank1["bank_num"].' ('.$bank1["bank_currency"].') </option>';
}
ECHO'</select>';
?>
</td>



<td><small style="color:blue">Cash Amount</small><input type="text" class="form-control" name="cash_amount" placeholder="Amount Cash " required></td>
<td><small style="color:blue">Conversion rate if $:</small><input type="number" class="form-control" name="cash_convertion" required value="1" min="1"></td>
<td><small style="color:blue">Type</small>
<select class="form-control" required name="cash_type"  required style="width:150px">

<option value="2">Cash IN & OUT</option>
</select>
</td>

<td><small style="color:blue">Remarks</small><input type="text" class="form-control" name="bank_remarks" ></td>

<td><input type="submit" name="addcashin" value="Add Transaction"  class="btn btn-info btn-xs" ></td>

</tr>
</table>
</form>	


 <form action="" method="post">
<div style="clear:both;height:10px"></div>
<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>
    <th STYLE="width:2%">#</th>
<th STYLE="width:10%">DATE</th>
<th>Type</th>
<th STYLE="width:15%">Cash OUT Bank</th>
<th STYLE="width:15%">Cash IN Bank</th>
<th>Amount</th>
<th>Conversion Rate</th>
<th>Total ₱</th>
<th>Remarks</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php


if(isset($_POST['delete_orders1111'])){
foreach ($_POST['delall'] as $delall) {
$app11 = mysql_query("delete  FROM cash_transaction where cash_id= ".$delall)

or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted</p>";
}



$cashin = mysql_query("SELECT * FROM  cash_transaction
order by cash_id desc  ");


while($cashin1=mysql_fetch_array($cashin))
{
$bank_out = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$cashin1['cash_bank_out']." ");
$bank_out1=mysql_fetch_array($bank_out);

$bank_in = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$cashin1['cash_bank_in']." ");
$bank_in1=mysql_fetch_array($bank_in);


if($cashin1["cash_type"]==1){$requestrr='Cash IN Only';$rcolor='blue';}
if($cashin1["cash_type"]==2){$requestrr='Cash IN & OUT';$rcolor='orange';}


echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$cashin1['cash_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($cashin1['cash_date']));echo'</td>';
echo'<td style="font-size:12px;color:BLACK;">'.$requestrr.'</td>';
echo'<td>BankName:'.$bank_out1["bank_name"].' <br> BankAccount:'.$bank_out1["bank_num"].' ('.$bank_out1["bank_currency"].')</td>';
echo'<td>BankName:'.$bank_in1["bank_name"].' <br> BankAccount:'.$bank_in1["bank_num"].' ('.$bank_in1["bank_currency"].')</td>';


echo'<td>'.$cashin1["cash_amount"].'</td>';
echo'<td>'.$cashin1["cash_convertion"].'</td>';
echo'<td> '.number_format($cashin1["cash_amount"]*$cashin1["cash_convertion"], 2).'</td>';
echo'<td>'.$cashin1["bank_remarks"].'</td>';
echo'<td>
<a href="bank_edit_trans.php?idme='.$cashin1["cash_id"].'" target="_blank">
<button type="button" class="btn btn-danger btn-xs" style="font-size:11px;margin:3px">Edit</button></a>

</td>';
echo'</tr>';
}
$tot = number_format($total, 2);
?>
</tbody>
</table>
</br></br>
<div class="row" >
<div class="col-5" style="width:100%,float:right">		
<input type="submit" value="Delete" name="delete_orders1111" style="width:130px;background:Red;padding:5px;margin-left:20px;margin-top:-1px;color:white">
</div>
</div>
</form>

 <?php } ?>



<div class="ln_solid"></div>
</div>
</div>
</div>  
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
<label class="control-label col-md-3 col-sm-3 col-xs-12">DATE Added</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="date" class="form-control" name="bank_date" value="<?=$date1?>" required>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">bank_name</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="bank_name" >
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Account #</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="bank_num" required>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">bank_currency_ACCOUNT</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control" name="bank_currency"  required >
<option></option>
<option>PHP</option>
<option>USD</option>
</select>
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">bank_remarks</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="bank_remarks">
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Type of Payment</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control" name="bank_user_type" required>
<option></option>
<option value="0">Bank Account</option>
<option value="1">Personal Account</option>
<option value="2">Office Account</option>
<option value="3">Office Deduction</option>
</select>
Success!
</div>
</div>





<div class="form-group">
<div class="col-md-12 col-sm-9 col-xs-12">	
<button type="submit" class="btn btn-danger pull-right"  name="add_bannk">Save</button>
</div>
</div>


</form>		  



</div>

</div>

</div>
</div>





