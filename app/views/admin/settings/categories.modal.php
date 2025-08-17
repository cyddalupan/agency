<div id="modalAddCategory" role="dialog" tabindex="-1" class="modal fade modal-darkorange" aria-hidden="false">
	<div class="modal-dialog">
    	<div class="modal-content">
        	<div class="modal-header">
            	<button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
                <h4 class="modal-title">New Category</h4>
            </div>
            
            <form method="post" class="form" role="form" action="<?php echo site_url(); ?>admin/settings/categories">
            <input type="hidden" name="flag" value="add" />
            <div class="modal-body">
                <div class="bootbox-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                            	<label for="category[name]">Category name</label>
                                <input name="category[name]" type="text" placeholder="Work category" class="form-control" value="<?php echo isset( $post['category']['name'] ) ? $post['category']['name'] : ''; ?>">
                            </div>
                            <div class="form-group">
                            	<label for="category[abbr]">Category photo <small><em>(optional): </em></small></label>
                                <input type="file" name="category[photo]" class="hide" />
								<a href="#" role="button" class="btn btn-md btn-info btn-category-photo"><i class="fa fa-upload"></i> Browse photo</a>
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

<div id="modalAddPosition" role="dialog" tabindex="-1" class="modal fade modal-darkorange" aria-hidden="false">
	<div class="modal-dialog">
    	<div class="modal-content">
        	<div class="modal-header">
            	<button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
                <h4 class="modal-title">--</h4>
            </div>
            
            <form method="post" class="form" role="form" action="<?php echo site_url(); ?>admin/settings/categories">
            <input type="hidden" name="flag" value="add-position" />
            <input type="hidden" name="position[category]" value="0">
            <div class="modal-body">
                <div class="bootbox-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                            	<label for="position[name]">Position name</label>
                                <input name="position[name]" type="text" placeholder="Position name" class="form-control" value="<?php echo isset( $post['position']['name'] ) ? $post['position']['name'] : ''; ?>">
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

<div id="modalEditCategory" role="dialog" tabindex="-1" class="modal fade modal-darkorange" aria-hidden="false">
	<div class="modal-dialog">
    	<div class="modal-content">
        	<div class="modal-header">
            	<button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
                <h4 class="modal-title">--</h4>
            </div>
            
            <form method="post" class="form" role="form" action="<?php echo site_url( 'admin/settings/categories' ); ?>">
            <input type="hidden" name="flag" value="edit" />
            <input type="hidden" name="category[category_id]" value="0" />
            <div class="modal-body">
                <div class="bootbox-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                            	<label for="category[name]">Category name</label>
                                <input name="category[name]" type="text" placeholder="Category name" class="form-control" value="">
                            </div>
                            <div class="form-group">
                            	<label for="category[abbr]">Category photo</label>
                                <input type="file" name="category[photo]" class="hide" />
								<a href="#" role="button" class="btn btn-md btn-info btn-category-photo"><i class="fa fa-upload"></i> Browse photo</a>
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


<div id="modalListPositions" role="dialog" tabindex="-1" class="modal fade modal-darkorange" aria-hidden="false">
	<div class="modal-dialog">
    	<div class="modal-content">
        	<div class="modal-header">
            	<button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
                <h4 class="modal-title">--</h4>
            </div>
            
            <form method="post" class="form" role="form">
            <div class="modal-body">
                <div class="bootbox-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                            	<thead>
                                	<tr>
                                    	<th width="5%">#</th>
                                        <th colspan="2">Position</th>
                                    </tr>
                                </thead>
                                <tbody>
                                	
                                	<tr>
                                    	<td colspan="2" align="center">-- No positions --</td>
                                    </tr>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div> 
                    
                </div>
           </div>
			<div class="modal-footer">
            	<a href="#" role="button" class="btn-add-position btn btn-xs btn-info pull-left" data-category="0" data-dismiss="modal" data-toggle="modal" data-target="#modalAddPosition">Add new position</a>
    			<button class="btn btn-default" data-dismiss="modal" type="button">Close</button>
			</div>
            </form>
		</div>
	</div>
</div>
