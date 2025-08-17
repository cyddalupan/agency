<?php include'inc/header.php';
include'inc/or.php';
include'tracking.php';
include'styles.php';
?>
<style>
td a{
border:1px solid #87CEEB;
padding:2px;
border-radius: 5px;
font-size:12px;
} 
td {
padding:2px;
font-size:11px;
}     
</style>
<body class="nav-md">
<div class="container body">
<div class="main_container">


        <div class="right_col" role="main">
 

			    <button type="button" class="btn btn-danger btn-sm pull-right" data-toggle="modal" data-target="#myModal">New Transaction</button>
					<h3 class="pull-left">Collection</h3>
  <div class="row">
  	
               <div class="col-md-12 col-xs-12">


					<div class="x_panel">
				
			

					
					<div class="x_content">
					
				
		  <?php $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>
 <a href="<?=$actual_link;?>" style="color:Red;text-align:right;margin-right:20px">Refresh</a>
<a href="profile.php?agent=<?=$_GET["agent"]?>" style="color:blue;font-size:14px">Back to Profile</a>
<table  cellspacing="0" id="example-checkbox">
	<thead>
	<tr>
<th STYLE="width:1%"></th>
<th STYLE="width:2%">#</th>
	<th STYLE="width:10%">DATE</th>

	<th>OR #</th>
	<th>Applicant</th>
	<th>Agent</th>
	<th>Particular</th>
	<th>account</th>
	<th>Amount</th>
	<th>Currency</th>
	<th>Prepare/Received by</th>
	<th>Paid Through </th>
	<th>Remarks </th>
	<th>ACTION</th>
	</tr>
	</thead>
	<tbody>
<?php	

$app = mysql_query("SELECT * FROM email_address6
where agent_id=".$_GET['agent']."
ORDER BY tr_id desc");
 
if(isset($_POST['Search']))
{
$app = mysql_query("SELECT * FROM email_address6 where
or_num LIKE '%".$_POST['search']."%'
");
}

 
while($row=mysql_fetch_array($app))
{
$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);
$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$row['app_id']." ");
$rowp=mysql_fetch_array($applicant);

$emp = mysql_query("SELECT * FROM employer where employer_id=".$rowp['applicant_employer']." ");
$emprow=mysql_fetch_array($emp);





echo'<tr>';
echo'<td></td>';
echo'<td style="font-size:1px">'.$row["tr_id"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['date']));echo'</td>';
echo'<td>'; echo date('md', strtotime($row['date']));echo'</td>';
echo'<td>
'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td>'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$row["particular"].'</td>';
echo'<td>'.$row["account"].'</td>';
echo'<td>'.$row["amount"].'</td>';
echo'<td>'.$row["currency"].'</td>';
echo'<td>'.$row["received"].'</td>';
echo'<td>'.$row["source_m"].'</td>';
echo'<td>'.$row["particular"].'</td>';
echo'<td><a href="collection_profile.php?trid='.$row['tr_id'].'&&apid='.$row['app_id'].'&&name='.$rowp["applicant_first"].''.$rowp["applicant_last"].'" STYLE="COLOR:BLUE" target="_blank">View</A></td>';
echo'</tr>';
}
?>
</tbody>
</table>

	</div>
	</div>

<div class="ln_solid"></div>

	</div>

	



</div>			
				
<!-- /footer content -->
</div>
</div>
<!-- jQuery -->
<script src="../vendors/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Skycons -->
<script src="../vendors/skycons/skycons.js"></script>

<!-- Custom Theme Scripts -->
<script src="../build/js/custom.min.js"></script>

</body>






<!-- Propeller Data table js-->
<script type="text/javascript" language="javascript" src="jsdatatables.js"></script>
<script>
//Propeller Customised Javascript code 
$(document).ready(function() {
$('#example-checkbox').DataTable({
responsive: false,
columnDefs: [ {
orderable: false,
className: 'select-checkbox',
targets:0,
} ],
select: {
style: 'multi',
selector: 'td:first-child'
},
order: [ 2, 'desc' ],
bFilter: true,
bLengthChange: true,
pagingType: "simple",
"paging": true,
"searching": true,
"language": {
"info": " _START_ - _END_ of _TOTAL_ ",
"sLengthMenu": "<span class='custom-select-title'>Rows per page:</span> <span class='custom-select'> _MENU_ </span>",
"sSearch": "",
"sSearchPlaceholder": "Search",
"paginate": {
"sNext": " ",
"sPrevious": " "
},
},
dom:
"<'row'<'col-sm-12'><'search-paper pmd-textfield'f>>" +
"<'custom-select-info'<'custom-select-item'><'custom-select-action'>>" +
"<'row'<'col-sm-12'tr>>" +
"<'pmd-card-footer' <'pmd-datatable-pagination' l i p>>",
});

/// Select value
$('.custom-select-info').hide();

$('#example-checkbox tbody').on( 'click', 'tr', function () {
if ( $(this).hasClass('selected') ) {
var rowinfo = $(this).closest('.dataTables_wrapper').find('.select-info').text();
$(this).closest('.dataTables_wrapper').find('.custom-select-info .custom-select-item').text(rowinfo);
if ($(this).closest('.dataTables_wrapper').find('.custom-select-info .custom-select-item').text() != null){
$(this).closest('.dataTables_wrapper').find('.custom-select-info').show();
//show delet button
} else{
$(this).closest('.dataTables_wrapper').find('.custom-select-info').hide();
}
}
else {
var rowinfo = $(this).closest('.dataTables_wrapper').find('.select-info').text();
$(this).closest('.dataTables_wrapper').find('.custom-select-info .custom-select-item').text(rowinfo);
}
if($('#example-checkbox').find('.selected').length == 0){
$(this).closest('.dataTables_wrapper').find('.custom-select-info').hide();
}
} );
$("div.data-table-title").html('');
$(".custom-select-action").html('<button class="btn btn-sm pmd-btn-fab pmd-btn-flat pmd-ripple-effect btn-primary" type="button"><i class="material-icons pmd-sm">delete</i></button><button class="btn btn-sm pmd-btn-fab pmd-btn-flat pmd-ripple-effect btn-primary" type="button"><i class="material-icons pmd-sm">more_vert</button>');

} );
</script>



  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">AGENT PAYMENT TRANSACTION</h4>
        </div>
        <div class="modal-body">
					
			<form class="form-horizontal form-label-left input_mask" method="post" action="">	
					 <input type="hidden" class="form-control" name="user" value="<?=$_SESSION['user_fullname']?>" required>
					 
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">DATE APPLIED</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="date" class="form-control" name="date" value="<?=$date1?>" required>
                        </div>
                      </div>
					
					  
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">OR #</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="or_num" >
                        </div>
                      </div>
					  
					   
					  <div class="form-group" style="display:none">
						<label class="control-label col-md-3 col-sm-3 col-xs-12">Applicant</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
						<?php
						echo'<select class="form-control" name="app_id" required>';	?>
						<option><?=$_GET['appid']?></option>
					

						</select>

						</div>
						</div>
					 
					
					 
					 
						
					  
					   <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="number" class="form-control" name="amount" step="any" required>
                        </div>
                      </div>
						
						
							  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">CURRENCY</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
								<select class="form-control" required  name="currency" required>
								<option VALUE=""></option>
								<option>USD</option>
								<option>PHP</option>
								
								</select>
                        </div>
                      </div>
						
						
								 
				<div class="form-group">
				<label class="control-label col-md-3 col-sm-3 col-xs-12">Type Of Collection</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
				<?php echo'
				<select class="form-control" name="account" required>
				<option>'.$row["account"].'</option>';
				$set_ = mysql_query("SELECT * FROM set_ ORDER BY account asc");
				while($setr=mysql_fetch_array($set_))
				{
				echo'<option value="'.$setr["account"].'">'.$setr["account"].'</option>';
				}
				echo'</select>';
				?>
				</div>
				</div>
						
						
					  	  <div class="form-group" style="display:none">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Source</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
						 <input type="text" class="form-control" name="agent_id" value="<?=$_GET['agent']?>"  readonly> 
								
                        </div>
                      </div>
					  
					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Paid Through </label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="source_m" placeholder="BDO,BPI,WESTERN" >
                        </div>
                      </div> 
					  
					  
					   <div class="form-group" >
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Particulars</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
						<textarea name="particular" class="col-md-9 col-sm-9 col-xs-12"></textarea>	
                        </div>
                      </div>
					  
					  
					  <div class="form-group"  style="display:none">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Received By</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="received" value="<?=$_SESSION['user_fullname']?>" readonly>
                        </div>
                      </div>
					  
					  	  <div class="form-group">
						<div class="col-md-12 col-sm-9 col-xs-12">	
							<button type="submit" class="btn btn-danger pull-right"  name="add_applicants">Save</button>
                        </div>
                      </div>
					  
					  
			</form>		  
					  
				  
					  
        </div>
        
      </div>
      
    </div>
  </div>
