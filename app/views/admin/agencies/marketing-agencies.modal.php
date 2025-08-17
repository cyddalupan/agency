<div id="modalAddMarketingAgency" role="dialog" tabindex="-1" class="modal fade modal-darkorange" aria-hidden="false">
	<div class="modal-dialog">
    	<div class="modal-content">
        	<div class="modal-header">
            	<button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
                <h4 class="modal-title">New Marketing Agency</h4>
            </div>
            
            <form method="post" class="form" role="form">
            <input type="hidden" name="flag" value="add" />
            <div class="modal-body">
                <div class="bootbox-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                            	<label for="agency[name]">Agency name</label>
                                <input name="agency[name]" type="text" placeholder="Agency name" class="form-control" value="<?php echo isset( $post['agency']['name'] ) ? $post['agency']['name'] : ''; ?>">
                            </div>
							<div class="form-group">
                            	<label for="agency[name]">Contact person</label>
                                <input name="agency[contact-person]" type="text" placeholder="Contact person" class="form-control" value="<?php echo isset( $post['agency']['contact-person'] ) ? $post['agency']['contact-person'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="">Contact numbers <small><em>(Press Enter to accept contact)</em></small></label>
                                <input name="agency[contacts]" type="text" value="" data-role="tagsinput" placeholder="Add contact" />
                            </div>
                            <div class="form-group">
                            	<label for="agency[email]">Email</label>
                                <input name="agency[email]" type="email" placeholder="Email address" class="form-control" value="<?php echo isset( $post['agency']['email'] ) ? $post['agency']['email'] : ''; ?>" style="text-transform:lowercase">
                            </div> 
                            <div class="form-group">
                            	<label for="agency[address]">Address</label>
                                <input name="agency[address]" type="text" placeholder="Address" class="form-control" value="<?php echo isset( $post['agency']['address'] ) ? $post['agency']['address'] : ''; ?>">
                            </div> 
                        </div>
                    </div> 
                    
                </div>
           </div>
			<div class="modal-footer">
    			<button class="btn btn-default" data-dismiss="modal" type="button">Cancel</button>
                <button class="btn btn-primary" type="submit">Add</button>
			</div>
            </form>
		</div>
	</div>
</div>


<div id="modalEditMarketingAgency" role="dialog" tabindex="-1" class="modal fade modal-darkorange" aria-hidden="false">
	<div class="modal-dialog">
    	<div class="modal-content">
        	<div class="modal-header">
            	<button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
                <h4 class="modal-title">--</h4>
            </div>
            
            <form method="post" class="form" role="form">
            <input type="hidden" name="flag" value="edit" />
            <input type="hidden" name="agency[agency_id]" value="0" />
            <div class="modal-body">
                <div class="bootbox-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                            	<label for="agency[name]">Agency name</label>
                                <input name="agency[name]" type="text" placeholder="Agency name" class="form-control" value="<?php echo isset( $post['agency']['name'] ) ? $post['agency']['name'] : ''; ?>">
                            </div>
							<div class="form-group">
                            	<label for="agency[name]">Contact person</label>
                                <input name="agency[contact-person]" type="text" placeholder="Contact person" class="form-control" value="<?php echo isset( $post['agency']['contact-person'] ) ? $post['agency']['contact-person'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="">Contact numbers <small><em>(Separated with comma)</em></small></label>
                                <input name="agency[contacts]" class="form-control" type="text" placeholder="Add contact" value="<?php echo isset( $post['agency']['contacts'] ) ? $post['agency']['contacts'] : ''; ?>" />
                            </div>
                            <div class="form-group">
                            	<label for="agency[email]">Email</label>
                                <input name="agency[email]" type="email" placeholder="Email address" class="form-control" value="<?php echo isset( $post['agency']['email'] ) ? $post['agency']['email'] : ''; ?>" style="text-transform:lowercase">
                            </div> 
                            <div class="form-group">
                            	<label for="agency[address]">Address</label>
                                <input name="agency[address]" type="text" placeholder="Address" class="form-control" value="<?php echo isset( $post['agency']['address'] ) ? $post['agency']['address'] : ''; ?>">
                            </div> 
                        </div>
                    </div> 
                    
                </div>
           </div>
			<div class="modal-footer">
    			<button class="btn btn-default" data-dismiss="modal" type="button">Cancel</button>
                <button class="btn btn-primary" type="submit">Save changes</button>
			</div>
            </form>
		</div>
	</div>
</div>
