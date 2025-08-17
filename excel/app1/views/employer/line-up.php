<div class="row">
	<div class="col-md-12 col-sm-12">
        <?php $app->renderAlerts(); ?>
        <?php if ( empty( $queryString ) ): ?>
         <div class="hero">
            <h3><span>Line-Up applicants</span></h3>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed sollicitudin leo in libero ullamcorper cursus. Cras convallis massa leo, ac posuere dolor feugiat pretium.</p>
         </div>
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
        	<?php foreach ( $applicants as $applicant ): ?>
			<div class="col-md-3 col-sm-3 col-xs-4">
				<div class="result-item">
                	<div align="center">

                    <?php if ( is_file( DIR_UPLOADS.'applicant'.DIRECTORY_SEPARATOR.$applicant['applicant_photo'] ) ): ?>
                        <img src="<?php echo base_url(); ?>files/applicant/<?php echo $applicant['applicant_photo']; ?>" alt="<?php echo $applicant['applicant_name']; ?>" />
                    <?php else: ?>
                        <img src="<?php echo $app->getPath()['images']; ?>avatars/no-picture.jpg" alt="No picture"   />
                    <?php endif; ?>
 
                    </div>
                    
					<h5><small class="pull-left">Name: </small> <?php echo $applicant['applicant_first'].' '.$applicant['applicant_middle'].' '.$applicant['applicant_last']; ?></h5>
                    <div class="well">
                        <p>
                            <small>Applicant #:</small>&nbsp;
                            <strong><span class="text-danger">STPM-<?php echo str_pad( $applicant['applicant_id'], 6, '0', STR_PAD_LEFT ); ?></span></strong>
                         </p>
                    </div>
                    <div class="well">
                        <p><small>Passport #:</small>&nbsp;
                            <strong><span class="text-danger">
                            <?php echo ! empty( $applicant['passport'] ) ? $applicant['passport']['passport_number'] : 'FOR UPDATE'; ?>
                            </span></strong>
                        </p>
                    </div>
                    <div class="well">
                        <p>
                            <small>Preferred Position:</small>&nbsp;
                            <strong><span class="text-danger pull-right" style="text-transform:uppercase"><?php echo $applicant['position_name']; ?></span></strong>
                         </p>
                    </div>
                    <div class="well">
                        <p>Age: <?php echo (int) $applicant['applicant_age']; ?></p>
                        <p>Status: <?php echo $applicant['applicant_civil_status']; ?></p>
                        <p>Religion: <?php echo $applicant['applicant_religion']; ?></p>
                        <p>Preferred Country: <strong><?php echo $applicant['country_name']; ?></strong></p>
                        <p>Date applied: <?php echo date( 'd-M-Y', strtotime( $applicant['applicant_date_applied'] ) ); ?></p>
                    </div>
                    
					<div class="result-buttons">
						<a target="_blank"  href="<?php echo site_url( 'employer/applicants/resume/'.$applicant['applicant_slug'] ); ?>" class="btn btn-sm btn-default" role="button"><i class="fa fa-search"></i> VIEW RESUME</a>
                        <a href="?select=<?php echo $applicant['applicant_id']; ?>&amp;ref_lnk=search" class="btn btn-sm btn-info" role="button"><i class="fa fa-check"></i> RESERVE</a>
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