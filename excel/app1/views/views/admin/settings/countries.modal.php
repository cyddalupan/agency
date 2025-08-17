<div id="modalAddCountry" role="dialog" tabindex="-1" class="modal fade modal-darkorange" aria-hidden="false">
	<div class="modal-dialog">
    	<div class="modal-content">
        	<div class="modal-header">
            	<button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
                <h4 class="modal-title">New Country</h4>
            </div>
            
            <form method="post" class="form" role="form" action="<?php echo site_url(); ?>admin/settings/countries">
            <input type="hidden" name="flag" value="add" />
            <div class="modal-body">
                <div class="bootbox-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                            	<label for="country[name]">Country name</label>
                                <input name="country[name]" type="text" placeholder="Country name" class="form-control" value="<?php echo isset( $post['country']['name'] ) ? $post['country']['name'] : ''; ?>" style="text-transform:uppercase">
                            </div>
                            <div class="form-group">
                            	<label for="country[abbr]">Abbr</label>
                                <input name="country[abbr]" type="text" placeholder="Country abbr" class="form-control" value="<?php echo isset( $post['country']['abbr'] ) ? $post['country']['abbr'] : ''; ?>" style="text-transform:uppercase">
                            </div>
                            <div class="form-group">
                            	<label for="country[code]">Country code</label>
                                <input name="country[code]" type="text" placeholder="Country code" class="form-control" value="<?php echo isset( $post['country']['code'] ) ? $post['country']['code'] : ''; ?>">
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


<div id="modalEditCountry" role="dialog" tabindex="-1" class="modal fade modal-darkorange" aria-hidden="false">
	<div class="modal-dialog">
    	<div class="modal-content">
        	<div class="modal-header">
            	<button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
                <h4 class="modal-title">--</h4>
            </div>
            
            <form method="post" class="form" role="form">
            <input type="hidden" name="flag" value="edit" />
            <input type="hidden" name="country[country_id]" value="0" />
            <div class="modal-body">
                <div class="bootbox-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                            	<label for="country[name]">Country name</label>
                                <input name="country[name]" type="text" placeholder="Country name" class="form-control" value="<?php echo isset( $post['country']['name'] ) ? $post['country']['name'] : ''; ?>" style="text-transform:uppercase">
                            </div>
                            <div class="form-group">
                            	<label for="country[abbr]">Abbr</label>
                                <input name="country[abbr]" type="text" placeholder="Country abbr" class="form-control" value="<?php echo isset( $post['country']['abbr'] ) ? $post['country']['abbr'] : ''; ?>" style="text-transform:uppercase">
                            </div>
                            <div class="form-group">
                            	<label for="country[code]">Country code</label>
                                <input name="country[code]" type="text" placeholder="Country code" class="form-control" value="<?php echo isset( $post['country']['code'] ) ? $post['country']['code'] : ''; ?>">
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
