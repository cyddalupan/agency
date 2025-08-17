<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reset extends Admin_Controller {
	
	const PAGE_ACCESS = parent::PAGE_PUBLIC;
	
	public function __construct() 
	{
		parent::__construct();
	}
	
	public function index()
	{
		$tables = [
			'applicant'                     => null,
			'applicant_certificate'         => null,
			'applicant_education'           => null,
			'applicant_experiences'         => null,
			'applicant_files'               => null,
			'applicant_log'                 => null,
			'applicant_passport'            => null,
			'applicant_preferred_countries' => null,
			'applicant_preferred_positions' => null,
			'applicant_requirement'         => null,
			'applicant_salary'              => null,
			'applicant_salary_record'       => null,
			'applicant_skills'              => null,
			'applicant_visa'                => null,
			'archive_applicant'             => null,
			'bill'                          => null,
			'bill_detail'                   => null,
			'bill_payment_applicant'        => null,
			'bill_payment_employer'         => null,
			'category'                      => null,
			'category_positions'            => null,
			'commission_marketing_agency'   => null,
			'commission_marketing_agent'    => null,
			'commission_recruitment_agent'  => null,
			'contract_marketing_agency'     => null,
			'contract_marketing_agent'      => null,
			'deployed'                      => null,
			'employer'                      => null,
			'employer_reservation'          => null,
			'employer_selected'             => null,
			'job'                           => null,
			'job_fee'                       => null,
			'marketing_agency'              => null,
			'marketing_agent'               => null,
			'or'                            => null,
			'paidall_employer_applicants'   => null,
			'payment_employer_detail'       => null,
			'payment_recruitment'           => null,
			'payment_worker_detail'         => null,
			'position'                      => null,
			'recruitment_agent'             => null,
			'salary_transactions'           => null,
			'user'                          => [
					'options'        => "user_id != 1",
					'auto-increment' => 2,
				],
			'voucher'                       => null,

		];

		$this->db->trans_begin();

		foreach ( $tables as $table => $options ) {

			$query = "DELETE FROM `$table`";

			if ( ! isset( $options['options'] ) ) {
				$query . " WHERE ".$options['options'];
			}

			$this->db->flush_cache();
			$this->db->query( $query );

		}


		$config['hostname'] = "127.0.0.1";
		$config['username'] = "root";
		$config['password'] = "";
		$config['database'] = "information_schema";
		$config['dbdriver'] = "mysqli";
		$config['dbprefix'] = "";
		$config['pconnect'] = TRUE;
		$config['db_debug'] = TRUE;
		$config['cache_on'] = FALSE;
		$config['cachedir'] = "/";
		$config['char_set'] = "utf8";
		$config['dbcollat'] = "utf8_general_ci";

		$dsn = 'mysql://root:@127.0.0.1/information_schema';

		$this->load->database($dsn);

		foreach ( $tables as $table => $options ) {

			$query = "UPDATE `information_schema`.`TABLES` SET `AUTO_INCREMENT` = " . ( isset( $options['auto-increment'] ) ? $options['auto-increment'] : '1' ) . " WHERE `TABLE_NAME` = '$table'";

			$this->db->flush_cache();
			$this->db->query( $query );
		}

		if ( ! $this->db->trans_status() ) {
			$this->db->trans_rollback();
			echo 'faield';
			exit;
		}

		$this->db->trans_commit();
		echo 'success';
		exit;
	}

}

/* End of file reset.php */
/* Location: ./app/controllers/admin/reset.php */