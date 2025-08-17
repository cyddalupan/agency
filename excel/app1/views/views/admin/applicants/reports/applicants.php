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
        	<?php if ( ! $groupByEmployer ): ?>
        	<p class="employer">Employer: <?php echo $employer['employer_name']; ?></p>
        	<?php endif; ?>
        	<p class="date-filter">Filter date start: <?php echo fdate( 'F,d Y', $dateFrom); ?></p>
        	<p class="date-filter">Filter date end: <?php echo fdate( 'F,d Y', $dateTo); ?></p>
        </div>
    	<!-- endOf: #header2 -->   
        
        <!-- #content -->
        <div id="content">
        	<table id="applicants">
            	<tbody>
                	<tr>
                    	<th width="1%">#</th>
                        <th>Applicant #</th>
                        <th>Date applied</th>
                        <th>Applicant</th>
                        <th>Recruitment Agent</th>
                        <th>Job Offer</th>
                        <th>Position</th>
                        <th>Country</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Last modified</th>
                    </tr>
                </tbody>
                <tbody>
                    <?php if ( count( $applicants ) == 0 ): ?>
                    <tr>
                        <td colspan="10" align="center">-- No records --</td>
                    </tr>
                    <?php endif; ?>

                    <?php $ctr = 0; ?>
                    <?php $currentGroup = ''; ?>
                    <?php foreach ( $applicants as $applicant ): ?>
                    <?php if ( $groupByEmployer && $currentGroup != $applicant['applicant_employer'] ): ?>
            	    <?php $currentGroup = $applicant['applicant_employer']; ?>
                    <?php if ( ! empty( $applicant['employer_name'] ) ): ?>
                	<tr>
                		<td colspan="10" align="center">
                		<strong><span class="employer"><?php echo $applicant['employer_name']; ?></span></strong>
                		</td>
                	</tr>
                    <?php endif; ?>
                	<?php endif; ?>
                    <?php $ctr ++; ?>
                    <tr>
                    	<td><?php echo $ctr; ?></td>
                        <td><?php echo $_SESSION["settings"]['client_short']; ?>-<?php echo str_pad( $applicant['applicant_id'], 7, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo fdate( 'Y-m-d', $applicant['applicant_date_applied'] ); ?></td>
                        <td><?php echo $applicant['applicant_name']; ?></td>
                        <td><?php echo $applicant['agent_first'].' '.$applicant['agent_last']; ?></td>
                        <td><?php echo $applicant['job_name']; ?></td>
                        <td><?php echo $applicant['position_name']; ?></td>
                        <td><?php echo $applicant['country_name']; ?></td>
                        <td><?php echo $statusText[$applicant['applicant_status']]; ?></td>
                        <td><?php echo $applicant['applicant_remarks']; ?></td>
                        <td><?php echo fdate( 'Y-m-d h:ia', $applicant['applicant_updated'] ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div>
                <p>Total applicants: <strong><?php echo number_format( count( $applicants ) ); ?></strong>.</p>
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