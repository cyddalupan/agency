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
                    <?php if ( count( $employers ) > 0 ): ?>
                    <?php foreach ( $employers as $employerId => $employer ): ?>
                    <tr>
                        <td colspan="4" style="text-align:left; ">
                            Employer: <strong><span style="color:blue"><?php echo $employer['employer_name']; ?></span></strong>
                        </td>
                    </tr>
                    <tr>
                        <th>#</th>
                        <th>Worker</th>
                        <th>Total</th>
                        <th>Balance</th>
                    </tr>
                    <?php $total = $ctr = 0; ?>
                    <?php foreach ( $employer['workers'] as $applicantId => $worker ): ?>
                    <?php echo $ctr ++; ?>
                    <?php $total += $worker['bill_employer_cost'] - $worker['bill_employer_deposit']; ?>
                    <tr>
                        <td><?php echo $ctr; ?></td>
                        <td><?php echo $worker['applicant_first'].' '.$worker['applicant_last']; ?></td>
                        <td><?php echo number_format( $worker['bill_employer_cost'], 2 ); ?></td>
                        <td><?php echo number_format( $worker['bill_employer_cost'] - $worker['bill_employer_deposit'], 2 ); ?></td>                        
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" align="right">Total Balance:</td>
                        <td>P <?php echo number_format( $total, 2 ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="9" align="center">No records</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
 
        </div>
        <!-- endOf: #content -->   
        
    </div>
    <!-- endOf: #wrapper -->
    
    <?php if ( count( $employers ) > 0 ): ?>
    <div style="clear:both" align="center">
        <p>&nbsp;</p>
        <a href="#" id="btn-print" role="button">Print</a>
    </div>        
    <?php endif; ?>

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