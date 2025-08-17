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
        	<p class="date-filter">Filter date start: <?php echo fdate( 'F,d Y', $dateFrom); ?></p>
        	<p class="date-filter">Filter date end: <?php echo fdate( 'F,d Y', $dateTo); ?></p>
        </div>
    	<!-- endOf: #header2 -->   
        
        <!-- #content -->
        <div id="content">

            <?php foreach ( $applicants as $applicant ): ?>
            <table>
                <thead>
                    <tr>
                        <th colspan="6">
                            <p><strong><?php echo $applicant['applicant_last'].', '.$applicant['applicant_first'] ?></strong></p>
                            <p><small>Date Applied: <?php echo $applicant['applicant_date_applied']; ?></small></p>
                        </th>
                    </tr>
                    <tr>
                        <th width="10%">Date Added</th>
                        <th width="20%">Status</th>
                        <th width="10%">Date Status</th>
                        <th>Remarks History</th>
                        <th width="15%">Country</th>
                        <th width="10%">Updated By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $applicant['logs'] as $log ): ?>
                    <tr>
                        <td><?php echo fdate( 'Y-m-d', $log['log_created'] ); ?></td>
                        <td><span style="color:#f03"><?php echo $statusText[ $log['log_status'] ]; ?></span></td>
                        <td><?php echo fdate( 'Y-m-d', $log['log_date'] ); ?></td>
                        <td><span style="color:#f03"><?php echo $log['log_remarks']; ?></span></td>
                        <td><?php echo $log['country_name']; ?></td>
                        <td><?php echo $log['user_name']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p>&nbsp;</p>
            <?php endforeach; ?>

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