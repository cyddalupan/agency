<?php //-->
use \Application\Message as Message;
use \Application\Pagination as Pagination;

require_once __DIR__.'/../../third_party/mpdf60/mpdf.php';

defined('BASEPATH') OR exit('No direct script access allowed');

class Resume  extends Public_Controller {

public function __construct() 
{
parent::__construct();

//make sure page has a settings
if(!isset($_SESSION["settings"])){
$this->session->set_flashdata('prev_url', current_url());
redirect('add_settings');
}

$this->load->model( 'm_applicant' );
$this->load->model( 'Cyd_Applicants_Alphatomo' );
$this->load->model( 'Cyd_Survey_Alphatomo' );
}

public function applicant( $applicantId )
{
if ( empty( $applicantId ) ) { 
show_404(); 
}

$applicant_raw = ( new m_applicant )->cyd_get_applicants_raw();
$applicant_certificate_raw = ( new m_applicant )->cyd_get_applicant_certificate_raw();
$applicant_requirement_raw = ( new m_applicant )->cyd_applicant_requirement_raw();
$applicant          = ( new m_applicant )->getApplicantById( $applicantId );
$applicant_alphatomo = ( new Cyd_Applicants_Alphatomo )->getApplicantsAlphatomoById( $applicantId );	
$survey_alphatomo    = ( new Cyd_Survey_Alphatomo )->getSurveyAlphatomoById( $applicantId );
$train          = ( new m_applicant )->getApplicantWorktrain( $applicantId );
$applicant_currency = ( new m_applicant )->getCurrencyById( $applicantId );
$fileWholeBody      = ( new m_applicant )->getApplicantFileByType( $applicantId, 'Whole Body Picture' );
$skill_cyd      	= ( new m_applicant )->get_skill_cyd( $applicantId);

$fileOptions['where'][] =   
" `file_type` IN ('Passport'



)";

$files              = ( new m_applicant )->getApplicantFiles( $applicantId, $fileOptions );

$profilePicture = $banner = $wholeBody = $documents = null;

if ( is_file( DIR_UPLOADS.'applicant'.DIRECTORY_SEPARATOR.$applicant['applicant_photo'] ) ) {
$profilePicture = base_url().'files/applicant/'.$applicant['applicant_photo'];
} else {
$profilePicture = $this->getPath()['images'].'avatars/no-picture.jpg';
}

//change banner name, Do not change logo.jpg, upload new image with new name.


if($applicant['applicant_employer']==0) { $banner = $this->getPath()['images'].'workasia2.png'; }
if($applicant['applicant_employer']==1) { $banner = $this->getPath()['images'].'workasia2.png'; }
if($applicant['applicant_employer']==2) { $banner = $this->getPath()['images'].'workasia2.png'; }
if($applicant['applicant_employer']==3) { $banner = $this->getPath()['images'].'alqurm.png'; }
if($applicant['applicant_employer']==4) { $banner = $this->getPath()['images'].'workasia2.png'; }
if($applicant['applicant_employer']==5) { $banner = $this->getPath()['images'].'workasia2.png'; }
if($applicant['applicant_employer']==6) { $banner = $this->getPath()['images'].'workasia2.png'; }
if($applicant['applicant_employer']==7) { $banner = $this->getPath()['images'].'workasia2.png'; }
if($applicant['applicant_employer']==8) { $banner = $this->getPath()['images'].'workasia2.png'; }
if($applicant['applicant_employer']==9) { $banner = $this->getPath()['images'].'workasia2.png'; }
if($applicant['applicant_employer']==10) { $banner = $this->getPath()['images'].'workasia2.png'; }
if($applicant['applicant_employer']==11) { $banner = $this->getPath()['images'].'workasia2.png'; }
if($applicant['applicant_employer']==12) { $banner = $this->getPath()['images'].'workasia2.png'; }
if($applicant['applicant_employer']==13) { $banner = $this->getPath()['images'].'workasia2.png'; }
if($applicant['applicant_employer']==14) { $banner = $this->getPath()['images'].'workasia2.png'; }
if($applicant['applicant_employer']==15) { $banner = $this->getPath()['images'].'workasia2.png'; }
if($applicant['applicant_employer']==16) { $banner = $this->getPath()['images'].'workasia2.png'; }
if($applicant['applicant_employer']==21) { $banner = $this->getPath()['images'].'workasia2.png'; }





if ( ! empty( $fileWholeBody ) ) {
if($applicant['applicant_position_type']!='Skilled') {
$path_parts = pathinfo(base_url().$fileWholeBody['file_path']);
if($path_parts['extension'] != 'bmp'){
$wholeBody = '<div>
<div class="gradient" style="width: 99%; margin-bottom: 0pt;border:0px solid black; ">
<img src="'.base_url().$fileWholeBody['file_path'].'" style="height:520px;width:260px;border:1px solid gray	 " />

</div>

</div>';
}
}
}

$contact=$applicant['applicant_contacts'];






$trainineme = 'aaa';




if ( ! empty( $train['experiencestraining'] ) && is_array( $train['experiencestraining'] ) ) {
$trainineme = '';

foreach ( $train['experiencestraining'] as $experiencestraining2 ) {
$trainineme .= 
'<tr>
<th style="color:black;text-left:center;font-size:14px;color:black;width:150px;font-weight:none">t_name : </th>
<td style="text-align:left">'.$experiencestraining2['t_name'].'</td>
</tr>

<tr>
<th style="color:black;text-left:center;font-size:14px;color:black;width:150px;font-weight:none">t_provider : </th>
<td style="text-align:left">'.$experiencestraining2['t_provider'].'</td>
</tr>

<tr>
<th style="color:black;text-left:center;font-size:14px;color:black;width:150px;font-weight:none">t_place : </th>
<td style="text-align:left">'.$experiencestraining2['t_place'].'</td>
</tr>

<tr>
<th style="color:black;text-left:center;font-size:14px;color:black;width:150px;font-weight:none">t_issue :</th>
<td style="text-align:left">'.date('M-d-Y', strtotime($experiencestraining2['t_issue'])).'</td>
</tr>


<tr>
<th style="color:black;text-left:center;font-size:14px;color:black;width:150px;font-weight:none">Duration :</th>
<td style="text-align:left">'.date('M-d-Y', strtotime($experiencestraining2['t_expired'])).'</td>
</tr>

<tr>
<th style="color:black;text-left:center;font-size:14px;color:black;width:150px;font-weight:none;
color:black;" colspan="2">Job Description:</th>
</tr>

<tr>
<td style="text-align:left"  colspan="2">
<textarea style="width:650px;height:280px;margin-bottom: 30pt;border:white;background:white;margin-top:10px">
'.$experience['salary'].'</textarea>	
</td>

</tr>'
.sprintf("\n");



}			
}


if($applicant['applicant_position_type']=='Skilled') {
$trainineme = 
'<table cellspacing="0" cellpadding="1">

<tbody>'.$trainineme.'</tbody>
</table>';
}










foreach ( $files as $key => $file ) {
if ( strpos( $file['file_mime'], 'image/' ) !== false ) {

$path_parts = pathinfo(base_url().$file['file_path']);
if($path_parts['extension'] != 'bmp'){
$documents .= 
'<div class="clearfix"></div>
<div class="" style="margin-top: -30pt; ">

<div style="border:0px solid #ccc">
<div class="gradient" style="margin-bottom: 0pt; ">
<img src="'.base_url().$file['file_path'].'" style="height:480px;width:470px;margin: auto 0;margin-left:110px " />
</div>
</div>
</div>';
}

}

}

	//get children count
		if($applicant_raw->applicant_children != '')
			$children_count = count(explode(',', $applicant_raw->applicant_children));
		else
			$children_count = 'none';
		
	//get json arrays 
		$survey_array = cydGetJson("survey.json");
		$wexp_array = cydGetJson("working_experience.json");
		$wabl_array = cydGetJson("working_ability.json");

		//Get gen_info_html
		$gen_info_html = '';
        foreach ($survey_array->survey as $survey_value) {
        	$srv_ans = $survey_alphatomo[$survey_value->string];
        	if($srv_ans == 1)
        		$srv_ans = 'Yes';
        	else
        		$srv_ans = 'No';
			$gen_info_html .= '
			<div class="white" style="float: left;">
				<div style="FONT-SIZE:13PX;float: left;width:80%;border-right:1px solid black">'.$survey_value->name.'</div>	
				<div style="FONT-SIZE:13PX;float: left;width:15%;text-align:center"><b>'.$srv_ans.'</b></div>	
			</div>';
        }

        $wexp_html = '';
        foreach ($wexp_array->working_experience as $wexp_value) {

			$wexp_html .= '
			<div class="" style="float: left;BORDER:1PX Solid black;HEIGHT:60PX;PADDING:5PX;width:100%">
					<div style="FONT-SIZE:13PX;float: left;width:100%;">'.$wexp_value->name.'</div>
					<div STyle="clear:both;HEIGHT:2PX"></div>			
					<div style="FONT-SIZE:13PX;float: left;width:100%"><b>'.$survey_alphatomo[$wexp_value->string].'</b></div>	
			</div>
			<div style="clear:both"></div>
			';
		}

		foreach ($wabl_array->working_ability as $wabl_value) {
			if($survey_alphatomo[$wabl_value->exp] == 1){
				$wabl_exp = 'YES';
				$wabl_will = '-';
			}else{
				$wabl_will = 'YES';
				$wabl_exp = '-'; 
			}
			$wabl_html .= '
			<div class="white" style="float: left;">
				<div style="FONT-SIZE:13PX;float: left;width:50%;TEXT-ALIGN:left">'.$wabl_value->name.'</div>	
				<div style="FONT-SIZE:13PX;float: left;width:20%;TEXT-ALIGN:center"><b>'.$wabl_exp.'</b></div>
				<div style="FONT-SIZE:13PX;float: left;width:20%;TEXT-ALIGN:center"><b>'.$wabl_will.'</b></div>			
			</div>
			';
		}
	
		

	foreach ( $files as $key => $file ) {
			if ( strpos( $file['file_mime'], 'image/' ) !== false ) {

				$path_parts = pathinfo(base_url().$file['file_path']);
				if($path_parts['extension'] != 'bmp'){
					$documents1 .= 
						'<div class="clearfix"></div>
						<div class="" style="margin-top: -30pt; ">
				    	
							<div style="border:0px solid #ccc">
						        <div class="gradient" style="margin-bottom: 0pt; ">
						        	<img src="'.base_url().$file['file_path'].'" style="height:820px;width:770px;margin: auto 0;margin-left:10px " />
						        </div>
						    </div>
				    	</div>';
				}
				
			}
			
		}




array_walk( $applicant , function( &$value ) {
$value = empty( $value ) ? '&nbsp;' : $value;
});

$education = 
'<table cellspacing="0" cellpadding="1" style="font-size:11px;font-family:Arial">
		    
			<tr>
			<td  style="text-align:center"><b >Educational Details</b></td>
			<td  style="text-align:center"><b style="text-align:center">Name of School</b></td>
			<td  style="text-align:center"><b style="text-align:center">Year</b></td>			
			</tr>
			
		        <tr>
					 <td width="120px"><b >Primary</b></td>
					<td>'.$applicant['education_mba'].'</td>
					<td>'.$applicant['education_mba_year'].'</td>					
		        </tr>
				
				
		        <tr>
					 <td width=""><b>Secondary</b></td>
					<td >'.$applicant['education_highschool'].'</td>
					<td>'.$applicant['education_highschool_year'].'</td>					
		        </tr>
				
		       
		        <tr>
					<td width=""><b>College/Course</b></td>
					<td>'.$applicant['education_college'].'</td>
					<td>'.$applicant['education_college_year'].'</td>					
		        </tr>
		        
		        <tr>
					<td width=""><b>Vocational</b></td>
					<td>'.$applicant['education_others'].'</td>
					<td>'.$applicant['education_others_year'].'</td>					
		        </tr>
				
		    </table>';
if($applicant['applicant_position_type']=='Skilled') {
$workExperiences = 
'<tr>
<td colspan="4" STYLE="color:black">-- FIRST TIMER--</td>
</tr>';

if ( ! empty( $applicant['experiences'] ) && is_array( $applicant['experiences'] ) ) {

$workExperiences = '';

foreach ( $applicant['experiences'] as $experience ) {
$workExperiences .= 
'<tr>
<th style="color:black;text-left:center;font-size:14px;color:black;width:150px;font-weight:none">Company : </th>
<td style="text-align:left">'.$experience['experience_company'].'</td>


</tr>

<tr>

<th style="color:black;text-left:center;font-size:14px;color:black;width:150px;font-weight:none">Position :</th>
<td  style="text-align:left">'.$experience['experience_position'].'</td>

</tr>


<tr>

<th style="color:black;text-left:center;font-size:14px;color:black;width:150px;font-weight:none">Location :</th>
<td  style="text-align:left"> '.$experience['experience_country'].'</td>


</tr>

<tr>

<th style="color:black;text-left:center;font-size:14px;color:black;width:150px;font-weight:none">Duration :</th>
<td style="text-align:left">'.date('M-d-Y', strtotime($experience['experience_from'])).' - '.date('M-d-Y', strtotime($experience['experience_to'])).'</td>

</tr>






<tr>
<td colspan="2" style="color:White">.</td>
</tr>



<tr>
<th style="color:black;text-left:center;font-size:14px;color:black;width:150px;font-weight:none;
color:black;" colspan="2">Job Description:</th>
</tr>

<tr>
<td style="text-align:left"  colspan="2">
<textarea style="width:650px;height:280px;margin-bottom: 30pt;border:white;background:white;margin-top:10px">
'.$experience['salary'].'</textarea>	
</td>

</tr>

<tr>
<td colspan="2" style="color:White">.</td>
</tr>
<tr>
<td colspan="2" style="color:White">.</td>
</tr>

<tr>
<td colspan="2" style="color:White">.</td>
</tr>'



.sprintf("\n");



}			
}
}
//household
if($applicant['applicant_position_type']=='Household') {
$workExperiences = 
'<tr>
<td colspan="4" STYLE="color:black;font-weight:bold">-- FIRST TIMER--</td>
</tr>';

if ( ! empty( $applicant['experiences'] ) && is_array( $applicant['experiences'] ) ) {

$workExperiences = '';

foreach ( $applicant['experiences'] as $experience ) {
$workExperiences .= 
'<tr>

<td color:black;font-weight:bold>'.$experience['salary'].' '.$experience['experience_position'].'</td>
<td color:black;font-weight:bold> '.$experience['experience_country'].'</td>
<td color:black;font-weight:bold>'.$experience['experience_from'].' - '.$experience['experience_to'].' </td>

</tr>'.sprintf("\n");
}			
}
}


if($applicant['applicant_position_type']=='Skilled') {

$workExperiences = 
'<table cellspacing="0" cellpadding="1">

<tbody>'.$workExperiences.'</tbody>
</table>';
}


if($applicant['applicant_position_type']=='Household') {

$workExperiences = 
'<table cellspacing="0" cellpadding="1">
<thead>
<tr >

<th width="24%">Position  </th>
<th width="17%">Country  </th>
<th width="31%">Duration  </th>

</tr>
</thead>
<tbody>'.$workExperiences.'</tbody>
</table>';
}

function skillCydConvert($skill){
if($skill == 1){
return 'YES';
}else{
return 'NO';
}
}

$data = array_merge(
$applicant,
$fileWholeBody,

[
'ref_no'                   => str_pad( $applicant['applicant_id'], 7, '0', STR_PAD_LEFT ),
'applicant_date_applied'   => fdate( 'd M Y', $applicant['applicant_date_applied'], '0000-00-00' ),
'contact'				   => $applicant['applicant_contacts'],
'age'				   => $applicant['applicant_age'],
'edu'				   => $applicant['education_others'],
'applicantNumber'	   =>$applicant_raw[$applicant['applicant_id']]->applicantNumber,
'applicant_mothers'	   =>$applicant_raw[$applicant['applicant_id']]->applicant_mothers,

'applicant_ex'	   =>$applicant_raw[$applicant['applicant_id']]->applicant_ex,
'applicant_remarks_3'	   =>$applicant_raw[$applicant['applicant_id']]->applicant_remarks_3,
'applicant_incase_name'	   =>$applicant_raw[$applicant['applicant_id']]->applicant_incase_name,
'applicant_jobs'	   =>$applicant_raw[$applicant['applicant_id']]->applicant_jobs,
'currency'	   =>$applicant_raw[$applicant['applicant_id']]->currency,
'applicant_remarks1'	   =>$applicant_raw[$applicant['applicant_id']]->applicant_remarks1,
'applicant_incase_relation'	   =>$applicant_raw[$applicant['applicant_id']]->applicant_incase_relation,
'applicant_incase_contact'	   =>$applicant_raw[$applicant['applicant_id']]->applicant_incase_contact,
'applicant_incase_address'	   =>$applicant_raw[$applicant['applicant_id']]->applicant_incase_address,
'requirement_offer_salary' => $applicant_currency.' '.number_format( (int) $applicant['requirement_offer_salary'], 2),
'applicant_name'           => strtoupper( $applicant['applicant_name'] ),
'fname'           => strtoupper( $applicant['applicant_first'] ),
'mname'           => $applicant['applicant_middle'] ,
'lname'           => strtoupper( $applicant['applicant_last'] ),
'applicant_children'  =>$applicant_raw[$applicant['applicant_id']]->applicant_children,

'applicant_birthdate'      => fdate( 'd M Y', $applicant['applicant_birthdate'], '0000-00-00' ),
'passport_issue'           => fdate( 'M. d, Y', $applicant['passport_issue'], '0000-00-00' ),
'passport_expiration'      => fdate( 'M. d, Y', $applicant['passport_expiration'], '0000-00-00' ),
'passport_place'           => $applicant['passport_issue_place'],
'profile_picture'          => $profilePicture,
'applicant_languages'   => empty( $applicant['applicant_languages'] ) ? '&nbsp;' : str_replace( ',', '<br>', $applicant['applicant_languages'] ),

'applicant_other_skills'   => empty( $applicant['applicant_other_skills'] ) ? '&nbsp;' : str_replace( ',', '<br>', $applicant['applicant_other_skills'] ),
'education'                => $education,
'work_experiences'         => $workExperiences,
'trainineme'         => $trainineme,
'banner'                   => $banner,
'whole_body'               => $wholeBody,
'documents'                => $documents,
'documents1'                => $documents1,
'contact'                => $contact,
'applicant_remarks5'	   =>$applicant_raw[$applicant['applicant_id']]->applicant_remarks5,
'applicant_remarks6'	   =>$applicant_raw[$applicant['applicant_id']]->applicant_remarks6,

//skills cyd
'ironing'			=> skillCydConvert($skill_cyd[0]->ironing),
'cooking'           => skillCydConvert($skill_cyd[0]->cooking),
'sewing'            => skillCydConvert($skill_cyd[0]->sewing),
'computer'          => skillCydConvert($skill_cyd[0]->computer),
'washing'           => skillCydConvert($skill_cyd[0]->washing),
'cleaning'          => skillCydConvert($skill_cyd[0]->cleaning),
'tutoring'          => skillCydConvert($skill_cyd[0]->tutoring),
'children_care'     => skillCydConvert($skill_cyd[0]->children_care),
'baby_sitting'      => skillCydConvert($skill_cyd[0]->baby_sitting),
'arabic_cooking'    => skillCydConvert($skill_cyd[0]->arabic_cooking),
'manicure'    => skillCydConvert($skill_cyd[0]->manicure),
'massage'    => skillCydConvert($skill_cyd[0]->massage),
'blower'    => skillCydConvert($skill_cyd[0]->blower),
'coloring'    => skillCydConvert($skill_cyd[0]->coloring),
'write_e'    => skillCydConvert($skill_cyd[0]->write_e),
'read_e'    => skillCydConvert($skill_cyd[0]->read_e),
'speak_e'    => skillCydConvert($skill_cyd[0]->speak_e),
'write_a'    => skillCydConvert($skill_cyd[0]->write_a),
'read_a'    => skillCydConvert($skill_cyd[0]->read_a),
'speak_a'    => skillCydConvert($skill_cyd[0]->speak_a),

'no_of_bro'           	   => $applicant_alphatomo->no_of_bro,
'no_of_sis'           	   => $applicant_alphatomo->no_of_sis, 
'pos_in_fam'           	   => $applicant_alphatomo->pos_in_fam,
'partner_husband'          => $applicant_alphatomo->partner_husband,
'partner_occupation'       =>$applicant_alphatomo->partner_occupation,
'nam_of_fat'           	   => $applicant_alphatomo->nam_of_fat,
'occ_of_fat'           	   => $applicant_alphatomo->occ_of_fat,
'applicant_mothers'        => $applicant_raw[$applicant['applicant_id']]->applicant_mothers,
'occ_of_mom'           	   => $applicant_alphatomo->occ_of_mom,
'relative_name'            => $applicant_alphatomo->relative_name,
'relative_mobile'          => $applicant_alphatomo->relative_mobile,
'applicant_expected_salary' => $applicant_raw->applicant_expected_salary,
'children_count'           => $children_count,
'gen_info_html'				=> $gen_info_html,
    			'wexp_html'					=> $wexp_html,
    			'wabl_html'					=> $wabl_html,
    			'future_plans'           	=> $this->emptest($survey_alphatomo['future_plans']),



]);

if($applicant['applicant_position_type']=='Household' && $applicant['applicant_preferred_country']==24) {
$html = file_get_contents( __DIR__.'/../../views/public/applicants/malaysia.php' );
}

if($applicant['applicant_position_type']=='Household' && $applicant['applicant_preferred_country']!=24) {
$html = file_get_contents( __DIR__.'/../../views/public/applicants/pdf.php' );
}


if($applicant['applicant_position_type']=='Skilled' && $applicant['applicant_preferred_country']!=24) {
$html = file_get_contents( __DIR__.'/../../views/public/applicants/skilled.php' );
}




foreach ($data as $key => $value) {
$html = str_replace( '{'.$key.'}' , $value, $html );
} 

$mpdf = new mPDF(); 

$mpdf->SetDisplayMode('fullpage');
$mpdf->WriteHTML($html);
$mpdf->Output(); 

exit;
}
}
/* End of file applicants.php */
/* Location: ./app/controllers/public/applicants.php */