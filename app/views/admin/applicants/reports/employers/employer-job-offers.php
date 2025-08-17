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
            <p class="employer">Employer: <?php echo $employer['employer_name']; ?></p>
        	<p class="">Marketing Agency: <?php echo $employer['agency_name']; ?></p>
        	<p class="">Marketing Agent: <?php echo $employer['agent_first'].' '.$employer['agent_last']; ?></p>
        </div>
    	<!-- endOf: #header2 -->   
        
        <!-- #content -->
        <div id="content">
        	<table id="applicants">
            	<tbody>
                	<tr>
                    	<th width="1%">#</th>
                        <th>Job title</th>
                        <th>Workers needed</th>
                        <th>Remaining slot</th>
                        <th>Offer salary</th>
                        <th>Total revenue</th>
                        <th>Remarks</th>
                        <th>Created</th>
                    </tr>
                </tbody>
                <tbody>
                    <?php if ( count( $employer['job-offers'] ) == 0 ): ?>
                    <tr>
                        <td colspan="6" align="center">-- No records --</td>
                    </tr>
                    <?php endif; ?>
                	<?php $ctr = 0; ?>
                	<?php foreach ( $employer['job-offers'] as $job ): ?>
                	<?php $ctr ++; ?>
                	<tr>
                    	<td><?php echo $ctr; ?></td>
                    	<td><?php echo $job['job_name']; ?></td>
                    	<td><?php echo $job['job_total']; ?></td>
                    	<td><?php echo $job['job_total'] - $job['job_occupied'] ; ?></td>
                    	<td><strong>P</strong>&nbsp;<?php echo number_format( $job['job_salary_from'], 2); ?> &minus; <strong>P</strong>&nbsp;<?php echo number_format( $job['job_salary_to'], 2); ?></td>
                    	<td><strong>P</strong>&nbsp;<?php echo number_format( $job['job_revenue'], 2); ?></td>
                    	<td><?php echo $job['job_remarks']; ?></td>
                    	<td><?php echo fdate( 'Y-m-d h:ia', $job['job_created'] ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div>
                <p>Total job offers: <strong><?php echo number_format( count( $employer['job-offers'] ) ); ?></strong>.</p>
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