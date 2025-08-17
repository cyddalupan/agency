<?php 
class Error extends MY_Controller 
{
    public function __construct() 
    {
        parent::__construct(); 
    } 

    public function index() 
    { 
        $this->output->set_status_header('404'); 
        
        $data = [
            'app' => $this,
        ];

        $this->load->view('error/404', $data);//loading in my template 
    } 
} 
?> 