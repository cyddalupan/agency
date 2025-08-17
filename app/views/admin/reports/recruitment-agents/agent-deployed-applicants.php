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
            <?php if ( ! empty( $employer ) ): ?>
            <p class="employer">Employer: <?php echo $employer['employer_name']; ?></p>
            <?php endif; ?>
            <p>Period of <span class="date-filter"><?php echo fdate( 'F, d Y', $dateFrom); ?></span> to <span class="date-filter"><?php echo fdate( 'F, d Y', $dateTo); ?></span></p>
        </div>
    	<!-- endOf: #header2 -->   
        
        <!-- #content -->
        <div id="content">
        	<table>
                <tbody>
                    <tr>
                        <th width="1%">#</th>
                        <th>Applicant #</th>
                        <th>Applicant name</th>
                        <th>Employer</th>
                        <th>Job Offer</th>
                        <th>Country</th>
                        <th>Offer Salary</th>
                        <th>Date deployed</th>
                        <th>Remarks</th>
                    </tr>
                    <?php $ctr = 0; ?>
                    <?php foreach ($agent['applicants'] as $applicantId => $applicant): ?>
                    <?php $ctr ++; ?>
                    <tr>
                        <td><?php echo $ctr; ?></td>
                        <td><?php echo $_SESSION["settings"]['client_short']; ?>-<?php echo str_pad( $applicant['applicant_id'], 7, '0', STR_PAD_LEFT ); ?></td>
                        <td><?php echo $applicant['applicant_first'].' '.$applicant['applicant_last']; ?></td>
                        <td><?php echo $applicant['employer_name'] ?></td> 
                        <td><?php echo $applicant['job_name']; ?></td>
                        <td><?php echo $applicant['country_name'] ?></td>
                        <td><?php echo $applicant['requirement_offer_salary'] ?></td>
                        <td><?php echo fdate( 'Y-m-d', $applicant['deployed_date'], '0000-00-00' ); ?></td>
                        <td><?php echo $applicant['deployed_remarks']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if ( ! $ctr ): ?>
                    <tr>
                        <td colspan="9" align="center">No records</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div>
                <p>Total deployed applicants: <strong><?php echo number_format( $ctr ); ?></strong>.</p>
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