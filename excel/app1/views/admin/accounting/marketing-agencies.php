<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo $app->getTitle(); ?></title>
    <?php $app->renderStyles(); ?>
</head>
<body>
    
    <!-- #wrapper -->
    <div id="wrapper">
    
        <!-- #header -->
        <div id="header">
            <h1><?php echo $app->getInfo()['applicationDescription']; ?></h1>
        </div>
        <!-- endOf: #header -->
        
        <!-- #header2 -->
        <div id="header2">
            <h2><?php echo $app->getTitle(); ?></h2>
            <p>&nbsp;</p>
            <p>Period of <span class="date-filter"><?php echo fdate( 'F, d Y', $dateFrom); ?></span> to <span class="date-filter"><?php echo fdate( 'F, d Y', $dateTo); ?></span></p>
        </div>
        <!-- endOf: #header2 -->   
        
        <!-- #content -->
        <div id="content">
            <table>
                <tbody>
                    <?php $txn = 0; ?>
                    <?php $total = 0; ?>
                    <?php $currentGroup = 0; ?>
                    <?php foreach ( $agencies as $agency ): ?>
                    <?php $txn ++; ?>
                    <?php if ( $currentGroup !== $agency['agency_id'] ): ?>
                    <?php $currentGroup = $agency['agency_id']; ?>
                    <?php $txn = 1; ?>
                    <?php if ( $total > 0 ): ?>
                    <tr>
                        <td colspan="10"></td>
                        <td align="left">Total: P <?php echo number_format( $total, 2 ); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td colspan="10" style="text-align:left; ">
                            Marketing Agency: <strong><span style="color:blue"><?php echo $agency['agency_name']; ?></span></strong>
                        </td>
                    </tr>
                    <tr>
                        <th width="1%">#</th>
                        <th>Voucher#</th>
                        <th>Company</th>
                        <th>Applicants</th>
                        <th>Remarks</th>
                        <th>Transaction Date</th>
                        <th>Amount</th>
                        <th>with Holding Tax (10%)</th>
                        <th>Total Amount Due (net of vat)</th>
                    </tr>
                    <?php $total = 0; ?>
                    <?php endif; ?>
                    <?php $total += ($agency['voucher_amount'] - ( $agency['voucher_amount'] * 0.1 ))/1.12; ?>
                    <tr>
                        <td><?php echo $txn; ?></td>
                        <td><?php echo $agency['voucher_number']; ?></td>
                        <td><?php echo $agency['employer_name']; ?></td>
                        <td><?php echo $agency['applicant_first'].' '.$agency['applicant_last']; ?></td> 
                        <td><?php echo $agency['voucher_remarks']; ?></td>
                        <td><?php echo fdate('Y-m-d', $agency['voucher_created'], '--'); ?></td>
                        <td>P<?php echo number_format($agency['voucher_amount'], 2); ?></td>
                        <td>P<?php echo number_format($agency['voucher_amount'] * 0.1, 2); ?></td>
                        <td>P<?php echo number_format(($agency['voucher_amount'] - ( $agency['voucher_amount'] * 0.1 ))/1.12, 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if ( $total > 0 ): ?>
                    <tr>
                        <td colspan="8"></td>
                        <td align="left">Total: P <?php echo number_format( $total, 2 ); ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>    
            </table>

            <div>
                <p>Total no. of transactions: <strong><?php echo number_format( $txn ); ?></strong>.</p>
            </div>
        </div>
        <!-- endOf: #content -->   
        
    </div>
    <!-- endOf: #wrapper -->

    <div style="clear:both" align="center">
        <p>&nbsp;</p>
        <a href="#" id="btn-print" role="button">Print</a>
    </div>        

    <script src="<?php echo $app->getPath()['scripts']; ?>jquery-2.0.3.min.js"></script>
    <?php $app->renderScripts(); ?>
    <script type="text/javascript">
        $(document).ready(function() {
            $('a#btn-print').on('click', function( event ) {

                event.preventDefault(); 

                var options = { 
                    mode:       'popup', 
                    popClose:   false, 
                    extraCss:   '',
                    retainAttr: ['class', 'id', 'style'],
                    extraHead:  '<meta charset="utf-8" />,<meta http-equiv="X-UA-Compatible" content="IE=edge"/>'
                };

                $('#wrapper').printArea( options );
            });     
        });
    </script>
</body>
</html>