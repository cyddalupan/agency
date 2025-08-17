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
        $applicant_currency = ( new m_applicant )->getCurrencyById( $applicantId );
        $fileWholeBody      = ( new m_applicant )->getApplicantFileByType( $applicantId, 'Whole Body Picture' );
		$skill_cyd      	= ( new m_applicant )->get_skill_cyd( $applicantId);

		$fileOptions['where'][] =   
		"IN `file_type` IN ('Passport)";

        $files              = ( new m_applicant )->getApplicantFiles( $applicantId, $fileOptions );

        $profilePicture = $banner = $wholeBody = $documents = null;

		if ( is_file( DIR_UPLOADS.'applicant'.DIRECTORY_SEPARATOR.$applicant['applicant_photo'] ) ) {
			$profilePicture = base_url().'files/applicant/'.$applicant['applicant_photo'];
		} else {
			$profilePicture = $this->getPath()['images'].'avatars/no-picture.jpg';
		}
		
		//change banner name, Do not change logo.jpg, upload new image with new name.
		    
		   	$banner = $this->getPath()['images'].'logobanner.png';   
		
			
		    
		if ( ! empty( $fileWholeBody ) ) {
			if($applicant['applicant_position_type']!='Skilled') {
				$path_parts = pathinfo(base_url().$fileWholeBody['file_path']);
				if($path_parts['extension'] != 'bmp'){
					$wholeBody = '<div>
					        <div class="gradient" style="width: 99%; margin-bottom: 0pt;border:1px solid black; ">
					        <img src="'.base_url().$fileWholeBody['file_path'].'" style="height:405px; " />
							
					        </div>
						
				    </div>';
				}
			}
		}
	
		$contact=$applicant['applicant_contacts'];
       
		foreach ( $files as $key => $file ) {
			if ( strpos( $file['file_mime'], 'image/' ) !== false ) {

				$path_parts = pathinfo(base_url().$file['file_path']);
				if($path_parts['extension'] != 'bmp'){
					$documents .= 
						'<div class="clearfix"></div>
						<div class="" style="margin-bottom: 12pt; height:100% ">
				    		<p>'.strtoupper( $file['file_type'] ).'</p>
							<div style="border:1px solid #ccc">
						        <div class="gradient" style="margin-bottom: 0pt; ">
						        	<img src="'.base_url().$file['file_path'].'" style="width:80%;" />
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
			'<table cellspacing="0" cellpadding="1">
		    	<tr >
	                <th width="">MBA</th>
	                <th width="">Course</th>
	                <th width="10%">Year</th>
		        </tr>
		        <tr>
					<td>'.$applicant['education_mba'].'</td>
					<td>'.$applicant['education_mba_course'].'</td>
					<td>'.$applicant['education_mba_year'].'</td>					
		        </tr>
		        <tr >
	                <th width="">College</th>
	                <th width="">Skills</th>
	                <th width="10%">Year</th>
		        </tr>
		        <tr>
					<td>'.$applicant['education_college'].'</td>
					<td>'.$applicant['education_college_skills'].'</td>
					<td>'.$applicant['education_college_year'].'</td>					
		        </tr>
		        <tr >
	                <th width="" colspan="2">Others</th>
	                <th width="10%">Year</th>
		        </tr>
		        <tr>
					<td colspan="2">'.$applicant['education_others'].'</td>
					<td>'.$applicant['education_others_year'].'</td>					
		        </tr>
				<tr >
	                <th width="" colspan="2">Highschool</th>
	                <th width="10%">Year</th>
		        </tr>
		        <tr>
					<td colspan="2">'.$applicant['education_highschool'].'</td>
					<td>'.$applicant['education_highschool_year'].'</td>					
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
					<td>'.$experience['experience_company'].'</td>
	            	<td>'.$experience['experience_position'].'</td>
					<td> '.$experience['experience_country'].'</td>
	            	<td>'.$experience['experience_from'].' - '.$experience['experience_to'].'</td>
	            	
	            </tr>'.sprintf("\n");
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
	            	<td color:black;font-weight:bold>'.$experience['experience_from'].' - '.$experience['experience_to'].' (<small>EX-ABROAD</small>)</td>
	            	
	            </tr>'.sprintf("\n");
			}			
		}
	}
	
	
		if($applicant['applicant_position_type']=='Skilled') {
	
		$workExperiences = 
			'<table cellspacing="0" cellpadding="1">
		    	<thead>
		            <tr >
				
		                <th width="24%">Position  </th>
						 <th width="17%">Location</th>
		                <th width="31%">Duration</th>
		               
		            </tr>
		        </thead>
		        <tbody>'.$workExperiences.'</tbody>
		    </table>';
	}
	
	
	if($applicant['applicant_position_type']=='Household') {
	
		$workExperiences = 
			'<table cellspacing="0" cellpadding="1">
		    	<thead>
		            <tr >
		               
		                <th width="24%">Position  الوظيفة</th>
						 <th width="17%">Country  المكان</th>
		                <th width="31%">Duration  من</th>
		               
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
				'applicant_remarks_3'	   =>$applicant_raw[$applicant['applicant_id']]->applicant_remarks_3,
				'applicant_incase_name'	   =>$applicant_raw[$applicant['applicant_id']]->applicant_incase_name,
				'currency'	   =>$applicant_raw[$applicant['applicant_id']]->currency,
				'applicant_remarks1'	   =>$applicant_raw[$applicant['applicant_id']]->applicant_remarks1,
				'applicant_incase_relation'	   =>$applicant_raw[$applicant['applicant_id']]->applicant_incase_relation,
				'applicant_incase_contact'	   =>$applicant_raw[$applicant['applicant_id']]->applicant_incase_contact,
				'applicant_incase_address'	   =>$applicant_raw[$applicant['applicant_id']]->applicant_incase_address,
				'requirement_offer_salary' => $applicant_currency.' '.number_format( (int) $applicant['requirement_offer_salary'], 2),
    			'applicant_name'           => strtoupper( $applicant['applicant_name'] ),
				'fname'           => strtoupper( $applicant['applicant_first'] ),
				'mname'           => strtoupper( $applicant['applicant_middle'] ),
				'lname'           => strtoupper( $applicant['applicant_last'] ),
				'applicant_children'  =>$applicant_raw[$applicant['applicant_id']]->applicant_children,
				'no_of_bro'           	   => $this->emptest($applicant_alphatomo->no_of_bro,'0'),
    			'no_of_sis'           	   => $this->emptest($applicant_alphatomo->no_of_sis,'0'),
    			'applicant_birthdate'      => fdate( 'd M Y', $applicant['applicant_birthdate'], '0000-00-00' ),
    			'passport_issue'           => fdate( 'M. d, Y', $applicant['passport_issue'], '0000-00-00' ),
    			'passport_expiration'      => fdate( 'M. d, Y', $applicant['passport_expiration'], '0000-00-00' ),
				'passport_place'           => $applicant['passport_issue_place'],
	    		'profile_picture'          => $profilePicture,
	    		'applicant_languages'   => empty( $applicant['applicant_languages'] ) ? '&nbsp;' : str_replace( ',', '<br>', $applicant['applicant_languages'] ),
				
				'applicant_other_skills'   => empty( $applicant['applicant_other_skills'] ) ? '&nbsp;' : str_replace( ',', '<br>', $applicant['applicant_other_skills'] ),
	    		'education'                => $education,
	    		'work_experiences'         => $workExperiences,
 	    		'banner'                   => $banner,
    			'whole_body'               => $wholeBody,
    			'documents'                => $documents,
				'contact'                => $contact,
				
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
    	]);
		
		if($applicant['applicant_position_type']=='Household') {
    	$html = file_get_contents( __DIR__.'/../../views/public/applicants/pdf.php' );
		}
		
		if($applicant['applicant_position_type']=='Skilled') {
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