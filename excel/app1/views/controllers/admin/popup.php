<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Popup extends Admin_Controller {
	
	const PAGE_ACCESS = parent::PAGE_DYNAMIC;
	
	private $segments = [];
	
	public function __construct() 
	{
		parent::__construct();
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );
		
		$this->segments = $this->uri->segment_array();
	}
	
	public function index()
	{
		show_404();
	}

	public function exchange_rate() 
	{
		if ( isset( $_POST['dollar'] ) ) {
			$this->db->flush_cache();
			$updated = 
			$this->db->where([
				'meta_type'   => 'fee',
				'meta_key'    => 'dollar-exchange',
			])->update('meta', [
				'meta_value'  => (float) $_POST['dollar'],
			]);

			echo $updated ? 'success': 'failed';
			exit;
		}

		$this->db->flush_cache();
		$this->db->from( 'meta')
			->where([
				'meta_type'   => 'fee',
				'meta_key'    => 'dollar-exchange',
			]);

		$meta = $this->db->get()->row_array();

		$dollar = 1;

		if ( ! empty( $meta ) ) {
			$dollar = (float) $meta['meta_value'];
		}

		$this->scripts[] = $this->getPath()['scripts'] . 'pages/settings/exchange-rate.js';

		$this->setVariables([
				'dollar' => $dollar,
			])
			->renderPage('settings/exchange-rate', true);
	}
	
}

/* End of file popup.php */
/* Location: ./app/controllers/admin/popup.php */