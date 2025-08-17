<?php include'review.php';?>
<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<!-- Head -->
<head>
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
<title></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../assets/styles/admin/bootstrap.min.css" rel="stylesheet" />
<link href="../assets/styles/admin/font-awesome.min.css" rel="stylesheet" />
</head>
<style>
body{
font-family:calibri;    
}

#shadow {
  -webkit-box-shadow: 3px 3px 5px 6px #ccc;  /* Safari 3-4, iOS 4.0.2 - 4.2, Android 2.3+ */
  -moz-box-shadow:    3px 3px 5px 6px #ccc;  /* Firefox 3.5 - 3.6 */
  box-shadow:         3px 3px 5px 6px #ccc;  /* Opera 10.5, IE 9, Firefox 4+, Chrome 6+, iOS 5 */
}
table td input{
color:black;
height:20px;
width:100px;
FONT-size:12px;
}
table th{
FONT-size:12px;
}
ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
  overflow: hidden;
  background-color: #000080;
  width:65%;
}

li {
  float: left;
  border-right:1px solid #bbb;
}

li:last-child {
  border-right: none;
}

li a {
  display: block;
  color: white;
  text-align: center;
  padding: 14px 16px;
  text-decoration: none;
}

li a:hover:not(.active) {
  color:white;
}

.active {
  background-color: #1E90FF;
}


</style>



<!-- /Head -->
<!-- Body -->
<body style="background:white">

<div class="col-md-14 col-sm-14" style="margin-top:-1px" >
<div class="row">
<div class="col-md-12">
  <form action="" method="post">
  
<div class="clients-page">

<div class="col-sm-2 pull-right" align="center">
</div>

<div style="clear:both;height:30px"></div>



<div class="row" style="padding:10px;margin:15px;border:0px solid gray;width:150%;margin-top:-10px" id="shadow">
<div class="col-md-12" style="">

<?php
include'db.php';

include'functions.php';

$employer = mysql_query("SELECT * FROM   assign
where a_user=".$_GET['user']." 
ORDER BY a_id desc");

$nameArray = array();
while($rowemployer = mysql_fetch_array($employer)) {
    // Append to the array
     $nameArray[] = $rowemployer['a_employer'];  
}
$employerr=implode(",",$nameArray);


?>
<style>
#example-checkbox table{
width:100%;	
}
#example-checkbox td {
font-size:16px;
text-align:center;	
}
#example-checkbox a{
font-size:16px;
text-align:center;	
}
#example-checkbox th{
font-size:18px;
text-align:center;	
}
</style>



<form action="" method="post">



<div class="hero">
<!-- Title. Don't forget the <span> tag -->

<ul id="menu">
<li><a  class="active" href="view_coordinator.php?user=<?=$_GET['user']?>">Dashboard </a></li>
<li><a  href="view_coordinator-all.php?user=<?=$_GET['user']?>">My Applicant </a></li>
<li><a href="view_line.php?user=<?=$_GET['user']?>">CV Sent</a></li>
<li><a href="view_coordinator1.php?user=<?=$_GET['user']?>">On Process</a></li>
<li><a  href="view_deployed.php?user=<?=$_GET['user']?>">Deployed</a></li>
<li><a   href="view_cancelled.php?user=<?=$_GET['user']?>">Cancelled</a></li>
<li><a href="view_reports.php?user=<?=$_GET['user']?>">Reports </a></li>
<li style="float:right">

</li>
</ul>
<h3><span>Summary</span></h3>

</div> 
<div style="clear:both;height:20px"></div>
<div class="row">
<div class="col-md-12"  style=" padding-bottom:20px;">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">
<div class="table-responsive">
<script>
function exportF(elem) {
  var table = document.getElementById("example-checkbox");
  var html = table.outerHTML;
  var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url 
  elem.setAttribute("href", url);
  elem.setAttribute("download", "export.xls"); // Choose the file name
  return false;
}
</script>
</br>
<a id="downloadLink" onclick="exportF(this)" style="color:red;margin-left:1000px">SAVE MY WORK</a>

</br></br>


<table  cellspacing="0"  class="table table-hover table-striped table-bordered"  id="example-checkbox" style="border:1px solid black;width:60%">
<thead>
<tr>
<th STYLE="width:2%">Code</th>
<th   >FRA</th>
<th >Available</th>
<th >Selected</th>
<th >On Process</th>
<th >Contract</th>
<th >Signing</th>
<th >OWWA</th>
<th >Visa</th>
<th >OEC</th>
<th >Booking</th>
<th >Ticket</th>
<th >ReBook</th>
<th>% DP</th>
<th >Deployed</th>
<th >Total </th>
</tr>
</thead>
<tbody>
<?php	
include'db.php';
include'tracking.php';
$app = mysql_query("SELECT * FROM employer
where employer_id IN(".implode(",",$nameArray).")
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

$aff = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed) as singils ,applicant.* FROM applicant
WHERE applicant_employer=".$row["employer_id"]."");
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


$expnese88= mysql_query("SELECT SUM(amount/50) as totalamount ,email_address.* FROM email_address
WHERE emp_id='".$row["employer_id"]."'  AND  currency='PHP' AND request_payment=3");
$expnese881=mysql_fetch_array($expnese88);



$oldme8 = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed) AS amount,applicant.* FROM applicant
WHERE  applicant_employer=".$row['employer_id']." AND   applicant_status NOT IN (25,1,21,14,22)   ");
$oldme81=mysql_fetch_array($oldme8);


//$colS = mysql_query("SELECT SUM(amount) as amounPHP ,emp_id,amount FROM email_address6
//WHERE emp_id='".$row["employer_id"]."'  AND  currency='PHP' ");
//$colphp=mysql_fetch_array($colS);

$backout = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed) AS amount,applicant.*  FROM applicant
WHERE applicant_status IN (25,1,21,14) AND applicant_employer=".$row['employer_id']." ");
$backout1=mysql_fetch_array($backout);


$repatresult = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed) AS amount,applicant.*,deployed.* FROM applicant
LEFT JOIN deployed
ON applicant.applicant_id = deployed.deployed_applicant
WHERE applicant_status  IN (22) AND applicant_employer=".$_GET['emp_id']."
AND deployed_date between date_sub(now(), interval 91 day)  AND date_add(now(), interval 1 day)");
$repatresult1=mysql_fetch_array($repatresult);

$singil=$expnese881["totalamount"]+$oldcollect1["amountusd"]+$oldme81["amount"];
$alldeduct=$colusd["amountusd"]+$deduction1["amountusd"]+$backout1["amount"]+$repatresult1["amount"];
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
where applicant_status   IN (35,32)
AND applicant_employer=".$row["employer_id"]." 

");
$visa = mysql_fetch_array($visas);



$sign = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status  NOT IN (9,21,22,1,14,25,28,29,30,31,32,33,34,35,8,12,36)
AND applicant_employer=".$row["employer_id"]." 
AND requirement_contract NOT IN ('1970-01-01','0000-00-00') ");
$signrow = mysql_fetch_array($sign);

$contractsign = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status IN (26,27)
AND applicant_employer=".$row["employer_id"]." 
AND requirement_contract NOT IN ('1970-01-01','0000-00-00')
AND requirement_contract_sign  IN ('1970-01-01','0000-00-00') ");
$contractsign1 = mysql_fetch_array($contractsign);





$transmittal = mysql_query("SELECT 
count(transnum) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status  NOT IN (9,21,22,1,14,25)
AND applicant_employer=".$row["employer_id"]." 
AND transnum!='' ");
$transmittal1 = mysql_fetch_array($transmittal);


$onprocess = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status    NOT IN (9,21,22,1,14,25,5)
AND requirement_contract  NOT IN ('1970-01-01','0000-00-00','')
AND applicant_employer=".$row["employer_id"]." ");
$onprocess1 = mysql_fetch_array($onprocess);


$onselected= mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status    NOT IN (9,21,22,1,14,25,5)
AND requirement_contract   IN ('1970-01-01','0000-00-00','')
AND applicant_employer=".$row["employer_id"]." ");
$onselected1 = mysql_fetch_array($onselected);


$oec = mysql_query("SELECT 
count(	requirement_oec_release_date) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status   IN (30,31,33)
AND applicant_employer=".$row["employer_id"]." 
 ");
$oec1 = mysql_fetch_array($oec);




$fit2 = mysql_query("SELECT 
count(applicant_id) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status     IN (5)
AND applicant_employer=".$row["employer_id"]." ");
$fitt = mysql_fetch_array($fit2);


$ticket = mysql_query("SELECT 
count(requirement_applicant) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status   IN (12,8)
AND flight_date  IN ('1970-01-01','0000-00-00','')
AND applicant_employer=".$row["employer_id"]." ");
$ticket1 = mysql_fetch_array($ticket);


$ticketwith = mysql_query("SELECT 
count(requirement_applicant) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status   IN (12,8)
AND flight_date  NOT IN ('1970-01-01','0000-00-00','')
AND applicant_employer=".$row["employer_id"]." ");
$ticketwith1 = mysql_fetch_array($ticketwith);


$rebook = mysql_query("SELECT 
count(requirement_applicant) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status   IN (36)

AND applicant_employer=".$row["employer_id"]." ");
$rebook1 = mysql_fetch_array($rebook); 



$owwa = mysql_query("SELECT 
count(certificate_applicant) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant.* FROM applicant
LEFT JOIN 	applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant
where applicant_status  IN (28,29)
AND applicant_employer=".$row["employer_id"]." ");
$owwa1 = mysql_fetch_array($owwa);


$medicals = mysql_query("SELECT 
count(certificate_applicant) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant.* FROM applicant
LEFT JOIN 	applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant
where applicant_status NOT IN (9,21,22,1,14,25)
AND certificate_owwa_to NOT IN ('1970-01-01','0000-00-00','')
AND applicant_employer=".$row["employer_id"]." ");
$medicals1 = mysql_fetch_array($medicals);


$percent=($deployedw['alls']+$onprocess1['alls']);
$percent1=($deployedw['alls']/$percent)*100;
$totalsaudi=($fitt['alls']+$onprocess1['alls']+$deployedw['alls']+$onselected1['alls']);


echo'<tr>';






echo'<td>000'.$row["employer_id"].'</td>';
echo'<td style="text-align:left"><b>'.$row["employer_name"].'</b></td>';
echo'<td  style="text-align:center;color:black;background:yellow"><a href="myreport/line.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$fitt['alls'].'</a></td>';
echo'<td style="text-align:center;color:black"><a href="myreport/undrprocess1.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$onselected1['alls'].'</a></td>';
echo'<td style="text-align:center;color:black"><a href="myreport/undrprocess.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$onprocess1['alls'].'</a></td>';
echo'<td><a href="myreport/contract.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$signrow['alls'].'</a></td>';
echo'<td><a href="myreport/notsigned.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$contractsign1['alls'].'</a></td>';

echo'<td><a href="myreport/owwa.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$owwa1['alls'].'</a></td>';
echo'<td><a href="myreport/visa.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$visa['alls'].'</a></td>';
echo'<td><a href="myreport/oec.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$oec1['alls'].'</a></td>';
echo'<td><a href="myreport/ticket.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$ticket1['alls'].'</a></td>';

echo'<td><a href="myreport/ticket1.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$ticketwith1['alls'].'</a></td>';
echo'<td><a href="myreport/rebook.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$rebook1['alls'].'</a></td>';





if($percent1!=0){ echo'<td style="BACKGROUND:#F08080">'.number_format($percent1,1).' %</td>'; }
if($percent1==0){ echo'<td style="">--</td>'; }
echo'<td  style="background:yellow"><a href="myreport/deployed.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$deployedw['alls'].'</a></td>';
echo'<td style="background:#00BFFF;color:black"><a href="myreport/all.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'" style="color:black">'.$totalsaudi.'</a></td>';


$deployedall=$deployedw['alls']+$deployedall;
$oecall=$oec1['alls']+$oecall;
$visaall=$visa['alls']+$visaall;
$signall=$signrow['alls']+$signall;
$fitall=$fitt['alls']+$fitall;
$onselected12=$onselected1['alls']+$onselected12;
$onprocessall=$onprocess1['alls']+$onprocessall;
$compute=$onprocessall+$deployedall;
$percentall=($deployedall/$compute)*100;
$grandbalance=$balancesheet+$grandbalance;
$ticketsaudi=$ticket1['alls']+$ticketsaudi;
$ticketwithsaudi=$ticketwith1['alls']+$ticketwithsaudi;
$owwasaudi=$owwa1['alls']+$owwasaudi;
$medicalsaudi=$medicals1['alls']+$medicalsaudi;
$totalsaudi1=$totalsaudi+$totalsaudi1;
$transmitalsaudi=$transmittal1['alls']+$transmitalsaudi;
$contractsignsaudi=$contractsign1['alls']+$contractsignsaudi;
$rebook2=$rebook1['alls']+$rebook2;


$perselect=($onprocessall/$totalsaudi1)*100;
$transper=($transmitalsaudi/$totalsaudi1)*100;
$persign=($signall/$totalsaudi1)*100;
$percontract=($contractsignsaudi/$totalsaudi1)*100;
$perowwa=($owwasaudi/$totalsaudi1)*100;
$pervisa=($visaall/$totalsaudi1)*100;
$peroec=($oecall/$totalsaudi1)*100;
$perticket=($ticketsaudi/$totalsaudi1)*100;
$perticket2=($ticketwithsaudi/$totalsaudi1)*100;
$perdeployed=($deployedall/$totalsaudi1)*100;
echo'</tr>';
}
?>
</tbody>
<tr>
<td colspan="2" style="text-align:center ;color:black;font-weight:bold;font-size:18px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Total </td>
<td style="text-align:center;color:black;font-weight:bold;background:yellow;font-size:18px"> <?=$fitall?></td>
<td style="text-align:center;color:black;font-weight:bold;font-size:18px"> <?=$onselected12?> </td>
<td style="text-align:center;color:black;font-weight:bold;font-size:18px"> <?=$onprocessall?> </td>
<td style="text-align:center;color:black;font-weight:bold;font-size:18px"> <?=$signall?> </td>
<td style="text-align:center;color:black;font-weight:bold;font-size:18px"> <?=$contractsignsaudi?></td>
<td style="text-align:center;color:black;font-weight:bold;font-size:18px"> <?=$owwasaudi?> </td>
<td style="text-align:center;color:black;font-weight:bold;font-size:18px"> <?=$visaall?> </td>
<td style="text-align:center;color:black;font-weight:bold;font-size:18px"> <?=$oecall?> </td>
<td style="text-align:center;color:black;font-weight:bold;font-size:18px"> <?=$ticketsaudi?></td>

<td style="text-align:center;color:black;font-weight:bold;font-size:18px"> <?=$ticketwithsaudi?> </td>
<td style="text-align:center;color:black;font-weight:bold;font-size:18px"> <?=$rebook2?> </td>



<td  style="text-align:center;color:black;font-weight:bold;BACKGROUND:#F08080"> <?=number_format($percentall,1)?>%</td>
<td style="text-align:center;color:black;font-weight:bold;background:yellow">  <?=$deployedall?></td>
<td style="text-align:center;color:black;font-weight:bold;background:#00BFFF"> <?=$totalsaudi1?></td>
</tr>
</table>





  </form>
 <div style="clear:both;height:30px"></div>
 
 <?php include'index2.php';?>
<div style="clear:both;height:940px"></div> 
<div style="clear:both;height:40px"></div>
</div>

</div>
</div>

</div>
</div>
</div>


</div>
</div>
</div>
</div>



</body>
<!--  /Body -->
</html>

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
