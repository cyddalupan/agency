<div class="row">
 <div class="col-md-12">
    
    <div class="clients-page">
       <?php $app->renderAlerts(); ?>
       <div class="row">
          <div class="col-md-12">
           
             <div class="hero">
                <!-- Title. Don't forget the <span> tag -->
                <h3><span>Job Orders</span></h3>
            </div> 
             
             <div class="row">
                <div class="col-md-12">
                
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Job offer title</th>
                                    <th>Number of workers needed</th>
                                   
                                    <th>Number of deployed applicants</th>
									<th>Date created</th>
                                    <th width="5%">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ( count( $jobs ) == 0 ): ?>
                                <tr>
                                    <td colspan="7" align="center">-- No records --</td>
                                </tr>
                                <?php endif; ?>
                                <?php $ctr = $paginationCounter['from']; ?>
                                <?php foreach ( $jobs as $job ): ?>
                                <tr class="">
                                    <td><?php echo $ctr; ?></td>
                                    <td><?php echo $job['job_name']; ?></td>
									<td><?php echo number_format( $job['job_total'] ); ?></td>

                                    <td>
                                       <?php echo number_format( $job['deployed'] ); ?>
                                    </td>
                                    <td><?php echo fdate( 'd-M-Y', $job['job_created'], '0000-00-00' ); ?></td>
                                    <td style="white-space:normal">
                                        <a href="#" class="btn btn-xs btn-info">
											Review
										</a>
                                    </td>                                    
                                </tr>
                                <?php $ctr ++; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                </div>
             </div>
             
          </div>
       </div>
    </div>
     
    
 </div>
</div>