<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url('admin/'); ?>">Home</a>
        </li>
        <li>Applicants</li>
        <li class="active">Expired Reservations</li>
    </ul>
</div>
<!-- /Page Breadcrumb -->
<!-- Page Header -->
<div class="page-header position-relative">
    <div class="header-title">
        <h1>
            Expired Reserved Applicants
        </h1>
    </div>
    <!--Header Buttons-->
    <div class="header-buttons">
        <a class="sidebar-toggler" href="#">
            <i class="fa fa-arrows-h"></i>
        </a>
        <a class="refresh" id="refresh-toggler" href="#">
            <i class="fa fa-refresh"></i>
        </a>
        <a class="fullscreen" id="fullscreen-toggler" href="#">
            <i class="fa fa-arrows-alt"></i>
        </a>
    </div>
    <!--Header Buttons End-->
</div>
<!-- /Page Header -->
<!-- Page Body -->
<div class="page-body page-<?php echo $app->getTemplate(); ?>">
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <div class="widget">
                <form method="post">
                <div class="widget-header with-footer">
                    <button type="submit" class="btn btn-sm btn-default pull-left" disabled onClick="return confirm('Are you sure?')" style="margin-top:5px;">Back to available</button>
                    <div class="widget-buttons">
                        <a href="#" data-toggle="maximize">
                            <i class="fa fa-expand"></i>
                        </a>
                        <a href="#" data-toggle="collapse">
                            <i class="fa fa-minus"></i>
                        </a>
                        <a href="#" data-toggle="dispose">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>
                </div>
                <div class="widget-body">
                    <?php $app->renderAlerts(); ?>
                    <div class="table-responsive" style="overflow:scroll; padding:10px;">
                        
                        <table class="table table-bordered ">
                            <?php if ( count( $applicants ) == 0 ): ?>
                            <tr>
                                <td>No entries</td>
                            </tr>
                            <?php else: ?>
                            <thead class="  bordered-palegreen">
                                <tr>
                                    <th></th>
                                    <th>
                                        <div class="checkbox">
                                            <label>
                                                <input name="select[all]" type="checkbox" class="chk-all inverted" value="all" >
                                                <span class="text"></span>
                                            </label>
                                        </div>
                                    </th>
                                    <th>#</th>
                                    <th>Applicant</th>
                                    <th>Employer</th>
                                    <th>Applicant #</th>
                                    <th>Passport</th>
                                    <th>Visa</th>
                                    <th>Date applied</th>
                                    <th>Source</th>
                                    <th>Date reserved</th>
                                    <th>Date expired</th>
                                    <th>&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $ctr = $paginationCounter['from']; ?>
                                <?php foreach ( $applicants as $applicant ): ?>
                                <?php $reservationExpired = strtotime( $applicant['reservation_expiration'] ) <= strtotime( date( 'Y-m-d', time() ) ); ?>
                                <tr>
                                    <td>
                                        <div class="checkbox">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" class="inverted applicants-selection" value="<?php echo $applicant['applicant_id']; ?>" >
                                                    <span class="text"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="checkbox">
                                            <label>
                                                <input name="select[<?php echo $applicant['applicant_id']; ?>]" type="checkbox" class="chk-applicants inverted" value="<?php echo $applicant['applicant_id']; ?>" >
                                                <span class="text"></span>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo str_pad( $ctr, 5, '0', STR_PAD_LEFT ); ?>
                                    </td>
                                    <td>
                                        <a class="btn-applicant-review" data-toggle="modal" data-target="#modalApplicantReview" role="button" href="javascript:void();" data-url="<?php echo site_url( 'admin/applicants/review/' . $applicant['applicant_slug'] ); ?>" title="Review">
                                            <strong><?php echo $applicant['applicant_first'].' '.$applicant['applicant_middle'].' '.$applicant['applicant_last']; ?></strong>
                                        </a> 
                                    </td>
                                    <td><?php echo $applicant['employer_name']; ?></td>
                                    <td>--</td>
                                    <td class="numeric">
                                        <?php if ( ! empty( $applicant['passport_id'] ) ): ?>
                                        <?php echo $applicant['passport_number']; ?>
                                        <?php else: ?>
                                        --
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($applicant['requirement_visa']) && ($applicant['requirement_visa'] == 1) ): ?>
                                        <span class="text-success"><i class="fa fa-check"></i></span>
                                        <?php else: ?>
                                        --
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo date( 'd-M-Y', strtotime( $applicant['applicant_date_applied'] ) ); ?>
                                    </td>
                                    <td class="numeric">
                                        <?php if ( $applicant['applicant_source'] == 0 ): ?>
                                        <span class="text-danger">DIRECT HIRE</span>
                                        <?php else: ?>
                                        
                                        <?php endif; ?>
                                    </td>
                                    <td class="numeric warning">
                                        <?php echo date( 'd M Y', strtotime( $applicant['reservation_created'] ) ); ?>
                                    </td>
                                    <td class="numeric danger">
                                        <?php echo date( 'd M Y', strtotime( $applicant['reservation_expiration'] ) ); ?>
                                    </td>
                                    <td>
                                        <?php if( in_array( $_SESSION['admin']['user']['user_type'], [7]) ):  ?>
                                            --
                                        <?php else: ?>
                                        <a href="#" role="button" class="btn btn-xs btn-default btn-extend-reserved"  data-toggle="modal" data-target="#modalExtendReserved" data-reservation="<?php echo $applicant['reservation_id']; ?>">
                                        Extend...
                                        </a>
                                        <?php endif;?>
                                    </td>
                                </tr>
                                <?php $ctr++; ?>
                                <?php endforeach; ?>
                                
                            </tbody>
                            <?php endif; ?>
                        </table> 
                    </div><!-- .table-responsive -->
                    <hr>
                    <div class="footer" align="right">
                        <span class="pull-left">
                           Showing <?php echo $paginationCounter['from']; ?> to <?php echo $paginationCounter['to']; ?> of <?php echo $paginationCounter['total-records']; ?> entries
                        </span>
                        <?php echo $paginationHTML; ?>
                        <div class="clearfix"></div>
                    </div> 

                    <hr>
                    <div class="row">
                        <div class="col-sm-3">
                            <p><strong>Send Applicants To:</strong></p>                            
                            
                            <div class="input-group">
                              <select id="employers-selection" class="form-control" disabled="disabled">
                                    <option value="">-- Select Employer --</option>
                                    <?php foreach ( $employers as $employer ): ?>
                                    <option value="<?php echo $employer['employer_id']; ?>"><?php echo $employer['employer_name']; ?></option>
                                    <?php endforeach; ?>
                                </select>

                              <span class="input-group-btn">
                                <button class="btn btn-sm btn-primary" id="btn-send-applicant" disabled="disabled">Send Applicants</button>
                              </span>
                            </div>
                        </div>
                    </div>

                </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /Page Body -->

<div id="modalExtendReserved" role="dialog" tabindex="-1" class="modal fade modal-darkorange" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
                <h4 class="modal-title">--</h4>
            </div>
            
            <form method="post" class="form" role="form">
            <input type="hidden" name="reservation[reservation_id]" value="0">
            <div class="modal-body">
                <div class="bootbox-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="reservation[extend]">Days to extend starting today (<?php echo date( 'd M Y', time() ); ?>):</label>
                                <input name="reservation[extend]" type="text" placeholder="0" class="form-control" value="" style="text-transform:uppercase">
                            </div>
                            
                            <hr class="wide" />
                            <div class="form-group"> 
                                <label for="reservation[remarks]">Note/Remarks:</label>
                                <textarea name="reservation[remarks]" rows="2" placeholder="Remarks" class="form-control"></textarea>
                            </div>
                            
                        </div>
                    </div> 
                    
                </div>
           </div>
            <div class="modal-footer">
                <button class="btn btn-default " data-dismiss="modal"  type="button">Cancel</button>
                <button class="btn btn-primary" type="submit">Update</button>
            </div>
            </form>
        </div>
    </div>
</div>