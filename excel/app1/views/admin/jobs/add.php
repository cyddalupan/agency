<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
            <h4 class="modal-title">New Job Offer</h4>
        </div>
        
        <form method="post" class="form" role="form" action="<?php echo site_url( 'admin/jobs/add' ); ?>">
        <div class="modal-body">
            <div class="bootbox-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Employer</label>
                            <select name="job[employer]" style="width:100%" required>
                                <option value="">-- Select --</option>
                                <?php foreach ($employers as $employer): ?>
                                <option value="<?php echo $employer['employer_id']; ?>" <?php echo ( isset( $post['job']['employer'] ) && $post['job']['employer'] == $employer['employer_id'] ) || ( isset( $_GET['ref_emp'] ) && $_GET['ref_emp'] == $employer['employer_id'] ) ? 'selected' : '';  ?>><?php echo $employer['employer_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
					
					   <div class="col-sm-3">
                        <div class="form-group">
                            <label>Accredatation No.</label>
                            <input type="text" name="job[accre]" class="form-control"  value="">
                        </div>
                    </div> 
					
						 <div class="col-sm-3">
                        <div class="form-group">
                            <label>Expired Date</label>
                            <input type="date" name="job[end]"  class="form-control"  value="">
                        </div>
                    </div> 
					
					
                </div>
                <div class="row">
                    <div class="col-sm-8">
                        <div class="form-group">
                            <label>Title of the post</label>
                            <input type="text" name="job[name]" class="form-control" placeholder="" value="<?php echo isset( $post['job']['name'] ) ? $post['job']['name'] : ''; ?>" required>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Number of workers needed</label>
                            <input type="text" name="job[total]" class="form-control" placeholder="0" value="<?php echo isset( $post['job']['total'] ) ? $post['job']['total'] : '0'; ?>">
                        </div>
                    </div> 
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Position</label>
                            <select name="job[position]" style="width:100%" required>
                                <option value="">-- Select position --</option>
                                <?php foreach ($categories as $category): ?>
                                <optgroup label="<?php echo $category['category_name']; ?>">
                                <?php foreach ($category['positions'] as $position): ?>
                                <option value="<?php echo $position['position_id']; ?>" <?php echo isset( $post['job']['position'] ) && $post['job']['position'] == $position['position_id'] ? 'selected' : '';  ?>><?php echo $position['position_name']; ?></option>
                                <?php endforeach; ?>
                                </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="job[gender]" class="form-control">
                                <option value="Any">Any</option>
                                <option value="Male" <?php echo isset( $post['job']['gender'] ) && $post['job']['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo isset( $post['job']['gender'] ) && $post['job']['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Salary (From)</label>
                            <input type="text" name="job[salary-from]" class="form-control" placeholder="0.00" value="<?php echo isset( $post['job']['salary-from'] ) ? $post['job']['salary_from'] : '0'; ?>" required>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Salary (To)</label>
                            <input type="text" name="job[salary-to]" class="form-control" placeholder="0.00" value="<?php echo isset( $post['job']['salary-to'] ) ? $post['job']['salary_to'] : '0'; ?>" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label>Qualification</label>
                            <span class="input-icon icon-right">
                                <textarea name="job[content]" class="form-control" rows="4" placeholder=""><?php echo isset( $post['job']['content'] ) ? $post['job']['content'] : ''; ?></textarea>
                                <i class="fa fa-briefcase darkorange"></i>
                            </span>
                        </div>
                    </div>
                </div>
				
				
				   <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label>Requirements</label>
                            <span class="input-icon icon-right">
                                <textarea name="job[requirements]" class="form-control" rows="4" placeholder=""><?php echo isset( $post['job']['requirements'] ) ? $post['requirements']['requirements'] : ''; ?></textarea>
                                <i class="fa fa-briefcase darkorange"></i>
                            </span>
                        </div>
                    </div>
                </div>
				
				
                
                <hr class="wide" />
                
                <div class="row" style="display:none">
                    <div class="col-sm-12">
                        
                        <table class="table table-bordered table-condensed">
                            <tbody>
                                <?php $currentGroup = ''; ?>
                                <?php foreach ( $fees as $fee ): ?>
                                <?php if ( $currentGroup != $fee['fee_group'] ): ?>
                                <?php $currentGroup = $fee['fee_group'];  ?>
                                <tr>
                                    <th align="center" colspan="3"><?php echo $fee['fee_group']; ?></th>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td>
                                        <?php echo $fee['fee_name']; ?>
                                        <?php if ( $fee['fee_id'] == 2 ): //Service Fee ?>
                                               <span class="pull-right">($<?php echo number_format( $dollar, 2); ?> = &#8369;1.00)</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ( $fee['fee_id'] == 1 ): //Placement Fee ?>
                                    <td colspan="3">
                                        <table class="table-condensed">
                                            <tr>
                                                <td>
                                                    <div class="checkbox">
                                                        <label>
                                                            <input name="job[fee][<?php echo $fee['fee_id']; ?>][E]" type="checkbox" class="colored-blue" checked>
                                                            <span class="text">E</span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                      <span class="input-group-addon">&#8369; / $</span>
                                                      <input type="text" placeholder="0.00" class="form-control fee-amount" name="job[fee][<?php echo $fee['fee_id']; ?>][amount][E]"> 
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="checkbox">
                                                        <label>
                                                            <input name="job[fee][<?php echo $fee['fee_id']; ?>][W]" type="checkbox" class="colored-blue" checked>
                                                            <span class="text">W</span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                      <span class="input-group-addon">&#8369; / $</span>
                                                      <input type="text" placeholder="0.00" class="form-control fee-amount" name="job[fee][<?php echo $fee['fee_id']; ?>][amount][W]"> 
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <th width="40%">&nbsp;</th>
                                    <th width="30%">Amount</th>
                                    <th colspan="2" width="30%">Bill to</th>
                                </tr>
                                    <?php else: ?>
                                    <td>
                                        <div class="input-group">
                                          <span class="input-group-addon">
                                          <?php echo $fee['fee_id'] == 2 ? '&#8369;/$' : '&#8369;/$'; ?>
                                          </span>
                                          <input name="job[fee][<?php echo $fee['fee_id']; ?>][amount]" type="text" class="form-control fee-amount" placeholder="0.00"> 
                                        </div>
                                    </td>
                                    <td width="15%">
                                        <?php if ( $fee['fee_shareable'] ): ?>
                                        <div class="checkbox">
                                            <label>
                                                <input name="job[fee][<?php echo $fee['fee_id']; ?>][E]" type="checkbox" class="colored-blue" checked>
                                                <span class="text">E</span>
                                            </label>
                                        </div>
                                        <?php else: ?>
                                        <div class="checkbox">
                                            <label>
                                                <input name="job[fee][<?php echo $fee['fee_id']; ?>][billto]" type="radio" value="E" class="colored-blue" checked>
                                                <span class="text">E</span>
                                            </label>
                                        </div>
                                        <?php endif; ?>                                                                    
                                    </td>
                                    <td width="15%">
                                        <?php if ( $fee['fee_shareable'] ): ?>
                                        <div class="checkbox">
                                            <label>
                                                <input name="job[fee][<?php echo $fee['fee_id']; ?>][W]" type="checkbox" class="colored-blue">
                                                <span class="text">W</span>
                                            </label>
                                        </div>
                                        <?php else: ?>
                                        <div class="checkbox">
                                            <label>
                                                <input name="job[fee][<?php echo $fee['fee_id']; ?>][billto]" type="radio" value="W" class="colored-blue">
                                                <span class="text">W</span>
                                            </label>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?> 
                        </table>
                    </div>
                </div>
                
                <hr class="wide" />
                
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label>Remarks <small><em>(optional)</em></small></label>
                            <span class="input-icon icon-right">
                                <textarea name="job[remarks]" class="form-control" rows="1" placeholder="Write some remarks..."><?php echo isset( $post['job']['remarks'] ) ? $post['job']['remarks'] : ''; ?></textarea>
                            </span>
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
