<style>
body{
background:white;	
}
#wrapper1{
border:0px solid black;	
min-height:800px;
WIDTH:100%;
height:!important;
padding:10px;
float:left;
background:white;	
}
#wrapper{
border:0px solid black;	
min-height:800px;
height:!important;
padding:10px;
float:left;
background:white;	
}
#header{
border:0px solid black;	
width:1000px;
height:60px;
height:!important;
float:left;
font-family:calibri;
}
#content1{
border:0px solid black;	
width:60px;
height:!important;
float:left;
font-size:17px;
margin:2px;
font-style:italic;
font-family:calibri;
}
#content2{
border-bottom:0px solid black;	
width:490px;
height:!important;
float:left;
font-size:17px;
margin:2px;
font-family:calibri;
}

</style><!-- Page Breadcrumb -->
<div id="wrapper1">

<div id="wrapper">
<div id="header"><img src="http://abbaphil.com/assets/images/admin/logo_abba.png" style="height:70px"></div>
<div style="clear:both;height:40px"></div>	

<div id="content1">Client : </div>
<div id="content2"><b><?php echo $employer['employer_name']; ?></b></div>	

<div id="content1" style="width:40px">Date:</div>
<div id="content2" style="width:100px"><b>June 7, 2016</b></div>	
<div style="clear:both"></div>	
<div id="content1" style="width:70px">Address : </div>
<div id="content2"><b><?php echo $employer['employer_address']; ?></b></div>	
<div id="content1" style="width:60px">Terms: </div>
<div id="content2" style="width:300px"><b> THRU Transfer</b></div>

<div style="clear:both;height:20px"></div>	


		<table class="table table-hover table-condensed" style="width:1000px;background:white">

		<?php $totalAmount = $totalCredit = $totalDebit = 0; ?>
		<?php foreach ( $workers as $worker ): ?>
		<?php   $amount = $credit = $debit = 0; ?>
		<?php   foreach ( $worker['breakdown'] as $fee ): ?>
		<?php       $amount += $fee['detail_employer_cost']; ?>
		<?php       $credit += ( $fee['detail_employer_cost'] - $fee['detail_employer_deposit'] ); ?>
		<?php       $debit  += $fee['detail_employer_deposit']; ?>
		<?php   endforeach; ?>
		<?php if ( ! $credit ){
		continue;
		} ?>
		<?php   $totalAmount += $amount; ?>
		<?php   $totalCredit += $credit; ?>
		<?php   $totalDebit  += $debit; ?>
		<thead>
		<tr>
		<th>Date</th>
		<th>REF #</th>
		<th>Applicant</th>
		<th>Job Offer</th>
		<th>Description</th>
		<th>Amount</th>
	
		<th>&nbsp;</th>
		</tr>
		</thead>
		<tbody>
		<tr>
		<td><?php echo fdate( 'm/d/Y', $worker['bill_created'], '0000-00-00' ); ?></td>
		<td>SOA-<?php echo str_pad( $worker['bill_id'], 6, '0', STR_PAD_LEFT ); ?></td>
		<td>
		<strong><?php echo $worker['applicant_first'].' '.$worker['applicant_last']; ?></strong>
		</td>
		<td><?php echo $worker['job_name']; ?></td>
		<td width="15%">
		<?php foreach ( $worker['breakdown'] as $fee ): ?>

		<?php $isPaid       = $fee['detail_employer_deposit'] >= $fee['detail_employer_cost']; ?>
		<p class="fee-text">
		<?php echo $fee['fee_name']; ?>
		<?php if ( $isPaid ): ?>
		&nbsp;<span class="text-success"><i class="fa fa-check"></i></span>
		<?php endif; ?>
		</p>
		<?php endforeach; ?>
		</td>
		<td width="15%">
		<?php foreach ( $worker['breakdown'] as $fee ): ?>
		<p class="fee-amount"> $ &nbsp;<span class="text-default"><?php echo number_format( $fee['detail_employer_cost'], 2 ); ?></span></p>
		<?php endforeach; ?>
		</td>
		
	
		
		</tr>

		<tr>
		<td colspan="3" align="center">&nbsp;</td>
		<td STYLE="FONT-SIZE:16PX;text-align:right" colspan="2">
		<p class="fee-text">TOTAL AMOUNT DUE:</p>                                                    
		</td>
		<td STYLE="FONT-SIZE:16PX">
		<p class="fee-amount"><strong>$ &nbsp;<span class="text-default"><?php echo number_format( $amount, 2 ); ?></span></strong></p>
		</td>
		
		
		<td>&nbsp;</td>
		</tr>
		</tbody>
		<?php endforeach; ?>
		
		
		</table>
<div style="clear:both;height:20px"></div>		
<div id="header"><img src="http://abbaphil.com/ben.PNG" style=""></div>
<div style="clear:both;height:50px"></div>	
</div>

<div style="clear:both;height:20px"></div>	
</div>