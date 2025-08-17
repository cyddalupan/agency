<ul class="nav side-menu" STYLE="font-size:11px">
<?php
echo'<li><a href="index.php"><i class="fa fa-laptop"></i>dashboard</a></li>';
//if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){
?>

<li><a><i class="fa fa-users"></i> Suppliers <small>(Ticket,medical etc)</small><span class="fa fa-chevron-down"></span></a>
<ul class="nav child_menu">
<li><a href="registration.php?status=2"><i class="fa fa-folder"></i>Suppliers</a></li>
</ul>
 
<?PHP //} ?>




<li><a><i class="fa fa-database"></i> Applicants <span class="fa fa-chevron-down"></span></a>
<ul class="nav child_menu">
<?php
//<li><a href="paid-app.php?paid=1&&stati=9"><i class="fa fa-folder"></i>Collectables/Deployed</a></li>
?>
<li><a href="applicants.php"><i class="fa fa-list"></i>Applicant List</a></li>
<?php
if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){
//echo'<li><a href="available-applicants.php" ><i class="fa fa-laptop"></i>Available</a></li>';
//echo'<li><a href="sent-applicants.php"><i class="fa fa-laptop"></i>CV Sent</a></li>';
}

?>


<li><a href="collection.php?who=2"><i class="fa fa-folder"></i>Received Payment of Applicant</a></li>

</ul>












<?PHP
if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){

?>
<li><a><i class="fa fa-database"></i> FRA<span class="fa fa-chevron-down"></span></a>
<ul class="nav child_menu">
<?php
echo'<li><a href="otherspaid.php?paid=1&&stati=9" target="_blank"><i class="fa fa-laptop"></i>Remittance Entry(Emergency Entry)</a></li>';
//echo'<li><a href="otherspaid3.php?paid=1&&stati=9" target="_blank"><i class="fa fa-laptop"></i>Remittance Entry(Automatic Amount Agree)</a></li>';
?>

<li><a href="collectiongroup.php" style="color:yellow"><i class="fa fa-folder"></i>Received Payment <small>(FRA)</small></a></li>
<li style="display:none"><a href="collectiongroup1.php"><i class="fa fa-folder"></i>Payable TO <small>(FRA)</small></a></li>

<li><a href="employer.php"><i class="fa fa-folder"></i>FRA REPORTS</a></li>
</ul>
<?php    } ?>



<li><a><i class="fa fa-database"></i> Agents & Payments<span class="fa fa-chevron-down"></span></a>
<ul class="nav child_menu">
<?php
if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){
echo'<li><a href="otherspaid2.php?paid=1&&stati=9" target="_blank"><i class="fa fa-laptop"></i>Agent Agreement </a></li>';

}
//echo'<li><a href="otherspaid1.php?paid=1&&stati=9" target="_blank"><i class="fa fa-laptop"></i>Commission Entry(Automatic Amount Agree)</a></li>';

?>
<li><a href="agentcom.php"  style="color:yellow"><i class="fa fa-folder"></i> Commmission Entry</a></li>
<li><a href="collection.php?who=1"><i class="fa fa-folder"></i> Payment of Agent</a></li>
<li><a href="deduction.php"><i class="fa fa-folder"></i>Deduction TO <small>(AGENT)</small></a></li>
<li><a href="agent.php" style="color:yellow"><i class="fa fa-users"></i> Agent Reports</a></li>
</ul>



<li><a><i class="fa fa-database"></i>Office Expense & Applicants<span class="fa fa-chevron-down"></span></a>
<ul class="nav child_menu">
<li><a href="expensesopen.php"><i class="fa fa-folder"></i> All Expenses</a></li>
<li><a href="expensesopen1.php"><i class="fa fa-folder"></i> Pending</a></li>
<li><a href="expensesopen2.php"><i class="fa fa-folder"></i> Approved</a></li>
<li><a href="expensesopen3.php"><i class="fa fa-folder"></i> New Released</a></li>
</ul>

<li><a href="expensesopen31.php"><i class="fa fa-folder"></i>Liquidaion List</a></li>

<li><a href="cancelled.php"><i class="fa fa-laptop"></i>Cancelled Expenses </a></li>



<li><a><i class="fa fa-database"></i> Settings <span class="fa fa-chevron-down"></span></a>
<ul class="nav child_menu">

<li><a href="bank.php">Bank & Accounts</a></li>
 <?php if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){ ?>
<li><a href="settings.php">Account Type</a></li>
<li><a href="userss.php">Users</a></li>
 
 
 <?php } ?>
</ul>





<?php
//}
//else{
//echo'<li><a href="liqui2.php"><i class="fa fa-laptop"></i>liquidation</a></li>';     
//}



?>


