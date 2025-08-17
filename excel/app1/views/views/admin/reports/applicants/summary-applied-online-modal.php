<?php $app->renderStyles(); ?>
<div class="modal-header">
    <button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
    <h4 class="modal-title">Summary Applied Online</h4>
</div>

<form method="post" class="form" action="<?php echo site_url( 'admin/reports/summary_applied_online' ); ?>" target="_blank">
<div class="modal-body">
    <div class="bootbox-body">

		<div class="row">			
			<div class="col-sm-3">
				Recruitment Agent:
			</div>
			<div class="col-sm-5">
				<select name="agent_id" class="form-control">
					<option value="0">-- All --</option>
					<?php foreach ( $recruitment_agents as $recruitment_agent ): ?>
					<option value="<?php echo $recruitment_agent['agent_id']; ?>"><?php echo $recruitment_agent['agent_first'].' '.$recruitment_agent['agent_last']; ?></option>
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