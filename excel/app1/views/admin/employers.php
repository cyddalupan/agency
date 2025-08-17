<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
<ul class="breadcrumb">
<li>
<i class="fa fa-home"></i>
<a href="<?php echo site_url('admin/dashboard'); ?>">Home</a>
</li>
<li class="active">Employers</li>
</ul>
</div>
<!-- /Page Breadcrumb -->
<!-- Page Header -->
<div class="page-header position-relative">
<div class="header-title">
<h1>
Employers
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

	<div class="widget-header with-footer">
		<span class="widget-caption">
			<a href="<?php echo site_url( 'admin/employers/add' ); ?>" role="button" class="btn btn-xs btn-default" data-toggle="modal" data-target="#modalReports">
				<i class="fa fa-plus"></i> Add new employer
			</a>
		</span>
		
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
			<table class="table table-bordered table-hover table-applicants">
				<thead class="bordered-palegreen">
					<tr>
						<th>&nbsp;</th> 
						<th>#</th>
					 
						<th>Agency name</th>
						<th><b>Selection Process</b></th>
						<th>Contact person</th>
						<th>Contact</th>
						<th>Country</th>
						



						
						
						<th>Agreement</th>
				
						<th>Access / Username</th>
						<th>Last login</th>
						<th>Date created</th>
					 
					</tr>
				</thead>
				<tbody>
					<?php if ( count( $employers ) == 0 ): ?>
						<tr>	
							<td colspan="11" align="center"> -- No employer records --</td>
						</tr>
					<?php endif;?>
					
					<?php foreach ( $employers as $employer ): ?>
					<?php $ctr = isset( $ctr ) ? $ctr + 1 : 1; ?>
					<tr>
							  <td class="numeric">
							<a href="<?php echo site_url( 'admin/employers/review/'.$employer['employer_id'] ); ?>" role="button" class="btn btn-xs btn-default" data-toggle="modal" data-target="#modalReports">
								<i class="fa fa-pencil"></i>
							</a>
							

						</td> 
					
						
						
						
						<td style="text-transform:uppercase">
							<abbr title="<?php echo $employer['employer_name']; ?>">000<?php echo $employer['employer_id']; ?></abbr>
						</td>
						<td>
							<a href="<?php echo site_url( 'admin/employers/review/'.$employer['employer_id'] ); ?>" role="button" class="" data-toggle="modal" data-target="#modalReports">
								<strong><?php echo $employer['employer_name']; ?></strong>
							</a>
						</td>
						<td>
							<?php echo $employer['employer_selections']; ?>
						</td>
						<td>
							<?php echo $employer['employer_contact_person']; ?>
						</td>
						<td>
							<?php echo $employer['employer_contact']; ?>
						</td>
						<td>
							<?php echo $employer['country_name']; ?>
						</td>
						
				
		
						<td>
							<?php echo $employer['user_name']; ?>
						</td>
						<td>                                        
							<?php if ( is_null( $employer['user_lastlogin'] ) || $employer['user_lastlogin'] == '0000-00-00 00:00:00'): ?>
							<span class="text-warning">Never been logged in</span>
							<?php else: ?>
							<small><a href="javascript:;" class="timeago" title="<?php echo date( 'c', strtotime( $employer['user_lastlogin'] ) ); ?>"><?php echo date( 'm-d-Y h:ia', strtotime( $employer['user_lastlogin'] ) ); ?></a></small>	
							<?php endif; ?>
						</td>
						
						<td class="numeric">
						   <?php echo date( 'd M Y', strtotime( $employer['employer_created'] ) ); ?>
						</td>
				  
					</tr>
					<?php endforeach; ?> 
				</tbody>
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
	</div>

</div>

</div>
</div>
</div>
<!-- /Page Body -->
