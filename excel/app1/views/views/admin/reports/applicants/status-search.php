<?php $app->renderStyles(); ?>
<div class="modal-header">
    <button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
    <h4 class="modal-title">Applicant Status Report</h4>
</div>

<form method="post" class="form" action="<?php echo site_url( 'admin/reports/applicants-status' ); ?>" target="_blank">
<div class="modal-body">
    <div class="bootbox-body">
    	<div class="row">
    		<div class="col-sm-3">
    			Status:
    		</div>
    		<div class="col-sm-5">
    			<select name="applicant[status]" class="form-control">
					<option value="">-- All --</option>	
					<?php foreach ( $status as $text => $id ): ?>
					<option value="<?php echo $id; ?>"><?php echo $text; ?></option>
					<?php endforeach; ?>
				</select>
    		</div>
    	</div>
    	
		<p>&nbsp;</p>

		<div class="row">
			<div class="col-sm-3">
				<input type="checkbox">Date Range <small><em>(optional)</em></small>:
			</div>
			<div class="col-sm-4">
				<div class="input-group">
			        <input name="applicant[date-from]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="From" value="">
			        <span class="input-group-addon">
			            <i class="fa fa-calendar"></i>
			        </span>
			    </div>			    
			</div>
			<div class="col-sm-4">
				<div class="input-group">
			        <input name="applicant[date-to]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="To" value="">
			        <span class="input-group-addon">
			            <i class="fa fa-calendar"></i>
			        </span>
			    </div>
			</div>

		</div>

		<p>&nbsp;</p>

		<div class="row">
			<div class="col-sm-3">
				<input type="checkbox">Age Bracket <small><em>(optional)</em></small>:
			</div>
			<div class="col-sm-2">
				<div class="input-group">
			        <input name="applicant[age-from]" type="text" class="form-control" placeholder="From" value="">
			    </div>			    
			</div>
			<div class="col-sm-2">
				<div class="input-group">
			        <input name="applicant[age-to]" type="text" class="form-control" placeholder="To" value="">
			    </div>
			</div>
		</div>

		<p>&nbsp;</p>

		<div class="row">
    		<div class="col-sm-3">
    			Gender:
    		</div>
    		<div class="col-sm-5">
    			<select name="applicant[gender]" class="form-control">
					<option value="0">-- All --</option>
					<option value="Male">Male</option>
					<option value="Female">Female</option>
				</select>
    		</div>
    	</div>

		<p>&nbsp;</p>

		<div class="row">
    		<div class="col-sm-3">
    			Position:
    		</div>
    		<div class="col-sm-5">
    			<select name="applicant[position]" class="form-control">
					<option value="0">-- All --</option>	
					<?php foreach ( $positions as $category ): ?>
					<optgroup label="<?php echo $category['category_name'] ?>">
					<?php foreach ( $category['positions'] as $position ): ?>
					<option value="<?php echo $position['position_id']; ?>"><?php echo $position['position_name']; ?></option>
					<?php endforeach; ?>
					</optgroup>
					<?php endforeach; ?>
				</select>
    		</div>
    	</div>

		<p>&nbsp;</p>

		<div class="row">			
			<div class="col-sm-3">
				Employer:
			</div>
			<div class="col-sm-5">
				<select name="applicant[employer]" class="form-control">
					<option value="">-- All --</option>
					<?php foreach ( $employers as $employer ): ?>
					<option value="<?php echo $employer['employer_id']; ?>"><?php echo $employer['employer_name']; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<p>&nbsp;</p>

		<div class="row">			
			<div class="col-sm-3">
				Recruitment Agent:
			</div>
			<div class="col-sm-5">
				<select name="applicant[recruitment_agents]" class="form-control">
					<option value="">-- All --</option>
					<?php foreach ( $recruitment_agents as $recruitment_agent ): ?>
					<option value="<?php echo $recruitment_agent['agent_id']; ?>"><?php echo $recruitment_agent['agent_first'].' '.$recruitment_agent['agent_last']; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<p>&nbsp;</p>

		<div class="row">			
			<div class="col-sm-3">
				Years of Experience:
			</div>
			<div class="col-sm-5">
				<select name="applicant[experience]" class="form-control">
					<option value="0">-- All --</option>
					<option value="1">6 months to 11 months</option>
					<option value="2">1 year to 1 year and 11 months</option>
					<option value="3">2 year to 2 year and 11 months</option>
					<option value="4">3 year to 3 year and 11 months</option>
					<option value="5">4 year to 4 year and 11 months</option>
					<option value="6">5 year and above</option>
				</select>
			</div>
		</div>
		
		<p>&nbsp;</p>
		<div class="row">			
			<div class="col-sm-3">
			Show Contact #:
			</div>
			<div class="col-sm-2">
				<div class="form-group">
					<div class="checkbox">
						<label>
							<input name="contact" type="radio" value="1" class="colored-blue" checked>
							<span class="text" >Show</span>
						</label>
					</div>
				</div>
			</div>
			<div class="col-sm-2">
				<div class="form-group">
					<div class="checkbox">
						<label>
							<input name="contact" type="radio" value="2" class="colored-blue" >
							<span class="text">Hide</span>
						</label>
					</div>
				</div>
			</div>
		</div>

		<div class="row">	
			<div class="col-sm-12">
				With Certificates:
			</div>
			<div class="col-sm-4">
				<div class="form-group">
					<div class="checkbox">
						<label>
							<input name="cert[prcrating]" type="checkbox" value="1" class="colored-blue"> 
							<span class="text" >PRC Rating</span>
						</label>
					</div>
				</div>
			</div>	
			<div class="col-sm-4">
				<div class="form-group">
					<div class="checkbox">
						<label>
							<input name="cert[dhacetificate]" type="checkbox" value="1" class="colored-blue"> 
							<span class="text" >DHA</span>
						</label>
					</div>
				</div>
			</div>	
			<div class="col-sm-4">
				<div class="form-group">
					<div class="checkbox">
						<label>
							<input name="cert[saudicounsilid]" type="checkbox" value="1" class="colored-blue"> 
							<span class="text" >Saudi Counsil ID</span>
						</label>
					</div>
				</div>
			</div>	
			<div class="col-sm-4">
				<div class="form-group">
					<div class="checkbox">
						<label>
							<input name="cert[ksaprometrics]" type="checkbox" value="1" class="colored-blue"> 
							<span class="text" >KSA Prometrics</span>
						</label>
					</div>
				</div>
			</div>	
			<div class="col-sm-4">
				<div class="form-group">
					<div class="checkbox">
						<label>
							<input name="cert[haadcert]" type="checkbox" value="1" class="colored-blue"> 
							<span class="text" >HAAD</span>
						</label>
					</div>
				</div>
			</div>	
			<div class="col-sm-4">
				<div class="form-group">
					<div class="checkbox">
						<label>
							<input name="cert[qatarprometrcs]" type="checkbox" value="1" class="colored-blue"> 
							<span class="text" >QATAR Prometrics</span>
						</label>
					</div>
				</div>
			</div>	
			<div class="col-sm-4">
				<div class="form-group">
					<div class="checkbox">
						<label>
							<input name="cert[nclex]" type="checkbox" value="1" class="colored-blue"> 
							<span class="text" >NCLEX</span>
						</label>
					</div>
				</div>
			</div>	
			<div class="col-sm-4">
				<div class="form-group">
					<div class="checkbox">
						<label>
							<input name="cert[ieltsscore]" type="checkbox" value="1" class="colored-blue"> 
							<span class="text" >IELTS Score</span>
						</label>
					</div>
				</div>
			</div>	
			<div class="col-sm-4">
				<div class="form-group">
					<div class="checkbox">
						<label>
							<input name="cert[cgfnscert]" type="checkbox" value="1" class="colored-blue"> 
							<span class="text" >CGFNS</span>
						</label>
					</div>
				</div>
				<input type="hidden" name="cert[filler]" value="zero">
			</div>	
		</div>
		

    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-default" data-dismiss="modal"  type="button">Cancel</button>
    <button class="btn btn-info" type="submit" formaction="<?php echo site_url( 'admin/reports/applicants-status-summary' ); ?>">Summary</button>
    <button class="btn btn-primary" type="submit" >Generate</button>
</div>
</form>
<?php $app->renderScripts(); ?>