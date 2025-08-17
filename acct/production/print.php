
		<?php
		include'inc/functions.php';
		$app = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$_GET['agent']." ");
		$row=mysql_fetch_array($app);
		?>

		<title>Accounting System</title>

		<!-- Bootstrap -->
		<link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
		<!-- Font Awesome -->
		<link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
		<!-- NProgress -->
		<link href="../vendors/nprogress/nprogress.css" rel="stylesheet">
		<!-- iCheck -->
		<link href="../vendors/iCheck/skins/flat/green.css" rel="stylesheet">

		<!-- bootstrap-progressbar -->
		<link href="../vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
		<!-- JQVMap -->
		<link href="../vendors/jqvmap/dist/jqvmap.min.css" rel="stylesheet"/>
		<!-- bootstrap-daterangepicker -->
		<link href="../vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">

		<!-- Custom Theme Style -->
		<link href="../build/css/custom.min.css" rel="stylesheet">
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
		padding:1px;
		}


		</style>

		<body style="background:white">
		<div class="row" style="width:90%;margin:0 auto">
		<H3 style="font-size:30px;color:black;text-align:center;TEXT-TRANSFORM:UPPERCASE"><?=$row['agent_first']?> <?=$row['agent_last']?></H3>
	<?php
	$sum = mysql_query("SELECT SUM(amount) as amountt,amount,agent_id FROM email_address6 where  agent_id=".$_GET['agent']."
		AND currency='PHP'
		order by date desc");
	$sumphp=mysql_fetch_array($sum);

$sumusd = mysql_query("SELECT SUM(amount) as amountt,amount,agent_id FROM email_address6 where  agent_id=".$_GET['agent']."
		AND currency='USD'
		order by date desc");
	$sumusd2=mysql_fetch_array($sumusd);	
	
		$appPHP = mysql_query("SELECT SUM(amount_paid) as amountt,amount_paid,agent_id,receivable,currency FROM email_address4 where  agent_id=".$_GET['agent']."  AND currency='PHP'
		and receivable!=0
		");	
		$appsump=mysql_fetch_array($appPHP);
	
	
			$appusd = mysql_query("SELECT SUM(amount_paid) as amountt,amount_paid,agent_id,receivable,currency FROM email_address4 where  agent_id=".$_GET['agent']."  AND currency='USD'
		and receivable!=0
		");	
		$appsumu=mysql_fetch_array($appusd);
	
	
	
	?>

	
<?PHP IF($sumphp['amountt']!=''){ ?>
		<div style="clear:both"></div>
		<h2 style="color:Red">PAID IN PESO</h2>
		<table class="table table-fixed">
		<thead>
		<tr>
		<th>Date</th>
		<th>OR #</th>
		<th style="width: 59%">Description</th>
		<th>Amount</th>
		</tr>

		</thead>
		<tbody>
		<?PHP
		$col = mysql_query("SELECT * FROM email_address6 where  agent_id=".$_GET['agent']."
		AND currency='PHP'
		order by date desc");
		while($colphp=mysql_fetch_array($col))
		{
		$colphp1=$colphp['amount']+$colphp1;
		echo'<tr>';
		echo'<td>'; echo date('Y-M-d', strtotime($colphp['date']));echo'</td>';
		echo'<td>'.$colphp["or_num"].'</td>';
		echo'<td>'.$colphp["particular"].'</td>';
		echo'<td  style="text-align:right">'.$colphp["amount"].'</td>';
		echo'</tr>';
		}
		?>	
		<tr>
		<td style="color:Red;text-align:right;font-size:14px" colspan="5"><?php echo number_format($colphp1,2);?></td>
		</tr>	
		</tbody>
		</table>
		<style type="text/css">
		h1{
		page-break-before: always;
		}
		</style>
		<DIV style="page-break-after:always"></DIV>
		
<?PHP } //END OF PHP FLTER 1?>		
		
		
<?PHP IF($sumusd2['amountt']!=''){ ?>
		<h2 style="color:Red">PAID IN USD</h2>
		<table class="table table-fixed">
		<thead>
		<tr>
		<th>Date</th>
		<th>OR #</th>
		<th style="width: 59%">Description</th>
		<th>Amount</th>
		</tr>

		</thead>
		<tbody>
		<?PHP
		$col2 = mysql_query("SELECT * FROM email_address6 where  agent_id=".$_GET['agent']."
		AND currency='USD'
		order by date desc");
		while($colusd=mysql_fetch_array($col2))
		{
		$colusd2=$colusd['amount']+$colusd2;
		echo'<tr>';
		echo'<td>'; echo date('Y-M-d', strtotime($colusd['date']));echo'</td>';
		echo'<td>'.$colusd["or_num"].'</td>';
		echo'<td>'.$colusd["particular"].'</td>';
		echo'<td  style="text-align:right">'.$colusd["amount"].'</td>';
		echo'</tr>';
		}
		?>	
		<tr>
		<td style="color:Red;text-align:right;font-size:14px" colspan="5"><?php echo number_format($colusd2,2);?></td>
		</tr>	
		</tbody>
		</table>


		<style type="text/css">
		h1{
		page-break-before: always;
		}
		</style>
		<DIV style="page-break-after:always"></DIV>

	<?PHP } //END OF USD FLTER 2?>		
		
		
	<?PHP IF($appsump['amountt']!=''){ ?>	
		<h2 style="color:ORANGE;height:30px">FOR PAYMENT PHP</h2>

		<table class="table table-fixed">
		<thead>

		<tr>
		<th style="width: 20%">Applicant</th>
		<th>Date</th>
		<th style="">Type</th>
		<th style="width: 20%">Description</th>
		<th style="text-align:right">Currency</th>
		<th style="text-align:right">Receivable</th>
		<th style="text-align:right">Amount Paid</th>
		<th>Date Paid</th>
		<th style="text-align:right">Action</th>

		</tr>

		</thead>
		<tbody>
		<?PHP
		$app = mysql_query("SELECT * FROM email_address4 where  agent_id=".$_GET['agent']."  AND currency='PHP'
		and receivable!=0 and type_status='charge'
		order by app_id desc ");
		while($row=mysql_fetch_array($app))
		{
		$total=$row['receivable']+$total;
			$profile = mysql_query("SELECT * FROM applicant where applicant_id=".$row['app_id']." ");
					$profilerow=mysql_fetch_array($profile);
		echo'<tr>';
		echo'<td>'.$profilerow["applicant_first"].' '.$profilerow["applicant_last"].'</td>';
		echo'<td>'.$row["date_transaction"].'</td>';
		echo'<td>'.$row["exp_type"].'</td>';
		echo'<td>'.$row["findings"].'</td>';
		echo'<td style="text-align:right">'.$row["currency"].'</td>';
		echo'<td style="text-align:right">'.$row["receivable"].'</td>';
		echo'<td  style="text-align:right">'.$row["amount_paid"].'</td>';
		echo'<td>'.$row["date_paid"].'</td>';
		echo'<td  style="text-align:right">'.$row["user"].'</td>';
		echo'</tr>';
		}
		?>	
		<tr>
		<td style="color:black;text-align:right;font-size:14px" colspan="6"> <?php echo number_format($total,2);?></td>
		</tr>	
		<tr>
		<td style="color:blue;text-align:right;font-size:14px" colspan="6">PAID:  <?php echo number_format($colphp1,2);?></td>
		</tr>
		<tr>
		<td style="color:Red;text-align:right;font-size:14px" colspan="6">GRAND TOTAL:  <?php echo number_format($php=$total-$colphp1,2);?></td>
		</tr>	
		</tbody>
		</table>


		<style type="text/css">
		h1{
		page-break-before: always;
		}
		</style>
		
		<DIV style="page-break-after:always"></DIV>
<?PHP } //END OF USD FLTER 3?>

	<?PHP IF($appsumu['amountt']!=''){ ?>	

		<h2 style="color:GREEN">FOR PAYMENT USD</h2>

		<form action="" method="post">

		<table class="table table-fixed">
		<thead>


		<tr>

		<th style="width: 20%">Applicant</th>
		<th>Date</th>
		<th style="">Type</th>
		<th style="width: 20%">Description</th>
		<th style="text-align:right">Currency</th>
		<th style="text-align:right">Receivable</th>
		<th style="text-align:right">Amount Paid</th>
		<th>Date Paid</th>
		<th style="text-align:right">Action</th>
		</tr>

		</thead>
		<tbody>
		<?PHP
		$app1 = mysql_query("SELECT * FROM email_address4 where  agent_id=".$_GET['agent']." AND currency='USD'
		order by app_id desc");
		while($row1=mysql_fetch_array($app1))
		{
		$total1=$row1['receivable']+$total1;
			$profile1 = mysql_query("SELECT * FROM applicant where applicant_id=".$app1['app_id']." ");
					$profilerow1=mysql_fetch_array($profile1);
		echo'<tr>';
		echo'<td>'.$profilerow1["applicant_first"].' '.$profilerow1["applicant_last"].'</td>';
					echo'<td>'.$row1["date_transaction"].'</td>';
		echo'<td>'.$row1["exp_type"].'</td>';
		echo'<td>'.$row1["findings"].'</td>';
		echo'<td style="text-align:right">'.$row1["currency"].'</td>';
		echo'<td style="text-align:right">'.$row1["receivable"].'</td>';
		echo'<td  style="text-align:right">'.$row1["amount_paid"].'</td>';
		echo'<td>'.$row1["date_paid"].'</td>';
		echo'<td  style="text-align:right">'.$row1["user"].'</td>';
		echo'</tr>';
		}
		?>	
		</div>
		<tr>
		<td style="color:black;text-align:right;font-size:14px" colspan="6">  <?php echo number_format($total1,2);?></td>
		</tr>
			<tr>
			<td style="color:blue;text-align:right;font-size:14px" colspan="6">PAID:  <?php echo number_format($colusd2,2);?></td>
			</tr>
			<tr>
			<td style="color:Red;text-align:right;font-size:14px" colspan="6">GRAND TOTAL:  <?php echo number_format($usd=$total1-$colusd2,2);?></td>
			</tr>	

		</tbody>
		</table>
		</form>	
<?PHP } //END OF USD FLTER 4?>
		</div>		


		</body>