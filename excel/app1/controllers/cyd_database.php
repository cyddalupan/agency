<?php
class Cyd_Database extends CI_Controller {


    function __construct()
    {
        parent::__construct();
		$this->load->dbforge();
    }

	public function applicant_salary()
	{
		$fields = array(
                //ids
                'applicant_salary_id' => array(
                    'type' => 'INT',
                    'auto_increment' => TRUE
                ),
                'applicant_id' => array(
                    'type' => 'INT' 
                ),

                //datas
                'period_pay' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                ),
                'basic_salary_pay' => array(
                    'type' => 'DOUBLE',
                ),
                'status_pay' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                ),
                'salary_date' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '10',
                ),

                'sss_deductions' => array(
                    'type' => 'DOUBLE',
                ),
                'phil_health_deductions' => array(
                    'type' => 'DOUBLE',
                ),
                'pag_ibig_deductions' => array(
                    'type' => 'DOUBLE',
                ),

                'hmo_miscellaneous' => array(
                    'type' => 'DOUBLE',
                ),
                'over_time_miscellaneous' => array(
                    'type' => 'DOUBLE',
                ),
                'night_differential_miscellaneous' => array(
                    'type' => 'DOUBLE',
                ),
                'holiday_pay_miscellaneous' => array(
                    'type' => 'DOUBLE',
                ),
                'absent_miscellaneous' => array(
                    'type' => 'DOUBLE',
                ),
                'tardiness_miscellaneous' => array(
                    'type' => 'DOUBLE',
                ),
                'undertime_miscellaneous' => array(
                    'type' => 'DOUBLE',
                ),

                'meal_allowances' => array(
                    'type' => 'DOUBLE',
                ),
                'transportation_allowances' => array(
                    'type' => 'DOUBLE',
                ),
                'cola_allowances' => array(
                    'type' => 'DOUBLE',
                ),
                'other_allowances' => array(
                    'type' => 'DOUBLE',
                ),
                'tax_shielded_allowances' => array(
                    'type' => 'DOUBLE',
                ),

                'total_deductions' => array(
                    'type' => 'DOUBLE',
                ),
                'total_allowances' => array(
                    'type' => 'DOUBLE',
                ),
                'total_misc' => array(
                    'type' => 'DOUBLE',
                ),
                'taxable_income' => array(
                    'type' => 'DOUBLE',
                ),
                'withholding_tax' => array(
                    'type' => 'DOUBLE',
                ),
                'net_income' => array(
                    'type' => 'DOUBLE',
                ),

                'salary_remarks' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '500',
                ),

                'last_salary' => array(
                    'type' => 'DATETIME' 
                ),
                //defaults
                'updated_at' => array(
                    'type' => 'DATETIME' 
                ),
                'created_at' => array(
                    'type' => 'DATETIME' 
                ),
                'deleted_at' => array(
                    'type' => 'DATETIME' 
                ),
        );

		$this->dbforge->add_field($fields); 
		$this->dbforge->add_key('applicant_salary_id', TRUE);
		$this->dbforge->create_table('applicant_salary',TRUE);//[TRUE] = IF NOT EXISTS
        echo 'applicant_salary table added!';
	}

    public function applicant_salary_record()
    {
        $fields = array(
                //ids
                'applicant_salary_record_id' => array(
                    'type' => 'INT',
                    'auto_increment' => TRUE
                ),
                'applicant_id' => array(
                    'type' => 'INT' 
                ),

                //datas
                'period_pay' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                ),
                'basic_salary_pay' => array(
                    'type' => 'DOUBLE',
                ),
                'status_pay' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                ),
                'salary_date' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '10',
                ),

                'sss_deductions' => array(
                    'type' => 'DOUBLE',
                ),
                'phil_health_deductions' => array(
                    'type' => 'DOUBLE',
                ),
                'pag_ibig_deductions' => array(
                    'type' => 'DOUBLE',
                ),

                'hmo_miscellaneous' => array(
                    'type' => 'DOUBLE',
                ),
                'over_time_miscellaneous' => array(
                    'type' => 'DOUBLE',
                ),
                'night_differential_miscellaneous' => array(
                    'type' => 'DOUBLE',
                ),
                'holiday_pay_miscellaneous' => array(
                    'type' => 'DOUBLE',
                ),
                'absent_miscellaneous' => array(
                    'type' => 'DOUBLE',
                ),
                'tardiness_miscellaneous' => array(
                    'type' => 'DOUBLE',
                ),
                'undertime_miscellaneous' => array(
                    'type' => 'DOUBLE',
                ),

                'meal_allowances' => array(
                    'type' => 'DOUBLE',
                ),
                'transportation_allowances' => array(
                    'type' => 'DOUBLE',
                ),
                'cola_allowances' => array(
                    'type' => 'DOUBLE',
                ),
                'other_allowances' => array(
                    'type' => 'DOUBLE',
                ),
                'tax_shielded_allowances' => array(
                    'type' => 'DOUBLE',
                ),

                'total_deductions' => array(
                    'type' => 'DOUBLE',
                ),
                'total_allowances' => array(
                    'type' => 'DOUBLE',
                ),
                'total_misc' => array(
                    'type' => 'DOUBLE',
                ),
                'taxable_income' => array(
                    'type' => 'DOUBLE',
                ),
                'withholding_tax' => array(
                    'type' => 'DOUBLE',
                ),
                'net_income' => array(
                    'type' => 'DOUBLE',
                ),

                'salary_remarks' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '500',
                ),

                //defaults
                'updated_at' => array(
                    'type' => 'DATETIME' 
                ),
                'created_at' => array(
                    'type' => 'DATETIME' 
                ),
                'deleted_at' => array(
                    'type' => 'DATETIME' 
                ),
        );

        $this->dbforge->add_field($fields); 
        $this->dbforge->add_key('applicant_salary_record_id', TRUE);
        $this->dbforge->create_table('applicant_salary_record',TRUE);//[TRUE] = IF NOT EXISTS
        echo 'applicant_salary_record table added!';
    }

    public function options()
    {
        $fields = array(
            //id
            'option_id' => array(
                'type' => 'INT',
                'auto_increment' => TRUE
            ),

            'option_name' => array(
                'type' => 'VARCHAR',
                'constraint' => '160',
            ),
            'option_value' => array(
                'type' => 'VARCHAR',
                'constraint' => '160',
            ),
            'option_remarks' => array(
                'type' => 'VARCHAR',
                'constraint' => '500',
            ),

            //defaults
            'updated_at' => array(
                'type' => 'DATETIME' 
            ),
            'created_at' => array(
                'type' => 'DATETIME' 
            ),
            'deleted_at' => array(
                'type' => 'DATETIME' 
            ),
        );

        //add to db
        $this->dbforge->add_field($fields); 
        $this->dbforge->add_key('option_id', TRUE);
        $this->dbforge->create_table('options',TRUE);//[TRUE] = IF NOT EXISTS

        echo 'options table added! </br>';

        //add value
        //option 1 - tax single
        $data = array(
           'option_name' => 'tax - single',
           'option_value' => '18',
           'option_remarks' => 'This is on Percent',
           'updated_at' => date('Y-m-d H:i:s') ,
           'created_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('options', $data); 
        echo 'tax - single data added! </br>';

        //option 2 - tax married
        $data = array(
           'option_name' => 'tax - married',
           'option_value' => '17',
           'option_remarks' => 'This is on Percent',
           'updated_at' => date('Y-m-d H:i:s') ,
           'created_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('options', $data); 
        echo 'tax - married data added! </br>';

        //option 3 - Single w/ 1 Dependent
        $data = array(
           'option_name' => 'tax - Single w/ 1 Dependent',
           'option_value' => '16',
           'option_remarks' => 'This is on Percent',
           'updated_at' => date('Y-m-d H:i:s') ,
           'created_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('options', $data); 
        echo 'tax - Single w/ 1 Dependent data added! </br>';


        //option 4 - tax Single w/ 2 Dependents
        $data = array(
           'option_name' => 'tax - Single w/ 2 Dependents',
           'option_value' => '15',
           'option_remarks' => 'This is on Percent',
           'updated_at' => date('Y-m-d H:i:s') ,
           'created_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('options', $data); 
        echo 'tax - Single w/ 2 Dependents data added! </br>';


        //option 5 - tax Single w/ 3 Dependents
        $data = array(
           'option_name' => 'tax - Single w/ 3 Dependents',
           'option_value' => '14',
           'option_remarks' => 'This is on Percent',
           'updated_at' => date('Y-m-d H:i:s') ,
           'created_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('options', $data); 
        echo 'tax - Single w/ 3 Dependents data added! </br>';

        //option 5 - tax Single w/ 4 Dependents
        $data = array(
           'option_name' => 'tax - Single w/ 4 Dependents',
           'option_value' => '13',
           'option_remarks' => 'This is on Percent',
           'updated_at' => date('Y-m-d H:i:s') ,
           'created_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('options', $data); 
        echo 'tax - Single w/ 4 Dependents data added! </br>';

        //option 6 - tax Married w/ 1 Dependents
        $data = array(
           'option_name' => 'tax - Married w/ 1 Dependents',
           'option_value' => '15',
           'option_remarks' => 'This is on Percent',
           'updated_at' => date('Y-m-d H:i:s') ,
           'created_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('options', $data); 
        echo 'tax - Married w/ 1 Dependents data added! </br>';

        //option 7 - tax Married w/ 2 Dependents
        $data = array(
           'option_name' => 'tax - Married w/ 2 Dependents',
           'option_value' => '14',
           'option_remarks' => 'This is on Percent',
           'updated_at' => date('Y-m-d H:i:s') ,
           'created_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('options', $data); 
        echo 'tax - Married w/ 2 Dependents data added! </br>';

        //option 8 - tax Married w/ 3 Dependents
        $data = array(
           'option_name' => 'tax - Married w/ 3 Dependents',
           'option_value' => '13',
           'option_remarks' => 'This is on Percent',
           'updated_at' => date('Y-m-d H:i:s') ,
           'created_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('options', $data); 
        echo 'tax - Married w/ 3 Dependents data added! </br>';

        //option 9 - tax Married w/ 4 Dependents
        $data = array(
           'option_name' => 'tax - Married w/ 4 Dependents',
           'option_value' => '12',
           'option_remarks' => 'This is on Percent',
           'updated_at' => date('Y-m-d H:i:s') ,
           'created_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('options', $data); 
        echo 'tax - Married w/ 4 Dependents data added! </br>';

        //option 10 - withholding_tax
        $data = array(
           'option_name' => 'withholding_tax',
           'option_value' => '1',
           'option_remarks' => 'value must be 1 or 2. if 1(withholding tax deduct on first cutoff and other deductions (like sss,pag-ibig,philhealth) on second cutoff)',
           'updated_at' => date('Y-m-d H:i:s') ,
           'created_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('options', $data); 
        echo 'withholding_tax data added! </br>';

        //option 11 - sss Deduction
        $data = array(
           'option_name' => 'sss_deduction',
           'option_value' => '2',
           'option_remarks' => 'Percent of sss deduction',
           'updated_at' => date('Y-m-d H:i:s') ,
           'created_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('options', $data); 
        echo 'sss_deduction data added! </br>';

        //option 12 - pagibig Deduction
        $data = array(
           'option_name' => 'pagibig_deduction',
           'option_value' => '100',
           'option_remarks' => 'deduction of pagibig, in peso',
           'updated_at' => date('Y-m-d H:i:s') ,
           'created_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('options', $data); 
        echo 'pagibig_deduction data added! </br>';

        //option 13 - sss Deduction
        $data = array(
           'option_name' => 'philhealth_deduction',
           'option_value' => '2',
           'option_remarks' => 'Percent of philhealth deduction',
           'updated_at' => date('Y-m-d H:i:s') ,
           'created_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('options', $data); 
        echo 'philhealth_deduction data added! </br>';

    }

    function salary_transactions(){

        $fields = array(
            //id
            'salary_transaction_id' => array(
                'type' => 'INT',
                'auto_increment' => TRUE
            ),

            'applicant_id' => array(
                'type' => 'INT'
            ),
            'payment_type' => array(
                'type' => 'VARCHAR',
                'constraint' => '20',
            ),
            'applicant_fullname' => array(
                'type' => 'VARCHAR',
                'constraint' => '500',
            ),

            //payroll
            'basic_pay' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
            ),
            'total_deduction' => array(
                'type' => 'VARCHAR',
                'constraint' => '500',
            ),
            'total_allowance' => array(
                'type' => 'VARCHAR',
                'constraint' => '500',
            ),
            'total_miscellaneous' => array(
                'type' => 'VARCHAR',
                'constraint' => '500',
            ),
            'tax' => array(
                'type' => 'VARCHAR',
                'constraint' => '500',
            ),
            'net_income' => array(
                'type' => 'VARCHAR',
                'constraint' => '500',
            ),

            'salary_date' => array(
                'type' => 'DATETIME' 
            ),

            //defaults
            'updated_at' => array(
                'type' => 'DATETIME' 
            ),
            'created_at' => array(
                'type' => 'DATETIME' 
            ),
            'deleted_at' => array(
                'type' => 'DATETIME' 
            ),
        );

        //add to db
        $this->dbforge->add_field($fields); 
        $this->dbforge->add_key('salary_transaction_id', TRUE);
        $this->dbforge->create_table('salary_transactions',TRUE);//[TRUE] = IF NOT EXISTS

        echo 'salary_transactions table added! </br>';
    }
}