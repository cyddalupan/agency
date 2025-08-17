<?php //-->
use \Application\Message as Message;
use \Application\Pagination as Pagination;

require_once __DIR__.'/../../third_party/mpdf60/mpdf.php';

defined('BASEPATH') OR exit('No direct script access allowed');

class Applicants  extends Public_Controller {
	
	public function __construct() 
	{
		parent::__construct();
		
		$this->load->model( 'm_applicant' );
	}
	
	public function index()
	{
		redirect( 'public/applicants/registration' );
		exit;
	}

	public function pdf( $applicantId )
	{
		if ( empty( $applicantId ) ) { 
            show_404(); 
        }
        
        $applicant          = ( new m_applicant )->getApplicantById( $applicantId );
        $fileWholeBody      = ( new m_applicant )->getApplicantFileByType( $applicantId, 'Whole Body Picture' );

        $fileOptions['where'][] = [
        	'file_type !=' => 'Whole Body Picture',
        ];

        $files              = ( new m_applicant )->getApplicantFiles( $applicantId, $fileOptions );

        $profilePicture = $banner = $wholeBody = $documents = null;

		if ( is_file( DIR_UPLOADS.'applicant'.DIRECTORY_SEPARATOR.$applicant['applicant_photo'] ) ) {
			$profilePicture = base_url().'files/applicant/'.$applicant['applicant_photo'];
		} else {
			$profilePicture = $this->getPath()['images'].'avatars/no-picture.jpg';
		}
		
		$banner = $this->getPath()['images'].'logo.jpg';

		if ( ! empty( $fileWholeBody ) ) {
			$wholeBody = '<div>
			        <div class="gradient" style="width: 100%; margin-bottom: 0pt; ">
			        <img src="'.base_url().$fileWholeBody['file_path'].'" style="height:400px; " />
			        </div>
		    </div>';
		}
		if($applicant['applicant_position_type']!='Skilled') {
		$contact='<tr>
            <th>CONTACT NO:</th>
            <td>'.$applicant['applicant_contacts'].'</td>
        </tr>';
		}

		foreach ( $files as $key => $file ) {
			if ( strpos( $file['file_mime'], 'image/' ) !== false ) {
				$documents .= 
					'<div class="clearfix"></div>
					<div class="" style="margin-bottom: 12pt; width:100% ">
			    		<p>'.strtoupper( $file['file_type'] ).'</p>
						<div style="border:1px solid #ccc">
					        <div class="gradient" style="margin-bottom: 0pt; ">
					        	<img src="'.base_url().$file['file_path'].'" style="" />
					        </div>
					    </div>
			    	</div>';
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

		$workExperiences = 
			'<tr>
            	<td colspan="4">-- No experiences --</td>
            </tr>';

		if ( ! empty( $applicant['experiences'] ) && is_array( $applicant['experiences'] ) ) {

			$workExperiences = '';

			foreach ( $applicant['experiences'] as $experience ) {
				$workExperiences .= 
				'<tr>
	            	<td>'.$experience['experience_company'].'</td>
	            	<td>'.$experience['experience_position'].'</td>
	            	<td>'.$experience['experience_from'].' - '.$experience['experience_to'].'</td>
	            	<td>'.number_format( $experience['experience_salary'], 2 ).'</td>
	            </tr>'.sprintf("\n");
			}			
		}

		$workExperiences = 
			'<table cellspacing="0" cellpadding="1">
		    	<thead>
		            <tr >
		                <th width="28%">Company</th>
		                <th width="24%">Position</th>
		                <th width="31%">Years</th>
		                <th width="17%">Salary</th>
		            </tr>
		        </thead>
		        <tbody>'.$workExperiences.'</tbody>
		    </table>';


    	$data = array_merge(
    		$applicant,
    		$fileWholeBody,
		
    		[
    			'ref_no'                   => $_SESSION["settings"]['client_short'].'-'.str_pad( $applicant['applicant_id'], 7, '0', STR_PAD_LEFT ),
    			'applicant_date_applied'   => fdate( 'd M Y', $applicant['applicant_date_applied'], '0000-00-00' ),
    			'contact'       => $contact,
				'requirement_offer_salary' => 'PHP '.number_format( (int) $applicant['requirement_offer_salary'], 2),
    			'applicant_name'           => strtoupper( $applicant['applicant_name'] ),
    			'applicant_birthdate'      => fdate( 'd M Y', $applicant['applicant_birthdate'], '0000-00-00' ),
    			'passport_issue'           => fdate( 'M. d, Y', $applicant['passport_issue'], '0000-00-00' ),
    			'passport_expiration'      => fdate( 'M. d, Y', $applicant['passport_expiration'], '0000-00-00' ),
	    		'profile_picture'          => $profilePicture,
	    		'applicant_languages'      => empty( $applicant['applicant_languages'] ) ? '&nbsp;' : $applicant['applicant_languages'],
	    		'applicant_other_skills'   => empty( $applicant['applicant_other_skills'] ) ? '&nbsp;' : str_replace( ',', '<br>', $applicant['applicant_other_skills'] ),
	    		'education'                => $education,
	    		'work_experiences'         => $workExperiences,
 	    		'banner'                   => $banner,
    			'whole_body'               => $wholeBody,
    			'documents'                  => $documents,
    	]);

    	$html = file_get_contents( __DIR__.'/../../views/public/applicants/pdf.php' );

    	foreach ($data as $key => $value) {
    		$html = str_replace( '{'.$key.'}' , $value, $html );
    	} 

		$mpdf = new mPDF(); 

		$mpdf->SetDisplayMode('fullpage');
		$mpdf->WriteHTML($html);
		$mpdf->Output(); 

		exit;
	}
 
	public function registration()
	{
		$this->load->model( 'm_position');
		$this->load->model( 'm_country');
        $this->load->model( 'm_recruitment_agent');
		
		//Form Submitted
		if ( isset( $_POST['applicant'], $_POST['applicant']['basic'], $_POST['applicant']['education'] ) ) {

			$_SESSION['post']['public']['applicants/registration'] = $_POST;
		
			self::checkDataAdd();
			
			$applicant = $this->m_applicant->addApplicant();
			
			if ( !empty( $applicant ) ) {
				Message::addModalSuccess('Your records has been successfully added.', 'Thank you!');
                Message::addSuccess('New applicant record has been added successfully.', false, 'Success');
				
				redirect( site_url( 'public/applicants/registration' ) );
				exit;
			}
			
			Message::addWarning('An unknown error has occur. Server not available. Please try again.', 'Oops!');
			redirect( site_url( 'public/applicants/registration' ) );
			exit;
		}
		//endOf: Form Submitted
		
		$categories = $this->m_position->getActivePositionsGroupByCategory();
		$countries  = $this->m_country->getCountries();	
        $agents     = $this->m_recruitment_agent->getRecruitmentAgents();
		
		$post = isset( $_SESSION['post']['public']['applicants/registration'] ) ? $_SESSION['post']['public']['applicants/registration'] : [];

		$this->styles[] = $this->getPath()['styles'] . 'pages/applicants/add.css';
		$this->scripts  = [			
			$this->getPath()['plugins'] . 'select2/select2.js',
			$this->getPath()['plugins'] . 'tagsinput/bootstrap-tagsinput.js',
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/applicants/add.js',
		];

		$this->setVariables([
				'categories' => $categories,
				'countries'  => $countries,
                'agents'     => $agents,
				'post'       => $post,
			])
			->setTitle('Online Applicant Registration')
			->renderPage('applicants/registration', true);
	}
	
	protected static function checkDataAdd()
	{
		$errors 	= [];
		$returnUrl 	= site_url( 'public/applicants/registration' );
		$applicant 	= $_POST['applicant'];
 
		if ( empty( $applicant['preferred-position']  ) ) {
			$errors[] = '* <strong>Preferred position</strong> is required.';
		}
		
		if ( empty( $applicant['preferred-country']  ) ) {
			$errors[] = '* <strong>Preferred country</strong> is required.';
		}

		if ( empty( $applicant['expected-salary']  ) ) {
			$errors[] = '* <strong>Expected salary</strong> is required.';
		}

		if ( empty( $applicant['basic']['first']  ) || empty( $applicant['basic']['last']  ) ) {
			$errors[] = '* <strong>First</strong> and <strong>last name</strong> is required.';
		}

		if ( empty( $applicant['basic']['birthdate']  ) ) {
			$errors[] = '* <strong>Date of birth</strong> is required.';
		} else {

			//Birthdate format: mm-dd-yyyy
			list( $year, $month, $day ) = explode( '-', $applicant['basic']['birthdate'] );

			if ( ! checkdate( $month, $day, $year) ) {
				$errors[] = '* <strong>Date of birth</strong> format is invalid.';
			}
		}
		
		if ( empty( $applicant['date-applied']  ) ) {
			$errors[] = '* <strong>Date applied</strong> is required.';
		} else {
			//Birthdate format: mm-dd-yyyy
			list( $year, $month, $day ) = explode( '-', $applicant['date-applied'] );

			if ( ! checkdate( $month, $day, $year) ) {
				$errors[] = '* <strong>Date applied</strong> format is invalid.';
			}
		}

		if ( empty( $applicant['basic']['address']  ) || empty( $applicant['basic']['address']  ) ) {
			$errors[] = '* <strong>Address</strong> is required.';
		}

		if ( count( $errors ) > 0 ) {
			Message::addWarning('Please check the following requirements:<br><br>' . implode( '<br>', $errors ), false, 'Oops!');

			redirect( $returnUrl );
			exit;
		}		
	}
}

/* End of file applicants.php */
/* Location: ./app/controllers/public/applicants.php */