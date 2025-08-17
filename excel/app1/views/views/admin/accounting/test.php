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
                    <?php $total = 0; ?>
                    <?php $ctr = 0; ?>
                    <?php $currentGroup = '';  ?>
                    <?php foreach ( $transactions as $transaction ): ?>
                    <?php if ( $currentGroup != $transaction['employer_id'] ): ?>
                    <?php $currentGroup = $transaction['employer_id']; ?>
                <?php $ctr ++; ?>
                    <tr>
                        <td colspan="8" style="text-align:left; ">
                            Employer: <strong><span style="color:blue"><?php echo $transaction['employer_name']; ?></span></strong>
                        </td>
                    </tr>
                    <tr>
                        <th width="1%">#</th>
                        <th>OR#</th>
                        <th>Remarks</th>
                        <th>Transaction Date</th>
                        <th>VATable Sales</th>
                        <th>VAT 12%</th>
                        <th>Total Sales</th>
                    </tr>
                    <?php endif; ?>
                    <?php $total += $transaction['or_amount'] + ( $transaction['or_amount'] * 0.12 ); ?>
                    <tr>
                        <td><?php echo $ctr; ?></td>
                        <td><?php echo $transaction['or_number'] ?></td>
                        <td><?php echo $transaction['or_remarks']; ?></td>
                        <td><?php echo fdate('Y-m-d', $transaction['or_date'], '--'); ?></td>
                        <td>P<?php echo number_format($transaction['or_amount'], 2); ?></td>
                        <td>P<?php echo number_format($transaction['or_amount'] * 0.12, 2); ?></td>
                        <td>P<?php echo number_format($transaction['or_amount'] + ( $transaction['or_amount'] * 0.12 ), 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if ( ! $ctr ): ?>
                    <tr>
                        <td colspan="9" align="center">No records</td>
                    </tr>
                    <?php else: ?>
                    <tr>
                        <td colspan="6"></td>
                        <td align="left">Total: P <?php echo number_format( $total, 2 ); ?></td>
                    </tr>
                    <?php endif; ?>

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
</body>
</html>