<ul class="nav side-menu" STYLE="font-size:11px">
<?php



echo'<li><a href="index.php"><i class="fa fa-laptop"></i>dashboard</a></li>';
echo'<li><a href="applicants.php"><i class="fa fa-list"></i>Applicant</a></li>';
echo'<li><a href="otherspaid.php?paid=1&&stati=9" target="_blank"><i class="fa fa-laptop"></i>Remittance Entry(FRA)</a></li>';
if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){
echo'<li><a href="otherspaid2.php?paid=1&&stati=9" target="_blank"><i class="fa fa-laptop"></i>Commission Entry(AGENT)</a></li>';
}
else{
echo'<li><a href="otherspaid1.php?paid=1&&stati=9" target="_blank"><i class="fa fa-laptop"></i>Commission Entry(AGENT)</a></li>';
}
?>

<li><a><i class="fa fa-users"></i> Enrollment <span class="fa fa-chevron-down"></span></a>
<ul class="nav child_menu">
<li><a href="registration.php?status=1"><i class="fa fa-folder"></i>Clients</a></li>
<li><a href="registration.php?status=2"><i class="fa fa-folder"></i>Suppliers</a></li>
<li><a href="agent-registration.php"><i class="fa fa-folder"></i>Agents</a></li>
</ul>




<li><a><i class="fa fa-database"></i> Collection <span class="fa fa-chevron-down"></span></a>
<ul class="nav child_menu">
<?php
//<li><a href="paid-app.php?paid=1&&stati=9"><i class="fa fa-folder"></i>Collectables/Deployed</a></li>
?>
<li><a href="collection.php"><i class="fa fa-folder"></i>Collection Payment of<small>(agent,applicant,)</small></a></li>
<li><a href="collectiongroup2.php"><i class="fa fa-folder"></i>Collectables OLD<small>(FRA)</small></a></li>
<li><a href="collectiongroup.php"><i class="fa fa-folder"></i>Collected <small>(FRA)</small></a></li>
</ul>






<li><a><i class="fa fa-database"></i> Expenses <span class="fa fa-chevron-down"></span></a>
<ul class="nav child_menu">
<?php
//<li><a href="applicants.php"><i class="fa fa-folder"></i>Applicant List</a></li>

//<li><a href="charge.php"><i class="fa fa-list"></i>Applicant .Expenses List</a></li>
?>
<li><a href="expenses.php"><i class="fa fa-folder"></i>Office Expenses</a></li>
<li><a href="agentcom.php"><i class="fa fa-folder"></i>Agent/Source Comm</a></li>
<?php
//<li><a href="bir.php"><i class="fa fa-folder"></i>BIR</a></li>
//<li><a href="benefits.php"><i class="fa fa-folder"></i>Benefits</a></li>

//echo'<li><a href="expense-payroll.php"><i class="fa fa-folder"></i>Salary</a></li>';
//echo'<li><a href="Loan.php"><i class="fa fa-folder"></i>Loan</a></li>';?>
</ul>


<li><a><i class="fa fa-database"></i> Payable <span class="fa fa-chevron-down"></span></a>
<ul class="nav child_menu">

<li><a href="collectiongroup1.php"><i class="fa fa-folder"></i>Payable TO <small>(FRA)</small></a></li>
</ul>

<li><a><i class="fa fa-database"></i> Deduction <span class="fa fa-chevron-down"></span></a>
<ul class="nav child_menu">

<li><a href="deduction.php"><i class="fa fa-folder"></i>Deduction TO <small>(AGENT)</small></a></li>
</ul>


<?php
//echo'<li><a href="sales.php"><i class="fa fa-file"></i>Remittance</a></li>';
//echo'<li><a href="marketing.php"><i class="fa fa-user"></i>Marketing</a></li>';
echo'<li><a href="employer.php"><i class="fa fa-user"></i>FRA / Principal</a></li>';
//echo'<li><a href="employer.php"><i class="fa fa-user"></i>Employer</a></li>';
//echo'<li><a href="training.php"><i class="fa fa-user"></i>Applicant</a></li>';
//echo'<li><a href="collection.php"><i class="fa fa-folder"></i>Collection</a></li>';
//echo'<li><a href="branch.php"><i class="fa fa-user"></i>Branch</a></li>';


//echo'<li><a href="case.php"><i class="fa fa-folder"></i>Cases</a></li>';
echo'<li><a href="agent.php"><i class="fa fa-users"></i>PRO / Agent</a></li>';
echo'<li><a href="branchview.php"><i class="fa fa-users"></i>Branch Source</a></li>';

?>
<li><a><i class="fa fa-database"></i> Settings <span class="fa fa-chevron-down"></span></a>
<ul class="nav child_menu">
<li><a href="settings.php">Account Type</a></li>
<li><a href="bank.php">Bank & Accounts</a></li>
 <?php if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){ ?>
<li><a href="userss.php">Users</a></li>
 
 
 <?php } ?>
</ul>



<li><a><i class="fa fa-database"></i> Reports <span class="fa fa-chevron-down"></span></a>
<ul class="nav child_menu">

<!--<li><a href="employer.html"><i class="fa fa-users"></i>Employer</a></li>
<li><a href="medical.html"><i class="fa fa-medkit"></i>Medical</a></li>
<li><a href="coordinator.html"><i class="fa fa-suitcase"></i>Coordinator</a></li>-->


<li><a><i class="fa fa-database"></i> Accounting Reports <span class="fa fa-chevron-down"></span></a>
<ul class="nav child_menu">
<li><a href="reports.php">Reports Expenses</a></li>

<!--<li><a href="collection_report.php">Report Collection</a></li>
<li><a href="deployedreports.php">Deployed </a></li>-->

</ul>

</ul>




<?php
//}
//else{
//echo'<li><a href="liqui2.php"><i class="fa fa-laptop"></i>liquidation</a></li>';     
//}



?>


