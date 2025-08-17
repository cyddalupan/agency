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
    width:1000px;

}
table tr td {
    padding:3px;
    font-size:14px;
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
            <h2><?php echo $app->getTitle(); ?></h2>
            <?php if($substatus != ''){
                echo "<h3>Substatus : ".$substatus."</h3>";
            } ?>
            <p>&nbsp;</p>
            <?php if ( ! $groupByEmployer ): ?>
            <h3 style="font-size:20px">Employer: <?php echo $employer['employer_name']; ?></h3>
            <?php endif; ?>
            <p class="date-filter">Filter date start: <?php echo fdate( 'F,d Y', $dateFrom); ?></p>
        </div>
        <!-- endOf: #header2 -->   
        
        <!-- #content -->
        <div id="content">
            <table id="applicants">
                <tbody>
                    <tr>
                        <th width="1%">#</th>
                      
                         <th>Company</th>
                        <th>Name of candidates</th>
                        <th>Position</th>
						 <th>Nationality</th>
						 <th>Trade Test</th>
						  <th>Medical</th>
						 <th>Visa Stamped</th>
						 <th>OEC</th>
						 <th>PDOS</th>
						 <th>Contact#</th>
						 <th>Email</th>
						 <th>Passport</th>
						 <th>Offer Made</th>
						 <th>Job Offer </br>Received</th>
						<th> Offer </br>Accepted</th>
                        
						<th>Medical Request</th>
						<th>Medical Date</th>
						<th>Result</th>
                       
					   <th>Visa Submitted</th>
					   <th>Visa Stamped</th>
					   <th>Visa Release</th>
					   <th>Origin</th>
					   
                        <th>Prometric Exam</th>
                        <th>Aunth. Docs</th>
                        <th>Visa</th>
                        <th>PTA</th>
                    
                        <th>ETD</th>
                        <th>Remarks</th>
                      
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
                    <?php foreach ( $applicants as $applicantId => $applicant ): ?>
                    <?php if ( $groupByEmployer && $currentGroup != $applicant['applicant_employer'] ): ?>
                    <?php $currentGroup = $applicant['applicant_employer']; ?>
                    <tr>
                        <td colspan="12" align="center">
                        <strong><span class="employer"><?php echo $applicant['employer_name']; ?></span></strong>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php $ctr ++; ?>
                    <tr style="
                    <?php 
                    /** 
                     * check if admin select substatus
                     * and hide appliants that has other substatus
                     */
                    if($substatus != ''){
                        if($substatus != $applicant['sub_status']){
                            echo 'display:none;';
                        }
                    }
                    ?>">
        <td><?php echo $ctr; ?></td>
		 <td><?php if($applicants_raw[$applicant['applicant_id']]->sub_employer != '') echo ''.$applicants_raw[$applicant['applicant_id']]->sub_employer; 
        ?>
        <td><?php echo $applicant['applicant_name']; ?></td>
       
        </td>
        <td><?php echo $applicant['position_name']; ?></td>                
        <td><?php echo $applicant['applicant_nationality']; ?></td>
		<td>
        <?php
        if(isset($applicant_requirement_raw))
        $applicant_requirement_raw[$applicant['applicant_id']]->requirement_trade_remarks;
        ?>
		</td>
		<td><?php echo $applicant['certificate_medical_clinic']; ?> </br> <b><?php echo $applicant['certificate_medical_result']; ?></b></td>

		 <td align="center">
        <?php if (  $applicant['requirement_visa_date'] != '0000-00-00' ): ?>
        <span class="text-success"><?php echo fdate( 'd-M-Y', $applicant['requirement_visa_date'], '' ); ?></span>
        <?php else: ?>
        --
        <?php endif; ?>
        </td>
		
		
		<td><?php echo $applicant['requirement_oec_number']; ?></td>
		<td><?php echo $applicant['certificate_pdos']; ?></td>
		
		<td>
		<?php
		$text_line = $applicant['applicant_contacts'];
		$text_line = explode(",",$text_line);
		?>
		<span class="text-success"><?php 
		for ($start=0; $start < count($text_line); $start++) {
		print $text_line[$start] . "<BR>";
		}
		?></span>
		</td>
		<td><?php echo $applicant['applicant_email']; ?></td>
		<td><?php echo $applicant['passport_number']; ?></td>
		
		<td>
        <?php
        if(isset($applicant_requirement_raw))
        $applicant_requirement_raw[$applicant['applicant_id']]->offer_letter;
        ?>
		</td>
		
		 

        <td align="center"><?php if(isset($applicant_requirement_raw)) $received=$applicant_requirement_raw[$applicant['applicant_id']]->requirement_job_received;?>
        <?php if (  $received != '0000-00-00' ): ?>
            <span class="text-success"><?php echo fdate( 'd-M-Y', $received, '' ); ?></span>
        <?php else: ?>
        --
        <?php endif; ?>
        </td>


        <td align="center">
        <?php
        if(isset($applicant_requirement_raw))
        $accepted=$applicant_requirement_raw[$applicant['applicant_id']]->requirement_job_accepted;
        ?>
        <?php if (  $received != '0000-00-00' ): ?>
        <span class="text-success"><?php echo fdate( 'd-M-Y', $accepted, '' ); ?></span>
        <?php else: ?>
        --
        <?php endif; ?>

        </td>


		<td><b></b></td>
		 <td align="center">
        <?php if (  $applicant['certificate_medical_exam_date'] != '0000-00-00' ): ?>
        <span class="text-success"><?php echo fdate( 'd-M-Y', $applicant['certificate_medical_exam_date'], '' ); ?></span>
        <?php else: ?>
        --
        <?php endif; ?>
        </td>
		<td><b><?php echo $applicant['certificate_medical_result']; ?></b></td>
		
		 <td>
        <?php
        if(isset($applicant_requirement_raw))
        $visasub=$applicant_requirement_raw[$applicant['applicant_id']]->requirement_visa_stamp;
        ?>
        <?php if (  $visasub != '0000-00-00' ): ?>
        <span class="text-success"><?php echo fdate( 'd-M-Y', $visasub, '' ); ?></span>
        <?php else: ?>
        --
        <?php endif; ?>
        </td>

		 <td align="center">
        <?php if (  $applicant['requirement_visa_date'] != '0000-00-00' ): ?>
        <span class="text-success"><?php echo fdate( 'd-M-Y', $applicant['requirement_visa_date'], '' ); ?></span>
        <?php else: ?>
        --
        <?php endif; ?>
        </td>
		
		

		 <td>
        <?php
        if(isset($applicant_requirement_raw))
        $visarelease=$applicant_requirement_raw[$applicant['applicant_id']]->requirement_visa_release_date;
        ?>
        <?php if (  $visarelease != '0000-00-00' ): ?>
        <span class="text-success"><?php echo fdate( 'd-M-Y', $visarelease, '' ); ?></span>
        <?php else: ?>
        --
        <?php endif; ?>
        </td>	
			
		<td>MANILA, PHILIPPINES</td>
       
        <td>
        <?php
        if(isset($applicant_certificate_raw))
        echo $applicant_certificate_raw[$applicant['applicant_id']]->certificate_prc_rating;
        ?>%
        </td>



        <td>OK</td>

        <td align="center">
        <?php if (  $applicant['requirement_visa_release_date'] != '0000-00-00' ): ?>
        <span class="text-success"><?php echo fdate( 'd-M-Y', $applicant['requirement_visa_release_date'], '' ); ?></span>
        <?php else: ?>
        --
        <?php endif; ?>
        </td>
        <td><?php echo $applicant['requirement_ticket']; ?></td>
        <td>
        <?php
        if(isset($applicant_requirement_raw))
        $flight=$applicant_requirement_raw[$applicant['applicant_id']]->flight_date;
        ?>
        <?php if (  $flight != '0000-00-00' ): ?>
        <span class="text-success"><?php echo fdate( 'd-M-Y', $flight, '' ); ?></span>
        <?php else: ?>
        --
        <?php endif; ?>
        </td>


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