<?php //-->
/*
 * This file is part a custom application package.
 * (c) 2014 Clemente Quiè´–ones Jr. <clemquinones@gmail.com>
 */

/**
 * Core Knowledge of all pages
 *
 * @author     Clemente Quiè´–ones Jr. <clemquinones@gmail.com>
 * @version    1.0.0
 */
 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class m_job extends MY_Model {
 	/* Constants
	-------------------------------*/
	/* Public Properties
	-------------------------------*/
	/* Protected Properties
	-------------------------------*/
	/* Private Properties
	-------------------------------*/
	/* Get
	-------------------------------*/
	/* Magic
	-------------------------------*/ 
	public function __construct() 
	{
		parent::__construct(); 
	}
	
	/* Public Methods
	-------------------------------*/
	/* Protected Methods
	-------------------------------*/
	public function getJobById( $jobId )
	{	
		//Get Job Offer Info
		$this->db->flush_cache();
		$this->db->from('job')
            ->join( 'employer', 'employer_id = job_employer' )
            ->join( 'position', 'position_id = job_position' )
			->where([
				'job_id'	=> $jobId,
			]);
			
		$job = $this->db->get()->row_array();
        
        $job['fees'] = $this->getJobFees( $jobId );
        
		return $job;
	}
	
	public function getJobs( $options = [], $limit = 0, $offset = 0, $sort = ['job_created', 'DESC'] )
	{
		
		//Get Get Job Offer Info
		$this->db->flush_cache();        
		$this->db->from('job')
            ->join( 'employer', 'employer_id = job_employer', 'inner' )
            ->join( 'position', 'position_id = job_position', 'inner' );
		
		$this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset )
			->setDBQueryOrders( $sort );
			
		$jobs = $this->db->get()->result_array();

        return $this->indexArray( $jobs, 'job_id' );
	}
	
	public function getJobsCount( $options = [] )
	{
		//Get Applicant Info
		$this->db->flush_cache();
		$this->db->from('job');
		
		$this->setDBQueryOptions( $options );
		
		$jobs = $this->db->count_all_results(); 
		
		return $jobs;
	}

    public function getJobCounter( $jobId )
    {
        //Get applicant statuses
        $this->load->model( 'm_applicant' );
        $status = ( new m_applicant )->status;

        $this->db->flush_cache();
        $this->db->select('
            (SELECT COUNT(*) FROM `applicant` WHERE `applicant_job` = `job_id` 
                AND `applicant_status` = '.$status['Deployed'].') AS `deployed`
            ')
            ->from( 'job' )
            ->where([
                'job_id' => $jobId,
            ]);

        return $this->db->get()->row_array();
    }
    
    public function getJobFees( $jobId )
    {
        $this->db->flush_cache();
        $this->db->from( 'job_fee j' )
            ->join( 'fee f', 'f.fee_id = j.fee_fee' )
            ->where([
                'j.fee_job' => $jobId,
            ])
            ->order_by( 'f.fee_id', 'ASC' );
        
        $fees = $this->db->get()->result_array();
        
        return $this->indexArray( $fees, 'fee_fee' );
    }
    
    public function addJob()
	{
		$job = $_POST['job'];
		
		$jobData    =
        $jobFeeData = [];
        
        $totalRevenue = 0;

		//Start Transaction
		$this->db->trans_begin();

        $this->load->model( 'm_fee' );
        $dollar = ( new m_fee )->getDollarExchange();
		
		//Insert Job
		$jobData = [
            'job_employer'        => $job['employer'], 
            'job_position'        => $job['position'],
            'job_gender'          => $job['gender'],
            //'job_salary'       => (float) $job['salary'],
            'job_salary_from'     => (float) $job['salary-from'],
            'job_salary_to'       => (float) $job['salary-to'],
            'job_total'           => $job['total'] <= 0 ? 1 : $job['total'],
            'job_occupied'        => 0,
            'job_name'            => $job['name'],
            'job_content'         => $job['content'],
            'job_dollar_exchange' => (float) $dollar,
            'job_revenue'         => 0,
            'job_status'          => 1, //Active
            'job_remarks'         => $job['remarks'],
            'job_createdby'       => $_SESSION['admin']['user']['user_id'],
            'job_updatedby'       => $_SESSION['admin']['user']['user_id'],
            'job_created'         => date ('Y-m-d H:i:s', time() ),
            'job_updated'         => date ('Y-m-d H:i:s', time() ),
		];
		
        $this->db->flush_cache();
		$jobInserted	= $this->db->insert( 'job', $jobData );
		$jobId 			= $this->db->insert_id();
        
        foreach ( $job['fee'] as $feeId => $item ) {
           
            //Placement fee
            if ( $feeId == 1 ) {
                $totalRevenue += ( (float) $item['amount']['E'] + (float) $item['amount']['W'] );
                 
                $jobFeeData[] = [
                    'fee_job'            => $jobId,
                    'fee_fee'            => $feeId,
                    'fee_amount'         => (float) $item['amount']['E'] + (float) $item['amount']['W'],
                    'fee_employer'       => isset( $item['E'] ),
                    'fee_applicant'      => isset( $item['W'] ),
                    'fee_employer_cost'  => (float) $item['amount']['E'],
                    'fee_applicant_cost' => (float) $item['amount']['W'],
                    'fee_createdby'      => $_SESSION['admin']['user']['user_id'],
                    'fee_updatedby'      => $_SESSION['admin']['user']['user_id'],
                    'fee_created'        => date ('Y-m-d H:i:s', time() ),
                    'fee_updated'        => date ('Y-m-d H:i:s', time() ), 
                ]; 
                 
                continue;  
            }

            //Service Fee
            if ( $feeId == 2 ) {
                $totalRevenue += (float) $item['amount'] * $dollar;
                 
                $jobFeeData[] = [
                    'fee_job'            => $jobId,
                    'fee_fee'            => $feeId,
                    'fee_amount'         => (float) $item['amount'] * $dollar,
                    'fee_employer'       => $item['billto'] == 'E',
                    'fee_applicant'      => $item['billto'] == 'W',
                    'fee_employer_cost'  => $item['billto'] == 'E' ? (float) $item['amount'] * $dollar : 0,
                    'fee_applicant_cost' => $item['billto'] == 'W' ? (float) $item['amount'] * $dollar : 0,
                    'fee_createdby'      => $_SESSION['admin']['user']['user_id'],
                    'fee_updatedby'      => $_SESSION['admin']['user']['user_id'],
                    'fee_created'        => date ('Y-m-d H:i:s', time() ),
                    'fee_updated'        => date ('Y-m-d H:i:s', time() ), 
                ]; 
                 
                continue;  
            }
            
            $totalRevenue += (float) $item['amount'];
            
            $jobFeeData[] = [
                'fee_job'             => $jobId,
                'fee_fee'             => $feeId,
                'fee_amount'          => (float) $item['amount'],
                'fee_employer'        => $item['billto'] == 'E',
                'fee_applicant'       => $item['billto'] == 'W',
                'fee_employer_cost'   => $item['billto'] == 'E' ? (float) $item['amount'] : 0,
                'fee_applicant_cost'  => $item['billto'] == 'W' ? (float) $item['amount'] : 0,
                'fee_createdby'       => $_SESSION['admin']['user']['user_id'],
                'fee_updatedby'       => $_SESSION['admin']['user']['user_id'],
                'fee_created'         => date ('Y-m-d H:i:s', time() ),
                'fee_updated'         => date ('Y-m-d H:i:s', time() ), 
            ]; 
        }
       
        if ( count( $jobFeeData ) > 0 ) {
            $this->db->flush_cache();
            $this->db->insert_batch( 'job_fee', $jobFeeData );
        }
        
        //Update job total revenue    
        $this->db->flush_cache();
        $this->db->where([
                'job_id' => $jobId,                
            ])->update( 'job', [ 'job_revenue' => $totalRevenue ] );
		
		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $jobInserted) {
			$this->db->trans_rollback();
			return false;
		} 
		
		$this->endProcess();
		
		//Commit transaction
		$this->db->trans_commit();	
		
		$job = $this->getJobById( $jobId );
		
		return $job;
	}
    
    public function updateJob( $jobId )
	{
		$job = $_POST['job'];
		
		$jobData          =
        $jobFeeDataInsert =
        $jobFeeData       = [];
        
        $totalRevenue = 0;

		//Start Transaction
		$this->db->trans_begin();

        $this->load->model( 'm_fee' );
        $dollar = ( new m_fee )->getDollarExchange();

		//Insert Job
		$jobData = [
            'job_employer'        => $job['employer'],
            'job_position'        => $job['position'],
            'job_gender'          => $job['gender'],
            'job_salary_from'     => (float) $job['salary-from'],
            'job_salary_to'       => (float) $job['salary-to'],
            'job_total'           => $job['total'],
            'job_name'            => $job['name'],
            'job_content'         => $job['content'],
            'job_dollar_exchange' => $dollar,
            'job_remarks'         => $job['remarks'],
            'job_updatedby'       => $_SESSION['admin']['user']['user_id'],
            'job_updated'         => date ('Y-m-d H:i:s', time() ),
		];
		
		$jobUpdated	= $this->db->where([
                'job_id' => $jobId,
            ])->update( 'job', $jobData );

        foreach ( $job['fee'] as $feeId => $item ) {

            $this->db->flush_cache();
            $this->db->from( 'job_fee' )
                ->where([
                    'fee_job' => $jobId,
                    'fee_fee' => $feeId,
                ]);
            
            $exists = $this->db->count_all_results();

            if ( $exists ) {
                if ( $feeId == 1 ) {
                    $totalRevenue += ( (float) $item['amount']['E'] + (float) $item['amount']['W'] );
                
                    $jobFeeData = [
                        'fee_amount'    => (float) $item['amount']['E'] + (float) $item['amount']['W'],
                        'fee_employer'  => isset( $item['E'] ),
                        'fee_applicant' => isset( $item['W'] ),
                        'fee_updatedby' => $_SESSION['admin']['user']['user_id'],
                        'fee_updated'   => date ('Y-m-d H:i:s', time() ), 
                    ];  
                    
                    $this->db->flush_cache();
                    $this->db->where([
                            'fee_job' => $jobId,
                            'fee_fee' => $feeId,
                        ])->update( 'job_fee', $jobFeeData );

                    continue; 
                }

                if ( $feeId == 2 ) {

                    $totalRevenue += (float) $item['amount'] * $dollar;

                    $jobFeeData = [                
                        'fee_amount'          => (float) $item['amount'] * $dollar,
                        'fee_employer'        => $item['billto'] == 'E',
                        'fee_applicant'       => $item['billto'] == 'W',
                        'fee_employer_cost'   => $item['billto'] == 'E' ? (float) $item['amount'] * $dollar : 0,
                        'fee_applicant_cost'  => $item['billto'] == 'W' ? (float) $item['amount'] * $dollar : 0,
                        'fee_updatedby'       => $_SESSION['admin']['user']['user_id'],
                        'fee_updated'         => date ('Y-m-d H:i:s', time() ), 
                    ]; 
                    
                    $this->db->flush_cache();
                    $this->db->where([
                            'fee_job' => $jobId,
                            'fee_fee' => $feeId,
                        ])->update( 'job_fee', $jobFeeData );
                    
                    continue;
                }

                $totalRevenue += (float) $item['amount'];

                $jobFeeData = [                
                    'fee_amount'          => (float) $item['amount'],
                    'fee_employer'        => $item['billto'] == 'E',
                    'fee_applicant'       => $item['billto'] == 'W',
                    'fee_employer_cost'   => $item['billto'] == 'E' ? (float) $item['amount'] : 0,
                    'fee_applicant_cost'  => $item['billto'] == 'W' ? (float) $item['amount'] : 0,
                    'fee_updatedby'       => $_SESSION['admin']['user']['user_id'],
                    'fee_updated'         => date ('Y-m-d H:i:s', time() ), 
                ]; 
                
                $this->db->flush_cache();
                $this->db->where([
                        'fee_job' => $jobId,
                        'fee_fee' => $feeId,
                    ])->update( 'job_fee', $jobFeeData );

                continue;
            } else {
                if ( $feeId == 1 ) {
                    $totalRevenue += ( (float) $item['amount']['E'] + (float) $item['amount']['W'] );
                
                    $jobFeeDataInsert[] = [
                        'fee_job'            => $jobId,
                        'fee_fee'            => $feeId,
                        'fee_amount'         => (float) $item['amount']['E'] + (float) $item['amount']['W'],
                        'fee_employer'       => isset( $item['E'] ),
                        'fee_applicant'      => isset( $item['W'] ),
                        'fee_employer_cost'  => (float) $item['amount']['E'],
                        'fee_applicant_cost' => (float) $item['amount']['W'],
                        'fee_createdby'      => $_SESSION['admin']['user']['user_id'],
                        'fee_updatedby'      => $_SESSION['admin']['user']['user_id'],
                        'fee_created'        => date ('Y-m-d H:i:s', time() ),
                        'fee_updated'        => date ('Y-m-d H:i:s', time() ), 
                    ];

                    continue;
                } 

                if ( $feeId == 2 ) {
                    $totalRevenue += (float) $item['amount'] * $dollar;
                     
                    $jobFeeData[] = [
                        'fee_job'            => $jobId,
                        'fee_fee'            => $feeId,
                        'fee_amount'         => (float) $item['amount'] * $dollar,
                        'fee_employer'       => $item['billto'] == 'E',
                        'fee_applicant'      => $item['billto'] == 'W',
                        'fee_employer_cost'  => $item['billto'] == 'E' ? (float) $item['amount'] * $dollar : 0,
                        'fee_applicant_cost' => $item['billto'] == 'W' ? (float) $item['amount'] * $dollar : 0,
                        'fee_createdby'      => $_SESSION['admin']['user']['user_id'],
                        'fee_updatedby'      => $_SESSION['admin']['user']['user_id'],
                        'fee_created'        => date ('Y-m-d H:i:s', time() ),
                        'fee_updated'        => date ('Y-m-d H:i:s', time() ), 
                    ]; 
                     
                    continue;  
                }

                $totalRevenue += (float) $item['amount'];

                $jobFeeDataInsert[] = [
                    'fee_job'             => $jobId,
                    'fee_fee'             => $feeId,
                    'fee_amount'          => (float) $item['amount'],
                    'fee_employer'        => $item['billto'] == 'E',
                    'fee_applicant'       => $item['billto'] == 'W',
                    'fee_employer_cost'   => $item['billto'] == 'E' ? (float) $item['amount'] : 0,
                    'fee_applicant_cost'  => $item['billto'] == 'W' ? (float) $item['amount'] : 0,
                    'fee_createdby'       => $_SESSION['admin']['user']['user_id'],
                    'fee_updatedby'       => $_SESSION['admin']['user']['user_id'],
                    'fee_created'         => date ('Y-m-d H:i:s', time() ),
                    'fee_updated'         => date ('Y-m-d H:i:s', time() ), 
                ]; 
                
                continue;
            }

            /*            
            if ( ! $exists && $feeId == 1 && ! isset( $item['billto'] ) ) {
                
                $totalRevenue += ( (float) $item['amount']['E'] + (float) $item['amount']['W'] );
                
                $jobFeeDataInsert[] = [
                    'fee_job'            => $jobId,
                    'fee_fee'            => $feeId,
                    'fee_amount'         => (float) $item['amount']['E'] + (float) $item['amount']['W'],
                    'fee_employer'       => isset( $item['E'] ),
                    'fee_applicant'      => isset( $item['W'] ),
                    'fee_employer_cost'  => (float) $item['amount']['E'],
                    'fee_applicant_cost' => (float) $item['amount']['W'],
                    'fee_createdby'      => $_SESSION['admin']['user']['user_id'],
                    'fee_updatedby'      => $_SESSION['admin']['user']['user_id'],
                    'fee_created'        => date ('Y-m-d H:i:s', time() ),
                    'fee_updated'        => date ('Y-m-d H:i:s', time() ), 
                ];
                
                continue; 
            }

            if ( $exists && $feeId == 1 && ! isset( $item['billto'] ) ) {
                
                $totalRevenue += ( (float) $item['amount']['E'] + (float) $item['amount']['W'] );
                
                $jobFeeData = [
                    'fee_amount'    => (float) $item['amount']['E'] + (float) $item['amount']['W'],
                    'fee_employer'  => isset( $item['E'] ),
                    'fee_applicant' => isset( $item['W'] ),
                    'fee_updatedby' => $_SESSION['admin']['user']['user_id'],
                    'fee_updated'   => date ('Y-m-d H:i:s', time() ), 
                ];  
                
                $this->db->flush_cache();
                $this->db->where([
                        'fee_job' => $jobId,
                        'fee_fee' => $feeId,
                    ])->update( 'job_fee', $jobFeeData );

                continue; 
            }
            
            $totalRevenue += (float) $item['amount'];
             
            if ( ! $exists ) { 
                
                $jobFeeDataInsert[] = [
                    'fee_job'             => $jobId,
                    'fee_fee'             => $feeId,
                    'fee_amount'          => (float) $item['amount'],
                    'fee_employer'        => $item['billto'] == 'E',
                    'fee_applicant'       => $item['billto'] == 'W',
                    'fee_employer_cost'   => $item['billto'] == 'E' ? (float) $item['amount'] : 0,
                    'fee_applicant_cost'  => $item['billto'] == 'W' ? (float) $item['amount'] : 0,
                    'fee_createdby'       => $_SESSION['admin']['user']['user_id'],
                    'fee_updatedby'       => $_SESSION['admin']['user']['user_id'],
                    'fee_created'         => date ('Y-m-d H:i:s', time() ),
                    'fee_updated'         => date ('Y-m-d H:i:s', time() ), 
                ]; 
                
                continue;
            }
            
            $jobFeeData = [                
                'fee_amount'          => (float) $item['amount'],
                'fee_employer'        => $item['billto'] == 'E',
                'fee_applicant'       => $item['billto'] == 'W',
                'fee_employer_cost'   => $item['billto'] == 'E' ? (float) $item['amount'] : 0,
                'fee_applicant_cost'  => $item['billto'] == 'W' ? (float) $item['amount'] : 0,
                'fee_updatedby'       => $_SESSION['admin']['user']['user_id'],
                'fee_updated'         => date ('Y-m-d H:i:s', time() ), 
            ]; 
            
            $this->db->flush_cache();
            $this->db->where([
                    'fee_job' => $jobId,
                    'fee_fee' => $feeId,
                ])->update( 'job_fee', $jobFeeData );
            */
        }
        
        if ( count( $jobFeeDataInsert ) > 0 ) {
            $this->db->insert_batch( 'job_fee', $jobFeeDataInsert );
        }
        
         //Update job total revenue    
        $this->db->where([
                'job_id' => $jobId,                
            ])->update( 'job', [ 'job_revenue' => $totalRevenue ] );
		
		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $jobUpdated) {
			$this->db->trans_rollback();
			return false;
		} 
		
		$this->endProcess();
		
		//Commit transaction
		$this->db->trans_commit();	
		
		$job = $this->getJobById( $jobId );

		return $job;
	}
    public function archive( $jobId )
    {
        $jobData = [
            'job_status' => 0,
        ];

        $this->db->flush_cache();
        $this->db->where([
                'job_id' => $jobId,
            ])
            ->update( 'job', $jobData );

        $this->db->flush_cache();
        $this->db->from( 'job' )
            ->where([
                'job_id' => $jobId,
            ]);

        $job = $this->db->get()->row_array();

        return ! empty( $job );
    }
    
	/* Protected Methods
	-------------------------------*/
	protected function endProcess()
	{
		if (isset($_SESSION['post']['admin']['jobs/all'])) {
			unset($_SESSION['post']['admin']['jobs/all']);
		}
		
		return $this;		
	}
	/* Private Methods
	-------------------------------*/
}
