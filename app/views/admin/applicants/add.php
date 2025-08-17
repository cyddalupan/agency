<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
<ul class="breadcrumb">
<li>
<i class="fa fa-home"></i>
<a href="<?php echo site_url('admin/'); ?>">Home</a>
</li>
<li>Applicants</li>
<li class="active">New</li>
</ul>
</div>
<!-- /Page Breadcrumb -->
<!-- Page Header -->
<div class="page-header position-relative">
<div class="header-title">
<h1>
Add New Applicant
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
<?php $app->renderAlerts(); ?>
<div class="row">
<div class="col-lg-12 col-sm-12 col-xs-12">

<div class="row">
<div class="col-lg-12 col-sm-12 col-xs-12">

<div class="row">
<div class="col-lg-11 col-md-11 col-sm-12 col-xs-12">
<div class="widget flat radius-bordered">
<?php if($this->session->flashdata('cyd_error_msg') != ''){ ?>
<div class="alert alert-danger" role="alert">
<i class="fa fa-exclamation-triangle"></i>
<span class="sr-only">Error:</span>
<?php echo $this->session->flashdata('cyd_error_msg'); ?>
</div>
<?php } ?>
<div class="widget-header bg-danger">
<span class="widget-caption">Registration Form</span>
</div>
<div class="widget-body">
<div id="registration-form">
<form class="form" role="form" method="post" action="<?php echo site_url();?>admin/applicants/add" enctype="multipart/form-data">
<div class="row">
<div class="col-sm-8">
<div class="form-title"  style="margin-left:14px;color:white;font-size:12px;background:#4682B4;padding:3px;width:200px;text-align:center">
<strong>Preferred Designation</strong>
</div>
<div class="row">
<div class="col-md-4
<?php if($_SESSION["settings"]['withTraining'] == 'no'){echo 'hidden';} ?>
">
<div class="form-group">
<label>Training Branch</label> 
<select name="applicant[training-branch]" style="width:100%">
<?php foreach ($trainingBranches as $trainingKey => $trainingBranch) {

if($applicant_raw->training_branches_id == $trainingKey )
$isBranchSelected = 'selected';
else
$isBranchSelected = '';

echo '<option value="'.$trainingKey.'" '.$isBranchSelected.'>'.$trainingBranch.'</option>';
} ?>
</select>
</div>
</div>
<div class="col-sm-4">
<div class="form-group">
<label>Position</label> 
<select name="applicant[preferred-position]" style="width:100%" required>
<option value="">-- Select position --</option>
<?php foreach ($categories as $category): ?>
<?php if ( count( $category['positions'] ) == 0 ) continue; ?>
<optgroup label="<?php echo $category['category_name']; ?>">
<?php foreach ($category['positions'] as $position): ?>
<option value="<?php echo $position['position_id']; ?>" <?php echo isset( $post['applicant']['preferred-position'] ) && $post['applicant']['preferred-position'] == $position['position_id'] ? 'selected' : '';  ?>><?php echo $position['position_name']; ?></option>
<?php endforeach; ?>
</optgroup>
<?php endforeach; ?>
</select>
</div>
</div>


<div class="col-sm-4">
<div class="form-group">
<label>Preferred country:</label> 
<select name="applicant[preferred-country]" style="width:100%" required>
<option value="">-- Select country --</option>
<?php foreach ($countries as $country): ?>
<option value="<?php echo $country['country_id']; ?>" <?php echo isset( $post['applicant']['preferred-country'] ) && $post['applicant']['preferred-country'] == $country['country_id'] ? 'selected' : '';  ?>><?php echo $country['country_name']; ?></option>
<?php endforeach; ?>
</select>
</div>
</div>    


<div class="col-sm-4"  style="display:none">
<label>Currency</label> 
<select name="applicant[currency]" >
<option value="">-- Select Currency --</option>
<?php foreach ($currencies as $currency) { ?>
<option><?php echo $currency->currency; ?></option>
<?php }//end foreach ?>
</select>
</div>


<div class="col-sm-4"  style="display:none">
<div class="form-group">
<label>Expected Salary</label>
<input type="text" name="applicant[expected-salary]" class="form-control"  value="400" >
</div>
</div>
</div>


<div class="row"  style="display:none">
<div class="col-sm-8">
<div class="form-group">
<label>Other preferred positions <small><em>(Press Enter to accept position)</em></small>:</label> 
<select name="applicant[other-preferred-positions][]" multiple style="width:100%">
<option value="">-- Select position --</option>
<?php foreach ($categories as $category): ?>
<optgroup label="<?php echo $category['category_name']; ?>">
<?php foreach ($category['positions'] as $position): ?>
<option value="<?php echo $position['position_id']; ?>" <?php echo isset( $post['applicant']['other-preferred-positions'] ) && in_array( $position['position_id'], $post['applicant']['other-preferred-positions']) ? 'selected' : '';  ?>><?php echo $position['position_name']; ?></option>
<?php endforeach; ?>
</optgroup>
<?php endforeach; ?>
</select>
</div>
</div>                                                        
</div>




<div class="row"> 
     
</div>
<div class="row">
<div class="col-sm-8"  style="display:none">
<div class="form-group">
<label>Other preferred countries <small><em>(Press Enter to accept country)</em></small>:</label> 
<select name="applicant[other-preferred-countries][]" multiple style="width:100%">
<option value="">-- Select country --</option>
<?php foreach ($countries as $country): ?>
<option value="<?php echo $country['country_id']; ?>" <?php echo isset( $post['applicant']['other-preferred-countries'] ) && in_array( $country['country_id'], $post['applicant']['other-preferred-countries']) ? 'selected' : '';  ?>><?php echo $country['country_name']; ?></option>
<?php endforeach; ?>
</select>
</div>
</div>                                                        
</div>

</div>
<div class="col-sm-4" style="display:none">

<div class="row" >
<div class="col-sm-10 col-sm-offset-2" align="center" style="border-left:1px dashed #999">
<img width="100" class="img-responsive img-rounded profile-photo" src="<?php echo $app->getPath()['images']; ?>avatars/no-picture.jpg" alt="Upload photo" /><br />
<input class="hide" type="file" name="applicant[photo]"/>
<button type="button" class="btn btn-md btn-info btn-photo-browse">Browse photo ...</button>
</div> 
</div>                                                     

</div>
</div>

<div class="row">
<?php show_customField($customFields,"Preferred Designation"); ?>
</div>

<hr class="wide" />

<div class="form-title"  style="margin-left:14px;color:white;font-size:12px;background:#4682B4;padding:3px;width:200px;text-align:center">
<strong>Basic Information</strong>
</div>
<div class="row">
<div class="col-sm-4">
<div class="form-group">
<label>First name</label>
<input type="text" name="applicant[basic][first]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['basic']['first'] ) ? $post['applicant']['basic']['first'] : ''; ?>" required>
</div>
</div>
<div class="col-sm-4">
<div class="form-group">
<label>Middle name</label>
<input type="text" name="applicant[basic][middle]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['basic']['middle'] ) ? $post['applicant']['basic']['middle'] : ''; ?>" placeholder="if no Middle Name put NMN">
</div>
</div>
<div class="col-sm-4">
<div class="form-group">
<label>Last name</label>
<input type="text" name="applicant[basic][last]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['basic']['last'] ) ? $post['applicant']['basic']['last'] : ''; ?>" required>
</div>
</div>
</div>
<div class="row">
<div class="col-sm-3">
<div class="form-group">
<label>Date of birth</label>

<input name="applicant[basic][birthdate]" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" >
</div>
</div>


<div class="col-sm-3">
<div class="form-group">
<label>Age</label>

<input name="applicant[basic][age]" type="text" class="form-control" required>
</div>
</div>




<div class="col-sm-4">
<div class="form-group">
<label for="">Contact number(s)</label>
<input name="applicant[basic][contacts]" class="form-control" type="text" placeholder="Add contact number" value="<?php echo isset( $post['applicant']['basic']['contacts'] ) ? $post['applicant']['basic']['contacts'] : ''; ?>" />
<input name="applicant[basic][contacts2]" class="form-control" type="text" placeholder="Other contact number" value="<?php echo isset( $post['applicant']['basic']['contacts2'] ) ? $post['applicant']['basic']['contacts2'] : ''; ?>" />
<input name="applicant[basic][contacts3]" class="form-control" type="text" placeholder="Other contact number" value="<?php echo isset( $post['applicant']['basic']['contacts3'] ) ? $post['applicant']['basic']['contacts3'] : ''; ?>" />
</div>
</div>

<div class="col-sm-4">
<div class="form-group">
<label>Address</label>
<input type="text" name="applicant[basic][address]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['basic']['address'] ) ? $post['applicant']['basic']['address'] : ''; ?>" required>
</div>
</div>



<div class="col-sm-4"   style="display:none">
<div class="form-group">
<label>Children(s)</label>
<input type="text" name="applicant[basic][applicant_children]" class="form-control" placeholder="example: 2 Girl 8 year(s) old,1 boy 2 year(s)" value="<?php echo isset( $post['applicant']['basic']['applicant_children'] ) ? $post['applicant']['basic']['applicant_children'] : ''; ?>">
</div>
</div>

<div style="clear:both;height:10px"></div>

<div class="col-sm-3"   style="">
<div class="form-group">
<label>Gender</label>
<select name="applicant[basic][gender]" class="form-control">
<option value="Male">Male</option>
<option value="Female">Female</option>
</select>
</div>
</div>


<div class="col-sm-3">
<div class="form-group"   style="">
<label>Civil status</label>
<select name="applicant[basic][status]" class="form-control">
<option value="Single">Single</option>
<option value="Single With Children">Single With Children</option>
<option value="Separated">Separated</option>
<option value="Married">Married</option>
<option value="Widow">Widow</option>
</select>
</div>
</div>



</div>
<div class="row">

<div class="col-sm-3"  style="display:none">
<div class="form-group">
<label>Mother Name </label>
<input type="text" name="applicant[basic][applicant_mothers]" class="form-control" placeholder="Mother Name" value="<?php echo isset( $post['applicant']['basic']['applicant_mothers'] ) ? $post['applicant']['basic']['applicant_mothers'] : ''; ?>">
</div>
</div>

<div class="col-sm-3"  style="display:none">
<div class="form-group">
<label>Email <small><em>(optional)</em></small></label>
<input type="email" name="applicant[basic][email]" class="form-control" placeholder="example@domain.com" value="<?php echo isset( $post['applicant']['basic']['email'] ) ? $post['applicant']['basic']['email'] : ''; ?>">
</div>
</div>

</div>




<div class="row">
<div class="col-sm-4"    style="display:none">
<div class="form-group">
<label>Nationality</label>
<input type="text" name="applicant[basic][nationality]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['basic']['nationality'] ) ? $post['applicant']['basic']['nationality'] : 'FILIPINO'; ?>" required>
</div>
</div>




<div class="col-sm-4"   style="display:none">

<div class="form-group">

<label>Religion / Belief</label>

<select  name="applicant[basic][religion]" class="form-control">
<option>Non Muslim</option>
<option>Muslim</option>
</select>
</div>

</div>


<div class="col-sm-2"   style="display:none">
<div class="form-group">
<label>Height</label>
<input type="text" name="applicant[basic][height]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['basic']['height'] ) ? $post['applicant']['basic']['height'] : ''; ?>">
</div>
</div>
<div class="col-sm-2"   style="display:none">
<div class="form-group">
<label>Weight</label>
<input type="text" name="applicant[basic][weight]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['basic']['weight'] ) ? $post['applicant']['basic']['weight'] : ''; ?>">
</div>
</div>
</div>

<div class="row"   style="display:none">
<div class="col-sm-6">
<div class="form-group">
<label for="">Languages <small><em>(Press Enter to accept language)</em></small></label>
<input name="applicant[basic][languages]" type="text" value="<?php echo isset( $post['applicant']['basic']['languages'] ) ? $post['applicant']['basic']['languages'] : 'English'; ?>" data-role="tagsinput" placeholder="Add language" />
</div>
</div>

</div>

<div class="row">
<?php show_customField($customFields,"Basic Information"); ?>
</div>

<hr class="wide" />

<div class="form-title"   style="display:none">
<strong>Incase of Emergency</strong>
</div>
<div class="row"   style="display:none">
<div class="col-sm-3">
<div class="form-group">
<label>Name </label>
<input type="text" name="applicant[basic][applicant_incase_name]" class="form-control" placeholder="Name" value="<?php echo isset( $post['applicant']['basic']['applicant_incase_name'] ) ? $post['applicant']['basic']['applicant_incase_name'] : ''; ?>">
</div>
</div>

<div class="col-sm-3">
<div class="form-group">
<label>Relationship </label>
<input type="text" name="applicant[basic][applicant_incase_relation]" class="form-control" placeholder="Relationship" value="<?php echo isset( $post['applicant']['basic']['applicant_incase_relation'] ) ? $post['applicant']['basic']['applicant_incase_relation'] : ''; ?>">
</div>
</div>

<div class="col-sm-3">
<div class="form-group">
<label>Contact Details </label>
<input type="text" name="applicant[basic][applicant_incase_contact]" class="form-control" placeholder="Contact Details" value="<?php echo isset( $post['applicant']['basic']['applicant_incase_contact'] ) ? $post['applicant']['basic']['applicant_incase_contact'] : ''; ?>">
</div>
</div>


<div class="col-sm-3">
<div class="form-group">
<label>Address </label>
<input type="text" name="applicant[basic][applicant_incase_address]" class="form-control" placeholder="Address" value="<?php echo isset( $post['applicant']['basic']['applicant_incase_address'] ) ? $post['applicant']['basic']['applicant_incase_address'] : ''; ?>">
</div>
</div>


</div>



<div class="row">
<?php show_customField($customFields,"Incase of Emergency"); ?>
</div>





<hr class="wide" />

<div class="form-title"   style="margin-left:14px;color:white;font-size:12px;background:#4682B4;padding:3px;width:200px;text-align:center;margin-top:-60px" >

</div>
<div class="row">
<div class="col-sm-4"  style="display:none">
<div class="form-group">
<label>Passport number</label>
<input type="text" name="applicant[passport][number]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['passport']['number'] ) ? $post['applicant']['passport']['number'] : ''; ?>">
</div>
</div>

<div class="col-sm-4" style="display:none">
<div class="form-group">
<label>Date issued</label>
<span class="input-icon icon-right">
<div class="input-group">
<input name="applicant[passport][issue]" type="date" class="form-control date-picker">

</div>
</span>
</div>
</div>


</div>


<div class="row"   style="display:none" >
<div class="col-sm-4">
<div class="form-group">
<label>Issue place</label>
<input type="text" name="applicant[passport][issue-place]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['passport']['issue-place'] ) ? $post['applicant']['passport']['issue-place'] : ''; ?>">
</div>
</div>

<div class="col-sm-4">
<div class="form-group">
<label>Expiration</label>
<span class="input-icon icon-right">
<div class="input-group">
<input name="applicant[passport][expiration]" type="date"  class="form-control date-picker">

</div>
</span>
</div>
</div>

</div>

<div class="row">
<?php show_customField($customFields,"Passport Information"); ?>
</div>

<hr class="wide"    style="display:none" />
<div class="form-title"    style="display:none">
<strong>Educational Background</strong>
</div>
<div class="row"   style="display:none">
<div class="col-sm-4">
<div class="form-group">
<label>MBA</label>
<input type="text" name="applicant[education][mba]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['mba'] ) ? $post['applicant']['education']['mba'] : ''; ?>">
</div>
</div>
<div class="col-sm-4">
<div class="form-group">
<label>Course</label>
<input type="text" name="applicant[education][mba-course]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['mba-course'] ) ? $post['applicant']['education']['mba-course'] : ''; ?>">
</div>
</div>
<div class="col-sm-2">
<div class="form-group">
<label>Year</label>
<input type="text" name="applicant[education][mba-year]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['mba-year'] ) ? $post['applicant']['education']['mba-year'] : ''; ?>">
</div>
</div>
</div>
<div class="row"   style="display:none">
<div class="col-sm-4">
<div class="form-group">
<label>College</label>
<input type="text" name="applicant[education][college]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['college'] ) ? $post['applicant']['education']['college'] : ''; ?>">
</div>
</div>
<div class="col-sm-4">
<div class="form-group">
<label>Skills</label>
<input type="text" name="applicant[education][college-skills]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['college-skills'] ) ? $post['applicant']['education']['college-skills'] : ''; ?>">
</div>
</div>
<div class="col-sm-2">
<div class="form-group">
<label>Year</label>
<input type="text" name="applicant[education][college-year]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['college-year'] ) ? $post['applicant']['education']['college-year'] : ''; ?>">
</div>
</div>
</div>

<div class="row"   style="display:none">
<div class="col-sm-8">
<div class="form-group">
<label>Others</label>
<input type="text" name="applicant[education][others]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['others'] ) ? $post['applicant']['education']['others'] : ''; ?>">
</div>
</div>
<div class="col-sm-2">
<div class="form-group">
<label>Year</label>
<input type="text" name="applicant[education][others-year]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['others-year'] ) ? $post['applicant']['education']['others-year'] : ''; ?>">
</div>
</div>
</div>

<div class="row"    style="display:none">
<div class="col-sm-8">
<div class="form-group">
<label>Highschool</label>
<input type="text" name="applicant[education][highschool]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['highschool'] ) ? $post['applicant']['education']['highschool'] : ''; ?>">
</div>
</div>
<div class="col-sm-2">
<div class="form-group">
<label>Year</label>
<input type="text" name="applicant[education][highschool-year]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['highschool-year'] ) ? $post['applicant']['education']['highschool-year'] : ''; ?>">
</div>
</div>
</div>
<div class="row">
<?php show_customField($customFields,"Educational Background"); ?>
</div>
<hr class="wide" />
<div class="form-title"    style="display:none">
<strong>Work Experience</strong>
</div>
<div class="work-experience-start"    style="display:none">
<div class="row work-experience">
<div class="col-sm-4">
<div class="form-group">
<label>Company</label>
<input type="text" name="applicant[work-experience][company][]" class="form-control" placeholder="">
</div>
</div>
<div class="col-sm-4" <?php echo wrkhde('extraExperience1'); ?>>
<div class="form-group">
<label><?php echo $_SESSION["settings"]['extraExperience1'] ?></label>
<input type="text" name="applicant[work-experience][experience_position][]" class="form-control" placeholder="">
</div>
</div>
<div class="col-sm-4" <?php echo wrkhde('extraExperience2'); ?>>
<div class="form-group">
<label><?php echo $_SESSION["settings"]['extraExperience2'] ?></label>
<input type="text" name="applicant[work-experience][salary][]" class="form-control" placeholder="">
</div>
</div>
<div class="col-sm-4">
<div class="form-group">
<label>Country / Address</label>
<input type="text" name="applicant[work-experience][country][]" class="form-control" placeholder="">
</div>
</div>
<div class="col-sm-2">
<div class="form-group">
<label>From</label>
<input name="applicant[work-experience][from][]" type="text" data-date-format="yyyy-mm-dd" class="form-control input-sm date-picker work-started cyd-work-started-1" placeholder="yyyy-mm-dd" value="">
</div>
</div>
<div class="col-sm-2">
<div class="form-group">
<label>To</label>
<input name="applicant[work-experience][to][]" type="text" data-date-format="yyyy-mm-dd" class="form-control input-sm date-picker work-ended cyd-work-ended-1" placeholder="yyyy-mm-dd" value="">                                                        </div>
</div>
<div class="clearfix"></div>
<div class="col-sm-4 cyd-work-years">
<div class="form-group">
<label>Years</label>
<input type="text" name="applicant[work-experience][years][]" class="form-control cyd-work-years-1" placeholder="0" readonly>
</div>
</div>

<div class="col-sm-4" <?php echo wrkhde('extraExperience3'); ?>>
<div class="form-group review-bedcapacity">
<label><?php echo $_SESSION["settings"]['extraExperience3'] ?></label>
<input type="text" name="applicant[work-experience][bed_capacity][]" class="form-control" placeholder="">
</div>
</div>

<div class="col-sm-4" <?php echo wrkhde('extraExperience4'); ?>>
<div class="form-group review-levelofhospital">
<label><?php echo $_SESSION["settings"]['extraExperience4'] ?></label>
<input type="text" name="applicant[work-experience][hospital_level][]" class="form-control" placeholder="">
</div>
</div>

<div class="col-sm-4" <?php echo wrkhde('extraExperience5'); ?>>
<div class="form-group">
<label><?php echo $_SESSION["settings"]['extraExperience5'] ?></label>
<input type="text" name="applicant[work-experience][experience_salary][]" class="form-control" placeholder="">
</div>
</div>
<div class="col-sm-4" <?php echo wrkhde('extraExperience6'); ?>>
<div class="form-group">
<label><?php echo $_SESSION["settings"]['extraExperience6'] ?></label>
<input type="text" name="applicant[work-experience][reasonOfLeaving][]" class="form-control" placeholder="">
</div>
</div>
<div class="col-sm-4" <?php echo wrkhde('extraExperience7'); ?>>
<div class="form-group">
<label><?php echo $_SESSION["settings"]['extraExperience7'] ?></label>
<input type="text" name="applicant[work-experience][typeOfResidence][]" class="form-control" placeholder="">
</div>
</div>
<div class="col-sm-4" <?php echo wrkhde('extraExperience8'); ?>>
<div class="form-group">
<label><?php echo $_SESSION["settings"]['extraExperience8'] ?></label>
<input type="text" name="applicant[work-experience][nationality][]" class="form-control" placeholder="">
</div>
</div>
<div class="col-sm-4" <?php echo wrkhde('extraExperience9'); ?>>
<div class="form-group">
<label><?php echo $_SESSION["settings"]['extraExperience9'] ?></label>
<input type="text" name="applicant[work-experience][NoFamilyMembers][]" class="form-control" placeholder="">
</div>
</div>
<div class="col-sm-4" <?php echo wrkhde('extraExperience10'); ?>>
<div class="form-group">
<label><?php echo $_SESSION["settings"]['extraExperience10'] ?></label>
<input type="text" name="applicant[work-experience][extraExperience10][]" class="form-control" placeholder="">
</div>
</div>
<div class="col-sm-4" <?php echo wrkhde('extraExperience11'); ?>>
<div class="form-group">
<label><?php echo $_SESSION["settings"]['extraExperience11'] ?></label>
<input type="text" name="applicant[work-experience][extraExperience11][]" class="form-control" placeholder="">
</div>
</div>
<div class="col-sm-4" <?php echo wrkhde('extraExperience12'); ?>>
<div class="form-group">
<label><?php echo $_SESSION["settings"]['extraExperience12'] ?></label>
<input type="text" name="applicant[work-experience][extraExperience12][]" class="form-control" placeholder="">
</div>
</div>

<div class="col-sm-1">
<a href="#" role="button" class="btn btn-danger btn-xs">Remove</a>
</div>
</div>
<div class="work-experience-separator"></div>
</div><!-- .work-experience-start -->

<div    style="display:none">
<hr />
<a href="#" id="btn-add-work-experience" role="button" class="btn btn-xs btn-default"><i class="fa fa-plus"></i> Add work experience</a>
</div>

<div <?php echo wrkhde('customCategory1'); ?>>
<hr class="wide">
<div class="form-title">
<?php echo $_SESSION["settings"]['customCategory1'] ?>
<div class="clearfix"></div>
</div>
<div class="row customCategory1-container">
<?php show_customField($customFields,"customCategory1"); ?>
</div>
</div>
<div <?php echo wrkhde('customCategory2'); ?>>
<hr class="wide">
<div class="form-title">
<?php echo $_SESSION["settings"]['customCategory2'] ?>
<div class="clearfix"></div>
</div>
<div class="row customCategory2-container">
<?php show_customField($customFields,"customCategory2"); ?>
</div>
</div>
<div <?php echo wrkhde('customCategory3'); ?>>
<hr class="wide">
<div class="form-title">
<?php echo $_SESSION["settings"]['customCategory3'] ?>
<div class="clearfix"></div>
</div>
<div class="row customCategory3-container">
<?php show_customField($customFields,"customCategory3"); ?>
</div>
</div>

<hr class="wide" />

<div class="form-title"    style="display:none">
<strong>Skills </strong>
</div>
<div class="row"    style="display:none">
<div class="col-sm-2">
<div class="form-group">
<div class="checkbox">
<label>
<input name="applicant[is_ironing]" type="checkbox" value="1" class="colored-blue">
<span class="text">Ironing</span>
</label>
</div>
</div>
</div>
<div class="col-sm-2">
<div class="form-group">
<div class="checkbox">
<label>
<input name="applicant[is_cooking]" type="checkbox" value="1" class="colored-blue">
<span class="text">Cooking</span>
</label>
</div>
</div>
</div>
<div class="col-sm-2">
<div class="form-group">
<div class="checkbox">
<label>
<input name="applicant[is_sewing]" type="checkbox" value="1" class="colored-blue">
<span class="text">Sewing</span>
</label>
</div>
</div>
</div>
<div class="col-sm-2">
<div class="form-group">
<div class="checkbox">
<label>
<input name="applicant[is_computer]" type="checkbox" value="1" class="colored-blue">
<span class="text">Computer</span>
</label>
</div>
</div>
</div>
<div class="col-sm-2">
<div class="form-group">
<div class="checkbox">
<label>
<input name="applicant[is_arabic_cooking]" type="checkbox" value="1" class="colored-blue">
<span class="text">Arabic Cooking</span>
</label>
</div>
</div>
</div>
<div class="col-sm-2">
<div class="form-group">
<div class="checkbox">
<label>
<input name="applicant[is_baby_sitting]" type="checkbox" value="1" class="colored-blue">
<span class="text">Baby Sitting</span>
</label>
</div>
</div>
</div>
<div class="col-sm-2">
<div class="form-group">
<div class="checkbox">
<label>
<input name="applicant[is_children_care]" type="checkbox" value="1" class="colored-blue">
<span class="text">Children Care</span>
</label>
</div>
</div>
</div>
<div class="col-sm-2">
<div class="form-group">
<div class="checkbox">
<label>
<input name="applicant[is_tutoring]" type="checkbox" value="1" class="colored-blue">
<span class="text">Tutoring</span>
</label>
</div>
</div>
</div>
<div class="col-sm-2">
<div class="form-group">
<div class="checkbox">
<label>
<input name="applicant[is_cleaning]" type="checkbox" value="1" class="colored-blue">
<span class="text">Cleaning</span>
</label>
</div>
</div>
</div>
<div class="col-sm-2">
<div class="form-group">
<div class="checkbox">
<label>
<input name="applicant[is_washing]" type="checkbox" value="1" class="colored-blue">
<span class="text">Washing</span>
</label>
</div>
</div>
</div>
</div>

<div class="row"    style="display:none">
<div class="col-sm-10">
<div class="form-group">
<label>Other Skills <small><em>(Press Enter to accept skills)</em></small></label>
<input name="applicant[other-skills]" type="text"  data-role="tagsinput" value="<?php echo isset( $post['applicant']['other-skills'] ) ? $post['applicant']['other-skills'] : 
'Cleaning,Babysitting,Laundry'; ?>">
</div>

</div>

</div>

<div class="row"   style="display:none">
<div class="col-sm-10">
<div class="form-group">
<label>Remarks <small><em>(optional)</em></small></label>
<span class="input-icon icon-right">
<textarea name="applicant[remarks]" class="form-control" rows="4" placeholder="Write some remarks..."><?php echo isset( $post['applicant']['remarks'] ) ? $post['applicant']['remarks'] : ''; ?></textarea>
<i class="fa fa-briefcase darkorange"></i>
</span>
<!--<p class="help-block">Write some remarks</p>-->
</div>
</div>
</div>

<hr class="wide" />
<div class="row" style="margin-top:-60px">
<div class="col-sm-1">
<div class="form-group">
<div class="checkbox">
<label>
<input name="applicant[type]" type="radio" value="Household" class="colored-blue" <?php echo isset( $post['applicant']['type'] ) && $post['applicant']['type'] == 'Household' ? '' : 'checked'; ?> style="background:white">
<span class="text">Household</span>
</label>
</div>
</div>
</div>
<div class="col-sm-2" style="">
<div class="form-group">
<div class="checkbox">
<label>
<input name="applicant[type]" type="radio" value="Skilled" class="colored-blue" <?php echo isset( $post['applicant']['type'] ) && $post['applicant']['type'] == 'Skilled' ? 'checked' : ''; ?> >
<span class="text">Skilled</span>
</label>
</div>
</div>
</div>







</div>

<hr class="wide" />
<div class="row">
<div class="col-sm-2">
<div class="form-group">
<label>Date applied</label>
<span class="input-icon icon-right">
<div class="input-group">
<input name="applicant[date-applied]" type="date" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo isset( $post['applicant']['date-applied'] ) ? $post['applicant']['date-applied'] : date( 'Y-m-d', time() ); ?>">

</div>
</span>
</div>
</div>




<?php

if($_SESSION['admin']['user']['branch_id']==0){$branchme="N/A";}
if($_SESSION['admin']['user']['branch_id']==1){$branchme=" MAIN OFFICE";}
if($_SESSION['admin']['user']['branch_id']==2){$branchme="Aklan BRANCH";}
if($_SESSION['admin']['user']['branch_id']==3){$branchme="Calapan BRANCH";}
if($_SESSION['admin']['user']['branch_id']==4){$branchme="Cagayan BRANCH";}
if($_SESSION['admin']['user']['branch_id']==5){$branchme="Davao BRANCH";}
if($_SESSION['admin']['user']['branch_id']==6){$branchme="Leyte BRANCH";}
if($_SESSION['admin']['user']['branch_id']==7){$branchme="Lucena BRANCH ";}
if($_SESSION['admin']['user']['branch_id']==8){$branchme="Pangasinan BRANCH";}



?>



<div class="col-sm-2">
<div class="form-group">
<label>Branch Reported</label>
<select name="applicant[typess]" class="form-control" required onchange="showapplicant(this.value)">
<option value="<?=$_SESSION['admin']['user']['branch_id']?>" selected ><?=$branchme?> -<?=$_SESSION['admin']['user']['branch_id']?></option>

 <?php if ( !in_array( $_SESSION['admin']['user']['user_type'], [17,29]) ): ?>  
                                 

<option VALUE="1"> MAIN OFFICE</option>
<option VALUE="2">Aklan BRANCH</option>
<option VALUE="3">Calapan BRANCH</option>
<option VALUE="4">Cagayan Valley BRANCH </option>
<option VALUE="5">Davao BRANCH </option>
<option VALUE="6">Leyte BRANCH </option>
<option VALUE="7">Lucena BRANCH </option>
<option VALUE="8">Pangasinan BRANCH</option>
<?php endif; ?>


</select>
</select>
</div>
</div>





<?php if ( !in_array( $_SESSION['admin']['user']['user_type'], [29]) ): ?> 

<div class="col-sm-2">
<div class="form-group">
<label>Agent/Source</label>
<select name="applicant[source]" class="form-control">
<option value="0" disabled>-- Source --</option>
<option value="0" selected >No Agent</option>
<?php foreach ( $agents as $agent ): ?>

<option value="<?php echo $agent['agent_id']; ?>" <?php echo isset( $post['applicant']['source'] ) && $post['applicant']['source'] == $agent['agent_id'] ? 'selected' : ''; ?>><?php echo $agent['agent_first'].' '.$agent['agent_last']; ?> 
</option>
<?php endforeach; ?>
</select>
</div>
</div>
<?php endif; ?>




<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [29]) ): ?> 

<div class="col-sm-2">
<div class="form-group">
<label>Source ID</label>



<input name="applicant[source]" type="text" value="<?=$_SESSION['admin']['user']['userassign']?>" readonly>

</div>
</div>
<?php endif; ?>



<div class="col-sm-2"  STYLE="display:none">
<div class="form-group">
<label>Principal</label>
<select name="applicant[employer]" class="form-control">

<option value="0">None</option>



<?php foreach ( $employers as $employer ): ?>
<option value="<?php echo $employer['employer_id']; ?>"><?php echo $employer['employer_name']; ?></option>
<?php endforeach; ?>
</select>
</div>
</div>

</br>


<div class="col-sm-2">
<div class="form-group">
<label>Status</label>
<select name="applicant[status-applicant]" class="form-control" required>
<option value="">-- Select --</option>
<option value="15">Passporting</option>
<option value="0">Available w/ Passport</option>
</select>	
</div>
</div>



<div class="col-sm-3">
<div class="form-group">
<label>Applicant Firstimer / Ex Abroad</label>
<select name="applicant[applicant_ex]" class="form-control" required>
<option value="">-- Select --</option>
<option>FIRST TIMER</option>
<option>EX ABROAD FINISHED CONTRACT</option>
<option>EX ABROAD UNFINISHED CONTRACT</option>
</select>	
</div>
</div>

</div>



<div style="clear:both;height:10px"></div>

<div class="row">
<div class="col-sm-2"  style="display:none">
<div class="form-group">
<label>Waitlist </label>
<select name="applicant[applicant_ppt_stat]" class="form-control" >
    
<?php if ( !in_array( $_SESSION['admin']['user']['user_type'], [29]) ): ?>     
<option VALUE="">N/A</option>
<option>Yes</option>
<option>No</option>
<?php endif; ?>



<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [29]) ): ?>     
<option>Yes</option>
<option>No</option>
<?php endif; ?>

</select>	
</div>
</div>



<div class="col-sm-2"  >
<div class="form-group">
<label>Waitlist</label>
<select name="applicant[applicant_ppt_pay]" class="form-control" required disabled>

<option>Yes</option>
<option>No</option>
<option>Done</option>
</select>	
</div>
</div>



</div>
<div class="row">
<?php show_customField($customFields,"Other"); ?>
</div>
<hr class="wide" />

<button type="submit" class="btn btn-danger pull-right">Register</button>

<small   style="display:none"><span class="pull-right">Signed in as <strong><?php echo $_SESSION['admin']['user']['user_fullname']; ?></strong> (<a href="?logout">Change user</a>)</span></small>
</form>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
<!-- /Page Body -->
<script type="text/javascript">
extraExperience1 = '<?php echo $_SESSION["settings"]["extraExperience1"]; ?>';
extraExperience2 = '<?php echo $_SESSION["settings"]["extraExperience2"]; ?>';
extraExperience3 = '<?php echo $_SESSION["settings"]["extraExperience3"]; ?>';
extraExperience4 = '<?php echo $_SESSION["settings"]["extraExperience4"]; ?>';
extraExperience5 = '<?php echo $_SESSION["settings"]["extraExperience5"]; ?>';
extraExperience6 = '<?php echo $_SESSION["settings"]["extraExperience6"]; ?>';
extraExperience7 = '<?php echo $_SESSION["settings"]["extraExperience7"]; ?>';
extraExperience8 = '<?php echo $_SESSION["settings"]["extraExperience8"]; ?>';
extraExperience9 = '<?php echo $_SESSION["settings"]["extraExperience9"]; ?>';
extraExperience10 = '<?php echo $_SESSION["settings"]["extraExperience10"]; ?>';
extraExperience11 = '<?php echo $_SESSION["settings"]["extraExperience11"]; ?>';
extraExperience12 = '<?php echo $_SESSION["settings"]["extraExperience12"]; ?>';
</script>