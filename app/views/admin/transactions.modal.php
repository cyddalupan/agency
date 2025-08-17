<div id="modalEditVoucher" role="dialog" tabindex="-1" class="modal fade modal-darkorange" aria-hidden="false">
	<div class="modal-dialog">
    	<div class="modal-content">
        	<div class="modal-header">
            	<button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
                <h4 class="modal-title">--</h4>
            </div>
            
            <form method="post" class="form" role="form">
            <input type="hidden" name="voucher[voucher_id]" value="0">
            <div class="modal-body">
                <div class="bootbox-body">
                	<div class="row">
                		<div class="col-sm-2">
                			<label>Voucher #:</label>
                		</div>
                		<div class="col-sm-5">
                			<input type="text" name="voucher[number]" placeholder="Voucher#" class="form-control" value="">
                		</div>
                		<div class="col-sm-5" align="right">
                			Amount Paid: &#8369;&nbsp;<span class="text-danger voucher-amount">--</span>
                		</div>
                	</div>

                	<hr class="wide" />

                	<div class="row">
                		<div class="col-sm-3">
                			<label>Marketing Agency:</label>
                		</div>
                		<div class="col-sm-6">
                			<span class="marketing-agency">--</span>
                		</div>
                	</div>

                	<hr class="wide" />

                	<div class="row">
                		<div class="col-sm-3">
                			<label>Marketing Agent:</label>
                		</div>
                		<div class="col-sm-6">
                			<span class="marketing-agent">--</span>
                		</div>
                	</div>

                	<hr class="wide" />

                	<div class="row">
                		<div class="col-sm-3">
                			<label>Recruitment Agent:</label>
                		</div>
                		<div class="col-sm-6">
                			<span class="recruitment-agent">--</span>
                		</div>
                	</div>

                	<hr class="wide" />

                	<div class="row">
                		<div class="col-sm-12">
                			<table class="table table-bordered voucher-applicants">
                				<thead>
                					<tr>
                						<th>Applicant</th>
                						<th>Employer</th>
                						<th>Date deployed</th>
                					</tr>
                				</thead>
                				<tbody></tbody>
                			</table>
                		</div>
                	</div>

                	<hr class="wide" />

                    <div class="row">
                        <div class="col-md-12"> 
                            <div class="form-group"> 
                            	<label for="voucher[remarks]">Remarks</label>
                                <textarea name="voucher[remarks]" rows="3" placeholder="Remarks" class="form-control"></textarea>
                            </div>
                            
                        </div>
                    </div> 
                    
                </div>
           </div>
			<div class="modal-footer">
    			<button class="btn btn-default " data-dismiss="modal"  type="button">Cancel</button>
                <button class="btn btn-primary" type="submit">Save changes</button>
			</div>
            </form>
		</div>
	</div>
</div>