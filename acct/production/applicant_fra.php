<?php include'inc/header2.php';
include'tracking.php';
include'inc/col.php';


if(isset($_POST['datasev']))
{
$applicant = mysql_query("UPDATE applicant
SET agent_ppt=".$_POST['agent_ppt']."

,agent_ftw=".$_POST['agent_ftw']."
,agent_contract=".$_POST['agent_contract']."
,agent_deployed=".$_POST['agent_deployed']."


WHERE applicant_id=".$_GET['app']." ");  
ECHo "Save!";
}
$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$_GET['app']." ");
$row=mysql_fetch_array($applicant);

$agent = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$agent1=mysql_fetch_array($agent);




?>

<style>
input{
color:black;
font-size:16px;		
}

body{
color:black;
font-size:16px;	
}
#customers {
font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
border-collapse: collapse;
width: 100%;
font-size:16px;
MARGIN:10PX;
color:black;

}

#customers td, #customers th {
border: 1px solid #ddd;
padding: 4px;

}
#customers td{
color:black;
text-transform:capitalized;	
}
#customers a{
color:#7a7a7a;	
text-transform:capitalized;	
}


#customers th {
text-align: left;
background:#DCDCDC;
color: white;
color:black;
border-bottom:2px solid #4CAF50;
 font-size:16px;
}

input{
width:100px;	
}
</style>
<body class="nav-md" style="background:white">



<!-- page content -->
<div class="right_col" role="main">
<div class="">


<div class="clearfix"></div>

<div class="row" style="width:90%;margin:0 auto;margin-top:20px;margin-bottom:20px">
<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">

<div class="x_content">







<DIV style="clear:both;height:30px"></DIV>
<?php $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>
<a href="<?=$actual_link;?>" style="color:Red;text-align:right">Refresh</a>






<table id="customers" style="width:80%">
<tr>
<td><b>Name # :</b>  <?=$row["applicant_last"]?>, <?=$row["applicant_first"]?> <?=$row["applicant_middle"]?></td>
<td><b>Agent Name :  </b><?=$agent1["agent_first"]?> <?=$agent1["agent_last"]?></td>

</tr>
</table>


<H2 style="color:blue;text-align:Center"><b>Agent Agreement</b></H1>
</ul>
<div id="" class="tab-content">
<div role="tabpanel"  class="" id="tab_content1" >

<?php
if($row['applicant_paid']==1){$mypaid="PAID";}
if($row['applicant_paid']==0){$mypaid="UNPAID";}
?>

<form class="form-horizontal form-label-left input_mask" method="post" action="" style="width:800px">	













<div class="form-group"  >
<label class="control-label col-md-3 col-sm-3 col-xs-12">WITH PASSPORT</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="agent_ppt" value="<?=$row['agent_ppt']?>" REQUIRED>
</div>
</div>

<div class="form-group"  >
<label class="control-label col-md-3 col-sm-3 col-xs-12">FIT TO WORK</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="agent_ftw" value="<?=$row['agent_ftw']?>" REQUIRED>
</div>
</div>

<div class="form-group"  >
<label class="control-label col-md-3 col-sm-3 col-xs-12">WITH CONTRACT</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="agent_contract" value="<?=$row['agent_contract']?>" REQUIRED>
</div>
</div>

<div class="form-group"  >
<label class="control-label col-md-3 col-sm-3 col-xs-12">DEPLOYED</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="agent_deployed" value="<?=$row['agent_deployed']?>" REQUIRED>
</div>
</div>




<div class="form-group">
<div class="col-md-12 col-sm-9 col-xs-12">	

<button type="submit" class="btn btn-danger pull-right"  name="datasev">Save</button>
</div>
</div>


</form>		


</div>









<script>
function exportF(elem) {
  var table = document.getElementById("customers");
  var html = table.outerHTML;
  var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url 
  elem.setAttribute("href", url);
  elem.setAttribute("download", "export.xls"); // Choose the file name
  return false;
}
</script>



<DIV style="clear:both;height:50px"></DIV>

<a id="downloadLink" onclick="exportF(this)" style="color:Red;FLOAT:right;font-size:14px;padding:5px">Export File</a> 
<a href="ded.php?agent=<?=$agent1['agent_id']?>" target="_blank" style="color:blue;FLOAT:right;font-size:14px;padding:5px">Print Deducton</a> 

<H2 style="color:blue;text-align:Center"><b>Summary Reports</b></H1>
<table  cellspacing="0" id="customers">
<thead>
<tr>

<th STYLE="width:1%">Code #</th>



<th >Request</th>
<th>DATE</th>
<th>Agent</th>
<th >Applicant</th>
<th>Type</th>
<th >Type of Expenses</th>
<th>Payment To</th>
<th STYLE="">Requesting</th>
<th >Description</th>

<th >CURRENCY</th>
<th>AMOUNT</th>


</tr>
</thead>
<tbody>
<?php	


$agent = mysql_query("SELECT email_address.*
,applicant.applicant_first
,applicant.applicant_middle
,applicant.applicant_last
,applicant.applicant_id FROM email_address
LEFT JOIN  	applicant
ON email_address.app_id = applicant.applicant_id
where  account  IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND agent_id=".$agent1['agent_id']."
order by date_ad  asc");





 


while($agentrow=mysql_fetch_array($agent))
{


$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$agentrow['agent_id']." ");
$row1=mysql_fetch_array($app1);

$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$agentrow['app_id']." ");
$rowp=mysql_fetch_array($applicant);

$USER = mysql_query("SELECT * FROM  user where user_id=".$agentrow['staff_id']." ");
$rowu=mysql_fetch_array($USER);


$LIQ = mysql_query("SELECT sum(l_amount) as cashfund FROM  e_liq 
where l_status=0 AND l_e_d=".$agentrow['e_id']."
and l_type='Agent'
");
$cashliq=mysql_fetch_array($LIQ);

$bank = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$agentrow['fund_bank_id']." ");
$bank1=mysql_fetch_array($bank);

$source = mysql_query("SELECT * FROM  source_payment where s_id=".$agentrow['fund_source_id']." ");
$source1=mysql_fetch_array($source);

if($agentrow["request_payment"]==0){$request='For Approval';$rcolor='black';}
if($agentrow["request_payment"]==1){$request='Approved';$rcolor='blue';}
if($agentrow["request_payment"]==2){$request='For Release';$rcolor='orange';}
if($agentrow["request_payment"]==3){$request='Released';$rcolor='green';}
if($agentrow["request_payment"]==4){$request='Decline';$rcolor='red';}
if($agentrow["request_payment"]==5){$request='Return to maker';$rcolor='red';}

echo'<tr>';

echo'<td>000'.$agentrow["e_id"].'</td>';




echo'<td style="font-size:12px;color:white;">
<i style="background:'.$rcolor.';padding:2px;margin:2px">'.$request.'</i>
</td>';
echo'<td>'; echo date('Y-M-d', strtotime($agentrow['date_ad']));echo'</td>';
echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$agentrow["applicant_first"].' '.$agentrow["applicant_last"].'</td>';

echo'<td>'.$agentrow["method"].'</td>';
echo'<td>'.$agentrow["account"].'</td>';
echo'<td>'.$agentrow["payment_to"].'</td>';
echo'<td>'.$rowu["user_fullname"].'</td>';
echo'<td>'.$agentrow["description"].'</td>';



echo'<td>'.$agentrow["currency"].'</td>';
echo'<td>'.$agentrow['amount'].'</td>';
echo'</tr>';

if($agentrow["currency"]=="USD" && $agentrow["request_payment"] =4 ){ $tetalpaid=$agentrow['amount']*50;}
if($agentrow["currency"]=="PHP" && $agentrow["request_payment"] =4) { $tetalpaid=$agentrow['amount'];}
else{$tetalpaid=0; }
$mytotalpaids=$tetalpaid+$mytotalpaids;
}

$mytodeduct = mysql_query("SELECT SUM(deduction_amount) AS totaldeduct, deduction.* FROM deduction
where agent_id=".$agent1['agent_id']." ");
$mytodeduct1=mysql_fetch_array($mytodeduct);
$mydeduct=$mytodeduct1['totaldeduct'];


$myagree = mysql_query("SELECT SUM(agent_ppt+agent_ftw+agent_contract+agent_deployed) AS totaldeduct, applicant.* FROM applicant
where applicant_source=".$agent1['agent_id']." ");
$myagree1=mysql_fetch_array($myagree);
$mytotalcoms=$myagree1['totaldeduct'];


$debitko=$mytotalpaids+$mydeduct;
$decredit=$mytotalcoms-$debitko;
?>

<tr>
<td style="FONT-WEIGHT:bold;text-align:right;font-size:22px;background:orange" colspan="14"> AGREECOM ₱:
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo number_format($mytotalcoms,2);?></td>
</tr>

<tr>
<td style="FONT-WEIGHT:bold;text-align:right;font-size:22px;background:lightblue" colspan="14"> RELEASED ₱:
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo number_format($mytotalpaids,2);?></td>
</tr>


<tr>
<td style="FONT-WEIGHT:bold;text-align:right;font-size:22px;background:lightblue" colspan="14"> DEDUCTION ₱:
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo number_format($mydeduct,2);?></td>
</tr>



<tr>
<td style="FONT-WEIGHT:bold;text-align:right;font-size:22px;background:black;color:white" colspan="14"> BALANCE ₱:
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo number_format($decredit,2);?></td>
</tr>

</tbody>
</table>














</div>
</div>


</div>
</div>
</div>
</div>
</div>
</div>
<!-- /page content -->

<!-- footer content -->





</body>
</html>