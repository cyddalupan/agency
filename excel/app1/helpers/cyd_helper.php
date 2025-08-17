<?php
function notset($value){
    if(isset($value))
        return $value;
}

/**
 * get the difference of 2 date (or datetime)
 * and returns in seconds
 * to convert by minutes devide by 60
 * in hours 3600
 * or use converters below
 */
function cyd_dateDifference($dt1, $dt2) { 
    $y1 = substr($dt1,0,4);
    $m1 = substr($dt1,5,2);
    $d1 = substr($dt1,8,2);
    $h1 = substr($dt1,11,2);
    $i1 = substr($dt1,14,2);
    $s1 = substr($dt1,17,2);    

    $y2 = substr($dt2,0,4);
    $m2 = substr($dt2,5,2);
    $d2 = substr($dt2,8,2);
    $h2 = substr($dt2,11,2);
    $i2 = substr($dt2,14,2);
    $s2 = substr($dt2,17,2);    

    $r1=date('U',mktime($h1,$i1,$s1,$m1,$d1,$y1));
    $r2=date('U',mktime($h2,$i2,$s2,$m2,$d2,$y2));
    return ($r1-$r2);
}

/** 
 * Working Experience Input Hidder
 *
 * check if working experience should show or hide the input
 *
 * @param $settingValue value from settings table
 * @return if css should be hidden
 */
function wrkhde($settingValue){
    if($_SESSION["settings"][$settingValue] == '')
        echo 'style="display:none"';
}

//converts seconds to days
function cyd_seconds_2_days($seconds){
	/*** get the days ***/
    $days = intval(intval($seconds) / (3600*24));
   	return $days;
}

/**
 * converts seconds to words
 * requires
 * cyd_seconds_2_days
 */
function cyd_secondsToWords($seconds)
{
    $ret = "";
    /*** get the days ***/
    if(cyd_seconds_2_days($seconds) > 0)
    {
    	$ret .= cyd_seconds_2_days($seconds).' days';
	}

    /*** get the hours ***/
    $hours = (intval($seconds) / 3600) % 24;
    if($hours > 0)
    {
        $ret .= "$hours hours ";
    }

    /*** get the minutes ***/
    $minutes = (intval($seconds) / 60) % 60;
    if($minutes > 0)
    {
        $ret .= "$minutes minutes ";
    }

    /*** get the seconds ***/
    $seconds = intval($seconds) % 60;
    if ($seconds > 0) {
        $ret .= "$seconds seconds";
    }

    return $ret;
}

function count_pos_by_name($applicants){
    //get count
    $poscount = array();
    foreach ( $applicants as $applicant ){
        if(!isset($poscount[$applicant['position_name']])){
            $poscount[$applicant['position_name']] = 0;
        }
        $poscount[$applicant['position_name']]++;
    }
    return $poscount;
}

/**
 * Get Json File from assets/json folder
 * secure encode and convert to array
 * @return Array
 */
function cydGetJson($json){
    $json_from_url = file_get_contents(site_url()."assets/json/".$json); 
    $json_from_url = utf8_encode($json_from_url); 
    return json_decode($json_from_url);
}

//login to api link
function apiLoginLink(){
    return site_url().'admin/ApiLogin';
}

/**
 * show_customField
 *
 * return a html input from custom field (in array)
 * 
 * @param $customFieldInArray(array) Get the customfield in array
 * @param $location(string) compare with $customFieldInArray
 * @param $applicantId(int) get value of input based on applicant
 * @return(string) html input for custom field
 */
function show_customField($customFieldInArray = array(),$location,$applicantId = 0){

    foreach ($customFieldInArray as $customField) {

        if($customField['location'] == $location){

            $inputValue = '';
            $description = cleanStr($customField['description']);
            $classname = cleanStr($customField['name']);

            if($applicantId != 0){
                $ci =& get_instance();
                $ci->load->database();
                $sql = "SELECT * FROM custom_field_values WHERE customFieldId = ".$customField['id']." AND applicantID = ".$applicantId." ;";
                $query = $ci->db->query($sql);
                if($query->num_rows() > 0)
                {
                   $valueArray = $query->result_array();
                   $inputValue = $valueArray[0]['value'];
                }
            }
        
            echo '
            <div class="col-sm-4 '.$classname.'">
                <div class="form-group">
                    <label>'.$customField['name'].'</label> 
                    <input name="'.$classname.'" type="text" placeholder="'.$description.'" class="form-control" value="'.$inputValue.'" maxlength="255" />
                </div>
            </div>';
        }
    }
}

function get_custom_value($applicantId,$customFieldId){
    $ci =& get_instance();
    $ci->load->database();
    $sql = "SELECT * FROM custom_field_values WHERE customFieldId = ".$customFieldId." AND applicantID = ".$applicantId." ;";
    $query = $ci->db->query($sql);
    if($query->num_rows() > 0)
    {
       $valueArray = $query->result_array();
       $inputValue = $valueArray[0]['value'];
    }
    return $inputValue;
}

function cleanStr($string) {
   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

   return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}