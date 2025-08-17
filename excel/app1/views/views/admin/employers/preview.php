<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
            <h4 class="modal-title"><?php echo $employer['employer_name']; ?></h4>
        </div>
        
        <form method="post" class="form" role="form" action="<?php echo site_url( 'admin/jobs/add' ); ?>">
        <div class="modal-body">
            <div class="bootbox-body">
                <div class="row">
                    <div class="col-sm-12">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th width="40%">Employer</th>
                                    <td><?php echo $employer['employer_name']; ?></td>
                                </tr>
                                <tr>
                                    <th>Employer #</th>
                                    <td><?php echo $employer['employer_no']; ?></td>
                                </tr>
                                <tr>
                                    <th>Contact person</th>
                                    <td><?php echo $employer['employer_contact_person']; ?></td>
                                </tr>
                                <tr>
                                    <th>Country</th>
                                    <td><?php echo $employer['country_name']; ?></td>
                                </tr>
                                <tr>
                                    <th>Email address</th>
                                    <td><a href="mailto:<?php echo $employer['employer_email']; ?>"><?php echo $employer['employer_email']; ?></a></td>
                                </tr>
                                <tr>
                                    <th>Contact#</th>
                                    <td><?php echo $employer['employer_contact']; ?></td>
                                </tr>
                                <tr>
                                    <th>Marketing Agency:</th>
                                    <td><?php echo $employer['agency_name']; ?></td>
                                </tr>
                                <tr>
                                    <th>Marketing Agent:</th>
                                    <td><?php echo $employer['agent_first'].' '.$employer['agent_last']; ?></td>
                                </tr>
                                <tr>
                                    <th>Remarks:</th>
                                    <td><?php echo $employer['employer_remarks']; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <hr class="wide" />
                
                <div class="row">
                    <div class="col-sm-12">
                        
                        <table class="table table-bordered table-condensed">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Job title</th>
                                    <th>Position</th>
                                    <th>Workers needed</th>
                                    <th>Offer Salary</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $ctr = 0; ?>
                                <?php foreach ( $jobs as $job ): ?>
                                <?php $ctr ++; ?>
                                <tr>
                                    <td><?php echo $ctr; ?></td>
                                    <td><?php echo $job['job_name']; ?></td>
                                    <td><?php echo $job['job_position']; ?></td>
                                    <td><?php echo $job['job_total']; ?></td>
                                    <td><?php echo number_format( $job['job_salary_from'], 2 ).' &minus; '.number_format( $job['job_salary_from'], 2 ); ?></td>
                                    <td><?php echo fdate('Y-m-d h:ia', $job['job_created']); ?></td>
                                </tr>
                                <?php endforeach; ?> 
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
       </div>
        <div class="modal-footer">
            <button class="btn btn-default" data-dismiss="modal" type="button">Okay</button>
        </div>
        </form>
    </div>
</div>
</div>
