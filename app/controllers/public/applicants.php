<?php //-->
use \Application\Message as Message;
use \Application\Pagination as Pagination;

require_once __DIR__.'/../../third_party/mpdf60/mpdf.php';

defined('BASEPATH') OR exit('No direct script access allowed');

class Applicants  extends Public_Controller {
	
	public function __construct() 
	{
		parent::__construct();
		
		//make sure page has a settings
		if(!isset($_SESSION["settings"])){
			$this->session->set_flashdata('prev_url', current_url());
			redirect('add_settings');
		}

		$this->load->model( 'm_applicant' );
		$this->output->cache(5);
	}
	
	public function index()
	{
		redirect( 'public/applicants/registration' );
		exit;
	}

	public function pdf( $applicantId )
	{
        redirect("public/resume/applicant/".$applicantId);
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

			//check if applicant already exist
			$this->db->flush_cache();
			$this->db->where('applicant_first', $_POST['applicant']['basic']['first']);
			$this->db->where('applicant_last', $_POST['applicant']['basic']['last']);
			$this->db->where('applicant_birthdate', $_POST['applicant']['basic']['birthdate']);
			$this->db->from('applicant');
			$existApplicant = $this->db->count_all_results();
			if($existApplicant > 0){
				$this->session->set_flashdata('cyd_error_msg', 'Applicant Already Exist!');
				redirect( site_url( 'public/applicants/registration' ) );
			}

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