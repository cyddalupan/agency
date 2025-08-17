@extends('layouts.applicants')

@section('title', 'Applicant Profile')

@section('content')
	<br><br>

	<!-- Nav tabs -->
	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active"><a href="#Profile" aria-controls="Profile" role="tab" data-toggle="tab">Profile</a></li>
		<li role="presentation"><a href="#Files" aria-controls="Files" role="tab" data-toggle="tab">Files</a></li>
	</ul>

	<br><br>

	<!-- Tab panes -->
	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active" id="Profile">

			<div class="col-md-3"></div>
			<div class="col-md-6">
				<h3>
					Welcome {{$applicant->applicant_first}} {{$applicant->applicant_last}}
				</h3>
					
				<form action="{{url()}}/applicant/login-submit" method="post">

					<div class="form-group">
						<label for="applicant_birthdate">Birthday</label>
						<input type="date" name="applicant_birthdate" class="form-control" placeholder="Birthday">
					</div>

					<div class="form-group">
						<label for="applicant_gender">Birthday</label>
						<select class="form-control" name="applicant_gender">
							<option>Male</option>
							<option>Female</option>
						</select>
					</div>
					
					<div class="form-group">
						<label for="applicant_contacts">Contacts</label>
						<input type="input" name="applicant_contacts" class="form-control" placeholder="Contacts">
					</div>
					
					<div class="form-group">
						<label for="applicant_address">Address</label>
						<input type="input" name="applicant_address" class="form-control" placeholder="Address">
					</div>

					<div class="form-group">
						<label for="applicant_nationality">Nationality</label>
						<input type="input" name="applicant_nationality" class="form-control" placeholder="Nationality">
					</div>

					<div class="form-group">
						<label for="applicant_civil_status">Civil Status</label>
						<input type="input" name="applicant_civil_status" class="form-control" placeholder="Civil Status">
					</div>
					
					<div class="form-group">
						<label for="applicant_religion">Religion</label>
						<input type="input" name="applicant_religion" class="form-control" placeholder="Religion">
					</div>
					
					<div class="form-group">
						<label for="applicant_height">Height</label>
						<input type="input" name="applicant_height" class="form-control" placeholder="Height">
					</div>

					<div class="form-group">
						<label for="applicant_weight">Weight</label>
						<input type="input" name="applicant_weight" class="form-control" placeholder="Weight">
					</div>
					
					<div class="form-group">
						<label for="applicant_preferred_position">Position</label>
						<select class="form-control" name="applicant_preferred_position">
							@foreach($positions as $position)
								<option>{{$position->position_name}}</option>
							@endforeach
						</select>
					</div>

					<div class="form-group">
						<label for="applicant_mothers">Mother's Name</label>
						<input type="input" name="applicant_mothers" class="form-control" placeholder="Mother's Name">
					</div>

					<div class="form-group">
						<label for="applicant_children">Children</label>
						<input type="input" name="applicant_children" class="form-control" placeholder="Children/s">
					</div>

					<div class="form-group">
						<label for="applicant_expected_salary">Ecpected Salary</label>
						<input type="input" name="applicant_expected_salary" class="form-control" placeholder="Ecpected Salary">
					</div>
					
					<div class="form-group">
						<label for="applicant_preferred_country">Preffered Country</label>
						<input type="input" name="applicant_preferred_country" class="form-control" placeholder="Preffered Country">
					</div>
					
					<div class="form-group">
						<label for="applicant_other_skills">Other Skills</label>
						<input type="input" name="applicant_other_skills" class="form-control" placeholder="Other Skills">
					</div>
					
					<div class="form-group">
						<label for="personalAbilities">Abilities</label>
						<textarea type="input" name="personalAbilities" class="form-control" placeholder="Abilities"></textarea>
					</div>
					
					<div class="form-group">
						<label for="">Abilities</label>
						<input type="input" name="applicant_photo" class="form-control" placeholder="Abilities">
					</div>
					
					<input type="submit" class="btn btn-default" value="Submit" />
				</form>
			</div>
			<div class="col-md-3"></div>

		</div>
		<div role="tabpanel" class="tab-pane" id="Files">

			<div class="form-group">
				<label for="applicant_photo">Upload Photo</label>
				<input type="file" id="applicant_photo">
				<p class="help-block">Applicant's Picture</p>
			</div>

			<br><br><br><br>

			<div class="form-group">
				<label for="applicant_photo">Upload Photo</label>
				<input type="file" id="applicant_photo">
				<p class="help-block">Softcopy Documents</p>
				<select class="form-control">
					<option>Whole Body Picture</option>
					<option>Resume</option>
					<option>Passport</option>
					<option>Visa</option>
					<option>Other</option>
				</select>
			</div>

			<br><br><br><br>
			
		</div>
	</div>	

@stop