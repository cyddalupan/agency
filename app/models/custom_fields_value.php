<?php
class Custom_Fields_Value extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * Update Value
     *
     * Save Custom Field Inputs to Database
     * 
     * @param applicantId(int) save the input to the applicant
     * @param customFieldsArray(array) list of all custom fields
     * @return (string) success message
     */
    public function update_value( $applicantId, $customFieldsArray){

    	$return = array();
    	
    	foreach ($customFieldsArray as $customField) {
            
    		$classname = str_replace(' ', '', $customField['name']);
    		$input = $this->input->post($classname);

    		if($input != ''){
    			if($this->role_exists($applicantId,$customField['id'])){
    				$this->db->flush_cache();

                    $data = array(
                       'value' => $input
                    );

					$this->db->where('customFieldId', $customField['id']);
					$this->db->where('applicantID', $applicantId);
					$this->db->update('custom_field_values', $data);
					$return[] = 'edited'.$input;
    			}else{
					$this->db->flush_cache();
    				$data = array(
					   'customFieldId' => $customField['id'] ,
					   'applicantID' => $applicantId ,
					   'value' => $input
					);
					$this->db->insert('custom_field_values', $data);
					$return[] = 'inserted';
    			}
    		}
    	}

    	return $return;
    }

    function role_exists($applicantId,$customfieldId)
	{
		$this->db->flush_cache();
	    $this->db->where('applicantID',$applicantId);
	    $this->db->where('customFieldId',$customfieldId);
	    $query = $this->db->get('custom_field_values');
	    if ($query->num_rows() > 0){
	        return true;
	    }
	    else{
	        return false;
	    }
	}
    
}