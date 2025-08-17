<?php $app->renderStyles(); ?>
<div class="modal-header">
	<button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
    <h4 class="modal-title">Dollar Exchange</h4>
</div>

<form id="frmExchangeRate" method="post" class="form" role="form">
<div class="modal-body">
    <div class="bootbox-body">
        <div class="row">
        	<div class="col-md-2" align="right">
        		<h3>Value:</h3>
        	</div>
            <div class="col-md-5">
                <div class="input-group">
					<span class="input-group-addon">$</span>
			        <input name="dollar" type="text" class="form-control input-lg" placeholder="0.00" value="<?php echo number_format($dollar, 2, '.', ''); ?>">
                </div>
            </div>
        </div> 
        
    </div>
</div>
<div class="modal-footer">
	<button class="btn btn-default modalClose" data-dismiss="modal"  type="button">Cancel</button>
    <button class="btn btn-primary" type="submit">Save</button>
</div>
</form>
<?php $app->renderScripts(); ?>