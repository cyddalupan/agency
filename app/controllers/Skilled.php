<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Skilled extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    public function login()
    {
        include APPPATH . '../skilled/login.php';
    }
}

/* End of file Skilled.php */
/* Location: ./application/controllers/Skilled.php */