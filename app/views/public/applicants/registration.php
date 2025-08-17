<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<!-- Head -->
<head>
    <meta charset="utf-8" />
    <title><?php echo $app->getTitle(); ?></title>

    <!-- Description, Keywords and Author -->
    <meta name="description" content="Dashboard" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <?php echo $app->renderMetas(); ?>
    <link rel="shortcut icon" href="<?php echo $app->getPath()['images']; ?>favicon.png" type="image/x-icon">

    <!--Basic Styles-->
    <link href="<?php echo $app->getPath()['styles']; ?>bootstrap.min.css" rel="stylesheet" />
    <link id="bootstrap-rtl-link" href="#" rel="stylesheet" />
    <link href="<?php echo $app->getPath()['styles']; ?>font-awesome.min.css" rel="stylesheet" />
    <link href="<?php echo $app->getPath()['styles']; ?>weather-icons.min.css" rel="stylesheet" />

    <!--Fonts-->
    <!-- <link href="http://font.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,400,600,700,300" rel="stylesheet" type="text/css"> -->

    <!--Beyond styles-->
    <link id="beyond-link" href="<?php echo $app->getPath()['styles']; ?>beyond.min.css" rel="stylesheet" type="text/css" />

    <link href="<?php echo $app->getPath()['styles']; ?>typicons.min.css" rel="stylesheet" />
    <link href="<?php echo $app->getPath()['styles']; ?>animate.min.css" rel="stylesheet" />
    <link href="<?php echo $app->getPath()['styles']; ?>style.css" rel="stylesheet" />
    <link id="skin-link" href="#" rel="stylesheet" type="text/css" />    
    
    <?php $app->renderStyles(); ?>
    
    <script type="text/javascript">
    <?php 
        $paths    = $app->getPath();
        $urls     = [];
        $urls[]   = sprintf("%s='%s'", 'baseUrl', str_replace("'", "\'", base_url()));
        $urls[]   = sprintf("%s='%s'", 'siteUrl', str_replace("'", "\'", site_url()));
        
        foreach ($paths as $key => $path) {
            $urls[] = sprintf("%sUrl='%s'", $key, str_replace("'", "\'", $path));
        }
        
        echo 'var '.implode(', ', $urls) . ';';
    ?>
    </script>
    
    <!--Skin Script: Place this script in head to load scripts for skins and rtl support-->
    <script src="<?php echo $app->getPath()['scripts']; ?>skins.min.js"></script>    
</head>
<!-- /Head -->
<!-- Body -->
<body>

    <div class="main-container container-fluid" ng-controller="online-registration">
        <!-- Page Container -->
        <div class="page-container">
            
            <!-- Page Content -->
            <div class="container">

                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="widget flat radius-bordered">
                            <?php $app->renderAlerts(); ?>
                            <?php if($this->session->flashdata('cyd_error_msg') != ''){ ?>
                                <div class="alert alert-danger" role="alert">
                                <i class="fa fa-exclamation-triangle"></i>
                                <span class="sr-only">Error:</span>
                                <?php echo $this->session->flashdata('cyd_error_msg'); ?>
                                </div>
                            <?php } ?>
                            <div class="widget-header bg-blue">
                                <span class="widget-caption">Registration Form</span>
                            </div>
                            <div class="widget-body">
                                <div id="registration-form">
                                    <form class="form" role="form" method="post" enctype="multipart/form-data">
                                    	<div class="row">
                                        	<div class="col-sm-8">
                                            	<div class="form-title">
                                                    <strong>Preferred Designation</strong>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-8">
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
                                                </div>
                                                <div class="row">
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
                                                    <div class="col-sm-8">
                                                    	<hr>
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
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-8">
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
                                            <div class="col-sm-4">
                                            
                                            	<div class="row" >
                                                	<div class="col-sm-10 col-sm-offset-2" align="center" style="border-left:1px dashed #999">
                                                    	<img width="100" class="img-responsive img-rounded profile-photo" src="<?php echo $app->getPath()['images']; ?>avatars/no-picture.jpg" alt="Upload photo" /><br />
                                                        <input class="hide" type="file" name="applicant[photo]"/>
                                                        <button type="button" class="btn btn-md btn-info btn-photo-browse">Browse photo ...</button>
                                                    </div> 
                                                </div>                                                     
                                                 
                                            </div>
                                        </div>
                                    	
                                        
                                        <hr class="wide" />
                                    
                                        <div class="form-title">
                                            <strong>Basic Information</strong>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>First name</label>
                                                    <input type="text" name="applicant[basic][first]" class="form-control input-uppercase" placeholder="" value="<?php echo isset( $post['applicant']['basic']['first'] ) ? $post['applicant']['basic']['first'] : ''; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Middle name</label>
                                                    <input type="text" name="applicant[basic][middle]" class="form-control input-uppercase" placeholder="" value="<?php echo isset( $post['applicant']['basic']['middle'] ) ? $post['applicant']['basic']['middle'] : ''; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Last name</label>
                                                    <input type="text" name="applicant[basic][last]" class="form-control input-uppercase" placeholder="" value="<?php echo isset( $post['applicant']['basic']['last'] ) ? $post['applicant']['basic']['last'] : ''; ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>Date of birth</label>
                                                    <span class="input-icon icon-right">
                                                        <div class="input-group">
                                                            <input name="applicant[basic][birthdate]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker fa fa-calendar" placeholder="yyyy-mm-dd" value="<?php echo isset( $post['applicant']['basic']['birthdate'] ) ? $post['applicant']['basic']['birthdate'] : date( 'Y-m-d', time() ); ?>" required>
                                                        </div>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>Age</label>
                                                    <span class="input-icon icon-right">
                                                        <div class="input-group">
                                                            <input type="text" name="applicant[basic][age]" class="form-control input-uppercase" placeholder="" value="<?php echo isset( $post['applicant']['basic']['age'] ) ? $post['applicant']['basic']['age'] : ''; ?>" required readonly>
                                                        </div>
                                                    </span>
                                                </div>
                                            </div>
                                            <?php /*
                                            http://stackoverflow.com/questions/5524743/jquery-age-calculation-on-date
                                            <script type="text/javascript">
                                                jQuery('.bootstrap-tagsinput').click(function(){
                                                    alert('woooot!');
                                                });
                                            </script>
                                            */ ?>
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>Civil status</label>
                                                    <select name="applicant[basic][status]" class="form-control" required>
                                                        <option value="Single">Single</option>
                                                        <option value="Married">Married</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>Gender</label>
                                                    <select name="applicant[basic][gender]" class="form-control" required>
                                                        <option value="Male">Male</option>
                                                        <option value="Female">Female</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Email <small><em>(optional)</em></small></label>
                                                    <input type="email" name="applicant[basic][email]" class="form-control" placeholder="example@domain.com" value="<?php echo isset( $post['applicant']['basic']['email'] ) ? $post['applicant']['basic']['email'] : ''; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="">Contact number(s) <small><em>(Press Enter to accept number)</em></small></label>
                                                    <input name="applicant[basic][contacts]" type="text" data-role="tagsinput" placeholder="Add contact number" value="<?php echo isset( $post['applicant']['basic']['contacts'] ) ? $post['applicant']['basic']['contacts'] : ''; ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-8">
                                                <div class="form-group">
                                                    <label>Address</label>
                                                    <input type="text" name="applicant[basic][address]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['basic']['address'] ) ? $post['applicant']['basic']['address'] : ''; ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Nationality</label>
                                                    <input type="text" name="applicant[basic][nationality]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['basic']['nationality'] ) ? $post['applicant']['basic']['nationality'] : ''; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Religion / Belief</label>
                                                    <input type="text" name="applicant[basic][religion]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['basic']['religion'] ) ? $post['applicant']['basic']['religion'] : ''; ?>" >
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>Height</label>
                                                    <input type="text" name="applicant[basic][height]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['basic']['height'] ) ? $post['applicant']['basic']['height'] : ''; ?>">
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>Weight</label>
                                                    <input type="text" name="applicant[basic][weight]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['basic']['weight'] ) ? $post['applicant']['basic']['weight'] : ''; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-sm-6">
                                 				<div class="form-group">
                                                    <label for="">Languages <small><em>(Press Enter to accept language)</em></small></label>
                                                    <input name="applicant[basic][languages]" type="text" value="<?php echo isset( $post['applicant']['basic']['languages'] ) ? $post['applicant']['basic']['languages'] : 'English'; ?>" data-role="tagsinput" placeholder="Add language" />
                                                </div>
                                            </div>
                                            
                                        </div>
                                        
                                        <hr class="wide" />
                                    
                                        <div class="form-title">
                                           <strong> Passport Information</strong>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Passport number</label>
                                                    <input type="text" name="applicant[passport][number]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['passport']['number'] ) ? $post['applicant']['passport']['number'] : ''; ?>">
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Expiration</label>
                                                    <span class="input-icon icon-right">
                                                        <div class="input-group">
                                                            <input name="applicant[passport][expiration]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo isset( $post['applicant']['passport']['expiration'] ) ? $post['applicant']['passport']['expiration'] : date( 'Y-m-d', time() ); ?>">
                                                            <span class="input-group-addon">
                                                                <i class="fa fa-calendar"></i>
                                                            </span>
                                                        </div>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Issue place</label>
                                                    <input type="text" name="applicant[passport][issue-place]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['passport']['issue-place'] ) ? $post['applicant']['passport']['issue-place'] : ''; ?>">
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Date issued</label>
                                                    <span class="input-icon icon-right">
                                                        <div class="input-group">
                                                            <input name="applicant[passport][issue]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo isset( $post['applicant']['passport']['issue'] ) ? $post['applicant']['passport']['issue'] : date( 'Y-m-d', time() ); ?>">
                                                            <span class="input-group-addon">
                                                                <i class="fa fa-calendar"></i>
                                                            </span>
                                                        </div>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <hr class="wide" />
                                        <div class="form-title">
                                            <strong>Educational Background</strong>
                                        </div>
                                        <div class="row">
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
                                        <div class="row">
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
                                        
                                        <div class="row">
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
                                        
                                        <div class="row">
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
                                        <hr class="wide" />
                                        <div class="form-title">
                                            <strong>Work Experience</strong>
                                        </div>
                                        <div class="work-experience-start">
                                            <div class="row work-experience">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Company</label>
                                                        <input type="text" name="applicant[work-experience][company][]" class="form-control" placeholder="">
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Position</label>
                                                        <input type="text" name="applicant[work-experience][position][]" class="form-control" placeholder="">
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>Salary</label>
                                                        <input type="text" name="applicant[work-experience][salary][]" class="form-control" placeholder="0.00">
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Country</label>
                                                        <input type="text" name="applicant[work-experience][country][]" class="form-control" placeholder="">
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>From</label>
                                                        <input type="text" name="applicant[work-experience][from][]" class="form-control work-started" placeholder="yyyy">
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>To</label>
                                                        <input type="text" name="applicant[work-experience][to][]" class="form-control work-ended" placeholder="yyyy">
                                                    </div>
                                                </div>
                                                <div class="col-sm-1">
                                                    <div class="form-group">
                                                        <label>Years</label>
                                                        <input type="text" name="applicant[work-experience][years][]" class="form-control work-years" placeholder="0" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-sm-1">
                                                	<a href="#" role="button" class="btn btn-danger btn-xs">Remove</a>
                                                </div>
                                            </div>
                                            <div class="work-experience-separator"></div>
                                        </div><!-- .work-experience-start -->
                                        
                                        <div>
                                        	<hr />
                                        	<a href="#" id="btn-add-work-experience" role="button" class="btn btn-xs btn-default"><i class="fa fa-plus"></i> Add work experience</a>
                                            &nbsp;<span class="pull-right">Total years experience: <span class="text-danger work-experience-years">0</span></span>
                                        </div>
                                        
                                        <hr class="wide" />
                                        
                                        <div class="form-title">
                                            <strong>Other Skills </strong>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-sm-10">
                                 				<div class="form-group">
                                                    <label>Skills <small><em>(Press Enter to accept skills)</em></small></label>
                                                    <input name="applicant[other-skills]" type="text"  data-role="tagsinput" placeholder="Type skills" value="<?php echo isset( $post['applicant']['other-skills'] ) ? $post['applicant']['other-skills'] : ''; ?>">
                                                </div>
                                                
                                            </div>
                                            
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-sm-10">
                                            	<div class="form-group">
                                                    <label>Remarks <small><em>(optional)</em></small></label>
                                                    <span class="input-icon icon-right">
                                                        <textarea name="applicant[remarks]" class="form-control" rows="4" placeholder="Write some remarks..." disabled><?php echo isset( $post['applicant']['remarks'] ) ? $post['applicant']['remarks'] : ''; ?></textarea>
                                                        <i class="fa fa-briefcase darkorange"></i>
                                                    </span>
                                                    <!--<p class="help-block">Write some remarks</p>-->
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <hr class="wide" />
                                        <div class="row">
                                        	<div class="col-sm-2">
                                                <div class="form-group">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input name="applicant[type]" type="radio" value="Household" class="colored-blue" <?php echo isset( $post['applicant']['type'] ) && $post['applicant']['type'] == 'Skilled' ? '' : 'checked'; ?> >
                                                            <span class="text">Household</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
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
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label>Date applied</label>
                                                    <span class="input-icon icon-right">
                                                        <div class="input-group">
                                                            <input name="applicant[date-applied]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo isset( $post['applicant']['date-applied'] ) ? $post['applicant']['date-applied'] : date( 'Y-m-d', time() ); ?>" disabled>
                                                            <span class="input-group-addon">
                                                                <i class="fa fa-calendar"></i>
                                                            </span>
                                                        </div>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label>Source</label>
                                                    <select name="applicant[source]" class="form-control">
                                                        <option value="0" disabled>-- Source --</option>
                                                        <option value="0" selected >DIRECT HIRE</option>
                                                     
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <hr class="wide" />
                                        <button type="reset" class="btn btn-default">Reset Form</button>
                                        <button type="submit" class="btn btn-blue">Register</button>
                                        
                                        <small><span class="pull-right">Signed in as <strong><?php echo $_SESSION['admin']['user']['user_fullname']; ?></strong> (<a href="?logout">Change user</a>)</span></small>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php $app->renderFooter(); ?>
            </div>
            <!-- /Page Content -->
        </div>
        <!-- /Page Container -->
        <!-- Main Container -->
    </div>
    <?php $app->renderModals(); ?>

    <!--Basic Scripts-->
    <script src="<?php echo $app->getPath()['scripts']; ?>angular.min.js"></script>
    <script src="<?php echo $app->getPath()['scripts']; ?>jquery-2.0.3.min.js"></script>

    <script src="<?php echo $app->getPath()['scripts']; ?>bootstrap.min.js"></script>
    <script src="<?php echo $app->getPath()['scripts']; ?>beyond.min.js"></script>
    
    <script src="<?php echo $app->getPath()['plugins']; ?>timeago/jquery.livequery.js"></script>
    <script src="<?php echo $app->getPath()['plugins']; ?>timeago/jquery.timeago.js"></script>
    <script src="<?php echo $app->getPath()['scripts']; ?>script.js"></script> 
    <?php $app->renderScripts(); ?>
    <script src="<?php echo base_url(); ?>page/public/js/all.js"></script>

</body>
<!--  /Body -->
</html>
