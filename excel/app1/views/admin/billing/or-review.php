<div class="modal-header">
	<button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
    <h4 class="modal-title">OR# <?php echo $or['or_number']; ?></h4>
</div>

<form method="post" class="form" role="form" action="<?php echo site_url( 'admin/billing/or-review/'.$or['or_id']); ?>">
<div class="modal-body">
    <div class="bootbox-body">
    	<div class="row">
    		<div class="col-sm-2">
    			<label>OR #:</label>
    		</div>
    		<div class="col-sm-5">
    			<input type="text" name="or[number]" placeholder="Voucher#" class="form-control" value="<?php echo $or['or_number']; ?>">
    		</div>
    		<div class="col-sm-5" align="right">
    			Amount Paid: &#8369;&nbsp;<span class="text-danger or-amount"><?php echo number_format($or['or_amount'], 2); ?></span>
    		</div>
    	</div>
        
        <hr class="wide" />
        <strong>Workers</strong>
        <table class="table">
            <tbody>
                <?php if ( count( $or['applicants'] ) > 0 ) : ?>
                <?php foreach ( $or['applicants'] as $applicant ): ?>
                <tr>
                    <td><?php echo $applicant['applicant_first'].' '.$applicant['applicant_last']; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td><?php echo $or['applicant_first'].' '.$or['applicant_last']; ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <hr class="wide" />

        <div class="row">
            <div class="col-sm-2">
                <label>Txn Date:</label>
            </div>
            <div class="col-sm-5">
                <div class="input-group">
                    <input name="or[date]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo date( 'Y-m-d', strtotime( $or['or_date'] ) ); ?>">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                </div>
            </div>
        </div>

    	<hr class="wide" />
 
        <div class="row">
            <div class="col-md-12"> 
                <div class="form-group"> 
                	<label for="or[remarks]">Remarks</label>
                    <textarea name="or[remarks]" rows="3" placeholder="Remarks" class="form-control"><?php echo $or['or_remarks']; ?></textarea>
                </div>
                
            </div>
        </div> 
        
    </div>
</div>
<div class="modal-footer">
    <?php if ( in_array( $_SESSION['admin']['user']['user_type'], [ 3, 4, 8 ]) ): ?>
    <a href="<?php echo site_url( 'admin/billing/or-revert/'.$or['or_id'] ); ?>" class="pull-left" id="delete-or" >Delete this OR</a>
    <?php endif; ?>
	<button class="btn btn-default " data-dismiss="modal"  type="button">Cancel</button>
    <button class="btn btn-primary" type="submit">Save changes</button>
</div>

</form>
<?php $app->renderScripts();  ?>
