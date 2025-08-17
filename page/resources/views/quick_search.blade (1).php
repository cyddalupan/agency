<?php
session_start();
?>
@extends('layouts.blank') 
@section('title', 'Quick Search') 
@section('content')

<form action="{{url()}}/applicants/send_multiple_lineup" method="post">
	<input type="hidden" name="keyword" value="{{$keyword}}">
	<table class="table">
		<tr>
			<th></th>
			<th>review</th>
			<th>applied</th>
			<th>name</th>
		  <th>Destination</th>
			<th>Position</th>
			<th>Medical</th>
			<th>status</th>
			<th>Sub status</th>
			<th>OWWA</th>
			<th>BIO</th>
			<th>employer</th>
			<th>Sponsor</th>
			<th>Agent</th>
			<th>remarks</th>
		</tr>

		@foreach($applicants as $applicant)
		<tr>
			<td>
	            @if($applicant->status['statusText'] != 'Selected' )
	            <input type="checkbox" name="applicant_select[]" value="{{$applicant->applicant_id}}">
	            @else
	            <span class="glyphicon glyphicon-stop text-primary pull-left" aria-hidden="true"></span>
	            @endif
			</td>
			<td>
			   <?php 
			   if($_SESSION['admin']['user']['user_type']!=13){
			    ?>
				<a class="btn btn-default xs" href="{{url()}}/../admin/applicants/review_single/{{$applicant->applicant_id}}" target="_blank" role="button">Review</a>
				<?php } ?>
			</td>
			<td>
				{{$applicant->applicant_date_applied}}
			</td>
			<td>
				{{$applicant->applicant_first}} {{$applicant->applicant_last}}
			</td>
			<td>
				{{$applicant->country['country_name']}}
			</td>
			
			<td>
				{{$applicant->position['position_name']}}
			</td>
			
			<td>
				  {{$applicant->applicant_certificate['certificate_medical_result']}} 
			</td>
			
			<td>
				<h4>
					<span class="label label-{{$applicant->status['statusColors']}}">
						{{$applicant->status['statusText']}}
			        </span>
					
                    </br>	
                    
                    <?php 
                    if($applicant->status['statusText']=='Deployed'){
                    ?>
                    
                      <pn style="font-size:12px">{{$applicant->applicant_requirement->flight_date}}</p>
                    
                    <?php } ?>
				</h4>
			</td>
			<td style="color:red">
			    
	           <?php 
			   if($applicant->status['statusText']=='Selected' || $applicant->status['statusText']=='Reserved' || $applicant->status['statusText']=='Pre-Selected' || 
			   $applicant->status['statusText']=='Line Up' || $applicant->status['statusText']=='w/ Transmittal' || $applicant->status['statusText']=='For Contract Signing' || 
			   $applicant->status['statusText']=='For Owwa Schedule' || 
			   $applicant->status['statusText']=='w/ Owwa Schedule'
			   || $applicant->status['statusText']=='For oec' || $applicant->status['statusText']=='w/ oec'){
			    ?>
	                {{$applicant->sub_status}}
	           	<?php } ?> 
	      
			     
			</td>
			
			<td>
                @if($applicant->applicant_certificate['certificate_owwa_from'] == '0000-00-00' || $applicant->applicant_certificate['certificate_owwa_from'] == '' )
                
                @else
              
                {{$applicant->applicant_certificate['certificate_owwa_from']}} 
                @endif
					  
			</td>
			
				<td>
                @if($applicant->applicant_requirement['vfs'] == '0000-00-00' || $applicant->applicant_requirement['vfs'] == '' )
                
                @else
              
                {{$applicant->applicant_requirement['vfs']}} 
                @endif
					  
			</td>
			
			<td>
				{{$applicant->employer['employer_name']}} 
			</td>
				<td>
				{{$applicant->sub_employer}}
			</td>
			<td>
			  
				{{$applicant->recruitment_agent['agent_first']}} {{$applicant->recruitment_agent['agent_last']}}
				<p>	{{$applicant->recruitment_agent['agent_contacts']}}</p>
					  
			</td>
			<td>
				{{$applicant->applicant_remarks}}
			</td>
		</tr>
		@endforeach
	</table>
	<hr>
	<?php echo $applicants->render(); ?> 
	<hr>
	




<?php
if ($_SESSION['admin']['user']['user_fullname']=='Developer' || $_SESSION['admin']['user']['user_fullname']=='Rupert San Gabriel Jr' ){
$hidejac="";	
}
else{
$hidejac="disabled";	
}
?>
	
	
	
	<div class="row">
		<div class="col-md-2 text-right">
			<h5>Send Applicants to: </h5>
		</div>
		<div class="col-md-3">
			<select class="form-control" name="employer" <?=$hidejac?>>
				<option value="0"></option>
				@foreach($employers as $employer)
				<option value="{{$employer->employer_id}}">{{$employer->employer_name}}</option>
				@endforeach
			</select>
		</div>
		<div class="col-md-3">
			<input type="submit" value="Submit" class="btn btn-primary">
		</div>
	</div>
	<br>
	<small class="text-muted">
		&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp
		Note: New Employers Take up to 1 hour to be added on the list
	</small>
	<br>
	<br>
</form>

@stop