
    <?php
    $oldposition = ''; 
    $data['poscount'] = $poscount;
    //getting summary
    $this->load->view('employer/applicants/_summary.php',$data); 
    ?>
 <div class="col-md-12 col-sm-12">
    <div class="row">
     <div class="col-md-12">
        
        <div class="clients-page">
           <?php $app->renderAlerts(); ?>
           <div class="row">
              <div class="col-md-12">
               
                 <div class="hero">
                    <!-- Title. Don't forget the <span> tag -->
                    <h3><span>Deployed Applicants</span></h3>
                    <!-- para -->
               </div> 
                 
                 <div class="row">
                    <div class="col-md-12">
                    
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
										  <th>Code</th>	
                                        <th>Applicant ID</th>
										<th>Date applied</th>
                                        <th>Name</th>
                                        <th>Passport</th>
                                        <th>Preferred Position</th>
                                        <th>Preferred Country</th>
                                        <th>Civil Status</th>
                                        <th>Age</th>
                                        <th>Date Deployed</th>
                                        <th width="5%">&nbsp;</th>
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
                                    <tr 
                                    <?php
                                    //add ID
                                    if($applicant['position_name'] != $oldposition){
                                        $oldposition = $applicant['position_name'];
                                        echo 'id="path_'.$applicant['position_name'].'"';
                                    }
                                    ?>  class="">
                                     

										<td><?php echo $ctr; ?></td>
										  <td>	
										<b>
										<?php 
										if($applicants_raw[$applicant['applicant_id']]->applicantNumber != '')
										echo ''.$applicants_raw[$applicant['applicant_id']]->applicantNumber; 
										?></b>
										</td>
                                        <td><?php echo $_SESSION["settings"]['client_short']; ?>-<?php echo str_pad( $applicant['applicant_id'], 9, '0', STR_PAD_LEFT ); ?></td>
										 <td><?php echo date( 'd-M-Y', strtotime( $applicant['applicant_date_applied'] ) ); ?></td>
                                        <td><strong><a title="View profile" target="_blank" href="<?php echo site_url( 'employer/applicants/resume/'.$applicant['applicant_slug'] ); ?>"><?php echo $applicant['applicant_first'].' '.$applicant['applicant_last']; ?></a></strong></td>
                                        <td><span class="text-danger">	<?php echo $applicant['passport_number']; ?></span></td>
                                        <td><span class="text-danger"><?php echo $applicant['position_name']; ?></span></td>
                                        <td><span class="text-default"><?php echo $applicant['country_name']; ?></span></td>
                                        <td><?php echo $applicant['applicant_civil_status']; ?></td>
                                        <td><?php echo $applicant['applicant_age']; ?> <small>y/o</small></td>
										 <td><?php echo date( 'd-M-Y', strtotime( $applicant['deployed_date'] ) ); ?></td>
                                        <td style="white-space:normal">
                                            <a target="_blank"  href="<?php echo site_url( 'employer/applicants/resume/'.$applicant['applicant_slug'] ); ?>" class="btn btn-xs btn-info"><i class="fa fa-search"></i> View resume</a>
                                        </td>                                    
                                    </tr>
                                    <?php $ctr ++; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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

