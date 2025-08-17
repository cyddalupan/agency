<style>
* {
    font-family:Arial, sans-serif;
    font-size:12px;
}
.table {
    border-radius:8px;
    border:1px solid #2A2929;
    margin:0;
    padding:0;
    width:100%;
}
.table th {
    text-align:left;
    padding:5px;
    text-transform: uppercase;
}

.table td {
    text-align:left;
    padding:5px;
    text-transform: uppercase;
}

.tr-bordered tr th {
    border-right:1px solid #ccc;
}
.tr-bordered tr:not(:last-child) th, .tr-bordered tr:not(:last-child) td {
    border-bottom:1px solid #ccc;           
}
.img-responsive {
    display:block;
    width:100% \9;
    max-width:100%;
    height:auto;
}
.box {
    border:1px solid #999; 
    padding:5px;
}
.box table th, .box table td {
    padding:5px;
}
.box img {
    margin:0 auto;
    position:relative;
    left:20px;
    right:20px;
}
</style>
<page>
<table class="" width="900" style="margin:0; padding:0;">

    <tr>
        <td colspan="2">
            <img width="500" height="150" class="logo img-responsive" src="assets/images/admin/logo.jpg">
        </td>        
    </tr>
    <tr>
        <td >
            <div class="box" style="width:460px;">
            <table class="">
                <tbody>
                    <tr>
                        <th width="35%">NAME:</th>
                        <td><?php echo $applicant['applicant_name']; ?></td>
                    </tr>
                    <tr>
                        <th >POST APPLIED FOR: </th>
                        <td><?php echo $applicant['position_name']; ?></td>
                    </tr>
                    <tr>
                        <th>MONTHLY SALARY: </th>
                        <td>PHP <?php echo number_format( $applicant['requirement_offer_salary'], 2); ?></td>
                    </tr>
                </tbody>
            </table>
            </div>
        </td>
        <td align="right" valign="bottom">
            <?php if ( is_file( DIR_UPLOADS.'applicant'.DIRECTORY_SEPARATOR.$applicant['applicant_photo'] ) ): ?>
                <img src="<?php echo base_url(); ?>files/applicant/<?php echo $applicant['applicant_photo']; ?>" alt="" width="90" class="profile-photo" />
            <?php else: ?>
                <img src="<?php echo $app->getPath()['images']; ?>avatars/no-picture.jpg" alt="" width="90" class="profile-photo"/>
            <?php endif; ?>
        </td>
    </tr>
</table>

<br>

<table class="" width="1000">
    <tr>
        <td width="50%" valign="top">
            <h4>BASIC DETAILS:</h4>
            <div class="box" style="width:350px;">
                <table class="">
                    <tr>
                        <th width="35%">NATIONALITY: </th>
                        <td><?php echo $applicant['applicant_nationality']; ?></td>
                    </tr>
                    <tr>
                        <th>RELIGION/BELIEF: </th>
                        <td><?php echo $applicant['applicant_religion']; ?></td>
                    </tr>
                    <tr>
                        <th>DATE OF BIRTH: </th>
                        <td><?php echo fdate( 'M. d, Y', $applicant['applicant_birthdate'], '0000-00-00' ); ?></td>
                    </tr>
                    <tr>
                        <th>AGE: </th>
                        <td><?php echo $applicant['applicant_age']; ?></td>
                    </tr>
                    <tr>
                        <th>LIVING TOWN: </th>
                        <td><?php echo $applicant['applicant_address']; ?></td>
                    </tr>
                    <tr>
                        <th>MARITAL STATUS: </th>
                        <td><?php echo $applicant['applicant_nationality']; ?></td>
                    </tr>
                    <tr>
                        <th>WEIGHT: </th>
                        <td><?php echo $applicant['applicant_weight']; ?></td>
                    </tr>
                    <tr>
                        <th>HEIGHT: </th>
                        <td><?php echo $applicant['applicant_height']; ?></td>
                    </tr>
                </table>
            </div>
            
            <h4>KNOWLEDGE OF LANGUAGES:</h4>
            <div class="box" style="width:350px;">
                <table >
                    <tbody>
                        <tr>
                            <td><?php echo implode( ', ', explode( ',', $applicant['applicant_languages'] ) ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h4>SKILLS:</h4>
            <div class="box" style="width:350px;">
                <table class="">
                    <tbody>
                        <?php foreach ( explode( ',', $applicant['applicant_other_skills'] ) as $skill ): ?>
                        <tr>
                            <td><?php echo $skill; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
        </td>
        <td width="50%" valign="top" style="padding:5px;">
            <h4>PASSPORT DETAILS:</h4>
            <div class="box" style="width:350px;">
                <table class="">
                    <tr>
                        <th width="35%">NUMBER: </th>
                        <td width="65%"><?php echo $applicant['passport_number']; ?></td>
                    </tr>
                    <tr>
                        <th>DATE OF BIRTH: </th>
                        <td><?php echo fdate( 'M. d, Y', $applicant['applicant_birthdate'], '0000-00-00' ); ?></td>
                    </tr>
                    <tr>
                        <th>PLACE OF ISSUE: </th>
                        <td><?php echo $applicant['passport_issue_place']; ?></td>
                    </tr>
                    <tr>
                        <th>DATE OF EXP.: </th>
                        <td><?php echo fdate( 'M. d, Y', $applicant['passport_expiration'], '0000-00-00' ); ?></td>
                    </tr>
                </table>
            </div>
            
            <?php if ( ! empty( $wholeBody ) ): ?>
            <h5>WHOLE BODY PICTURE: </h5>
            <div class="box" style="width:350px;">
                <img class="" width="300" src="<?php echo base_url().$wholeBody['file_path']; ?>" alt="<?php echo $wholeBody['file_type']; ?>">
            </div>
            <?php endif; ?>
        </td>
    </tr>
    
    <tr>
        <td colspan="2" align="left">
            <h4>WORK EXPERIENCES</h4>
            <div class="box" style="width:100%">
            <?php if ( count( $applicant['experiences'] ) ): ?>
            <table class="">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Position</th>
                        <th>Salary</th>
                        <th>Years</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $applicant['experiences'] as  $experience ): ?>
                    <tr>
                        <td><?php echo $experience['experience_company']; ?></td> 
                        <td><?php echo $experience['experience_position']; ?></td>
                        <td>PHP <?php echo number_format( $experience['experience_salary'], 2 ); ?></td>
                        <td><?php echo $experience['experience_years']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>--</p>
            <?php endif; ?>
            </div>
        </td>
    </tr>
    
</table>
</page>
