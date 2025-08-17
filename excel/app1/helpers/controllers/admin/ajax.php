<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends Admin_Controller {
	
	const PAGE_ACCESS = parent::PAGE_DYNAMIC;
	
	private $segments = [];
	
	public function __construct() 
	{
		parent::__construct();
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );
		
		$this->segments = $this->uri->segment_array();
		
		if ( count ( $this->segments ) < 4 ) {
			show_404();
		}
		
		if ( ! method_exists( $this, $this->segments[3] ) ) {
			show_404();
		}
	}
	
	public function index()
	{
		show_404();
	}
    
    public function applicants( $method )
    {
        switch ( $method ) {
			case 'update-visibility':
                if ( ! isset( $_POST['ai'], $_POST['scope'], $_POST['visible'] ) ) {
                    echo 'Error: Missing arguments';
                    exit;
                }
                
				$applicantId   = $_POST['ai'];
                $scope         = $_POST['scope'];
                $visible       = $_POST['visible'];
                
                $data = $options = [];

				if ( $scope == 'passport' ) {
                    $conditions = [
                        'passport_applicant' => $applicantId,
                    ];
                    $data = [ 
                        'passport_visible' => $visible ,
                    ];

                    $this->db->flush_cache();
                    $this->db->where( $conditions )
                        ->update( 'applicant_passport', $data );
                     
                     echo 'success';
                     exit;
                }
                
                echo 'Error: Unrecognizable scope';
				exit;
            
            case 'job-offers':
                if ( ! ( isset( $_POST['ai'] ) && is_numeric( $_POST['ai'] ) ) ) {
                    echo 'Error: Missing arguments';
                    exit;
                }
                
                $applicantId = (int) $_POST['ai'];
                $jobs        = [];
            
                $this->db->flush_cache();
                $this->db->from( 'applicant' )
                        ->where([
                            'applicant_id' => $applicantId,
                        ]);
                $applicant = $this->db->get()->row_array();
                
                if ( empty( $applicant ) ) {
                    echo 'Applicant #'.$applicantId.' not found';
                    exit;
                }
                
                if ( $applicant['applicant_employer'] ) {
                    $this->db->flush_cache();
                    $this->db->from( 'job' )
                        ->where([
                           'job_employer' => $applicant['applicant_employer'], 
                        ])
                        ->order_by( 'job_created', 'DESC' );
                    
                    $jobs = $this->db->get()->result_array();
                }
                
                echo json_encode([ 'jobs' => $jobs ]);
                exit;
            case 'statistic':
            	$this->load->model( 'm_applicant' );

            	$counter = ( new m_applicant )->getCounters();

            	echo json_encode([ 'applicants' => $counter ]);
            	exit;

            case 'accounting-status':
            	if ( ! ( isset( $_POST['ai'] ) && is_numeric( $_POST['ai'] ) ) ) {
                    echo 'Error: Missing arguments';
                    exit;
                }
                
                $applicantId = (int) $_POST['ai'];
                $isChecked   = $_POST['is-checked'];

                $this->db->flush_cache();
                $this->db->where([
                		'applicant_id' => $applicantId,
                	])
                	->update( 'applicant', [
                		'applicant_paid' => $isChecked,
                	]);

                echo json_encode([ 'status' => 'success' ]);
            	exit;

			default:
                echo 'Error: Unrecognizable method - '.$method;
				exit;
		}
    }

	public function users( $method )
	{
		switch ( $method ) {
			case 'detail':
				$userId = $this->segments[5];
				
				$this->load->model( 'm_user' );
				$user = $this->m_user->getUserById( $userId );
				
				//Remove password element
				if ( isset( $user['user_password'] ) ) {
					unset( $user['user_password'] );
				}

				echo json_encode([
					'user'	=> $user,
				]);
				exit;
			default:
				break;
		}
		
	}	
	
	public function employers( $method )
	{
		switch ( $method ) {
			case 'detail':
				$employerId = $this->segments[5];
				
				$this->load->model( 'm_employer' );
				$employer = $this->m_employer->getEmployerById( $employerId );
				
				//Remove password element
				if ( isset( $employer['user_password'] ) ) {
					unset( $employer['user_password'] );
				}

				echo json_encode([
					'employer'	=> $employer,
				]);
				exit;
			default:
				break;
		}
		
	}
    
    public function reservation( $method )
	{
		switch ( $method ) {
			case 'detail':
				$reservationId = $this->segments[5];
				
				$this->db->flush_cache();
                $this->db->from( 'employer_reservation' )
                    ->join( 'applicant', 'applicant_id = reservation_applicant' )
                    ->where([
                        'reservation_id' => $reservationId,
                    ]);
                
                $reservation = $this->db->get()->row_array();
                
                echo json_encode([
					'reservation'	=> $reservation,
				]);
				exit;
			default:
				break;
		}
		
	}
	
	public function categories( $method )
	{
		switch ( $method ) {
			case 'get-positions':
				$categoryId = $this->segments[5];
				
				$this->load->model( 'm_category' );
				$positions = $this->m_category->getCategoryPositions( $categoryId );
				
				foreach ( $positions as $key => $position ) {
					$position = [
						'position_id'	=> $position['position_id'],
						'position_name'	=> $position['position_name'],
					];
					
					$positions[ $key ] = $position;
				}
				
				echo json_encode([
					'positions'	=> $positions,
				]);
				exit;
			case 'detail':
				$categoryId = $this->segments[5];
				
				$this->load->model( 'm_category' );
				$category = $this->m_category->getCategoryById( $categoryId );
				
				echo json_encode([
					'category'	=> $category,
				]);
				exit;
			default:
				break;
		}
	}
	
	public function countries( $method )
	{
		switch ( $method ) {
			case 'detail':
				$countryId = $this->segments[5];
				
				$this->load->model( 'm_country' );
				$country = $this->m_country->getCountryById( $countryId );
				
				echo json_encode([
					'country'	=> $country,
				]);
				exit;
			default:
				break;
		}
	}
	
	public function marketing_agency( $method )
	{
		switch ( $method ) {
			case 'detail':
				$agencyId = $this->segments[5];
				
				$this->load->model( 'm_marketing_agency' );
				$agency = $this->m_marketing_agency->getMarketingAgencyById( $agencyId );
				
				echo json_encode([
					'agency'	=> $agency,
				]);
				exit;
			default:
				break;
		}
	}
    
    public function marketing_agent( $method )
	{
		switch ( $method ) {
			case 'detail':
				$agentId = $this->segments[5];
				
				$this->load->model( 'm_marketing_agent' );
				$agent = $this->m_marketing_agent->getMarketingAgentById( $agentId );
				
				echo json_encode([
					'agent'	=> $agent,
				]);
				exit;
			default:
				break;
		}
	}
	
	public function recruitment_agent( $method )
	{
		switch ( $method ) {
			case 'detail':
				$agentId = $this->segments[5];
				
				$this->load->model( 'm_recruitment_agent' );
				$agent = $this->m_recruitment_agent->getRecruitmentAgentById( $agentId );
				
				echo json_encode([
					'agent'	=> $agent,
				]);
				exit;
			default:
				break;
		}
	}

	public function voucher( $method ) 
	{
		switch ( $method ) {
			case 'detail':
				$voucherId = $this->segments[5];
				
				$this->load->model( 'm_voucher' );
				$voucher = ( new m_voucher )->getVoucherById( $voucherId, true );
				
				echo json_encode([
					'voucher'	=> $voucher,
				]);
				exit;
			default:
				break;
		}
	}

	public function commissions( $method )
	{
		switch ($method) {
			case 'approval':
				$flag          = $_POST['action'];
				$transactionId = $_POST['tId'];
				$response = [
					'status'  => 'failed',
					'message' => '',
				];

				if ( ! in_array($flag, ['approve', 'unapprove']) ) {
					$response['message'] = 'Transaction: Undefine action.';
					echo json_encode($response);
					exit;
				}

				$voucherData = [
					'voucher_status'     => $flag == 'approve' ? 1 : 0,
					'voucher_approvedby' => $_SESSION['admin']['user']['user_id'],
				];

				$this->db->flush_cache();
				$updated = 
				$this->db->where([
						'voucher_id' => $transactionId,
					])->update('voucher', $voucherData);

				$response['status'] = $updated ? true : false;
				echo  json_encode($response);
				exit;
			default:
				# code...
				break;
		}
	}

	public function billing( $method )
	{
		switch ($method) {
			case 'approval':
				$flag          = $_POST['action'];
				$transactionId = $_POST['tId'];
				$response = [
					'status'  => 'failed',
					'message' => '',
				];

				if ( ! in_array($flag, ['approve', 'unapprove']) ) {
					$response['message'] = 'Transaction: Undefine action.';
					echo json_encode($response);
					exit;
				}

				$orData = [
					'or_status'     => $flag == 'approve' ? 1 : 0,
					'or_approvedby' => $_SESSION['admin']['user']['user_id'],
				];

				$this->db->flush_cache();
				$updated = 
				$this->db->where([
						'or_id' => $transactionId,
					])->update('or', $orData);

				$response['status'] = $updated ? true : false;
				echo  json_encode($response);
				exit;
			default:
				# code...
				break;
		}
	}
	
}

/* End of file ajax.php */
/* Location: ./app/controllers/admin/ajax.php */