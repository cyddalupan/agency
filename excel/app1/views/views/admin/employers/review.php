<div class="modal-dialog">

    <div class="modal-content">

        <div class="modal-header">

            <button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>

            <h4 class="modal-title"><?php echo $employer['employer_name']; ?></h4>

        </div>

        

        <form method="post" class="form" role="form" action="<?php echo site_url( 'admin/employers/review/'.$employer['employer_id'] ); ?>">

        <input type="hidden" name="employer[employer_id]" value="0">

        <div class="modal-body">

            <div class="bootbox-body">

                <div class="row">

                    <div class="col-md-12">

                        <div class="form-group">

                            <label for="employer[country]">Preferred country</label>

                            <select name="employer[country]" style="width:100%">

                                <option value="" >-- Select --</option>

                                <?php foreach ( $countries as $country ): ?>

                                <option value="<?php echo $country['country_id']; ?>" <?php echo $employer['employer_country'] == $country['country_id'] ? 'selected' : ''; ?> ><?php echo $country['country_name']; ?></option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="form-group">

                            <label for="employer[number]">Employer number</label>

                            <input name="employer[number]" type="text" placeholder="Employer number" class="form-control" value="<?php echo $employer['employer_no']; ?>" style="text-transform:uppercase">

                        </div>

                        <div class="form-group">

                            <label for="employer[name]">Employer / Agency name</label>

                            <input name="employer[name]" type="text" placeholder="Employer / Agency name" class="form-control" value="<?php echo $employer['employer_name']; ?>">

                        </div>

                        <div class="form-group">

                            <label for="employer[employer_user]">Recruitment Specialist</label>

                            <select name="employer[employer_user]" class="form-control">
                                <option value="0">N/A</option>
                                <!-- for selecting results <?php echo $employer['employer_user']; ?>-->
                                <?php foreach ($users as $key => $value) {
                                    if($employer['employer_user'] == $value['user_id'])
                                        $active_user = 'selected';
                                    else
                                        $active_user = '';

                                    echo '<option value="'.$value['user_id'].'" '.$active_user.'>'.$value['user_name'].'</option>';
                                }?>
                            </select>

                        </div>


                        <div class="form-group">

                            <label for="employer[address]">Address <small><em>(optional)</em></small></label>

                            <input name="employer[address]" type="text" placeholder="Employer address" class="form-control" value="<?php echo $employer['employer_address']; ?>">

                        </div>

                        <div class="form-group">

                            <label for="employer[email]">Email address <small><em>(optional)</em></small></label>

                            <input name="employer[email]" type="text" placeholder="E-mail address" class="form-control" value="<?php echo $employer['employer_email']; ?>" style="text-transform:lowercase">

                        </div>

                        <div class="form-group">

                            <label for="employer[contact_person]">Hospital / Contact Person</label>

                            <input name="employer[contact_person]" type="text" placeholder="Contact person" class="form-control" value="<?php echo $employer['employer_contact_person']; ?>">

                        </div>

                        <div class="form-group">

                            <label for="employer[contact]">Contact number</label>

                            <input name="employer[contact]" type="text" placeholder="Contact number" class="form-control" value="<?php echo $employer['employer_contact']; ?>">

                        </div>

                        

                        <hr class="wide" />

                        

                        <div class="form-group">

                           <div class="checkbox">

                                <label>

                                    <input name="employer[change-password]" type="checkbox">

                                    <span class="text">Change password</span>

                                </label>

                            </div> 

                        </div>

                                                

                        <hr class="wide" />

                        

                        <div class="change-password hide">

                            <div class="form-group">

                                <label for="employer[user][name]">User name</label>

                                <input name="employer[user][name]" type="text" placeholder="Username" class="form-control" value="<?php echo $employer['user_name']; ?>" style="text-transform:lowercase" disabled="disabled" readonly>

                            </div>

                            <div class="form-group">

                                <label for="employer[user][password]">New password</label>

                                <input name="employer[user][password]" type="password" placeholder="Password" class="form-control">

                            </div>

                            <div class="form-group">

                                <label for="employer[user][password2]">Confirm password</label>

                                <input name="employer[user][password2]" type="password" placeholder="Confirm password" class="form-control">

                            </div>

                            

                             <hr class="wide" /> 

                        </div>

                       

                        <div class="form-group">

                            <div class="row">

                                <div class="col-sm-6"> 

                                    <label> Source (Marketing Agency):</label>

                                    <select name="employer[source_agency]" style="width:100%">

                                        <option value="" >-- Select --</option>

                                        <option value="0" >----</option>

                                        <?php foreach ( $agencies as $agency ): ?>

                                        <option value="<?php echo $agency['agency_id']; ?>" <?php echo $employer['employer_source_agency'] == $agency['agency_id']? 'selected' : ''; ?>><?php echo $agency['agency_name']; ?></option>

                                        <?php endforeach; ?>

                                    </select>

                                    

                                    <div class="row">

                                        <div class="col-sm-6">

                                            <label>Commission (%)</label>                                        

                                            <div class="input-group">

                                              <input name="employer[source_agency_commission]" type="text" class="form-control" placeholder="0" min="0" max="100" value="<?php echo $employer['employer_agency_commission']; ?>">

                                              <span class="input-group-addon">%</span>

                                            </div>

                                        </div>

                                        <div class="col-sm-6">

                                            <label>From</label>

                                            <select name="employer[source_agency_commission_from]" class="form-control">

                                                <option value="Placement Fee" <?php echo $employer['employer_agency_commission_from'] == 'Placement Fee' ? 'selected' : ''; ?>>Placement Fee</option>

                                                <option value="Service Fee" <?php echo $employer['employer_agency_commission_from'] == 'Service Fee' ? 'selected' : ''; ?>>Service Fee</option>

                                            </select>

                                        </div>

                                    </div>

                                    

                                </div>

                                <div class="col-sm-6" style="border-left:1px dashed #999">

                                    <label> Source (Marketing Agent):</label>

                                    <select name="employer[source_agent]" style="width:100%">

                                        <option value="" >-- Select --</option>

                                        <option value="0" >----</option>

                                        <?php foreach ( $agents as $agent ): ?>

                                        <option value="<?php echo $agent['agent_id']; ?>" <?php echo $employer['employer_source_agency'] == $agent['agent_id']? 'selected' : ''; ?>><?php echo $agent['agent_first'].' '.$agent['agent_last']; ?></option>

                                        <?php endforeach; ?>

                                    </select>

                                    

                                    <div class="row">

                                        <div class="col-sm-6">

                                            <label>Commission (%)</label>                                        

                                            <div class="input-group">

                                              <input name="employer[source_agent_commission]" type="text" class="form-control" placeholder="0" min="0" max="100" value="<?php echo $employer['employer_agent_commission']; ?>">

                                              <span class="input-group-addon">%</span>

                                            </div>

                                        </div>

                                        <div class="col-sm-6" >

                                            <label>From</label>

                                            <select name="employer[source_agent_commission_from]" class="form-control">

                                                <option value="Placement Fee" <?php echo $employer['employer_agent_commission_from'] == 'Placement Fee' ? 'selected' : ''; ?>>Placement Fee</option>

                                                <option value="Service Fee" <?php echo $employer['employer_agent_commission_from'] == 'Service Fee' ? 'selected' : ''; ?>>Service Fee</option>

                                            </select>

                                        </div>

                                    </div>

                                    

                                </div>

                            </div>                              

                        </div>

                        

                        <hr class="wide" />

                        <div class="form-group"> 

                            <label for="employer[remarks]">Remarks</label>

                            <textarea name="employer[remarks]" rows="5" placeholder="Remarks" class="form-control"><?php echo $employer['employer_remarks']; ?></textarea>

                        </div>

                        

                    </div>

                </div> 



                <hr class="wide">



                <div class="row">

                    <div class="col-sm-12">

                        <p><a href="<?php href( 'admin/jobs/add' ); ?>?ref_emp=<?php echo $employer['employer_id']; ?>" class="btn btn-xs btn-info btn-add-new-job" data-dismiss="modal"  type="button">ADD NEW</a></p>

                        <table class="table table-condensed table-bordered">

                            <thead>

                                <tr>

                                    <th>#</th>

                                    <th>Job title</th>

                                    <th>Position</th>

                                    <th>Salary offer</th>

                                    <th>Gender</th>

                                    <th>Workers needed</th>

                                    <th>Remaining slot</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php if ( count( $employer['job-offers'] ) == 0 ): ?>

                                <tr>

                                    <td align="center" colspan="6">-- No job offers yet --</td>

                                </tr>

                                <?php endif; ?>

                                <?php $ctr = 0; ?>

                                <?php foreach ($employer['job-offers'] as $job): ?>

                                <?php $ctr ++; ?>

                                <tr>

                                    <td><?php echo $ctr; ?></td>

                                    <td><?php echo $job['job_name'] ?></td>

                                    <td><?php echo $job['position_name']; ?></td>

                                    <td>P<?php echo number_format($job['job_salary_from']).' to P'.number_format($job['job_salary_to']); ?></td>

                                    <td><?php echo $job['job_gender']; ?></td>

                                    <td><?php echo (int) $job['job_total']; ?></td>

                                    <td><?php echo (int) $job['job_total'] - $job['job_occupied']; ?></td>

                                </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>



                    </div>

                </div>

                

            </div>

       </div>

        <div class="modal-footer">

            <a href="<?php echo site_url( 'admin/employers/all?delete='.$employer['employer_id'].'&amp;token='.session_id() ); ?>" onClick="return confirm('This will delete the user account of this employer\nThis will delete all job offers of this employer\nThis will delete all transactions of this employer\n\nAre you sure you want to do this?')">Delete employer</a>

            <button class="btn btn-default btn-close-employer" data-dismiss="modal"  type="button">Cancel</button>

            <button class="btn btn-primary" type="submit">Save changes</button>

        </div>

        </form>

    </div>

</div>

<script>



$('.btn-add-new-job').on('click', function() {

    

    var href = $(this).attr( 'href' );



    $(".modal-backdrop").fadeOut(function() {

        $('.btn-add-job').attr('href', href).click();

    });

});



$('input[name="employer[change-password]"]').on('change', function() {

    if ( $(this).is(':checked') ) {

        $('.change-password').hide().removeClass('hide').slideDown();

        $('input[name="employer[user][password]"]').focus();

        return;

    }

    

    $('.change-password').slideUp(function() {

        $(this).addClass('hide');

    });

});



</script>