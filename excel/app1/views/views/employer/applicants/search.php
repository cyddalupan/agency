<div class="row">
	<div class="col-md-3 col-sm-4">
		<?php $app->renderSideBar(); ?>
	</div>
	<div class="col-md-9 col-sm-8">
        <?php $app->renderAlerts(); ?>
        <?php if ( empty( $queryString ) ): ?>
         <div class="hero">
            <h3><span>Newest applied applicants</span></h3>
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
        	<?php foreach ( $applicants as $applicant ): ?>
			<div class="col-md-4 col-sm-4 col-xs-6">
				<div class="result-item">
                	<div align="center">
                        <?php if ( is_file( DIR_UPLOADS.'applicant'.DIRECTORY_SEPARATOR.$applicant['applicant_photo'] ) ): ?>
                            <img src="<?php echo base_url(); ?>files/applicant/<?php echo $applicant['applicant_photo']; ?>" alt="<?php echo $applicant['applicant_name']; ?>" class="img-responsive img-responsive img-rounded" style="min-height:180px; max-height:180px" />
                        <?php else: ?>
                            <img src="<?php echo $app->getPath()['images']; ?>avatars/no-picture.jpg" class="img-responsive img-responsive img-rounded" alt="<?php echo $applicant['applicant_name']; ?>" style="min-height:180px; max-height:180px">
                        <?php endif; ?>
                    </div>
                    
					<h5><small class="pull-left">Name: </small> <?php echo $applicant['applicant_first'].' '.$applicant['applicant_middle'].' '.$applicant['applicant_last']; ?></h5>
                    <div class="well">
                        <p>
                            <small>Applicant #:</small>&nbsp;
                            <strong><span class="text-danger"><?php echo $_SESSION["settings"]['client_short']; ?>-<?php echo str_pad( $applicant['applicant_id'], 6, '0', STR_PAD_LEFT ); ?></span></strong>
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
		</div>

		<hr>

		<div class="row">
        	<div class="clearfix"></div>
            <div class="col-md-6 col-sm-6" align="left">
				<?php echo 'Showing '.$paginationCounter['from'].' to '.$paginationCounter['to'].' of '.$paginationCounter['total-records'].' entries.'; ?>
			</div>
			<div class="col-md-6 col-sm-6 pull-right" align="right">
				<!-- Pagination -->
                 <?php echo $paginationHTML; ?>
			</div>
		</div>


	</div>
</div><!-- .row -->