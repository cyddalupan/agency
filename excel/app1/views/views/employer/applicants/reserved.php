<?php
$oldposition = ''; 
$data['poscount'] = $poscount;
//getting summary
$this->load->view('employer/applicants/_summary.php',$data); 
?>
 <div class="col-md-14 col-sm-14">
    <div class="row">
     <div class="col-md-12">
        
        <div class="clients-page">
           <?php $app->renderAlerts(); ?>
           <div class="row">
              <div class="col-md-12">
               
                 <div class="hero">
                    <!-- Title. Don't forget the <span> tag -->
                    <h3><span>Reserved Applicants</span></h3>
                    <!-- para -->
                 </div> 
                 
                 <div class="row">
                    <div class="col-md-16"  style=" padding-bottom:20px;">
                    
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered" >
                                <thead>
                                    <tr>
                                        <th>#</th>
    									<th width="5%">&nbsp;</th>
										 <th>Code</th>	
									   <th>Applicant ID</th>
                                        <th>Name</th>
    									 <th>Gender</th>
                                        <th>Passport</th>
                                        <th> Position</th>
                                        <th> Country</th>
                                        <th>Civil Status</th>
                                        <th>Age</th>
                                        <th>Reservation<br>Expiration</th>
    									<th>Medical result</th>
    									<th>OWWA certificate</th>
    									<th>OEC #</th>
    									<th>Ticket</th>
										<th>Status Remarks</th>
										 <th>Remarks</th>
                                        <th>Date applied</th>
                                 
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ( count( $applicants ) == 0 ): ?>
                                    <tr>
                                        <td colspan="10" align="center">-- No records --</td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php $ctr = $paginationCounter['from']; ?>

                                    <?php foreach ( $applicants as $applicant ): ?>
                                    <?php $reservationExpired = strtotime( $applicant['reservation_expiration'] ) <= strtotime( date( 'Y-m-d', time() ) ); ?>
                                    <tr 
                                    <?php
                                    //add ID
                                    if($applicant['position_name'] != $oldposition){
                                        $oldposition = $applicant['position_name'];
                                        echo 'id="path_'.$applicant['position_name'].'"';
                                    }
                                    ?>                
                                    class="<?php echo $reservationExpired? 'danger' : ''; ?>">
                                        <td><?php echo $ctr; ?></td>
    									  <td>	
										<b>
										<?php 
										if($applicants_raw[$applicant['applicant_id']]->applicantNumber != '')
										echo ''.$applicants_raw[$applicant['applicant_id']]->applicantNumber; 
										?></b>
										</td>
    									<td style="white-space:normal">
                                            <a target="_blank"  href="<?php echo site_url( 'employer/applicants/resume/'.$applicant['applicant_slug'] ); ?>" class="btn btn-xs btn-info"><i class="fa fa-search"></i> View resume</a>
                                             <a onclick="return confirm('Do you want to unselect this applicant?')" href="?unselect=<?php echo str_pad( $applicant['applicant_id'], 9, '0', STR_PAD_LEFT ); ?>" class="btn btn-xs btn-default"><i class="fa fa-ban"></i> Unselect</a>
                                        </td>  
    									
    									
                                        <td><?php echo $_SESSION["settings"]['client_short']; ?>-<?php echo str_pad( $applicant['applicant_id'], 7, '0', STR_PAD_LEFT ); ?></td>
                                        <td><strong><a title="View profile" target="_blank" href="<?php echo site_url( 'employer/applicants/resume/'.$applicant['applicant_slug'] ); ?>"><?php echo $applicant['applicant_first'].' '.$applicant['applicant_last']; ?></a></strong></td>
                                        <td>
    									<?php
    									 if($applicant['applicant_gender']=='Female'){
    									 $gender='F';
    									 }
    									  if($applicant['applicant_gender']=='Male'){
    									 $gender='M';
    									 }
    									?>
    									<?php echo $gender; ?>
    									</td>
    									<td><span class="text-danger">	<?php echo $applicant['passport_number']; ?></span></td>
                                        <td><span class="text-danger"><?php echo $applicant['position_name']; ?></span></td>
                                        <td><span class="text-default"><?php echo $applicant['country_name']; ?></span></td>
                                        <td><?php echo $applicant['applicant_civil_status']; ?></td>
                                        <td><?php echo $applicant['applicant_age']; ?> <small>y/o</small></td>
                                        <td>
                                            <?php echo date( 'd-M-Y', strtotime( $applicant['reservation_expiration'] ) ); ?>
                                            <?php if ( $reservationExpired ): ?>
                                            <br><span class="label label-danger">Expired</span>
                                            <?php endif;?>
                                        </td>
                                       <td><?php echo $applicant['certificate_medical_result']; ?></td>
    									<td><?php echo $applicant['requirement_owwa_certificate']; ?></td>
    									<td><?php echo $applicant['requirement_oec_number']; ?></td>
    									<td><?php echo $applicant['requirement_ticket']; ?></td>
    									
    									 <td style="color:red"><span class="text-default"><?php echo $applicant['reservation_remarks']; ?></span></td>   
									
    									
    									<td><span class="text-default"><?php echo $applicant['applicant_remarks']; ?></span></td>	
    									
    									 <td><?php echo date( 'd-M-Y', strtotime( $applicant['applicant_date_applied'] ) ); ?></td>
    									
                                    
    																	
                                    </tr>
                                    <?php $ctr ++; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
    						<div style="clear:both;height:40px"></div>
                        </div>
                        
                    </div>
                 </div>
                 
              </div>
           </div>
        </div>
         
        
     </div>
    </div>
  </div>
</div>