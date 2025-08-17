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
        	<h1>Repat Report</h1>
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
        	<table id="applicants">
            	<tbody>
                    <tr>
                        <th>#</th>
                        <th>Applicant #</th>
                        <th>Repat Date</th>
                        <th>Applicant</th>
                        <th>Status</th>
                        <th>Gender</th>
                        <th>Remarks</th>
                    </tr>
                    <?php $cnt = 0; ?>
                    <?php foreach ($app_repat as $app): ?>
                    <?php $cnt++; ?>
                    <tr>
                        <td><?php echo $cnt; ?></td>
                        <td><?php echo $_SESSION["settings"]['client_short']; ?>-<?php echo str_pad( $app->applicant_id , 7, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo $app->repat_date; ?></td>
                        <td><?php echo $app->applicant_first; ?> <?php echo $app->applicant_middle; ?> <?php echo $app->applicant_last; ?></td>
                        <td><?php echo $statusText[$app->applicant_status]; ?></td>
                        <td><?php echo $app->applicant_gender; ?></td>
                        <td><?php echo $app->applicant_remarks; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    	<!-- endOf: #content -->   
        
    </div>
    <!-- endOf: #wrapper -->


    <script src="<?php echo $app->getPath()['scripts']; ?>jquery-2.0.3.min.js"></script>
    <?php $app->renderScripts(); ?>
</body>
</html>