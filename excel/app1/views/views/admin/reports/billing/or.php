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
                <p>STEPUPMANPOWER OR No. <span class="voucher-no"><?php echo $or['or_number']; ?></span></p>
            </div>
            <div class="statement">
                <table>
                    <tbody>
                        <?php if ( ! is_null( $or['employer_name'] ) ): ?>
                        <tr>
                            <th>Employer:</th>
                            <td>
                                <span>
                                <?php echo $or['employer_name'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php if ( ! is_null( $or['applicant_first'] ) ): ?>
                        <tr>
                            <th>Worker:</th>
                            <td>
                                <span>
                                <?php echo $or['applicant_first'].' '.$or['applicant_last']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php if ( count( $or['applicants'] ) > 0 ): ?>
                        <tr>
                            <th>Worker<?php echo count( $or['applicants'] ) > 1? 's' : ''; ?>:</th>
                            <td>
                                <?php foreach ( $or['applicants'] as $applicant ): ?>
                                <p>
                                <span>
                                <?php echo $applicant['applicant_first'].' '.$applicant['applicant_last']; ?>
                                </span>
                                </p>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr>						<?PHP						$VATable=$or['or_amount']/ 1.12;						$VAT=$VATable * .12;						?>
                            <th>VATable Sales: </th>
                            <td>P <span><?php echo number_format($VATable,2); ?></span></td>
                        </tr>
                        <tr>
                            <th>VAT 12%: </th>
                            <td>P <span><?php echo number_format($VAT, 2); ?></span></td>
                        </tr>
                        <tr>
                            <th>Total Sales: </th>
                            <td>P <span><?php echo number_format( $or['or_amount'], 2); ?></span></td>
                        </tr>
                        
                    </tbody>
                </table>
            </div>
            <div class="footer">
                <p>Date printed: <?php echo date('m/d/Y h:iA', time()); ?></p>
            </div>
        </div>
        
    </div>
 
    <script src="<?php echo $app->getPath()['scripts']; ?>jquery-2.0.3.min.js"></script>
    <?php $app->renderScripts(); ?>
</body>
</html>