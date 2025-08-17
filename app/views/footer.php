    
    <!--cyd jquery-->
    <script>base_url = '<?php echo base_url(); ?>';</script>

    <script src="<?php echo base_url(); ?>assets/scripts/admin/angular.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/scripts/admin/ng-file-upload-all.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/scripts/admin/angular-cookies.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/scripts/admin/jquery-2.0.3.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/scripts/admin/bootstrap.min.js"></script>
    <script src="<?php echo base_url(); ?>page/public/js/all.js"></script>
    <script type="text/javascript">
	$.ajax({
	    async:false,
	    url: '<?php echo site_url(); ?>admin/applicants/review/<?php echo $applicant_id; ?>',
	    type:'GET',
	    cache:false,
	    dataType: 'html',
	    success:function( response ) {
	         $('.review_body').html( response );
	    }
	});
	</script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/plugins/select2/select2.js"></script>
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/plugins/datetime/bootstrap-datepicker.js"></script>
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/scripts/admin/pages/applicants/review.js"></script>
</body>
<!--  /Body -->
</html>