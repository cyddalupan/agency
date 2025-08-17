@extends('layouts.applicants')

@section('title', 'Applicant login')

@section('content')
	<br><br><br><br>
	<div class="col-md-4"></div>
	<div class="col-md-4">
			@foreach ($errors->all(':message') as $message)
				<p class="text-danger">
					{{$message}}
				</p>
			@endforeach
		<form action="{{url()}}/applicant/login-submit" method="post">
			<div class="form-group">
				<label for="applicant-email">Email address</label>
				<input type="email" name="email" class="form-control" id="applicant-email" placeholder="Email">
			</div>
			<div class="form-group">
				<label for="applicant-password">Password</label>
				<input type="password" name="password" class="form-control" id="applicant-password" placeholder="Password">
			</div>
			<input type="submit" class="btn btn-default" value="Submit" />
		</form>
	</div>
	<div class="col-md-4"></div>
@stop