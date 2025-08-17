
<?php
include'inc/functions.php';
$app = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$_GET['agent']." ");
$row=mysql_fetch_array($app);
?>

<title><?=$row['agent_first']?></title>

<!-- Bootstrap -->
<link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">

<!-- Custom Theme Style -->
<link href="../build/css/custom.min.css" rel="stylesheet">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<style>
#wrapper{
border:0px solid black;
width:90%;
min-height:630px;
height:!important;
margin: 0 auto;	
padding:10px;
font-size:12px;
font-family:calibri;
}
.table-fixed td,th{
font-size:12px;
padding:6px;
}gent_i
}gent_i


</style>

<body style="background:white">






<div id="wrapper">
<button onclick="goBack()">Go Back</button>

<script>
function goBack() {
window.history.back();
}
</script></p>
<div>
<h1 style="font-size:30px;color:black;text-align:center;"><?=$row['agent_first']?> <?=$row['agent_last']?> </h1>

</div>

<div id="mainb" style="height:10px;"></div>

<div class="col-md-12 col-sm-12 col-xs-12">

<?php
$allapplicant = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$_GET['agent']."'");
$allapplicant1 = mysql_fetch_array($allapplicant);


$onprocess = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$_GET['agent']."' 
AND applicant_status IN (8,12,4,3,2,5)
");
$onprocess1 = mysql_fetch_array($onprocess);

$deployed = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$_GET['agent']."' 
AND applicant_status IN (9)
");
$deployed1 = mysql_fetch_array($deployed);


$repat = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$_GET['agent']."' 
AND applicant_status IN (22)
");
$repat1 = mysql_fetch_array($repat);


$pass = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$_GET['agent']."' 
AND applicant_status IN (15,0,10)
");
$pass1 = mysql_fetch_array($pass);


$not = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$_GET['agent']."' 
AND applicant_status IN (1,14,21,7)
");
$not1 = mysql_fetch_array($not);


?>


<ul class="stats-overview">
<li>
<span class="name">Total Applicants</span>
<span class="value text-success"><?=$allapplicant1['countme']?>  </span>
</li>

<li>
<span class="name">Available / Passporting</span>
<span class="value text-success"><?=$pass1['countme']?>  </span>
</li>

<li>   
<span class="name"> Backout/Cancelled/Unfit</span>
<span class="value text-success"> <?=$not1['countme']?> </span>
</li>


</ul>



<ul class="stats-overview">
<li>   
<span class="name"> On Process Applicants</span>
<span class="value text-success"> <?=$onprocess1['countme']?> </span>
</li>

<li class="hidden-phone">
<span class="name"> Deployed Applicants</span>
<span class="value text-success"> <?=$deployed1['countme']?> </span>
</li>

<li>
<span class="name"># Repat Applicants</span>
<span class="value text-success"> <?=$repat1['countme']?> </span>
</li>

</ul>



<?PHP
$cr1 = mysql_query("SELECT sum(receivable) as total FROM  email_address4 where  agent_id=".$_GET['agent']."
AND currency='PHP'
AND type_status IN ('Agent','Repat')");
$cr1row = mysql_fetch_array($cr1);


$cr2 = mysql_query("SELECT sum(amount) as total FROM  email_address where  agent_id=".$_GET['agent']."
AND currency='PHP'
AND account IN ('Cash Advance agent')");
$cr2row = mysql_fetch_array($cr2);

$collected1 = mysql_query("SELECT sum(amount) as total FROM  email_address6 where  agent_id=".$_GET['agent']."
AND currency='PHP'");
$collectedphp = mysql_fetch_array($collected1);

$tph=$cr1row['total']+$cr2row['total'];
$balancephp=$tph-$collectedphp['total'];

$tph1 = number_format($tph, 2);
$balancephp1 = number_format($balancephp, 2);
$collectedphp1 = number_format($collectedphp['total'], 2);
?>

<ul class="stats-overview">
<li>   
<span class="name">TOTAL CREDIT PESO : </span>
<span class="value text-success"  STYLE="color:red;font-size:15px;letter-spacing:1px"> <?=$tph1?> </span>
</li>

<li class="hidden-phone">
<span class="name"> TOTAL Collected :</span>
<span class="value text-success"  STYLE="color:blue;font-size:15px;letter-spacing:1px"> <?=$collectedphp1?> </span>
</li>

<li>
<span class="name" >BALANCE : </span>
<span class="value text-success"  STYLE="color:green;font-size:15px;letter-spacing:1px"> <?=$balancephp1?> </span>
</li>

</ul>



<?PHP
$cr1usd = mysql_query("SELECT sum(receivable) as total FROM  email_address4 where  agent_id=".$_GET['agent']."
AND currency='USD'
AND type_status IN ('Agent','Repat')");
$cr1rowusd = mysql_fetch_array($cr1usd);


$cr2usd = mysql_query("SELECT sum(amount) as total FROM  email_address where  agent_id=".$_GET['agent']."
AND currency='USD'
AND account IN ('Cash Advance agent')");
$cr2rowusd = mysql_fetch_array($cr2usd);

$collected1usd = mysql_query("SELECT sum(amount) as total FROM  email_address6 where  agent_id=".$_GET['agent']."
AND currency='USD'");
$collectedusd = mysql_fetch_array($collected1usd);

$tusd=$cr1rowusd['total']+$cr2rowusd['total'];
$balancephp=$tusd-$collectedusd['total'];

$tus = number_format($tusd, 2);
$balanceusd = number_format($balancephp, 2);
$collectedusd = number_format($collectedusd['total'], 2);
?>

<ul class="stats-overview">
<li>   
<span class="name">TOTAL CREDIT $ : </span>
<span class="value text-success"  STYLE="color:red;font-size:15px;letter-spacing:1px"> <?=$tus?> </span>
</li>

<li class="hidden-phone">
<span class="name"> TOTAL Collected :</span>
<span class="value text-success"  STYLE="color:blue;font-size:15px;letter-spacing:1px"> <?=$collectedusd?> </span>
</li>

<li>
<span class="name" >BALANCE : </span>
<span class="value text-success"  STYLE="color:green;font-size:15px;letter-spacing:1px"> <?=$balanceusd?> </span>
</li>

</ul>




<br />

<div id="mainb" style="height:10px;"></div>
</div>




<!-- page content -->
<div class="right_col" role="main" >
<div class="">


<div class="clearfix"></div>

<div class="row" >
<div class="col-md-12" >
<div class="x_panel"  style="background:#FFFFF0">

<div class="x_content">

<section class="content invoice">
<div class="row invoice-info">




<div class="col-sm-2 invoice-col pull-right" style="">
<a href="collection_agent.php?agent=<?=$_GET['agent']?>"><button type="button" class="btn btn-round btn-info pull-right btn-sm">Add Collection</button></a>
<a href="exp_agent.php?agent=<?=$_GET['agent']?>"><button type="button" class="btn btn-round btn-DANGER pull-right btn-sm">Add Charge / Repat</button></a>
</div>








</div>

</BR>



<div class="container">
<ul class="nav nav-tabs">
<li class="active"><a data-toggle="tab" href="#home">Commision List</a></li>
<li><a data-toggle="tab" href="#menu1">Total Credit / Cash Advance</a></li>
<li><a data-toggle="tab" href="#menu2">Collected List</a></li>

</ul>

<div class="tab-content">
<div id="home" class="tab-pane fade in active">

			<div class="col-xs-12 table">
			</br></br>
			
			<h5 style="color:GREEN">Agent Commission</h5>
			<table class="table table-fixed">
			<thead>
			<tr>
			<th>Date Transaction</th>
			<th>VOUCHER NO.</th>
			<th style="width: 26%">Applicant</th>
			<th>Method</th>
			<th>Account</th>
			<th >Description</th>
			<th>Currency</th>
			<th>Amount</th>
			</tr>

			</thead>
			<tbody>
			<?PHP
			$app = mysql_query("SELECT * FROM email_address where  agent_id=".$_GET['agent']."
			AND account='Commission'
			order by date_ad desc");
			while($row=mysql_fetch_array($app))
			{





			$totalamountUSD=$row['amount']+$totalamountUSD;


			echo'<tr>';
			echo'<td>'; echo date('Y-M-d', strtotime($row['date_ad']));echo'</td>';
			echo'<td>'.$row["ref"].'</td>';
			echo'<td>';
			$list = mysql_query("SELECT * FROM e_liq where l_status=0
			AND l_e_d=".$row['e_id']."
			and l_type='Agent'
			order by l_id desc");
			while($select=mysql_fetch_array($list))

			{
			$app1 = mysql_query("SELECT * FROM applicant where applicant_id=".$select['liq_app']." ");
			$row1=mysql_fetch_array($app1);
			echo'<p><div  style="width:210px;border:0px solid black;float:left">'.$row1["applicant_first"].' '.$row1["applicant_last"].' </div>
			<div style="width:100px;float:left;color:blue;FONT-SIZE:11PX">'.$select2222["l_currency"].''.$select["l_amount"].'  <small>('.$select["type_applicant"].') </small></div></p>
			';
			}
			echo' </td>';
			echo'<td>'.$row["method"].'</td>';
			echo'<td>'.$row["account"].'</td>';
			echo'<td>'.$row["description"].'</td>';
			echo'<td  style="text-align:right">'.$row["currency"].'</td>';
			echo'<td  style="text-align:right">'.$row["amount"].'</td>';
			echo'</tr>';


			}
			?>	
			<tr>
			<td style="color:green;text-align:right;font-size:12px" colspan="9"><?php echo number_format($totalamountUSD,2);?></td>
			</tr>	
			</tbody>
			</table>
			</div>
</div>




<div id="menu1" class="tab-pane fade">
		<div class="col-xs-12 table">
		</br></br>
		<h5 style="color:red">Total Credit (Charge to Agent)</h5>
		<table class="table table-fixed">
		<thead>
		<tr>
		<th style="width: 20%">Applicant</th>
		<th>Date</th>
		<th style="width: 20%">Agent</th>
		<th style="">Type</th>
		<th style="width: 20%">Description</th>
		<th style="text-align:right">Charge To</th>
		<th style="text-align:right">Currency</th>
		<th style="text-align:right">Amount</th>
		<th style="text-align:right">Status</th>
		</tr>
		</thead>
		<tbody>
		<?PHP
		$app11 = mysql_query("SELECT * FROM email_address4 where  agent_id=".$_GET['agent']."
		AND type_status IN ('Agent','Repat')
		order by date_transaction desc");
		while($row2=mysql_fetch_array($app11))
		{
		$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row2['agent_id']." ");
		$row1=mysql_fetch_array($app1);

		$app12 = mysql_query("SELECT * FROM applicant where applicant_id=".$row2['app_id']." ");
		$row12=mysql_fetch_array($app12);	
		
		
		$totalamountex=($row2['receivable']+$row2["othercharge"])+$totalamountex;
		if($row2["status_paid"]==0){$status1='Not Paid';}
		if($row2["status_paid"]==1){$status1='Paid';}


		echo'<tr>';
		echo'<td>'.$row12["applicant_first"].' '.$row12["applicant_last"].'</td>';			
		echo'<td>'.$row2["date_transaction"].'</td>';
		echo'<td>'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
		echo'<td>'.$row2["exp_type"].'</td>';
		echo'<td>'.$row2["findings"].'</td>';
		echo'<td style="text-align:right">'.$row2["type_status"].'</td>';

		echo'<td style="text-align:right">'.$row2["currency"].'</td>';
		echo'<td style="text-align:right">'.$row2["receivable"].'</td>';
		echo'<td style="text-align:right;font-size:10px;color:Red">
		<a href="edit_unfit.php?exp='.$row2["exp_id"].'" target="_blank" style="color:red;font-size:10px;">View</a></td>';
		echo'</tr>';
		}
		?>	
		<tr>
		<td style="color:red;text-align:right;font-size:12px" colspan="9"><?php// echo number_format($totalamountex,2);?></td>
		</tr>	
		</tbody>
		</table>
		</div>





		<div class="col-xs-12 table">

		<h5 style="color:red">Total Cash Advance </h5>
		<table class="table table-fixed">
		<thead>
		<tr>
		<th STYLE="width:1%"></th>
		<th STYLE="width:2%">#</th>
		<th STYLE="width:10%">DATE</th>
		<th STYLE="width:10%">VOUCHER NO.</th>
		<th STYLE="width:10%">Applicant</th>
		<th STYLE="width:5%">Type</th>
		<th STYLE="width:10%">Type of Expenses</th>
		<th STYLE="width:15%">Description</th>
		<th STYLE="width:5%">CURRENCY</th>
		<th STYLE="width:5%">AMOUNT</th>
		</tr>
		</thead>
		<tbody>
		<?PHP
		

		
		$app33 = mysql_query("SELECT * FROM email_address
		where account  IN ('Cash Advance agent')
		AND agent_id=".$_GET['agent']."
		order by date_ad,e_id asc");

		while($row222=mysql_fetch_array($app33))
		{
		$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row222['agent_id']." ");
		$row1=mysql_fetch_array($app1);

		$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$row222['app_id']." ");
		$rowp=mysql_fetch_array($applicant);

		$cashadvance=$row222['amount']+$cashadvance;



		echo'<tr>';
		echo'<td></td>';
		echo'<td>000'.$row222["e_id"].'</td>';
		echo'<td>'; echo date('Y-M-d', strtotime($row222['date_ad']));echo'</td>';

		echo'<td>'.$row222["ref"].'</td>';
		echo'<td>';
			$listrrrr = mysql_query("SELECT * FROM e_liq where l_status=0
			AND l_e_d=".$row222['e_id']."
			and l_type='Agent'
			order by l_id desc");
			while($select2222=mysql_fetch_array($listrrrr))

			{
			$app1222 = mysql_query("SELECT * FROM applicant where applicant_id=".$select2222['liq_app']." ");
			$row122=mysql_fetch_array($app1222);
			echo'<p><div  style="width:210px;border:0px solid black;float:left">'.$row122["applicant_first"].' '.$row122["applicant_last"].' </div>
			<div style="width:100px;float:left;color:blue;FONT-SIZE:11PX">'.$select2222["l_currency"].''.$select2222["l_amount"].' </div> <small>('.$select2222["type_applicant"].') </small></p>
			';
			}
			echo' </td>';
		echo'<td>'.$row222["method"].'</td>';
		echo'<td>'.$row222["account"].'</td>';
		echo'<td>'.$row222["description"].'</td>';
		echo'<td>'.$row222["currency"].'</td>';
		echo'<td>'.$row222['amount'].'</td>';
		echo'</tr>';

		}
		?>	
		<tr>
		<td style="color:red;text-align:right;font-size:12px" colspan="11"><?php //echo number_format($cashadvance,2);?></td>
		</tr>	
		</tbody>
		</table>
		</div>
</div>




<div id="menu2" class="tab-pane fade">
<div class="col-xs-12 table">
</br></br>
<h5 style="color:blue">Total Collection </h5>
<table class="table table-fixed">
<thead>
<tr>
<th STYLE="width:1%"></th>
<th STYLE="width:2%">#</th>
<th STYLE="width:10%">DATE</th>

<th>OR #</th>
<th>Applicant</th>
<th>Particular</th>
<th>account</th>

<th>Paid Through </th>
<th>Remarks </th>
<th>Currency</th>
<th>Amount</th>
</tr>
</thead>
<tbody>
<?PHP
$colec = mysql_query("SELECT * FROM email_address6
where agent_id=".$_GET['agent']."
ORDER BY tr_id desc");

while($rcolec=mysql_fetch_array($colec))
{


$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$row222['app_id']." ");
$rowp=mysql_fetch_array($applicant);

$cashadvance=$row222['amount']+$cashadvance;



echo'<tr>';
echo'<td></td>';
echo'<td style="font-size:1px">'.$rcolec["tr_id"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($rcolec['date']));echo'</td>';
echo'<td>'; echo date('md', strtotime($rcolec['date']));echo'</td>';
echo'<td>
'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td>'.$rcolec["particular"].'</td>';
echo'<td>'.$rcolec["account"].'</td>';
echo'<td>'.$rcolec["source_m"].'</td>';
echo'<td>'.$rcolec["particular"].'</td>';
echo'<td>'.$rcolec["currency"].'</td>';
echo'<td>'.$rcolec["amount"].'</td>';

echo'</tr>';

}
?>	
<tr>
<td style="color:blue;text-align:right;font-size:12px" colspan="11"><?php //echo number_format($cashadvance,2);?></td>
</tr>	
</tbody>
</table>
</div>
</div>

</div>
</div>

<!-- Table row -->

























</section>

</div>
</div>
</div>
</div>
</div>
<!-- /page content -->


</div>

</body>