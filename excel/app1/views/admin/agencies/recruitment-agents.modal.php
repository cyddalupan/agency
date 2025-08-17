<div id="modalAddRecruitmentAgent" role="dialog" tabindex="-1" class="modal fade modal-darkorange" aria-hidden="false">
	<div class="modal-dialog">
    	<div class="modal-content">
        	<div class="modal-header">
            	<button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
                <h4 class="modal-title">New Recruitment Agent & Source</h4>
            </div>
            
            <form method="post" class="form" role="form" action="<?php echo site_url(); ?>admin/agencies/recruitment-agents">
            <input type="hidden" name="flag" value="add" />
            <div class="modal-body">
                <div class="bootbox-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                
                                  <div class="form-group">
                            	<label for="agent[email]">Code</label>
                                <input name="agent[email]" type="text" class="form-control" value="<?php echo isset( $post['agent']['email'] ) ? $post['agent']['email'] : ''; ?>" style="text-transform:lowercase">
                            </div> 
							
                          
                                
                                
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="agent[first]">First name</label>
                                        <input name="agent[first]" type="text" placeholder="First" class="form-control" value="<?php echo isset( $post['agent']['first'] ) ? $post['agent']['first'] : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="agent[last]">Last name</label>
                                        <input name="agent[last]" type="text" placeholder="Last" class="form-control" value="<?php echo isset( $post['agent']['last'] ) ? $post['agent']['last'] : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="">Contact numbers <small><em>(Press Enter to accept contact)</em></small></label>
                                <input name="agent[contactsss]" type="text"  class="form-control"  placeholder="Add contact" />
                            </div>
                          
								<div class="form-group">
							<label for="agent[email]">Branch</label>
							<select class="form-control" name="agent[branch_type]" >
							<option></option>
							<option VALUE="1">MAIN</option>
							<option VALUE="2">ILOILO</option>
							<option VALUE="3">BACOLOD</option>
							<option VALUE="4">CDO</option>
							<option VALUE="5">GENSAN</option>
					
							</select> </div> 
							
							
							
							

							    <div class="form-group">
                                <label for="">Bank Details <small><em></em></small></label>
                                <input name="agent[agent_remarks]" type="text"  class="form-control"  placeholder="Add Bank Details"  value="<?php echo isset( $post['agent']['agent_remarks'] ) ? $post['agent']['agent_remarks'] : ''; ?>"/>
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


<div id="modalEditRecruitmentAgent" role="dialog" tabindex="-1" class="modal fade modal-darkorange" aria-hidden="false">
	<div class="modal-dialog">
    	<div class="modal-content">
        	<div class="modal-header">
            	<button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
                <h4 class="modal-title">--</h4>
            </div>
        
            <form method="post" class="form" role="form" action="<?php echo site_url(); ?>admin/agencies/recruitment-agents">
            <input type="hidden" name="flag" value="edit" />
            <input type="hidden" name="agent[agent_id]" value="0" />
            <div class="modal-body">
                <div class="bootbox-body">
                    <div class="row">
                        <div class="col-md-12">
                            
                             <div class="form-group">
                            	<label for="agent[email]">Code</label>
                                <input name="agent[email]" type="text"  class="form-control" value="<?php echo isset( $post['agent']['email'] ) ? $post['agent']['email'] : ''; ?>" style="text-transform:lowercase">
                            </div> 
							
                            
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="agent[first]">First name</label>
                                        <input name="agent[first]" type="text" placeholder="First" class="form-control" value="<?php echo isset( $post['agent']['first'] ) ? $post['agent']['first'] : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="agent[last]">Last name</label>
                                        <input name="agent[last]" type="text" placeholder="Last" class="form-control" value="<?php echo isset( $post['agent']['last'] ) ? $post['agent']['last'] : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="">Contact numbers <small><em>(Press Enter to accept contact)</em></small></label>
                                <input name="agent[contacts]" class="form-control" type="text" placeholder="Add contact" value="<?php echo isset( $post['agent']['contacts'] ) ? $post['agent']['contacts'] : ''; ?>" />
                            </div>
                         
<div class="form-group">
<label for="agent[email]">Type</label>
<select class="form-control" name="agent[branch_type]" >
<option></option>
<option VALUE="1">MAIN</option>
<option VALUE="2">ILOILO</option>
<option VALUE="3">BACOLOD</option>
<option VALUE="4">CDO</option>
<option VALUE="5">GENSAN</option>

</select> </div> 
					


		


   <div class="form-group">
                                <label for="">Bank Details <small><em></em></small></label>
                                <input name="agent[agent_remarks]" type="text"  class="form-control"  placeholder="Add Bank Details" value="<?php echo isset( $post['agent']['agent_remarks'] ) ? $post['agent']['agent_remarks'] : ''; ?>"/>
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
