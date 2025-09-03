<?php
// Test the entire skilled worker flow: registration, login, profile view, and logout.

// Include necessary files
require_once __DIR__ . '/../app/core/CodeIgniter.php';

class SkilledFlowTest {

    private $ci;

    public function __construct() {
        $this->ci = &get_instance();
        $this->ci->load->library('session');
        $this->ci->load->database();
    }

    public function run() {
        $this->test_register();
        $this->test_login();
        $this->test_profile();
        $this->test_logout();
    }

    public function test_register() {
        $_POST['firstName'] = 'John';
        $_POST['middleName'] = 'Doe';
        $_POST['lastName'] = 'Smith';
        $_POST['age'] = 30;
        $_POST['contactNumber'] = '1234567890';
        $_POST['email'] = 'john.smith@example.com';
        $_POST['password'] = 'password123';
        $_POST['remarks'] = 'Test user';
        $_POST['workLocation'] = 'USA';
        $_POST['workDetails'] = 'Software Engineer';

        // Capture output
        ob_start();
        $this->ci->load->controller('skilled');
        $skilled = new Skilled();
        $skilled->register();
        ob_end_clean();

        // Check if user was created
        $this->ci->db->where('applicant_email', 'john.smith@example.com');
        $query = $this->ci->db->get('applicant');
        $user = $query->row();

        if ($user) {
            echo "Registration test passed!\n";
        } else {
            echo "Registration test failed!\n";
        }
    }

    public function test_login() {
        $_POST['email'] = 'john.smith@example.com';
        $_POST['password'] = 'password123';

        // Capture output
        ob_start();
        $this->ci->load->controller('skilled');
        $skilled = new Skilled();
        $skilled->login();
        ob_end_clean();

        // Check if session contains user data
        if ($this->ci->session->userdata('user_id')) {
            echo "Login test passed!\n";
        } else {
            echo "Login test failed!\n";
        }
    }

    public function test_profile() {
        // Capture output
        ob_start();
        $this->ci->load->controller('skilled');
        $skilled = new Skilled();
        $skilled->profile();
        $output = ob_get_clean();

        // Check if output contains user's name
        if (strpos($output, 'John Smith') !== false) {
            echo "Profile view test passed!\n";
        } else {
            echo "Profile view test failed!\n";
        }
    }

    public function test_logout() {
        // Capture output
        ob_start();
        $this->ci->load->controller('skilled');
        $skilled = new Skilled();
        $skilled->logout();
        ob_end_clean();

        // Check if session is empty
        if (!$this->ci->session->userdata('user_id')) {
            echo "Logout test passed!\n";
        } else {
            echo "Logout test failed!\n";
        }
    }
}

$test = new SkilledFlowTest();
$test->run();
