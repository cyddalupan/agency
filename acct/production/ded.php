
		<?php
		include'inc/functions.php';
	
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
		font-size:15px;
		padding:1px;
		color:black;
		}


		</style>


		<body style="background:white">
		<div class="row" style="width:90%;margin:0 auto">


<h2>DEDUCTION</h2>


	<table class="table table-fixed">
<thead>
<tr>

<th STYLE="width:10%">DATE</th>
<th STYLE="width:10%">Agent</th>
<th STYLE="width:10%">Applicant</th>
<th STYLE="width:25%">Remarks</th>
<th STYLE="width:5%">Amount</th>

</tr>
</thead>
<tbody>
<?php





$app = mysql_query("SELECT * FROM  deduction

LEFT JOIN  	applicant
ON deduction.app_id = applicant.applicant_id
where agent_id=".$_GET['agent']."
 order by deduction_id desc  ");


while($row=mysql_fetch_array($app))
{


$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);
$totel=$row["deduction_amount"]+$totel;

echo'<tr>';
echo'<td>'; echo date('Y-M-d', strtotime($row['deduction_date']));echo'</td>';
echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$row["applicant_first"].' '.$row["applicant_last"].'</td>';
echo'<td>'.$row["deduction_remarks"].'</td>';
echo'<td> '.number_format($row["deduction_amount"], 2).'</td>';

echo'</tr>';


}

?>
<tr>
<td style="text-align:right;font-size:22px;FONT-WEIGHT:bold" colspan="6">TOTAL:  <?php echo number_format($totel,2);?></td>
</tr>


</tbody>
</table>

		</div>	
		
				</div>	