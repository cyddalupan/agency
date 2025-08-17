<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo $app->getTitle(); ?></title>
	
    <?php $app->renderStyles(); ?>
</head>
<body>

    <div class="container">
        <div id="wrapper">
            <div class="header">
                <div>
                    <img class="img-responsive" src="<?php echo $app->getPath()['images']; ?>logo.jpg" align="STEPUPMANPOWER logo">
                </div>
                <p>STEPUPMANPOWER Voucher No. <span class="voucher-no"><?php echo $voucher['voucher_number']; ?></span></p>
            </div>
            <div class="statement">
                <table>
                    <tbody>
                        <tr>
                            <th>Date:</th>
                            <td><?php echo fdate('m/d/Y', $voucher['voucher_created']); ?></td>
                        </tr>
                        <tr>
                            <th>Received by:</th>
                            <td>
                                <span>
                                <?php if ( ! empty( $voucher['marketing-agency'] ) ): ?>
                                <?php echo $voucher['marketing-agency']['agency_name']; ?>
                                <?php elseif ( ! empty( $voucher['marketing-agent'] ) ): ?>
                                <?php echo $voucher['marketing-agent']['agent_first'].' '.$voucher['marketing-agent']['agent_last']; ?>
                                <?php elseif ( ! empty( $voucher['recruitment-agent'] ) ): ?>
                                <?php echo $voucher['recruitment-agent']['agent_first'].' '.$voucher['recruitment-agent']['agent_last']; ?>
                                <?php endif; ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
						
                            <th>Amount: </th>
                            <td>P <span><?php echo number_format($voucher['voucher_amount'], 2); ?></span></td>
						</tr>
						<tr>
						<th>Less: </th>
						<td></td>
						</tr>
						<tr>
						<th>WithHolding Tax: </th>							 
						<?php if ( $voucher['recruitment-agent'] || $voucher['marketing-agent'] ): { ?>
						<td>P <span><?php echo number_format( $voucher['voucher_amount'] * 0, 2); ?>
						</td>							
						<?php } ?>								 
						<?php endif; ?>	
						
						<?php if ($voucher['marketing-agency'] ): { ?>                           
						<td>P <span><?php echo number_format( $voucher['voucher_amount'] * 0.1, 2); ?></td>				
						<?php } ?>
						<?php endif; ?>						
						</tr>
						<tr>
						
						
						<th>Total Amount Due (net of vat): </th>	
						<?php if ( $voucher['recruitment-agent'] || $voucher['marketing-agent'] ): { ?>
						<td>P <span><?php echo number_format( $voucher['voucher_amount']  , 2); ?>
						</td>
						<?php } ?>
						<?php endif; ?>		
						
						<?php if ($voucher['marketing-agency'] ): { ?>
						<td>P <span><?php 
						$total_amount_due=$voucher['voucher_amount'] - ( $voucher['voucher_amount'] * 0.1);
						$vat_of_net=$total_amount_due/1.12;
						echo number_format( $vat_of_net , 2); ?></td>
						<?php } ?>
						<?php endif; ?>
						</tr>

                        
                    </tbody>
                </table>
            </div>

            <?php if ( count( $voucher['workers'] ) > 0 ): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Employer</th>
                        <th>Date deployed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $voucher['workers'] as $worker ): ?>
                    <tr>
                        <td><?php echo $worker['applicant_first'].' '.$worker['applicant_last'] ?></td>
                        <td><?php echo $worker['employer_name'] ?></td>
                        <td><?php echo $worker['deployed_date']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <div class="footer">
                <p>Date printed: <?php echo date('m/d/Y h:iA', time()); ?></p>
                <p>Remarks:</p>
            </div>
        </div>
        
    </div>   

    
</body>
</html>