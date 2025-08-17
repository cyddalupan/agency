<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo $app->getTitle(); ?></title>
	<?php $app->renderStyles(); ?>
</head>
<style>
table {
	border:1px solid #ccc;
	
	background-color: #fff;
		margin-bottom: 0;
	border-collapse: collapse;
	border-spacing: 0;
	width:1400px;

}
table tr td {
	padding:3px;
	font-size:13px;
	white-space: nowrap;
	border: 1px solid #ddd;
}


</style>

<body>
	
    <!-- #wrapper -->
    <div id="wrapper1" style="border:0px solid black">
    
    	<!-- #header -->
    	<div id="header">
        	<h1><?php echo $app->getInfo()['applicationDescription']; ?></h1>
        </div>
    	<!-- endOf: #header -->
        
    	<!-- #header2 -->
        <div id="header2">
        	<h1><?php echo $app->getTitle(); ?></h1>
            <?php if(isset($employer['employer_name'])){ ?>
            <h2><small>Employer</small> - <?php echo $employer['employer_name']; ?></h2>
            <?php }//end if search has employer ?>
        	<p>&nbsp;</p>
        	<p class="date-filter">Date: <?php echo fdate( 'F,d Y', $dateFrom); ?> &minus;&minus; <?php echo fdate( 'F,d Y', $dateTo); ?></p>
        </div>
    	<!-- endOf: #header2 -->   
        
        <!-- #content -->
		 	
        <div id="content" style="border:0px solid black;width:1100px">
            <?php $statusGroup = null; ?>

           <table>
			 <?php if ( $statusGroup =='' &&  $status ): ?>
					 <thead>
                    <tr>
						<th>#</th>
                        <th>Applicant #</th>
						<th>Code</th>
                        <th>Date Applied</th>
                        <th>Recruitment Agent</th>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Gender</th>
						<th>Contact #</th>
						<th>Passport</th>
						<th>Medical</th>
						<th>Result</th>
						<th>VISA</th>
						<th>OEC</th>
						<th>OWWA</th>
						<th>Ticket</th>
					
		
                        <th>Job Offer</th>
                   
						<th>Remarks</th>
						
						
					
						
						
                    </tr>
                </thead> 

			   <?php endif; ?>	
                <tbody>
			<?php $ctr = 1; ?>	
            <?php foreach ( $applicants as $applicant ): ?>
            <?php if ( $statusGroup !== $applicant['applicant_status'] && ! $status ): ?>
				<?php $ctr=1; ?>  
            <?php $statusGroup = $applicant['applicant_status']; ?>
			
			
			
			
                </tbody>
            </table>
            <p>&nbsp;</p>
            <h3 style="color:#08BDF9"><?php echo strtoupper( $statusText[ $statusGroup ] ); ?> APPLICANTS</h3>
				   <table>
					
						 <thead>
                    <tr>
                        <th>#</th>
						<th>Applicant #</th>
						<th>Code</th>
                        <th>Date Applied</th>
                        <th>Recruitment Agent</th>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Gender</th>
						<th>Contact #</th>
						<th>Passport</th>
						<th>Medical</th>
						<th>Result</th>
						<th>VISA</th>
						<th>OEC</th>
						<th>OWWA</th>
						<th>Ticket</th>
					

                        <th>Pref. Position</th>
                     
						<th>Remarks</th>	
						
					
						
						
                    </tr>
                </thead> 
					
                <tbody>
				
				
            <?php endif; ?>
		
                <tr>
					<td><?php echo $ctr; ?></td>
                    <td><?php echo str_pad( $applicant['applicant_id'], 7, '0', STR_PAD_LEFT ); ?> </td>
					<td>	
					<b>
					<?php 
					if($applicants_raw[$applicant['applicant_id']]->applicantNumber != '')
					echo ''.$applicants_raw[$applicant['applicant_id']]->applicantNumber; 
					?></b>
					</td>
				   <td><?php echo $applicant['applicant_date_applied']; ?></td>
                    <td><?php echo $applicant['agent_first'].' '.$applicant['agent_last']; ?></td>
                    <td>
                    	<?php echo $applicant['applicant_name']; ?>
                    
						
                    </td>
                    <td><?php echo $applicant['applicant_age']; ?></td>
                    <td><?php echo $applicant['applicant_gender']; ?></td>
					
							<?php
						$text_line = $applicant['applicant_contacts'];
						$text_line = explode(",",$text_line);
						?>
						<td>
						<?php if ($_POST['contact'] == 1 ): ?>
						<span class="text-success"><?php 
						for ($start=0; $start < count($text_line); $start++) {
						print $text_line[$start] . "<BR>";
						}
						?></span>
						
						<?php endif; ?>
						</td>
					
					<td><?php echo $applicant['passport_number']; ?></td>

					
					<td><?php echo $applicant['certificate_medical_clinic']; ?> </td>
					<td><b><?php echo $applicant['certificate_medical_result']; ?></b></td>
					
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
                  
                  
                    <td><?php echo $applicant['position_name']; ?></td>                
                
						<?php
						$remarks = $applicant['applicant_remarks'];
						$remarks = explode(",",$remarks);
						?>
						<td>
						<span class="text-success"><?php 
						for ($start=0; $start < count($remarks); $start++) {
						print $remarks[$start] . "<BR>";
						} ?></span>
						<b style="color:Red">
						
						<?php 
                        if($applicants_raw[$applicant['applicant_id']]->sub_status != '')
                            echo ' - '.$applicants_raw[$applicant['applicant_id']]->sub_status; 
                        ?>
						</b>
						</td>
					
					
				

					

                </tr>
				<?php $ctr ++; ?>
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
    <!-- endOf: 

    <div style="clear:both" align="center">
        <p>&nbsp;</p>
        <a href="#" id="btn-print" role="button">Print</a>
    </div>        
#wrapper -->
    <script src="<?php echo $app->getPath()['scripts']; ?>jquery-2.0.3.min.js"></script>
    <?php $app->renderScripts(); ?>
</body>
</html>