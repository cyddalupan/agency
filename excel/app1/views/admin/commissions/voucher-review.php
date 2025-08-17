<div class="modal-header">
	<button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
    <h4 class="modal-title">Voucher# <?php echo $voucher['voucher_number']; ?></h4>
</div>

<form method="post" class="form" role="form" action="<?php echo site_url( 'admin/commissions/voucher-review/'.$voucher['voucher_id']); ?>">
<div class="modal-body">
    <div class="bootbox-body">
    	<div class="row">
    		<div class="col-sm-2">
    			<label>Voucher #:</label>
    		</div>
    		<div class="col-sm-5">
    			<input type="text" name="voucher[number]" placeholder="Voucher#" class="form-control" value="<?php echo $voucher['voucher_number']; ?>">
                <br>
                <input type="text" name="voucher[check]" placeholder="Check#" class="form-control" value="<?php echo $voucher['voucher_check']; ?>" style="text-transform:uppercase;">
    		</div>
    		<div class="col-sm-5" align="right">
    			Amount Paid: &#8369;&nbsp;<span class="text-danger voucher-amount"><?php echo number_format($voucher['voucher_amount'], 2); ?></span>
    		</div>
    	</div> 

    	<?php if ( ! is_null( $voucher['marketing-agency']['agency_name'] ) ): ?>
        <hr class="wide" />
    	<div class="row">
    		<div class="col-sm-3">
    			<label>Marketing Agency:</label>
    		</div>
    		<div class="col-sm-6">
    			<span class="marketing-agency"><?php echo $voucher['marketing-agency']['agency_name']; ?></span>
    		</div>
    	</div> 
        <?php endif; ?>

        <?php if ( ! is_null( $voucher['marketing-agent']['agent_first'] ) ): ?>
        <hr class="wide" />
    	<div class="row">
    		<div class="col-sm-3">
    			<label>Marketing Agent:</label>
    		</div>
    		<div class="col-sm-6">
    			<span class="marketing-agent"><?php echo $voucher['marketing-agent']['agent_first'].' '.$voucher['marketing-agent']['agent_last']; ?></span>
    		</div>
    	</div>

        <?php endif; ?>
    	
        <?php if ( ! is_null( $voucher['recruitment-agent']['agent_first'] ) ): ?>
        <hr class="wide" />        

    	<div class="row">
    		<div class="col-sm-3">
    			<label>Recruitment Agent:</label>
    		</div>
    		<div class="col-sm-6">
    			<span class="recruitment-agent"><?php echo $voucher['recruitment-agent']['agent_first'].' '.$voucher['recruitment-agent']['agent_last']; ?></span>
    		</div>
    	</div>
        <?php endif; ?>

        <hr class="wide" />

        <?php if ( ! is_null( $voucher['recruitment-agent']['agent_first'] ) ): ?>
        <div class="row">
            <div class="col-sm-2">
                <label>Amount:</label>
            </div>
            <div class="col-sm-5">
                <input type="text" name="voucher[amount]" placeholder="0.00" class="form-control" value="<?php echo $voucher['voucher_amount']; ?>">
            </div>
        </div>

        <br>

        <?php endif; ?>

        <div class="row">
            <div class="col-sm-2">
                <label>Txn Date:</label>
            </div>
            <div class="col-sm-5">
                <div class="input-group">
                    <input name="voucher[date]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo date( 'Y-m-d', strtotime( $voucher['voucher_date'] ) ); ?>">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                </div>
            </div>
        </div>

        <?php if ( count( $voucher['workers'] ) > 0 ): ?>
    	<hr class="wide" />

    	<div class="row">
    		<div class="col-sm-12">
                <p>Workers:</p>
    			<table class="table table-bordered voucher-applicants">
    				<thead>
    					<tr>
    						<th>Applicant</th>
    						<th>Employer</th>
    						<th>Date deployed</th>
    					</tr>
    				</thead>
    				<tbody>
    					<?php foreach ( $voucher['workers'] as $worker ): ?>
    					<tr>
    						<td><?php echo $worker['applicant_first'].' '.$worker['applicant_last'] ?></td>
    						<td><?php echo $worker['employer_name'] ?></td>
    						<td><?php echo $worker['deployed_date']; ?></td>
    					</tr>
    					<?php endforeach; ?>
    				</tbody>
    			</table>
    		</div>
    	</div>
        <?php endif; ?>

    	<hr class="wide" />

        <div class="row">
            <div class="col-md-12"> 
                <div class="form-group"> 
                	<label for="voucher[remarks]">Remarks</label>
                    <textarea name="voucher[remarks]" rows="3" placeholder="Remarks" class="form-control"><?php echo $voucher['voucher_remarks']; ?></textarea>
                </div>
                
            </div>
        </div> 
        
    </div>
</div>
<div class="modal-footer">
    <?php if ( in_array( $_SESSION['admin']['user']['user_type'], [ 3, 4, 8 ]) ): ?>
    <a href="<?php echo site_url( 'admin/commissions/voucher-revert/'.$voucher['voucher_id'] ); ?>" class="pull-left" id="delete-voucher" >Delete this voucher</a>
    <?php endif; ?>
	<button class="btn btn-default " data-dismiss="modal"  type="button">Cancel</button>
    <button class="btn btn-primary" type="submit">Save changes</button>
</div>
</form>
<?php $app->renderScripts(); ?>