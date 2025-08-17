<div class="row" ng-controller="employer-resume">
<?php 



?>	
	<div class="col-md-12">
		<!-- Resume starts -->
		<div class="resume">
		

		
			<?php $app->
			renderAlerts(); ?>
			<div class="row">
				<div class="col-md-12">
					<h2><?php echo $applicant['applicant_first'].' '.$applicant['applicant_last']; ?> <span class="rsmall"><span class="color">@</span> <?php echo $applicant['position_name']; ?></span></h2>
				
					<hr/>
					<!-- Resume -->
					<div class="row">
						<div class="col-md-12">
                            <!-- Preferred Designation -->
							<div class="rblock">
								<div class="row">
									<div class="col-md-3">
								

										<h4>Preferred Designation</h4>
									</div>
									<div class="col-md-9">
										<div class="rinfo">
                                            <div class="row">
											
											
											<div class="col-sm-8" style="text-align:right">
												<?php if ( is_file( DIR_UPLOADS.'applicant'.DIRECTORY_SEPARATOR.$applicant['applicant_photo'] ) ): ?>
												<img src="<?php echo base_url(); ?>files/applicant/<?php echo $applicant['applicant_photo']; ?>" alt="" class="header-avatar profile-photo" style="width:150px"/>
												<?php else: ?>
												<img src="<?php echo $app->getPath()['images']; ?>avatars/no-picture.jpg" alt="" class="header-avatar profile-photo" style="width:150px"/>
												<?php endif; ?>
												<div class="clearfix"></div>
												
												<div class="clearfix"></div>
											     </div>
                                                <div class="col-sm-12" >
												<div class="clearfix"></div>
									

												
												<div class="col-sm-12" style="text-align:LEFT">
												
												 <span class="field">Preferred position:</span>
                                                </div>
                                                <div class="col-sm-9" >
                                                    <span class="description"><?php echo $applicant['position_name']; ?></span>
                                                </div>
                                                
                                                <div class="clearfix"></div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-sm-3" >
                                                    <span class="field"><small>Other preferred positions:</small></span>
                                                </div>
                                                <div class="col-sm-9" >
                                                    <?php $positions = []; ?>
                                                    <?php foreach ( $applicant['other-preferred-positions'] as $position ): ?>
                                                    <?php $positions[] = $position['position_name']; ?>
                                                    <?php endforeach; ?>
                                                    <span class="description">
                                                        <small><?php echo implode( ',&nbsp;', $positions ); ?></small>
                                                    </span>
                                                </div>
                                            </div>
                                            
										
                                            
                                            
										</div>
									</div>
								</div>
							</div>
                            
							<!-- About -->
							<div class="rblock">
								<div class="row">
									<div class="col-md-3">
										<h4>About <?php echo $applicant['applicant_first']; ?></h4>
									</div>
									<div class="col-md-9">
										<div class="rinfo">
                                            <div class="row">
                                                <div class="col-sm-3" >
                                                    <h5><small>Name:</small></h5>
                                                </div>
                                                <div class="col-sm-9" >
                                                    <h5><?php echo $applicant['applicant_first'].' '.$applicant['applicant_last']; ?></h5>
                                                </div>
                                            </div>
                                            
											<div class="row">
                                                <div class="col-sm-3" >
                                                    <span class="field"><small>Date of birth:</small></span>
                                                </div>
                                                <div class="col-sm-9" >
                                                    <span class="description">
                                                        <small><?php echo $applicant['applicant_birthdate']; ?></small>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-sm-3" >
                                                    <span class="field"><small>Civil status:</small></span>
                                                </div>
                                                <div class="col-sm-9" >
                                                    <span class="description">
                                                        <small><?php echo $applicant['applicant_civil_status']; ?></small>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-sm-3" >
                                                    <span class="field"><small>Gender:</small></span>
                                                </div>
                                                <div class="col-sm-9" >
                                                    <span class="description">
                                                        <small><?php echo $applicant['applicant_gender']; ?></small>
                                                    </span>
                                                </div>
                                            </div>
                                         
                                            
                                            <div class="row">
                                                <div class="col-sm-3" >
                                                    <span class="field"><small>Address:</small></span>
                                                </div>
                                                <div class="col-sm-9" >
                                                    <span class="description">
                                                        <small><?php echo $applicant['applicant_address']; ?></small>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-sm-3" >
                                                    <span class="field"><small>Nationality:</small></span>
                                                </div>
                                                <div class="col-sm-9" >
                                                    <span class="description">
                                                        <small><?php echo $applicant['applicant_nationality']; ?></small>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-sm-3" >
                                                    <span class="field"><small>Belief/Religion:</small></span>
                                                </div>
                                                <div class="col-sm-9" >
                                                    <span class="description">
                                                        <small><?php echo $applicant['applicant_religion']; ?></small>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-sm-3" >
                                                    <span class="field"><small>Height:</small></span>
                                                </div>
                                                <div class="col-sm-9" >
                                                    <span class="description">
                                                        <small><?php echo $applicant['applicant_height']; ?></small>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-sm-3" >
                                                    <span class="field"><small>Weight:</small></span>
                                                </div>
                                                <div class="col-sm-9" >
                                                    <span class="description">
                                                        <small><?php echo $applicant['applicant_weight']; ?></small>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-sm-3" >
                                                    <span class="field"><small>Languages:</small></span>
                                                </div>
                                                <div class="col-sm-9" >
                                                    <span class="description">
                                                        <small><?php echo str_replace( ',', ',&nbsp;', $applicant['applicant_languages'] ); ?></small>
                                                    </span>
                                                </div>
                                            </div>
                                            
										</div>
									</div>
								</div>
							</div>
                            
                            <!-- Passport -->
							<div class="rblock">
								<div class="row">
									<div class="col-md-3">
										<h4>Passport</h4>
									</div>
									<div class="col-md-9">
										<div class="rinfo">
                                        
											<div class="row">
                                                <div class="col-sm-3" >
                                                    <span class="field"><small>Passport number:</small></span>
                                                </div>
                                                <div class="col-sm-9" >
                                                    <span class="description">
                                                        <small><?php echo $applicant['passport_number']; ?></small>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-sm-3" >
                                                    <span class="field"><small>Date of expiration:</small></span>
                                                </div>
                                                <div class="col-sm-9" >
                                                    <span class="description">
                                                        <small><?php echo date( 'd M Y', strtotime( $applicant['passport_expiration'] ) ); ?></small>
                                                    </span>
                                                </div>
                                            </div>
                                            
										</div>
									</div>
								</div>
							</div>
                            
						
                            <!-- Work Experiences -->
							<div class="rblock">
								<div class="row">
									<div class="col-md-3">
										<h4>Work Experiences</h4>
									</div>
									<div class="col-md-9">
                                        <div class="rinfo">
                                            <?php if ( empty($applicant['experiences'] ) ): ?>
                                                FIRST TIMER
                                            <?php endif; ?>
                                            <?php foreach ( $applicant['experiences'] as $experience ): ?>
											<div class="row">
                                                <div class="col-sm-12" >
                                                    <div>
                                                        <span class="description"><small><?php echo $experience['experience_company']; ?></small></span>
                                                    </div>
                                                    <div class="rmeta"><strong><?php echo $experience['experience_position']; ?></strong> for <?php echo $experience['experience_years']; ?> years</div>
                                                    <p><?php echo $experience['experience_from']; ?> &minus; <?php echo $experience['experience_to']; ?>, &#8369; <?php echo  $experience['experience_salary']; ?></p>
                                                </div>
                                            </div>                                           
                                            
                                            <?php endforeach; ?>                                          
                                            
										</div>
									</div>
								</div>
							</div>
                            
							<!-- Skills -->
							<div class="rblock">
								<div class="row">
									<div class="col-md-3">
										<h4>Other Skills</h4>
									</div>
									<div class="col-md-9">
										<div class="rinfo">
											<!-- Class "rskills" is important -->
											<div class="rskills">
                                                <?php if ( empty( $applicant['applicant_other_skills'] ) ): ?>
                                                No more other skills defined
                                                <?php endif; ?>
                                                <?php foreach ( explode( ',', $applicant['applicant_other_skills'] ) as $skill ): ?>
												<span><?php echo $skill; ?></span>&nbsp;
                                                <?php endforeach; ?>
											</div>
										</div>
									</div>
								</div>
							</div>
							
<style>
textarea#contjob{
color:#666;
-moz-border-radius: 8px; -webkit-border-radius: 8px;
margin-left:-10px;
padding:10px;
height:205px;
width:760px;
font-family:"Lucida Sans Unicode", "Lucida Grande", sans-serif;
background:#F8F8F8;
font-size:14px;
width:95%;
border:1px solid #ddd;
color:Red;
}


</style>
							
							
							<!-- Remarks -->
							<div class="rblock">
								<div class="row">
									<div class="col-md-3">
										<h4>Remarks</h4>
									</div>
									<div class="col-md-9">
										<div class="rinfo">
											<!-- Class "rskills" is important -->
											<div class="rskills">
											
											<textarea   rows="15" cols="200" id="contjob" readonly><?php echo $applicant['applicant_remarks']; ?> </textarea>
                          
												
											</div>
										</div>
									</div>
								</div>
							</div>
							
					
							
						

                            <!-- Remarks -->
                            <div class="rblock" STYLE="dispaly:none">
                                <div class="row">
                                    <div class="col-md-3">
                                        <h4>Files</h4>
                                    </div>
									

<script>
// JavaScript popup window function
	function basicPopup(url) {
popupWindow = window.open(url,'popUpWindow','height=500,width=1000,left=150,top=50,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes')
	}

</script>
									
									
									
<a href="https://workasia.agency/system/others/download.php?appid=<?=$applicant['applicant_id']?>" 
target="_blank" style="background:#DC143C;color:white;padding:10px;font-size:14px;border:1px solid black" onclick="basicPopup(this.href);return false">Download Files</a>
								 
								 
								 
								 
								 
								 
                                </div>
                            </div>
							
							
							
							
							
							
							
							
							
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Resume ends -->
		<!-- CTA starts -->
		<div class="cta">
			<div class="row">
				<div class="col-sm-6">
					<!-- First line -->
					<p class="cbig">
						Download applicant's resume
					</p>
					<!-- Second line -->
					<p class="csmall">
						Click 'PDF' or 'DOCX' button to download the resume
					</p>
				</div>
				<div class="col-sm-4" align="right">
					<!-- Button -->
                    <a href="<?php echo site_url( 'public/applicants/pdf/'.$applicant['applicant_slug'] ); ?>" target="_blank" class="btn btn-md btn-danger">PDF</a>
                    <?php if ( empty( $resume ) ): ?>                    
              
           
                    <?php else: ?>
                    <!-- <a href="#" class="btn disabled btn-md btn-danger">PDF</a>
                    <a href="<?php echo base_url().$resume['file_path']; ?>" class="btn btn-md btn-info">DOCX</a> -->
                    <?php endif; ?>
                </div>
                <div class="col-sm-2 pull-right" align="center">
                    <a href="javascript:window.close()" class="btn btn-md btn-default">Close</a>
				</div>
			</div>
		</div>
		<!-- CTA Ends -->
	</div>
</div>
