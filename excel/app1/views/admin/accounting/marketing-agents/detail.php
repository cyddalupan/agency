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
            <p>Marketing Agent: <strong><?php echo $agent['agent_first'].' '.$agent['agent_last']; ?></strong></p>
            <p>Period of <span class="date-filter"><?php echo fdate( 'F, d Y', $dateFrom); ?></span> to <span class="date-filter"><?php echo fdate( 'F, d Y', $dateTo); ?></span></p>
        </div>
        <!-- endOf: #header2 -->   
        
        <!-- #content -->
        <div id="content">
            <table>
                <thead>
                    <tr>
                        <th width="1%">#</th>
                        <th>Voucher#</th>
                        <th>Company</th>
                        <th>Applicants</th>
                        <th>Remarks</th>
                        <th>Transaction Date</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( count( $agent['transactions'] ) == 0 ): ?>
                    <tr>
                        <td colspan="9" align="center">No records</td>
                    </tr>
                    <?php endif; ?>
                    <?php $ctr = 0; ?>                    
                    <?php foreach ($agent['transactions'] as $voucher): ?>
                    <?php $ctr ++; ?>
                    <tr>
                        <td><?php echo $ctr; ?></td>
                        <td><?php echo $voucher['voucher_number']; ?></td>
                        <td><?php echo $voucher['employer_name']; ?></td>
                        <td><?php echo $voucher['applicant_first'].' '.$voucher['applicant_last']; ?></td> 
                        <td><?php echo $voucher['voucher_remarks']; ?></td>
                        <td><?php echo fdate('Y-m-d', $voucher['voucher_created'], '--'); ?></td>
                        <td>P<?php echo number_format($voucher['voucher_amount'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table> 

            <div>
                <p>Total no. of transactions: <strong><?php echo number_format( $ctr ); ?></strong>.</p>
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