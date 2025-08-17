<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo $app->getTitle(); ?></title>
	<?php $app->renderStyles(); ?>
</head>
<body>
	
    <!-- #wrapper -->
    <div id="wrapper1" style="border:0px solid black;width:1300px">
    
    	<!-- #header -->
    	<div id="header">
        	<h1><?php echo $app->getInfo()['applicationDescription']; ?></h1>
        </div>
    	<!-- endOf: #header -->
        
    	<!-- #header2 -->
        <div id="header2">
        	<h1><?php echo $app->getTitle(); ?></h1>
        	<p>&nbsp;</p>
        	<p class="date-filter">Date: <?php echo fdate( 'F,d Y', $dateFrom); ?> &minus;&minus; <?php echo fdate( 'F,d Y', $dateTo); ?></p>
        </div>
    	<!-- endOf: #header2 -->   
        
        <!-- #content -->
        <div id="content" style="border:0px solid black;width:1200px">
            <?php $statusGroup = null; ?>
           <table  style="width:80%;border:0px solid red">
			 <?php if ( $statusGroup =='' &&  $status ): ?>
					 <thead style="width:900px">
                    <tr>
                        <th>Applicant #</th>
                        <th>Date Applied</th>
                        <th>Recruitment Agent</th>
                        <th>Name</th>
                        <th>Age</th>
						<th  style="width:50px"> Contact #</th>
						<th>Passport</th>
						<th>NBI</th>
						<th>Red Ribbon</th>
						<th>Prom Exam</th>
						<th style="width:50px">Medical</th>
						<th>VISA</th>
						<th>OEC</th>
						<th>OWWA</th>
						<th>Ticket</th>
		
                        <th>Job Offer</th>
                        <th>Pref. Position</th>
                        <th>Pref. Country</th>
						
						<th>Remarks</th>
						
                    </tr>
                </thead> 
			   <?php endif; ?>	
                <tbody>
            <?php foreach ( $applicants as $applicant ): ?>
            <?php if ( $statusGroup !== $applicant['applicant_status'] && ! $status ): ?>
            <?php $statusGroup = $applicant['applicant_status']; ?>
			
						
			
			
			
                </tbody>
            </table>
            <p>&nbsp;</p>
            <h3 style="color:#08BDF9"><?php echo strtoupper( $statusText[ $statusGroup ] ); ?> APPLICANTS</h3>
				   <table  style="width:900px">
					
						 <thead style="width:900px">
                    <tr>
                        <th>Applicant #</th>
                        <th>Date Applied</th>
                        <th>Recruitment Agent</th>
                        <th>Name</th>
                        <th>Age</th>
						<th>Contact #</th>
						<th>Passport</th>
						<th>NBI</th>
						<th>Red Ribbon</th>
						<th>Prom Exam</th>
						<th>Medical</th>
						<th>VISA</th>
						<th>OEC</th>
						<th>OWWA</th>
						<th>Ticket</th>
		
                        <th>Job Offer</th>
                        <th>Pref. Position</th>
                        <th>Pref. Country</th>
						
						<th>Remarks</th>
						
                    </tr>
                </thead> 
					
                <tbody>
				
				
            <?php endif; ?>
                <tr>
                    <td><?php echo str_pad( $applicant['applicant_id'], 7, '0', STR_PAD_LEFT ); ?> </td>
                    <td><?php echo $applicant['applicant_date_applied']; ?></td>
                    <td><?php echo $applicant['agent_first'].' '.$applicant['agent_last']; ?></td>
                    <td><?php echo $applicant['applicant_name']; ?></td>
                    <td><?php echo $applicant['applicant_age']; ?></td>
					
						<td>
						<?php if ($_POST['contact'] == 1 ): ?>
						<span class="text-success"><?php echo $applicant['applicant_contacts']; ?></span>
						
						<?php endif; ?>
						</td>
					
					
					
					 <td><?php echo $applicant['passport_number']; ?></td>
					 
						<td align="center">
						<?php if (  $applicant['certificate_authenticated_nbi'] == 1 ): ?>
						<span class="text-success"><OK</span>
						<?php else: ?>
						--
						<?php endif; ?>
						</td>
						
						<td align="center">
						<?php if (  $applicant['certificate_authenticated'] == 1 ): ?>
						<span class="text-success"><OK</span>
						<?php else: ?>
						--
						<?php endif; ?>
						</td>
						<td>--</td>
						
						
					
						<td><?php echo $applicant['certificate_medical_clinic']; ?> </br> <b><?php echo $applicant['certificate_medical_result']; ?></b></td>
						<td align="center">
						<?php if (  $applicant['requirement_visa_release_date'] != '0000-00-00' ): ?>
						<span class="text-success"><?php echo $applicant['requirement_visa_release_date']; ?></span>
						<?php else: ?>
						--
						<?php endif; ?>
						</td>
						
					
					
					<td><?php echo $applicant['requirement_oec_number']; ?></td>
					
					<td><?php echo $applicant['requirement_owwa_certificate']; ?></td>	
					<td><?php echo $applicant['requirement_ticket']; ?></td>	
					
                    <td><?php echo $applicant['job_name']; ?></td>
                    <td><?php echo $applicant['position_name']; ?></td>
                    <td><?php echo $applicant['country_name']; ?></td>
					<td><?php echo $applicant['applicant_remarks']; ?></td>
                </tr>
            <?php endforeach; ?>
                </tbody>
            </table>
          
            <p>&nbsp;</p>
 
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