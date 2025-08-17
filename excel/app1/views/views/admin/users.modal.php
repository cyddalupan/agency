<div id="modalAddUser" role="dialog" tabindex="-1" class="modal fade modal-darkorange" aria-hidden="false">
	<div class="modal-dialog">
    	<div class="modal-content">
        	<div class="modal-header">
            	<button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
                <h4 class="modal-title"><h4>
            </div>
            
            <form method="post" class="form" role="form" action="<?php echo site_url( 'admin/users/add' ); ?>">
            <div class="modal-body">
                <div class="bootbox-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                            	<label for="user[fullname]">Name</label>
                                <input name="user[fullname]" type="text" placeholder="Full name" class="form-control" value="<?php echo isset( $post['user']['fullname'] ) ? $post['user']['fullname'] : ''; ?>" required>
                            </div>
                            <div class="form-group">
                            	<label for="user[type]">User type</label>
                                <select name="user[type]" style="width:100%" required>
                                	<option value="" >-- Select --</option>
                                    <?php foreach ( $userTypes as $typeId => $type ): ?>
                                    <?php if ( $typeId == 4 && $_SESSION['admin']['user']['user_type'] != 4 ) continue; ?>
                                    <option value="<?php echo $typeId; ?>" ><?php echo $type; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group hide employer-account">
                                <label for="user[employer]">=> Employer account:</label>
                                <select name="user[employer]" style="width:100%">
                                    <?php foreach ( $employers as $employerId => $employer ): ?>
                                    <option value="<?php echo $employerId; ?>" ><?php echo $employer['employer_name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                            	<label for="user[email]">Email address <small><em>(optional)</em></small></label>
                                <input name="user[email]" type="text" placeholder="E-mail address" class="form-control" value="<?php echo isset( $post['user']['email'] ) ? $post['user']['email'] : ''; ?>" style="text-transform:lowercase">
                            </div>
                            <hr class="wide" />
                            <div class="form-group">
                            	<label for="user[name]">User name</label>
                                <input name="user[name]" type="text" placeholder="Username" class="form-control" value="<?php echo isset( $post['user']['name'] ) ? $post['user']['name'] : ''; ?>" style="text-transform:lowercase" required>
                            </div>
                            <div class="form-group">
                            	<label for="user[password]">Password</label>
                                <input name="user[password]" type="password" placeholder="Password" class="form-control" required>
                            </div>
                            <div class="form-group">
                            	<label for="user[password2]">Confirm password</label>
                                <input name="user[password2]" type="password" placeholder="Confirm password" class="form-control" required>
                            </div>
                            <hr class="wide" />
                            <div class="form-group">
                            	<label for="user[remarks]">Remarks</label>
                                <textarea name="user[remarks]" rows="5" placeholder="Remarks" class="form-control"><?php echo isset( $post['user']['remarks'] ) ? $post['user']['remarks'] : ''; ?></textarea>
                            </div>
                            
                        </div>
                    </div> 
                    
                </div>
           </div>
			<div class="modal-footer">
    			<button class="btn btn-default" data-dismiss="modal"  type="button">Cancel</button>
                <button class="btn btn-primary" type="submit">Add</button>
			</div>
            </form>
		</div>
	</div>
</div>



<div id="modalSettings" role="dialog" tabindex="-1" class="modal fade modal-darkorange" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
                <h4 class="modal-title">Edit User Account</h4>
            </div>
            
            <form method="post" class="form" role="form" action="<?php echo site_url( 'admin/users/edit/' ); ?>">
            <input type="hidden" name="user[id]" value="0">
            <div class="modal-body">
                <div class="bootbox-body">
                    <div class="row">
                        <div class="col-md-12">


                              <div class="form-group">
                                <label for="user[type]">User type</label>
                                <select name="user[type]" style="width:100%" required>
                                    <option value="" >-- Select --</option>
                                    <?php foreach ( $userTypes as $typeId => $type ): ?>
                                    <?php if ( $typeId == 4 && $_SESSION['admin']['user']['user_type'] != 4 ) continue; ?>
                                    <option value="<?php echo $typeId; ?>" ><?php echo $type; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>     



                            <div class="form-group">
                                <label for="user[fullname]">Name</label>
                                <input name="user[fullname]" type="text" placeholder="Full name" class="form-control" value="" required>
                            </div>
                            <div class="form-group">
                                <label for="user[email]">Email address <small><em>(optional)</em></small></label>
                                <input name="user[email]" type="text" placeholder="E-mail address" class="form-control" value="" style="text-transform:lowercase">
                            </div>
                            <hr class="wide" />
                            <div class="form-group select-team-lead">
                                <label for="user[teamlead]">Team Lead</label>
                                <br/>
                                <select name="user[teamlead]" class="form-control select-team-lead-input">
                                    <option value="0"></option>
                                    <?php foreach ($users as $key => $value) {
                                        if($value['user_type'] == 9)
                                            echo '<option value="'.$value['user_id'].'">'.$value['user_name'].'</option>';
                                    }//end loop all users ?>
                                </select>
                            </div>
                            <hr class="wide" />
                            <div class="form-group">
                                <label for="user[name]">User name</label>
                                <input name="user[name]" type="text" placeholder="Username" class="form-control" value="" style="text-transform:lowercase" disabled="disabled" required>
                            </div>
                           
                            <div class="form-group">
                                <label for="user[password]">New password</label>
                                <input name="user[password]" type="password" placeholder="New password" class="form-control"   required>
                            </div>
                            <div class="form-group">
                                <label for="user[password2]">Confirm password</label>
                                <input name="user[password2]" type="password" placeholder="Confirm password" class="form-control" required>
                            </div>
                            <hr class="wide" />
                            <div class="form-group">
                                <label for="user[remarks]">Remarks</label>
                                <textarea name="user[remarks]" rows="5" placeholder="Remarks" class="form-control"></textarea>
                            </div>
                            
                        </div>
                    </div> 
                    
                </div>
           </div>
            <div class="modal-footer">
                <button class="btn btn-danger cyd_delete_user" style="float:left;" data-dismiss="modal" type="button">
                    Delete User
                </button>
                <button class="btn btn-default" data-dismiss="modal"  type="button">Cancel</button>
                <button class="btn btn-primary" type="submit">Save changes</button>
            </div>
            </form>
        </div>
    </div>
</div>
