<?php $app->renderStyles(); ?>
<div class="modal-header">
    <button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
    <h4 class="modal-title">Applicants Report</h4>
</div>

<form method="post" class="form" action="<?php echo site_url( 'admin/reports/applicants' ); ?>" target="_blank">
<div class="modal-body">
    <div class="bootbox-body">
    	<div class="row">
    		<div class="col-sm-3">
    			Status:
    		</div>
    		<div class="col-sm-5">
    			<select name="applicant[status]" class="form-control">
					<option value="0">-- All --</option>	
					<option value="<?php echo $status['Selected']; ?>">Selected</option>
					<option value="<?php echo $status['Reserved']; ?>">Reserved</option>	
					<option value="<?php echo $status['Deployed']; ?>">Deployed</option>	
				</select>
    		</div>
    	</div>
    	<div class="row selected-option hide">
			<div class="col-sm-5 col-sm-offset-3">
				<div class="checkbox">
                    <label>
                        <input name="applicant[selected]" type="radio" value="medical" class="colored-blue" >
                        <span class="text">but NO Medical</span>
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input name="applicant[selected]" type="radio" value="visa" class="colored-blue" >
                        <span class="text">but NO VISA</span>
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input name="applicant[selected]" type="radio" value="contract" class="colored-blue" >
                        <span class="text">but NO Contract</span>
                    </label>
                </div>
			</div>
		</div>

		<p>&nbsp;</p>

		<div class="row">
			<div class="col-sm-3">
				Date (From):
			</div>
			<div class="col-sm-5">
				<div class="input-group">
			        <input name="applicant[date-from]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo date( 'Y-m-d', time() ); ?>">
			        <span class="input-group-addon">
			            <i class="fa fa-calendar"></i>
			        </span>
			    </div>
			</div>
		</div>

		<p>&nbsp;</p>

		<div class="row">
			<div class="col-sm-3">
				Date (to):
			</div>
			<div class="col-sm-5">
				<div class="input-group">
			        <input name="applicant[date-to]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo date( 'Y-m-d', time() ); ?>">
			        <span class="input-group-addon">
			            <i class="fa fa-calendar"></i>
			        </span>
			    </div>
			</div>
		</div>

		<p>&nbsp;</p>

		<div class="row">			
			<div class="col-sm-3">
				Employer:
			</div>
			<div class="col-sm-5">
				<select name="applicant[employer]" class="form-control">
					<option value="0">-- All --</option>
					<?php foreach ( $employers as $employer ): ?>
					<option value="<?php echo $employer['employer_id']; ?>"><?php echo $employer['employer_name']; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
 
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-default" data-dismiss="modal"  type="button">Cancel</button>
    <button class="btn btn-primary" type="submit">Generate</button>
</div>
</form>
<?php $app->renderScripts(); ?>