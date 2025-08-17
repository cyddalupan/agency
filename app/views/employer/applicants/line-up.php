<?php
$oldposition = ''; 
$data['poscount'] = $poscount;
//getting summary
$this->load->view('employer/applicants/_summary.php',$data); 
?>
	<div class="col-md-9 col-sm-8">
        <?php $app->renderAlerts(); ?>
        <?php if ( empty( $queryString ) ): ?>
        <?php /*
         <div class="hero">
            <h3><span>Line-Up applicants</span></h3>
		</div> */ ?>
        <?php else: ?>
		<div class="row">
			<div class="col-md-12 col-sm-12">
				<h2><?php echo number_format( count( $applicants ) ); ?> results<?php echo ! empty( $queryString['q'] ) ? ' for "'.$queryString['q'].'"' : ''; ?><?php echo isset( $queryString['position'], $positions[$queryString['position']] ) ? ', <small>'.$positions[$queryString['position']]['position_name'].'</small>' : ''; ?><?php echo isset( $queryString['country'], $countries[$queryString['country']] ) ? ', <small>'.$countries[$queryString['country']]['country_name'].'</small>' : ''; ?></h2>
			</div> 
		</div>
        <hr>
        <?php endif; ?>

		<div class="row">
            <?php if ( count( $applicants ) == 0 ): ?>
            <p>-- No line-up applicants --</p>
            <?php else: ?>

            <?php //summary of count ?>
       

        	<?php //all applicants ?>
            <?php foreach ( $applicants as $applicant ): ?>
            <?php
                //New Position Batch 
                if($applicant['position_name'] != $oldposition){
                    $oldposition = $applicant['position_name'];
            ?>                
                <div style="clear:both;"></div>
                <div id="path_<?php echo $applicant['position_name']; ?>" class="hero">
                    <h3><span><?php echo $applicant['position_name']; ?> (<?php echo $poscount[$applicant['position_name']]; ?>)</span></h3>
                </div>
            <?php }//end if ?>

			<div class="col-md-4 col-sm-3 col-xs-4" style="height:520px;border:0px solid black">
				<div class="result-item">
                	<div align="center">

                    <?php if ( is_file( DIR_UPLOADS.'applicant'.DIRECTORY_SEPARATOR.$applicant['applicant_photo'] ) ): ?>
                        <img src="<?php echo base_url(); ?>files/applicant/<?php echo $applicant['applicant_photo']; ?>" alt="<?php echo $applicant['applicant_name']; ?>" STYLE="height:150px"  />
                    <?php else: ?>
                        <img src="<?php echo $app->getPath()['images']; ?>avatars/no-picture.jpg" alt="No picture"   STYLE="height:150px" />
                    <?php endif; ?>
 
                    </div>
                    
					<h5><small class="pull-left">Name: </small> <?php echo $applicant['applicant_first'].' '.$applicant['applicant_middle'].' '.$applicant['applicant_last']; ?></h5>
                    
					 <div class="well">
                        <p>
                            <small>Code:</small>&nbsp;
								<strong>
								<span class="text-danger"><?php 
								if($applicants_raw[$applicant['applicant_id']]->applicantNumber != '')
								echo ''.$applicants_raw[$applicant['applicant_id']]->applicantNumber; 
								?>
								</span></strong>
                         </p>
                    </div>
					
					<div class="well">
                        <p>
                            <small>Applicant #:</small>&nbsp;
                            <strong><span class="text-danger"><?php echo $_SESSION["settings"]['client_short']; ?>-<?php echo str_pad( $applicant['applicant_id'], 6, '0', STR_PAD_LEFT ); ?></span></strong>
                         </p>
                    </div>
                    <div class="well">
                        <p><small>Passport #:</small>&nbsp;
                            <strong><span class="text-danger">
           
							<?php echo $applicant['passport_number']; ?>
                            </span></strong>
                        </p>
                    </div>
                    <div class="well">
                   
						   <p><small>Position</small>&nbsp;
                            <strong><span class="text-danger">
           
							<?php echo $applicant['position_name']; ?>
                            </span></strong>
                        </p>
                    </div>
                    <div class="well">
                        <p>Category: <?php echo $applicant['applicant_position_type']; ?></p>
						<p>Age: <?php echo (int) $applicant['applicant_age']; ?></p>
                        <p>Status: <?php echo $applicant['applicant_civil_status']; ?></p>
                        <p>Religion: <?php echo $applicant['applicant_religion']; ?></p>
                        <p>Preferred Country: <strong><?php echo $employer['country_name']; ?></strong></p>
                        <p>Date applied: <?php echo date( 'd-M-Y', strtotime( $applicant['applicant_date_applied'] ) ); ?></p>
                    </div>
                    
					<div class="result-buttons">
					 <a target="_blank"  href="<?php echo site_url( 'employer/applicants/resume/'.$applicant['applicant_slug'] ); ?>" class="btn btn-danger"></i> View resume</a>
                       <a onclick="return confirm('Do you want to reserve this applicant?')" href="?select=<?php echo $applicant['applicant_id']; ?>&amp;ref_lnk=search" class="btn btn-sm btn-info" role="button"><i class="fa fa-check"></i> Select</a>
					
					</div>
				</div>

				<div class="clearfix"></div>
			</div>
            <?php endforeach; ?>
            <?php endif; ?>
		</div>

		<hr>

		<div class="row">
        	<div class="clearfix"></div>
             
		</div>


	</div>
</div><!-- .row -->