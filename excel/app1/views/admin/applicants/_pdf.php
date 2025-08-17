<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $applicant['applicant_first'].' '.$applicant['applicant_last']; ?></title>
<style>
h3 {
    font-size: 14px;
    padding-left:10px;
    padding-right:10px;
}
.details {
    padding-left:10px;
    padding-right:10px;
    margin:10px;
}
.container {
    font-family:Arial, Helvetica, sans-serif;
    font-size:12px;
    margin:10px;
}
.wrapper {
    border:0px solid #ccc;
}

.header {
    text-align:left;
    padding-top:10px;
    min-height:80px;
    margin-bottom:10px;
}
.header .name {
    font-weight:bold;
    font-size:150%;
}
.content {
    margin-bottom:10px;
}
.footer {

}

table {
    width:100%;
}
table tbody tr th {
    font-weight:500;
    text-align:left;
    padding:5px;
}

.header {
	
}
.header img {
	width:150px;
}
</style>

</head>
<body>

<div class="container">
    <div class="wrapper">

        <div class="header">
        	<div class="picture" style=" display:inline-block; width:45%;">
            	<?php if ( is_file( DIR_UPLOADS.'applicant'.DIRECTORY_SEPARATOR.$applicant['applicant_photo'] ) ): ?>
                    <img src="<?php echo base_url(); ?>files/applicant/<?php echo $applicant['applicant_photo']; ?>" alt="" class="" />
                <?php else: ?>
                    <img src="<?php echo $app->getPath()['images']; ?>avatars/no-picture.jpg" alt="" class="" />
                <?php endif; ?>
            </div>
            
            <div class="" style=" display:inline-block; width:50%;">
	            <p><span class="name"><?php echo $applicant['applicant_first'].' '.$applicant['applicant_last']; ?></span></p>
		         <p>International Placement Assistance Company Inc.</p>   
            </div>
            
        </div>
        
        <div class="content">
            <h3>Preferred Designation</h3>
            <hr />
            <div class="details">
                <table>
                    <tbody>
                        <tr>
                            <th width="30%">Preferred position:</th>
                            <td><?php echo $applicant['position_name']; ?></td>
                        </tr> 
                        <tr>
                            <th><small>Other preferred positions:</small></th>
                            <td>
                                <?php $positions = []; ?>
                                <?php foreach ( $applicant['other-preferred-positions'] as $position ): ?>
                                <?php $positions[] = $position['position_name']; ?>
                                <?php endforeach; ?>
                                <span class="description">
                                    <small><?php echo implode( ',&nbsp;', $positions ); ?></small>
                                </span>
                            </td>
                        </tr>                     
                        <tr>
                            <th>Preferred country:</th>
                            <td><?php echo $applicant['country_name']; ?></td>
                        </tr>
                        <tr>
                            <th><small>Other preferred countries:</small></th>
                            <td>
                                <?php $countries = []; ?>
                                <?php foreach ( $applicant['other-preferred-countries'] as $country ): ?>
                                <?php $countries[] = $country['country_name']; ?>
                                <?php endforeach; ?>
                                <span class="description">
                                    <small><?php echo implode( ',&nbsp;', $countries ); ?></small>
                                </span>
                            </td>
                        </tr> 
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="content">
            <h3>About <?php echo strtoupper( $applicant['applicant_first'] ); ?></h3>
            <hr />
            <div class="details">
                <table>
                    <tbody>
                        <tr>
                            <th width="20%">Name:</th>
                            <td><?php echo $applicant['applicant_first'].' '.$applicant['applicant_last']; ?></td>
                        </tr>
                        <tr>
                            <th>Date of birth:</th>
                            <td><?php echo $applicant['applicant_birthdate']; ?></td>
                        </tr>
                        <tr>
                            <th>Civil status:</th>
                            <td><?php echo $applicant['applicant_civil_status']; ?></td>
                        </tr>
                        <tr>
                            <th>Gender:</th>
                            <td><?php echo $applicant['applicant_gender']; ?></td>
                        </tr>
                        <tr>
                            <th>E-mail address:</th>
                            <td><a href="mailto:<?php echo $applicant['applicant_email']; ?>"><?php echo $applicant['applicant_email']; ?></a></td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td><?php echo $applicant['applicant_address']; ?></td>
                        </tr>
                        <tr>
                            <th>Nationality:</th>
                            <td><?php echo $applicant['applicant_nationality']; ?></td>
                        </tr>
                        <tr>
                            <th>Belief/Religion:</th>
                            <td><?php echo $applicant['applicant_religion']; ?></td>
                        </tr>
                        <tr>
                            <th>Height:</th>
                            <td><?php echo $applicant['applicant_height']; ?></td>
                        </tr>
                        <tr>
                            <th>Weight:</th>
                            <td><?php echo $applicant['applicant_weight']; ?></td>
                        </tr>
                        <tr>
                            <th>Languages:</th>
                            <td><?php echo str_replace( ',', ',&nbsp;', $applicant['applicant_languages'] ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="content">
            <h3>Passport</h3>
            <hr />
            <div class="details">
                <table>
                    <tbody>
                        <tr>
                            <th width="20%">Passport number:</th>
                            <td><?php echo $applicant['passport_number']; ?></td>
                        </tr>                        
                        <tr>
                            <th>Date of expiration:</th>
                            <td><?php echo date( 'd M Y', strtotime( $applicant['passport_expiration'] ) ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="content">
            <h3>Education</h3>
            <hr />
            <div class="details">
                <table>
                    <tbody>
                        <tr>
                            <th width="10%">MBA:</th>
                            <td><?php echo $applicant['education_mba']; ?></td>
                            <th width="10%">Course:</th>
                            <td><?php echo $applicant['education_mba_course']; ?></td>
                            <th width="5%">Year:</th>
                            <td><?php echo $applicant['education_mba_year']; ?></td>
                        </tr>
                        <tr>
                            <th>College:</th>
                            <td><?php echo $applicant['education_college']; ?></td>
                            <th>Skills:</th>
                            <td><?php echo $applicant['education_college_skills']; ?></td>
                            <th>Year</th>
                            <td><?php echo $applicant['education_college_year']; ?></td>
                        </tr>
                        <tr>
                            <th>Others:</th>
                            <td colspan="3"><?php echo $applicant['education_others']; ?></td>
                            <th>Year</th>
                            <td><?php echo $applicant['education_others_year']; ?></td>
                        </tr>
                        <tr>
                            <th>HS:</th>
                            <td colspan="3"><?php echo $applicant['education_highschool']; ?></td>
                            <th>Year</th>
                            <td><?php echo $applicant['education_highschool_year']; ?></td>
                        </tr>
                        
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="content">
            <h3>Work Experiences</h3>
            <hr />
            <div class="details">
                <?php if ( empty($applicant['experiences'] ) ): ?>
                    -- No working experiences --
                <?php endif; ?>
                <?php foreach ( $applicant['experiences'] as $experience ): ?>
                <div class="row">
                    <div class="col-sm-12" >
                        <div>
                            <span class="description"><small><?php echo $experience['experience_company']; ?></small></span>
                        </div>
                        <div class="rmeta"><strong><?php echo $experience['experience_position']; ?></strong> for <?php echo $experience['experience_years']; ?> years</div>
                        <p><?php echo $experience['experience_from']; ?> &minus; <?php echo $experience['experience_to']; ?>, &#8369; <?php echo number_format( $experience['experience_salary'], 2 ); ?></p>
                    </div>
                </div>                                           
                
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="content">
            <h3>Other Skills</h3>
            <hr />
            <div class="details">
                <?php if ( empty( $applicant['applicant_other_skills'] ) ): ?>
                -- No more other skills defined --
                <?php endif; ?>
                <?php foreach ( explode( ',', $applicant['applicant_other_skills'] ) as $skill ): ?>
                <span><?php echo $skill; ?></span>&nbsp;
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ( ! empty( $wholeBody ) ): ?>
        <h3>Whole body picture</h3>
        <img src="<?php echo base_url().$wholeBody['file_path']; ?>" alt="<?php echo $wholeBody['file_type']; ?>" class="img-rounded" height="500">
        <?php endif; ?>
       
    </div>
</div>
</body>
</html>
