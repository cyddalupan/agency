<?php //--&gt;
use \Application\Message as Message;

/*
* This file is part a custom application package.
* (c) 2014 Clemente Quiones Jr. &lt;clemquinones@gmail.com&gt;
* (c) 2015 Cyd Dalupan &lt;cydmdalupan@gmail.com&gt;
*/

/**
* Core Knowledge of all pages
*
* @author     Clemente Quiones Jr. &lt;clemquinones@gmail.com&gt;
* @author     Cyd Dalupan &lt;cydmdalupan@gmail.com&gt;
* @version    1.0.0
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class m_skilled_applicant extends MY_Model {
/* Constants
-------------------------------*/
const RESERVED_DAYS_EXPIRATION = 7; //7-days, update also on the MySQL trigger

/* Public Properties
-------------------------------*/    
/* Protected Properties
-------------------------------*/

public $status = [
'New'     =&gt; 10,
'For Interview'  =&gt; 11,
'Passporting'    =&gt; 15,
'Pending Medical'  =&gt; 7, 
'For QVC'      =&gt; 6, 
'Available'      =&gt; 0,
'Line Up'        =&gt; 5, //interview
'Reserved'       =&gt; 2,
'Pre-Selected'   =&gt; 3,
'Selected'       =&gt; 4,
'For Booking International'    =&gt; 12,      
 'For Deployment' =&gt; 8,
'Deployed'       =&gt; 9,
'For QVC Appt'       =&gt; 10,
//'Failed Interview'    =&gt; 20,
'For Medical'    =&gt; 13,
'Unfit'    =&gt; 21,
'Repat'    =&gt; 22,
'Cancelled'      =&gt; 1,
'A to A'      =&gt; 14,
'Backout'      =&gt; 25,
'Transmittal'      =&gt; 26,
'For Contract Signing'      =&gt; 27,
'For OWWA'      =&gt; 28,
'Owwa Reschedule'      =&gt; 29,
'FOR OEC'      =&gt; 30,
'OEC Released'      =&gt; 31,
'Visa Stamped'      =&gt; 32,
'for OEC'      =&gt; 33,
'Visa Released'      =&gt; 35,
'Re Booking'      =&gt; 36,
'Re Booking'      =&gt; 36,
'For Visa Stamping'      =&gt; 37,
'For PDOS'      =&gt; 38,
'For Line Up'      =&gt; 39,
'For Biometric Appointment'      =&gt; 40,
'For Biometric'      =&gt; 41,
'For Tesda'      =&gt; 42,
'WAITING VISA (UAE)'      =&gt; 43,
'HOLD'      =&gt; 43,
/* id 16 to 19 is used on  training status */
];

public $statusText = [
10 =&gt; 'New',
15 =&gt; 'Passporting',
7 =&gt; 'Pending Medical',
0 =&gt; 'Available',
5 =&gt; 'Line Up',
2 =&gt; 'Reserved',
//3 =&gt; 'Pre-Selected',
4 =&gt; 'Selected',
6 =&gt; 'For QVC',
12 =&gt; 'For Booking International',
8 =&gt; 'For Deployment',
9 =&gt; 'Deployed',
10 =&gt; 'For QVC Appt',  
11 =&gt; 'For Interview',
13 =&gt; 'For Medical',
//16 =&gt; 'Enrolled to training',
//17 =&gt; 'Started training',
//18 =&gt; 'Failed Training',
//19 =&gt; 'Graduate Training',
//20 =&gt; 'Failed Interview',
21 =&gt; 'Unfit',
22 =&gt; 'Repat',
1 =&gt; 'Cancelled',
14 =&gt; 'A to A',
25 =&gt; 'Backout',
26 =&gt; 'Transmittal', 
27 =&gt; 'For Contract Signing', 
28 =&gt; 'For OWWA', 
29 =&gt; 'Owwa Reschedule', 
30 =&gt; 'OEC Filed', 
31 =&gt; 'OEC Released',   
32 =&gt; 'Visa Stamped', 
35 =&gt; 'Visa Received', 
33 =&gt; 'FOR OEC	',  
36 =&gt; 'Re Booking', 
37 =&gt; 'For Visa Stamping',  
38 =&gt; 'For PDOS',
39 =&gt; 'For Line Up',  
40 =&gt; 'For Biometric Appointment', 
41 =&gt; 'For Biometric', 
42 =&gt; 'For Tesda',  
43 =&gt; 'WAITING VISA (UAE)', 
44 =&gt; 'HOLD', 
];

public $statusColors = [
0 =&gt; 'success',
1 =&gt; 'danger',
2 =&gt; 'primary',
3 =&gt; 'default',
4 =&gt; 'info',
5 =&gt; 'danger',
6 =&gt; 'primary',
7 =&gt; 'danger',
8 =&gt; 'warning',
9 =&gt; 'success',    
10 =&gt; 'info',     
11 =&gt; 'primary',     
12 =&gt; 'warning',
//13 =&gt; 'info',
14 =&gt; 'default',
15 =&gt; 'danger',	
//16 =&gt; 'primary',
//17 =&gt; 'default',
//18 =&gt; 'success',
//19 =&gt; 'info',
//20 =&gt; 'danger',
21 =&gt; 'danger',
22 =&gt; 'default',
25 =&gt; 'danger',
26 =&gt; 'default',
27 =&gt; 'primary',
28 =&gt; 'default',
29 =&gt; 'default',
30 =&gt; 'default',
31 =&gt; 'warning',
32 =&gt; 'warning',
33 =&gt; 'default',
35 =&gt; 'warning',
36 =&gt; 'warning',
37 =&gt; 'warning',
38 =&gt; 'warning',
39 =&gt; 'info',
40 =&gt; 'warning',
41 =&gt; 'warning',
42 =&gt; 'info',
43 =&gt; 'warning',
44 =&gt; 'warning',
];




public $fileTypes = [   
'Whole Body Picture' =&gt; 'Whole Body Picture',
'Resume'             =&gt; 'Resume/CV',
'Passport'           =&gt; 'Passport',
'Branch 2x2'           =&gt; 'Branch 2x2',
'Branch Whole Body Picture'           =&gt; 'Branch Whole Body Picture',
'Branch Pre Med Result'           =&gt; 'Branch Pre Med Result',
'Tattoo'           =&gt; 'Tattoo',
'Visa'               =&gt; 'Visa',
'OEC'               =&gt; 'OEC',
'NBI'               =&gt; 'NBI',
'OWWA'               =&gt; 'OWWA',
'VISA STAMPED'        =&gt; 'VISA STAMPED',
'PDOS'               =&gt; 'PDOS',
'TESDA'               =&gt; 'TESDA',
'OMA'               =&gt; 'OMA',
'CONTRACT'               =&gt; 'CONTRACT',
'MEDICAL'               =&gt; 'MEDICAL',
'INSURANCE'               =&gt; 'INSURANCE',
'TRADE TEST'               =&gt; 'TRADE TEST',
'INFOSHEET'               =&gt; 'INFOSHEET',
'TICKET'               =&gt; 'TICKET',
'MOFA'               =&gt; 'MOFA',
'E-Reg'               =&gt; 'E-Reg',
'PEOS'               =&gt; 'PEOS',
'VACCINE CERTIFICATE'               =&gt; 'VACCINE CERTIFICATE',
'QVC Appointment'               =&gt; 'QVC Appointment',
'EPP'               =&gt; 'EPP',
'BOQ Yellow Card'               =&gt; 'BOQ Yellow Card',
'TATTOO 1'               =&gt; 'TATTOO 1',
'TATTOO 2'               =&gt; 'TATTOO 2',
'TATTOO 3'               =&gt; 'TATTOO 3',
'Doc 1'              =&gt; 'Docs 1',
'Doc 2'              =&gt; 'Docs 2',
'Doc 3'              =&gt; 'Docs 3',
'Doc 4'              =&gt; 'Docs 4',
'Doc 5'              =&gt; 'Docs 5',
'Doc 6'              =&gt; 'Docs 6',
'Doc 7'              =&gt; 'Docs 7',
'Doc 8'              =&gt; 'Docs 8',
'Other'              =&gt; 'Other',
'Agency Files 1'      =&gt; 'Agency Files 1',
'Agency Files 2'      =&gt; 'Agency Files 2',
'Agency Files 3'      =&gt; 'Agency Files 3',
'Agency Files 4'      =&gt; 'Agency Files 4',
'Agency Files 5'      =&gt; 'Agency Files 5',
'Agency Files 3'      =&gt; 'Agency Files 6'

];

/* Private Properties
-------------------------------*/
/* Get
-------------------------------*/
/* Magic
-------------------------------*/ 
public function __construct() 
{

if($_SESSION["settings"]['withTraining'] == 'yes'){
$status['Enrolled to training']	= 16;
$status['Started training']		= 17;
$status['Failed Training']		= 18;
$status['Graduate Training']	= 19;
}

parent::__construct(); 

}

/* Public Methods
-------------------------------*/
/* Protected Methods
-------------------------------*/
public function searchApplicants()
{
$search = $_GET['search'];

$all_apid_sub_pos = '';
if (isset($search['position']) &&  $search['position'] &gt; 0 ) {
//get Subposition
$this->db-&gt;flush_cache();
$query = $this-&gt;db-&gt;get_where('applicant_preferred_positions', array('position_position' =&gt;  $search['position']));
$result = $query-&gt;result();
foreach ($result as $positions_value) {
$all_apid_sub_pos[] =$positions_value-&gt;position_applicant.'"';
}
}

if ( ! empty( $search['q'] ) ) {

//requirement_oec_number search
$this-&gt;db-&gt;flush_cache();
$query = $this-&gt;db-&gt;get_where('applicant_requirement', array('requirement_oec_number' =&gt; $search['q']), 1);
$result = $query-&gt;result();
if(isset($result[0]-&gt;requirement_applicant))
$requirement_id = $result[0]-&gt;requirement_applicant;
else
$requirement_id = 0;

//insurance_no search
$this-&gt;db-&gt;flush_cache();
$query = $this-&gt;db-&gt;get_where('applicant_certificate', array('insurance_no' =&gt; $search['q']), 1);
$result = $query-&gt;result();
if(isset($result[0]-&gt;certificate_applicant))
$certificate_id = $result[0]-&gt;certificate_applicant;
else
$certificate_id = 0;

//ticket_no search
$this-&gt;db-&gt;flush_cache();
$query = $this-&gt;db-&gt;get_where('applicant_requirement', array('ticket_no	' =&gt; $search['q']), 1);
$result = $query-&gt;result();
if(isset($result[0]-&gt;requirement_applicant))
$ticket_no_id = $result[0]-&gt;requirement_applicant;
else
$ticket_no_id = 0;

//ticket_no search
$this-&gt;db-&gt;flush_cache();
$query = $this-&gt;db-&gt;get_where('applicant', array('applicantNumber' =&gt; $search['q']), 1);
$result = $query-&gt;result();
if(isset($result[0]-&gt;applicant_id))
$applicantNumber_id = $result[0]-&gt;applicant_id;
else
$applicantNumber_id = 0;



}

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;select( 'a.*' )
-&gt;from('applicant_view a')
-&gt;join( 'position p', 'p.position_id = a.applicant_preferred_position', 'left' );

if ( ! empty( $search['q'] ) ) {
$this-&gt;db-&gt;where('(
a.applicant_first LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
a.applicant_id LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
a.applicant_last LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
a.applicant_middle	LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
CONCAT(a.applicant_first, \' \', a.applicant_last) LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
a.passport_number LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
a.position_name LIKE \'%'.addslashes( $search['q'] ).'%\' OR
a.applicant_remarks LIKE \'%'.addslashes( $search['q'] ).'%\') OR
a.sub_employer LIKE \'%'.addslashes( $search['q'] ).'%\') OR
a.applicant_id = '.$requirement_id.' OR
a.applicant_id = '.$ticket_no_id.' OR
a.applicant_id = '.$applicantNumber_id.' OR
a.applicant_id = '.$certificate_id.'
', null, false);
}

if (isset($search['country']) && $search['country'] &gt; 0 ) {
$this-&gt;db-&gt;where([
'applicant_preferred_country' =&gt; $search['country']
]);
}

if (isset($search['status']) && $search['status'] != 111 ) {
$this-&gt;db-&gt;where([
'applicant_status' =&gt; $search['status']
]);
}

if (isset($search['position']) && $search['position'] &gt; 0 ) {
$this-&gt;db-&gt;where([
'applicant_preferred_position' =&gt; $search['position']
]);
$this-&gt;db-&gt;or_where_in('a.applicant_id',$all_apid_sub_pos);
}

if (isset($search['employer']) &&  $search['employer'] &gt; 0 ) {
$this-&gt;db-&gt;where([
'employer_id' =&gt; $search['employer']
]);
}

if ( ! empty( $search['gender'] ) ) {
$this-&gt;db-&gt;where([
'applicant_gender' =&gt; $search['gender']
]);
}

if (isset($search['age']['from']) && $search['age']['from'] &gt; 0 ) {
$this-&gt;db-&gt;where( 'applicant_age BETWEEN '.(int) $search['age']['from'].' AND '.(int) $search['age']['to'],
null, false );
}

$this-&gt;db-&gt;where( 'applicant_expected_salary BETWEEN '.
null, false );
}

if ( isset( $search['date-applied']['from'], $search['date-applied']['to'] ) 
&& date('Y-m-d', strtotime( $search['date-applied']['from'] )) != date('Y-m-d', strtotime(null))
&& date('Y-m-d', strtotime( $search['date-applied']['to'] )) != date('Y-m-d', strtotime(null))
) {

$dateFrom = date('Y-m-d', strtotime( $search['date-applied']['from'] ));
$dateTo   = date('Y-m-d', strtotime( $search['date-applied']['to'] ));

$this-&gt;db-&gt;where( 'DATE(applicant_date_applied) BETWEEN \''.$dateFrom.'\' AND \''.$dateTo.'\'',
null, false );
}
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$this-&gt;db-&gt;group_by( 'a.applicant_id' );

$applicants = $this-&gt;db-&gt;get()-&gt;result_array();
// dd($this-&gt;db-&gt;last_query());

return $applicants;
}

public function searchApplicantsCount()
{
$search = $_GET['search'];

$all_apid_sub_pos = '';
if (isset($search['position']) && $search['position'] &gt; 0 ) {
//get Subposition
$this-&gt;db-&gt;flush_cache();
$query = $this-&gt;db-&gt;get_where('applicant_preferred_positions', array('position_position' =&gt;  $search['position']));
$result = $query-&gt;result();
foreach ($result as $positions_value) {
$all_apid_sub_pos[] =$positions_value-&gt;position_applicant.'"';
}
}

if ( ! empty( $search['q'] ) ) {

//requirement_oec_number search
$this-&gt;db-&gt;flush_cache();
$query = $this-&gt;db-&gt;get_where('applicant_requirement', array('requirement_oec_number' =&gt; $search['q']), 1);
$result = $query-&gt;result();
if(isset($result[0]-&gt;requirement_applicant))
$requirement_id = $result[0]-&gt;requirement_applicant;
else
$requirement_id = 0;

//insurance_no search
$this-&gt;db-&gt;flush_cache();
$query = $this-&gt;db-&gt;get_where('applicant_certificate', array('insurance_no' =&gt; $search['q']), 1);
$result = $query-&gt;result();
if(isset($result[0]-&gt;certificate_applicant))
$certificate_id = $result[0]-&gt;certificate_applicant;
else
$certificate_id = 0;

//ticket_no search
$this-&gt;db-&gt;flush_cache();
$query = $this-&gt;db-&gt;get_where('applicant_requirement', array('ticket_no	' =&gt; $search['q']), 1);
$result = $query-&gt;result();
if(isset($result[0]-&gt;requirement_applicant))
$ticket_no_id = $result[0]-&gt;requirement_applicant;
else
$ticket_no_id = 0;

//ticket_no search
$this-&gt;db-&gt;flush_cache();
$query = $this-&gt;db-&gt;get_where('applicant', array('applicantNumber' =&gt; $search['q']), 1);
$result = $query-&gt;result();
if(isset($result[0]-&gt;applicant_id))
$applicantNumber_id = $result[0]-&gt;applicant_id;
else
$applicantNumber_id = 0;



}

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;select( 'a.*' )
-&gt;from('applicant_view a')
-&gt;join( 'position p', 'p.position_id = a.applicant_preferred_position', 'left' );

if ( ! empty( $search['q'] ) ) {
$this-&gt;db-&gt;where('(
a.applicant_first LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
a.applicant_id LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
a.applicant_last LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
a.applicant_middle	LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
CONCAT(a.applicant_first, \' \', a.applicant_last) LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
a.passport_number LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
a.position_name LIKE \'%'.addslashes( $search['q'] ).'%\' OR
a.applicant_remarks LIKE \'%'.addslashes( $search['q'] ).'%\') OR
a.sub_employer LIKE \'%'.addslashes( $search['q'] ).'%\') OR
a.applicant_id = '.$requirement_id.' OR
a.applicant_id = '.$ticket_no_id.' OR
a.applicant_id = '.$applicantNumber_id.' OR
a.applicant_id = '.$certificate_id.'
', null, false);
}

if (isset($search['country']) && $search['country'] &gt; 0 ) {
$this-&gt;db-&gt;where([
'applicant_preferred_country' =&gt; $search['country']
]);
}

if (isset($search['position']) && $search['position'] &gt; 0 ) {
$this-&gt;db-&gt;where([
'applicant_preferred_position' =&gt; $search['position']
]);
$this-&gt;db-&gt;or_where_in('a.applicant_id',$all_apid_sub_pos);
}

if (isset($search['employer']) && $search['employer'] &gt; 0 ) {
$this-&gt;db-&gt;where([
'employer_id' =&gt; $search['employer']
]);
}

if ( ! empty( $search['gender'] ) ) {
$this-&gt;db-&gt;where([
'applicant_gender' =&gt; $search['gender']
]);
}

if (isset($search['age']['from']) && $search['age']['from'] &gt; 0 ) {
$this-&gt;db-&gt;where( 'applicant_age BETWEEN '.(int) $search['age']['from'].' AND '.(int) $search['age']['to'],
null, false );
}

$this-&gt;db-&gt;where( 'applicant_expected_salary BETWEEN '.
null, false );
}

if ( isset( $search['date-applied']['from'], $search['date-applied']['to'] ) 
&& date('Y-m-d', strtotime( $search['date-applied']['from'] )) != date('Y-m-d', strtotime(null))
&& date('Y-m-d', strtotime( $search['date-applied']['to'] )) != date('Y-m-d', strtotime(null))
) {

$dateFrom = date('Y-m-d', strtotime( $search['date-applied']['from'] ));
$dateTo   = date('Y-m-d', strtotime( $search['date-applied']['to'] ));

$this-&gt;db-&gt;where( 'DATE(applicant_date_applied) BETWEEN \''.$dateFrom.'\' AND \''.$dateTo.'\'',
null, false );
}
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$this-&gt;db-&gt;group_by( 'a.applicant_id' );

$applicants = $this-&gt;db-&gt;get()-&gt;result_array();
// dd($this-&gt;db-&gt;last_query());

return count($applicants);
}


public function getApplicantById( $applicantId )
{	
//Get Applicant Info
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;where([
'applicant_id'	=&gt; $applicantId,
]);
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$applicant               = $this-&gt;db-&gt;get()-&gt;row_array();
$workExperiences         = $this-&gt;getApplicantWorkExperiences( $applicantId );
$otherPreferredPositions = $this-&gt;getApplicantOtherPreferredPositions( $applicantId );
$otherPreferredCountries = $this-&gt;getApplicantOtherPreferredCountries( $applicantId );

$experiences = [];
foreach ( $workExperiences as $experience ) {
$experiences[ $experience['experience_id'] ] = $experience;
}
$workExperiences = $experiences;

$positions = [];
foreach ( $otherPreferredPositions as $position ) {
$positions[ $position['position_id'] ] = $position;
}
$otherPreferredPositions = $positions;

$countries = [];
foreach ( $otherPreferredCountries as $country ) {
$countries[ $country['country_id'] ] = $country;
}
$otherPreferredCountries = $countries;

$applicant['experiences']               = $workExperiences;
$applicant['other-preferred-positions'] = $otherPreferredPositions;
$applicant['other-preferred-countries'] = $otherPreferredCountries;
return $applicant;
}

function getApplicantCertificateById( $applicantId ){
$query = $this-&gt;db-&gt;get_where('applicant_certificate', array('certificate_applicant' =&gt; $applicantId));
$result =  $query-&gt;result();
return $result[0];
}

function getApplicantRawById( $applicantId ){
$query = $this-&gt;db-&gt;get_where(' applicant', array('applicant_id' =&gt; $applicantId));
$result =  $query-&gt;result();
return $result[0];
}

function getApplicantRequirementsById( $applicantId ){
$query = $this-&gt;db-&gt;get_where('applicant_requirement', array('requirement_applicant' =&gt; $applicantId));
$result =  $query-&gt;result();
return $result[0];
}
function getApplicantPassById( $applicantId ){
$query = $this-&gt;db-&gt;get_where('applicant_passport', array('passport_applicant' =&gt; $applicantId));
$result =  $query-&gt;result();
return $result[0];
}

	
public function getCurrencyById($applicantId){
$this-&gt;db-&gt;from( 'applicant' )
-&gt;where([
'applicant_id'	=&gt; $applicantId,
]);
$applicant = $this-&gt;db-&gt;get()-&gt;row_array();
return $applicant['currency'];
}

//For admin/applicants/send_applicants
public function getApplicantsByIds( $applicantIds )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant' )
-&gt;where_in('applicant_id', $applicantIds);
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$applicants = $this-&gt;db-&gt;get()-&gt;result_array();

return $applicants;
}

public function lineUpApplicants( $applicantIds, $employerId )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where_in( 'applicant_id', $applicantIds )
-&gt;update( 'applicant', [
'applicant_status'   =&gt; $this-&gt;status['Line Up'],
'applicant_employer' =&gt; $employerId,
]);
foreach ($applicantIds as $applicantId) {
$logInserted = $this-&gt;addLog( 'Send Applicant', $applicantId, $employerId, $this-&gt;status['Line Up'], date( 'Y-m-d', time() ) );
}	
}



public function getApplicants( $options = [], $limit = 0, $offset = 0, $sort = ['applicant_updated', 'DESC'])
{
if($_SESSION['admin']['user']['user_type'] == 9){
$user_rs = $this-&gt;db-&gt;get_where('user', array('team_lead_id' =&gt; $_SESSION['admin']['user']['user_id']));
foreach ($user_rs-&gt;result() as $key =&gt; $value) {
$users_rs_id[] = $value-&gt;user_id;
}

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where_in('employer_user',$users_rs_id);
$employers_all = $this-&gt;db-&gt;get('employer')-&gt;result_array();
foreach ($employers_all as $key =&gt; $value) {
$employers_id[] = $value['employer_id'];
}
}

if($_SESSION['admin']['user']['user_type'] == 10){
$employers = $this-&gt;db-&gt;get_where('employer', array('rs_id' =&gt; $_SESSION['admin']['user']['user_id']));
foreach ($employers-&gt;result() as $key =&gt; $value) {
$employers_id[] = $value-&gt;employer_id;
}
}

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_view' ); 

if(($_SESSION['admin']['user']['user_type'] == 10) || ($_SESSION['admin']['user']['user_type'] == 9)){
$this-&gt;db-&gt;where_in('applicant_employer',$employers_id);
}

if(isset($_GET['skill'])){
$this-&gt;db-&gt;where('applicant_position_type',$_GET['skill']);
}

//For Selected
$this-&gt;db-&gt;join( 'employer_selected', 'selected_employer = applicant_employer AND selected_applicant = applicant_id', 'left' );

//For Deployed
$this-&gt;db-&gt;join( 'deployed', 'deployed_employer = applicant_employer AND deployed_applicant = applicant_id', 'left' );

$this-&gt;setDBQueryOptions( $options )
-&gt;setDBQueryRange( $limit, $offset )
-&gt;setDBQueryOrders( $sort );
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1'); 
$applicants = $this-&gt;db-&gt;get()-&gt;result_array();

return $this-&gt;indexArray( $applicants, 'applicant_id' );
}

public function getApplicantsCount( $options = [] )
{
if($_SESSION['admin']['user']['user_type'] == 9){
$user_rs = $this-&gt;db-&gt;get_where('user', array('team_lead_id' =&gt; $_SESSION['admin']['user']['user_id']));
foreach ($user_rs-&gt;result() as $key =&gt; $value) {
$users_rs_id[] = $value-&gt;user_id;
}

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where_in('employer_user',$users_rs_id);
$employers_all = $this-&gt;db-&gt;get('employer')-&gt;result_array();
foreach ($employers_all as $key =&gt; $value) {
$employers_id[] = $value['employer_id'];
}
}

if($_SESSION['admin']['user']['user_type'] == 10){
$employers = $this-&gt;db-&gt;get_where('employer', array('rs_id' =&gt; $_SESSION['admin']['user']['user_id']));
foreach ($employers-&gt;result() as $key =&gt; $value) {
$employers_id[] = $value-&gt;employer_id;
}
}


$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_view' ); 

if(($_SESSION['admin']['user']['user_type'] == 10) || ($_SESSION['admin']['user']['user_type'] == 9)){
$this-&gt;db-&gt;where_in('applicant_employer',$employers_id);
}

$this-&gt;setDBQueryOptions( $options );

$applicants = $this-&gt;db-&gt;count_all_results();

return $applicants;
}

public function cyd_get_multiple_employer( $applicant_id = [] )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'multiple_lineups' )-&gt;where([
'applicant_id' =&gt; $applicant_id,
]);
$this-&gt;db-&gt;group_by('applicant_employer');
$lineup_ids = $this-&gt;db-&gt;get()-&gt;result_array();

$result = '';
foreach ($lineup_ids as $lineup_id) {
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'employer' )-&gt;where([
'employer_id' =&gt; $lineup_id['applicant_employer'],
]);
$tbl_employer = $this-&gt;db-&gt;get()-&gt;result_array();
$result .= $tbl_employer[0]['employer_name'].', ';
}
return $result;
}

public function getPreSelected( $options = [], $limit = 0, $offset = 0, $sort = ['reservation_expiration', 'ASC'])
{
$this-&gt;db-&gt;flush_cache();

$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;join( 'applicant_log', 'log_applicant = applicant_id' )

-&gt;where([
'applicant_status' =&gt; $this-&gt;status['Pre-Selected'],
]);

$this-&gt;setDBQueryOptions( $options )
-&gt;setDBQueryRange( $limit, $offset )
-&gt;setDBQueryOrders( $sort );
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1'); 
$applicants = $this-&gt;db-&gt;get()-&gt;result_array();

return $this-&gt;indexArray( $applicants, 'applicant_id' );
}
public function getPreSelectedCount( $options = [] )
{
$this-&gt;db-&gt;flush_cache();

$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;join( 'applicant_log', 'log_applicant = applicant_id' )
-&gt;where([
'applicant_status' =&gt; $this-&gt;status['Pre-Selected'],
]);

$this-&gt;setDBQueryOptions( $options );
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1'); 
$applicants = $this-&gt;db-&gt;count_all_results();

return $applicants;
}

public function getForBooking( $options = [], $limit = 0, $offset = 0, $sort = ['applicant_id', 'DESC'])
{
$this-&gt;db-&gt;flush_cache();

$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;join( 'applicant_log', 'log_applicant = applicant_id' )

-&gt;where([
'applicant_status' =&gt; $this-&gt;status['Deployment'],
]);

$this-&gt;setDBQueryOptions( $options )
-&gt;setDBQueryRange( $limit, $offset )
-&gt;setDBQueryOrders( $sort );
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1'); 
$applicants = $this-&gt;db-&gt;get()-&gt;result_array();

return $this-&gt;indexArray( $applicants, 'applicant_id' );
}
public function getForBookingCount( $options = [] )
{
$this-&gt;db-&gt;flush_cache();

$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;join( 'applicant_log', 'log_applicant = applicant_id' )
-&gt;where([
'applicant_status' =&gt; $this-&gt;status['Deployment'],
]);

$this-&gt;setDBQueryOptions( $options );
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1'); 
$applicants = $this-&gt;db-&gt;count_all_results();

return $applicants;
}

public function getReservedApplicants( $options = [], $limit = 0, $offset = 0, $sort = ['reservation_expiration', 'ASC'])
{
$this-&gt;db-&gt;flush_cache();

$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;join( 'employer_reservation', 'reservation_applicant = applicant_id' )
-&gt;where([
'applicant_status' =&gt; $this-&gt;status['Reserved'],
]);

$this-&gt;setDBQueryOptions( $options )
-&gt;setDBQueryRange( $limit, $offset )
-&gt;setDBQueryOrders( $sort );
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1'); 
$applicants = $this-&gt;db-&gt;get()-&gt;result_array();

return $this-&gt;indexArray( $applicants, 'applicant_id' );
}
public function getReservedApplicantsCount( $options = [] )
{
$this-&gt;db-&gt;flush_cache();

$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;join( 'employer_reservation', 'reservation_applicant = applicant_id' )
-&gt;where([
'applicant_status' =&gt; $this-&gt;status['Reserved'],
]);

$this-&gt;setDBQueryOptions( $options );
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1'); 
$applicants = $this-&gt;db-&gt;count_all_results();

return $applicants;
}

public function getExpiredReservedApplicants( $options = [], $limit = 0, $offset = 0, $sort = [ 'reservation_expiration', 'ASC' ] )
{
$this-&gt;db-&gt;flush_cache();

$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;join( 'employer_reservation', 'reservation_applicant = applicant_id' )
-&gt;where([
'applicant_status'          =&gt; $this-&gt;status['Reserved'],
'reservation_expiration &lt;=' =&gt; date( 'Y-m-d', time() ),
'reservation_expiration &gt;'  =&gt; DATE_EMPTY,
]);

$this-&gt;setDBQueryOptions( $options )
-&gt;setDBQueryRange( $limit, $offset )
-&gt;setDBQueryOrders( $sort );
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1'); 
$applicants = $this-&gt;db-&gt;get()-&gt;result_array();

return $this-&gt;indexArray( $applicants, 'applicant_id' );
}

public function getExpiredReservedApplicantsCount( $options = [] )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;join( 'employer_reservation', 'reservation_applicant = applicant_id' )
-&gt;where([
'applicant_status'          =&gt; $this-&gt;status['Reserved'],
'reservation_expiration &lt;=' =&gt; date( 'Y-m-d', time() ),
'reservation_expiration &gt;'  =&gt; DATE_EMPTY,
]);

$this-&gt;setDBQueryOptions( $options );

$applicants = $this-&gt;db-&gt;count_all_results();

return $applicants; 
}

public function getExpiredMedicalApplicants( $options = [], $limit = 0, $offset = 0, $sort = [ 'certificate_medical_expiration', 'ASC' ] )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;where([
'certificate_medical_clinic !='     =&gt; '',
'certificate_medical_expiration &lt;=' =&gt; date( 'Y-m-d', time() + 60*60*24*14 ),
'certificate_medical_expiration &gt;'  =&gt; DATE_EMPTY,
]);
$notinclude = array(1,7,9,22,21);
$this-&gt;db-&gt;where_not_in('applicant_status',$notinclude);

$this-&gt;setDBQueryOptions( $options )
-&gt;setDBQueryRange( $limit, $offset )
-&gt;setDBQueryOrders( $sort );
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$applicants = $this-&gt;db-&gt;get()-&gt;result_array();

return $this-&gt;indexArray( $applicants, 'applicant_id' );
}

public function getExpiredMedicalApplicantsCount( $options = [] )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;where([
'certificate_medical_clinic !='     =&gt; '',
'certificate_medical_expiration &lt;=' =&gt; date( 'Y-m-d', time() + 60*60*24*14 ),
'certificate_medical_expiration &gt;'  =&gt; DATE_EMPTY,
]);
$notinclude = array(1,7,9,22,21);
$this-&gt;db-&gt;where_not_in('applicant_status',$notinclude);

$this-&gt;setDBQueryOptions( $options );

$applicants = $this-&gt;db-&gt;count_all_results();

return $applicants; 
}

public function getExpiredVisaApplicants( $options = [], $limit = 0, $offset = 0, $sort = [ 'requirement_visa_expiration', 'ASC' ] )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;where([
'requirement_visa'               =&gt; 1,
'requirement_visa_expiration &lt;=' =&gt; date( 'Y-m-d', time() + 60*60*24*30 ),
'requirement_visa_expiration &gt;'  =&gt; DATE_EMPTY,
]);

$notinclude = array(1,7,9,22,21);
$this-&gt;db-&gt;where_not_in('applicant_status',$notinclude);

$this-&gt;setDBQueryOptions( $options )
-&gt;setDBQueryRange( $limit, $offset )
-&gt;setDBQueryOrders( $sort );
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$applicants = $this-&gt;db-&gt;get()-&gt;result_array();

return $this-&gt;indexArray( $applicants, 'applicant_id' );
}

public function getExpiredVisaApplicantsCount( $options = [] )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;where([
'requirement_visa'               =&gt; 1,
'requirement_visa_expiration &lt;=' =&gt; date( 'Y-m-d', time() + 60*60*24*30 ),
]);

$notinclude = array(1,7,9,22,21);
$this-&gt;db-&gt;where_not_in('applicant_status',$notinclude);

$this-&gt;setDBQueryOptions( $options );

$applicants = $this-&gt;db-&gt;count_all_results();

return $applicants; 
}

public function getExpiredPassportsApplicants( $options = [], $limit = 0, $offset = 0, $sort = [ 'passport_expiration', 'ASC' ] )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_view' )
// -&gt;where_in( 'applicant_status', [
//     $this-&gt;m_applicant-&gt;status['Selected'],
//     $this-&gt;m_applicant-&gt;status['Reserved'],
//     $this-&gt;m_applicant-&gt;status['For Deployment'],
// ])
-&gt;where([
'passport_number !='     =&gt; '',
'passport_expiration &lt;=' =&gt; date( 'Y-m-d', time() + 60*60*24*30*8 ),
'passport_expiration &gt;'  =&gt; DATE_EMPTY,
]);

$notinclude = array(1,7,9,22,21);
$this-&gt;db-&gt;where_not_in('applicant_status',$notinclude);

$this-&gt;setDBQueryOptions( $options )
-&gt;setDBQueryRange( $limit, $offset )
-&gt;setDBQueryOrders( $sort );
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$applicants = $this-&gt;db-&gt;get()-&gt;result_array();

return $this-&gt;indexArray( $applicants, 'applicant_id' );
}

public function getExpiredPassportsApplicantsCount( $options = [] )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_view' )
// -&gt;where_in( 'applicant_status', [
//     $this-&gt;m_applicant-&gt;status['Selected'],
//     $this-&gt;m_applicant-&gt;status['Reserved'],
//     $this-&gt;m_applicant-&gt;status['For Deployment'],
// ])
-&gt;where([
'passport_number !='     =&gt; null,
'passport_number !='     =&gt; '',
'passport_expiration &lt;=' =&gt; date( 'Y-m-d', time() + 60*60*24*30*8 ),
'passport_expiration &gt;'  =&gt; DATE_EMPTY,
]);

$notinclude = array(1,7,9,22,21);
$this-&gt;db-&gt;where_not_in('applicant_status',$notinclude);

$this-&gt;setDBQueryOptions( $options );

$applicants = $this-&gt;db-&gt;count_all_results();

return $applicants; 
}




public function getLineUpApplicants( $options = [], $limit = 0, $offset = 0, $sort = ['applicant_updated', 'DESC'] )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;where([
'applicant_status'     =&gt; $this-&gt;status['Line Up'],
]);

$this-&gt;setDBQueryOptions( $options )
-&gt;setDBQueryRange( $limit, $offset )
-&gt;setDBQueryOrders( $sort );
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$applicants = $this-&gt;db-&gt;get()-&gt;result_array();

return $this-&gt;indexArray( $applicants, 'applicant_id' );
} 

public function cyd_getLineUpApplicants( $options = [], $limit = 0, $offset = 0, $sort = ['applicant_updated', 'DESC'] )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;select('*');
$this-&gt;db-&gt;from('applicant_view');
$this-&gt;db-&gt;where([
'applicant_status' =&gt; $this-&gt;status['Line Up'],
'applicant_employer' =&gt; $options['where'][0]['applicant_employer'],
]);
$this-&gt;setDBQueryRange( $limit, $offset )
-&gt;setDBQueryOrders( $sort );
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');

$applicants = $this-&gt;db-&gt;get()-&gt;result_array();

return $this-&gt;indexArray( $applicants, 'applicant_id' );
} 

public function getLineUpApplicantsCount( $options = [] )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;join( 'employer_selected', 'selected_applicant = applicant_id' )
-&gt;where([
'applicant_status'     =&gt; $this-&gt;status['Line Up'],
]);

$this-&gt;setDBQueryOptions( $options );

$applicants = $this-&gt;db-&gt;count_all_results();

return $applicants;
}










public function getSelectedApplicants( $options = [], $limit = 0, $offset = 0, $sort = ['applicant_updated', 'DESC'] )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;join( 'employer_selected', 'selected_applicant = applicant_id' )
-&gt;where([
'applicant_status'     =&gt; $this-&gt;status['Selected'],
]);

$this-&gt;setDBQueryOptions( $options )
-&gt;setDBQueryRange( $limit, $offset )
-&gt;setDBQueryOrders( $sort );
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$applicants = $this-&gt;db-&gt;get()-&gt;result_array();

return $this-&gt;indexArray( $applicants, 'applicant_id' );
} 

public function getSelectedApplicantsCount( $options = [] )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;join( 'employer_selected', 'selected_applicant = applicant_id' )
-&gt;where([
'applicant_status'     =&gt; $this-&gt;status['Selected'],
]);

$this-&gt;setDBQueryOptions( $options );

$applicants = $this-&gt;db-&gt;count_all_results();

return $applicants;
}

public function getDeployedApplicants( $options = [], $limit = 0, $offset = 0, $sort = ['applicant_updated', 'DESC'] )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;join( 'deployed', 'deployed_applicant = applicant_id', 'inner' )
-&gt;where([
'applicant_status'     =&gt; $this-&gt;status['Deployed'],
]);

$this-&gt;setDBQueryOptions( $options )
-&gt;setDBQueryRange( $limit, $offset )
-&gt;setDBQueryOrders( $sort );
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$applicants = $this-&gt;db-&gt;get()-&gt;result_array();

return $this-&gt;indexArray( $applicants, 'applicant_id' );
} 

public function getDeployedApplicantsCount( $options = [] )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;join( 'deployed', 'deployed_applicant = applicant_id', 'inner' )
-&gt;where([
'applicant_status'     =&gt; $this-&gt;status['Deployed'],
]);

$this-&gt;setDBQueryOptions( $options );

$applicants = $this-&gt;db-&gt;count_all_results();

return $applicants;
}

public function getApplicantWorkExperiences( $applicantId )
{
//Get Work Experiences
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from('applicant_experiences')
-&gt;where([
'experience_applicant'	=&gt; $applicantId,
]);
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$experiences = $this-&gt;db-&gt;get()-&gt;result_array();

return $this-&gt;indexArray( $experiences, 'experience_id' );
}


public function getApplicantWorktrain( $applicantId )
{
//Get Work training
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from('appliocant_train')
-&gt;where([
'm_app'	=&gt; $applicantId,
]);
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$experiencestraining = $this-&gt;db-&gt;get()-&gt;result_array();

return $this-&gt;indexArray( $experiencestraining, 't_id' );
}




public function getApplicantOtherPreferredPositions( $applicantId )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;select( 'p.*' )
-&gt;from('position p')
-&gt;join('applicant_preferred_positions pp', 'pp.position_position = p.position_id')
-&gt;where([
'pp.position_applicant' =&gt; $applicantId,
]);
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$positions = $this-&gt;db-&gt;get()-&gt;result_array();

return $this-&gt;indexArray( $positions, 'position_id' );
}

public function getApplicantOtherPreferredCountries( $applicantId )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;select( 'c.*' )
-&gt;from('country c')
-&gt;join('applicant_preferred_countries pc', 'pc.country_country = c.country_id')
-&gt;where([
'pc.country_applicant' =&gt; $applicantId,
]);
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$countries = $this-&gt;db-&gt;get()-&gt;result_array();

return $this-&gt;indexArray( $countries, 'country_id' );
}

public function getApplicantFiles( $applicantId, $options = [], $limit = 0, $offset = 0 )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_files' )
-&gt;join( 'user', 'user_id = file_createdby' )
-&gt;where([
'file_applicant' =&gt; $applicantId,
'file_status'    =&gt; 1,
]);

$this-&gt;setDBQueryOptions( $options )
-&gt;setDBQueryRange( $limit, $offset );

$this-&gt;db-&gt;order_by( 'file_created', 'DESC' );
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$files = $this-&gt;db-&gt;get()-&gt;result_array();

return $this-&gt;indexArray( $files, 'file_type' );
}

public function getApplicantFileByType( $applicantId, $type )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from('applicant_files')
-&gt;where([
'file_applicant' =&gt; $applicantId,
'file_type'      =&gt; $type,
]);
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$file = $this-&gt;db-&gt;get()-&gt;row_array();

return $file;
}

public function getApplicantFileById( $fileId )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_files' )
// -&gt;join( 'user', 'user_id = file_createdby' )
-&gt;where([
'file_id' =&gt; $fileId,
]);

$file = $this-&gt;db-&gt;get()-&gt;row_array();

return $file;
}

public function getApplicantLogs( $applicantId, $options = [], $limit = 0, $offset = 0, $sort = [ 'log_created', 'DESC' ] )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicants_logs_view' )
-&gt;where([
'log_applicant' =&gt; $applicantId,
]);

$this-&gt;setDBQueryOptions( $options )
-&gt;setDBQueryRange( $limit, $offset )
-&gt;setDBQueryOrders( $sort );
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$logs = $this-&gt;db-&gt;get()-&gt;result_array();

return $this-&gt;indexArray( $logs, 'log_id' );

}


public function getStatus( $applicantId )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_view' )
-&gt;where([
'applicant_id' =&gt; $applicantId,
]);
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$applicant = $this-&gt;db-&gt;get()-&gt;row_array();

$response = [
'employer' =&gt; $applicant['applicant_employer'],
'country'  =&gt; $applicant['country_name'],
'status'   =&gt; $applicant['applicant_status'],
'date'     =&gt; date( 'Y-m-d', time() ),
'remarks'  =&gt; null,
];

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_log' )
-&gt;where([
'log_applicant' =&gt; $applicantId,
'log_status'    =&gt; $applicant['applicant_status'],
])
-&gt;order_by( 'log_created', 'DESC' )
-&gt;limit(1);
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$log = $this-&gt;db-&gt;get()-&gt;row_array();

if ( ! empty( $log ) ) {

$response = array_merge( $response, [
'status'   =&gt; $log['log_status'],
'date'     =&gt; date( 'Y-m-d', strtotime( $log['log_date'] ) ),
'remarks'  =&gt; $log['log_remarks'],
]);
}

return $response;
}

public function getAllLogs( $options = [], $limit = 0, $offset = 0, $sort = [ 'log_created', 'DESC' ] )
{
$this-&gt;db-&gt;from( 'applicants_logs_view' );

$this-&gt;setDBQueryOptions( $options )
-&gt;setDBQueryRange( $limit, $offset )
-&gt;setDBQueryOrders( $sort );
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');				
$logs = $this-&gt;db-&gt;get()-&gt;result_array();

return $this-&gt;indexArray( $logs, 'log_id' );
}

public function getCounters()
{
$this-&gt;db-&gt;query('SET SQL_BIG_SELECTS=1');
$counter = [];

for ( $m = 6; $m &gt;= 0; $m-- ) {

$month    = date('Y-F', strtotime('-'.$m.' months'));
$dateFrom = date( 'Y-m-01', strtotime( '-'.$m.' months' ) );
$dateTo   = date( 'Y-m-t', strtotime( '-'.$m.' months' ) );

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant' )
-&gt;where( "`applicant_date_applied` BETWEEN '".$dateFrom."' AND '".$dateTo."' ", null, false);

$counter['applied'][$month] = $this-&gt;db-&gt;count_all_results();

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_view' );
$this-&gt;db-&gt;where([
'applicant_status' =&gt; $this-&gt;status['Deployed'],
]);
$this-&gt;db-&gt;join( 'deployed', 'deployed_applicant = applicant_id' );
$this-&gt;db-&gt;where(
"DATE(deployed_date) BETWEEN '".$dateFrom."' AND '".$dateTo."'",
null, false);
$this-&gt;db-&gt;where("deployed_id = (SELECT deployed_id FROM deployed WHERE deployed_applicant = applicant_id ORDER BY deployed_created DESC LIMIT 1)", null, false);

$counter['deployed'][$month] = $this-&gt;db-&gt;count_all_results();

/*
$this-&gt;db-&gt;from( 'applicant_log' )
-&gt;join( 'applicant', 'applicant_id = log_applicant' )
-&gt;where( "`log_created` BETWEEN '".$dateFrom."' AND '".$dateTo."' ", null, false)
-&gt;where([
'log_status'       =&gt; $this-&gt;status['Deployed'],
]);
$counter['deployed'][$month] = $this-&gt;db-&gt;count_all_results();
*/

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_log' )
-&gt;where( "`log_created` BETWEEN '".$dateFrom."' AND '".$dateTo."' ", null, false)
-&gt;where([
'log_status'       =&gt; $this-&gt;status['Reserved'],
]);

$counter['reserved'][$month] = $this-&gt;db-&gt;count_all_results();

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_log' )
-&gt;where( "`log_created` BETWEEN '".$dateFrom."' AND '".$dateTo."' ", null, false)
-&gt;where([
'log_status'       =&gt; $this-&gt;status['Selected'],
]);

$counter['Selected'][$month] = $this-&gt;db-&gt;count_all_results();

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;from( 'applicant_log' )
-&gt;where( "`log_created` BETWEEN '".$dateFrom."' AND '".$dateTo."' ", null, false)
-&gt;where([
$this-&gt;db-&gt;trans_commit();

return true;
}

function cyd_get_id(){
$logid = 0;
if(isset($_SESSION['admin']['user']['user_id']))
$logid = $_SESSION['admin']['user']['user_id'];
else
$logid = $_SESSION['employer']['user']['employer_id'];

return $logid;
}
public function updateApplicantProfile( $applicantId )
{
$applicant = $this-&gt;getApplicantById( $applicantId );

$post      = $_POST['applicant'];

$basic                    =
$passport                 =
$visa                     =
$education                =
$preferredPositions       =
$preferredPositionsRemove =
$preferredPositionsData   =
$preferredCountries       =
$preferredCountriesRemove =
$preferredCountriesData   =
$passportData             =
$oldWorkExperiences       =
$newWorkExperiences       =
$workExperiencesRemove    =
$workExperiencesData       =
$visaData                 = [];

$basic                    = $post['basic'];
$passport                 = $post['passport'];
$education                = $post['education'];
$preferredPositions       = isset( $post['other-preferred-positions'] ) ? $post['other-preferred-positions'] : [];
$preferredCountries       = isset( $post['other-preferred-countries'] ) ? $post['other-preferred-countries'] : [];
$oldWorkExperiences       = isset( $post['work-experience-old'] ) ? $post['work-experience-old'] : [];
$newWorkExperiences       = isset( $post['work-experience'] ) ? $post['work-experience'] : [];

//Start Transaction
//$this-&gt;db-&gt;trans_begin();

//Update applicant profile

if(!isset($post['type']))$post['type'] = '';

$applicantData = [
'applicant_first'			   =&gt; ucwords( $basic['first'] ),
'applicant_middle'			   =&gt; ucwords( $basic['middle'] ),
'applicant_last'		 	   =&gt; ucwords( $basic['last'] ),
'applicant_birthdate'          =&gt; date('Y-m-d', strtotime( $basic['birthdate'] ) ),
'applicant_age'				   =&gt; $basic['age'],
'applicant_gender'			   =&gt; $basic['gender'],
'applicant_contacts'           =&gt; $basic['contacts'],
'applicant_contacts_2'           =&gt; $basic['contacts2'],
'applicant_contacts_3'           =&gt; $basic['contacts3'],
'applicant_address'			   =&gt; $basic['address'],
'applicant_email'			   =&gt; $basic['email'],
'applicant_civil_status'	   =&gt; $basic['status'],
'applicant_religion'		   =&gt; $basic['religion'],
'applicant_languages'		   =&gt; $basic['languages'],
'applicant_height'			   =&gt; $basic['height'],
'applicant_weight'			   =&gt; $basic['weight'],
'applicant_preferred_position' =&gt; $post['preferred-position'],
'currency' 					   =&gt; $post['currency'],
'applicant_children'		   =&gt; $basic['children'],
'applicant_mothers'			   =&gt; $basic['applicant_mothers'],
'applicant_expected_salary'    =&gt; $post['expected-salary'],
'applicant_preferred_country'  =&gt; $post['preferred-country'],
'applicant_other_skills'       =&gt; $post['other-skills'],
'applicant_position_type'		=&gt; $post['type'],
'other_source'			   =&gt; $basic['other_source'],
'applicant_date_interview'	=&gt;  $basic['date-interview'],
'applicant_remarks1'	=&gt;  	$basic['remarks1'],
'applicant_remarks_3'	=&gt;  	$basic['remarks_3'],
'applicant_jobs'	=&gt;  	$basic['applicant_jobs'],

't1'	=&gt;  	$basic['t1'],
't2'	=&gt;  	$basic['t2'],
't3'	=&gt;  	$basic['t3'],
't4'	=&gt;  	$basic['t4'],
't5'	=&gt;  	$basic['t5'],
't6'	=&gt;  	$basic['t6'],
't7'	=&gt;  	$basic['t7'],
't8'	=&gt;  	$basic['t8'],

'applicant_by_interview'	=&gt;  $basic['date-by'],
'applicant_ex'	=&gt;  $basic['applicant_ex'],
'applicant_engslish'	=&gt;  $basic['applicant_engslish'],
'applicant_arabic'	=&gt;  $basic['applicant_arabic'],
'applicant_ppt_stat'	=&gt;  $basic['applicant_ppt_stat'],
'applicant_ppt_pay'	=&gt;  $basic['applicant_ppt_pay'],
'applicant_incase_name'		 =&gt; $basic['applicant_incase_name'],
'applicant_incase_relation'	 =&gt; $basic['applicant_incase_relation'],
'applicant_incase_contact'	 =&gt; $basic['applicant_incase_contact'],
'applicant_incase_address'	 =&gt; $basic['applicant_incase_address'],
'typess1'            =&gt; $basic['typess1'],
'typess'            =&gt; $basic['typess'],
'applicant_remarks'            =&gt; $post['remarks'],
'applicant_slug'               =&gt; str_pad( $applicantId, 10, '0', STR_PAD_LEFT )
		 .'/'.strSlug( $basic['first'].' '.$basic['middle'].' '.$basic['last'] ),
'applicant_date_applied'       =&gt; date( 'Y-m-d', strtotime( $post['date-applied'] ) ),
'applicant_updatedby'          =&gt; $_SESSION['user_id'],
'applicant_updated'            =&gt; date('Y-m-d H:i:s', time()),
];

//update cyd skills
if(!isset($post['is_ironing'])) $post['is_ironing'] = 0;
if(!isset($post['is_cooking'])) $post['is_cooking'] = 0;
if(!isset($post['is_sewing'])) 	$post['is_sewing'] = 0;
if(!isset($post['is_computer'])) $post['is_computer'] = 0;
if(!isset($post['is_arabic_cooking'])) $post['is_arabic_cooking'] = 0;
if(!isset($post['is_baby_sitting'])) $post['is_baby_sitting'] = 0;
if(!isset($post['is_children_care'])) $post['is_children_care'] = 0;
if(!isset($post['is_tutoring'])) $post['is_tutoring'] = 0;
if(!isset($post['is_cleaning'])) $post['is_cleaning'] = 0;
if(!isset($post['is_washing'])) $post['is_washing'] = 0;
if(!isset($post['is_manicure'])) $post['is_manicure'] = 0;
if(!isset($post['is_massage'])) $post['is_massage'] = 0;
if(!isset($post['is_blower'])) $post['is_blower'] = 0;
if(!isset($post['is_coloring'])) $post['is_coloring'] = 0;
if(!isset($post['write_e'])) $post['write_e'] = 0;
if(!isset($post['read_e'])) $post['read_e'] = 0;
if(!isset($post['speak_e'])) $post['speak_e'] = 0;
if(!isset($post['write_a'])) $post['write_a'] = 0;
if(!isset($post['read_a'])) $post['read_a'] = 0;
if(!isset($post['speak_a'])) $post['speak_a'] = 0;


$skill_cyd_data = [
'applicant_id'			=&gt; $applicantId,
'ironing'				=&gt; $post['is_ironing'],
'cooking'				=&gt; $post['is_cooking'],
'sewing'				=&gt; $post['is_sewing'],
'computer'				=&gt; $post['is_computer'],
'arabic_cooking'		=&gt; $post['is_arabic_cooking'],
'baby_sitting'			=&gt; $post['is_baby_sitting'],
'children_care'			=&gt; $post['is_children_care'],
'tutoring'				=&gt; $post['is_tutoring'],
'cleaning'				=&gt; $post['is_cleaning'],
'washing'				=&gt; $post['is_washing'],
'manicure'			=&gt; $post['is_manicure'],
'massage'			=&gt; $post['is_massage'],
'blower'			=&gt; $post['is_blower'],
'coloring'			=&gt; $post['is_coloring'],
'write_e'			=&gt; $post['write_e'],
'read_e'			=&gt; $post['read_e'],
'speak_e'			=&gt; $post['speak_e'],
'write_a'			=&gt; $post['write_a'],
'read_a'			=&gt; $post['read_a'],
'speak_a'			=&gt; $post['speak_a'],
'updated_at'			=&gt; date( 'Y-m-d H:i:s', time() ),
];

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where('applicant_id', $applicantId);
$this-&gt;db-&gt;from('applicant_skills_cyds');
$count_skill_cyd = $this-&gt;db-&gt;count_all_results();

if($count_skill_cyd == 0){
$this-&gt;db-&gt;insert('applicant_skills_cyds', $skill_cyd_data);
}else{
$this-&gt;db-&gt;where([
'applicant_id' =&gt; $applicantId,
])
-&gt;update( 'applicant_skills_cyds', $skill_cyd_data );
}


if(isset($post['training-branch'])) 	$applicantData['training_branches_id'] 	= $post['training-branch'];
if(isset($post['training-status'])) 	$applicantData['applicant_status'] 	= $post['training-status'];
if(isset($post['training-start'])) 		$applicantData['start_training_at'] 	= $post['training-start'];
if(isset($post['training-end'])) 		$applicantData['end_training_at'] 		= $post['training-end'];
if(isset($post['training-remarks'])) 	$applicantData['training_remarks'] 	= $post['training-remarks'];

$this-&gt;db-&gt;flush_cache();
$applicantUpdated =
$this-&gt;db-&gt;where([
'applicant_id' =&gt; $applicantId,
])
-&gt;update( 'applicant', $applicantData );

//Remove preferred positions
foreach ( $applicant['other-preferred-positions'] as $position ) {
if ( ! in_array( $position['position_id'], $preferredPositions ) ) {
$preferredPositionsRemove[] = $position['position_id'];
continue;
}
}

if ( count( $preferredPositionsRemove ) &gt; 0 ) {
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where_in( 'position_position', $preferredPositionsRemove )
-&gt;where([
'position_applicant' =&gt; $applicantId,
])
-&gt;delete( 'applicant_preferred_positions' );
}

//Add preferred Positions
foreach ( $preferredPositions as $positionId ) {
if ( ! array_key_exists( $positionId, $applicant['other-preferred-positions'] ) ) {
$preferredPositionsData[] = [
'position_applicant'   =&gt; $applicantId,
'position_position'    =&gt; $positionId,
'position_createdby'   =&gt; $_SESSION['user_id'],
'position_created'     =&gt; date( 'Y-m-d H:i:s', time() ),
];
}
}

if ( count( $preferredPositionsData ) &gt; 0 ) {
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;insert_batch( 'applicant_preferred_positions', $preferredPositionsData );
}

//Remove preferred countries
foreach ( $applicant['other-preferred-countries'] as $country ) {
if ( ! in_array( $country['country_id'], $preferredCountries ) ) {
$preferredCountriesRemove[] = $country['country_id'];
continue;
}
}

if ( count( $preferredCountriesRemove ) &gt; 0) {
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where_in( 'country_country', $preferredCountriesRemove )
-&gt;where([
'country_applicant' =&gt; $applicantId,
])
-&gt;delete( 'applicant_preferred_countries' );
}
//Add preferred Countries
foreach ( $preferredCountries as $countryId ) {
if ( ! array_key_exists( $countryId, $applicant['other-preferred-countries'] ) ) {
$preferredCountriesData[] = [
'country_applicant'  =&gt; $applicantId,
'country_country'    =&gt; $countryId,
'country_createdby'  =&gt; $_SESSION['user_id'],
'country_created'    =&gt; date( 'Y-m-d H:i:s', time() ),
];
}
}

if ( count( $preferredCountriesData ) &gt; 0 ) {
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;insert_batch( 'applicant_preferred_countries', $preferredCountriesData );
}

//Update passport
$passportData = [
'passport_number'         =&gt; $passport['number'],
'passport_issue'          =&gt; date( 'Y-m-d', strtotime( $passport['issue'] ) ),
'passport_issue_place'    =&gt; $passport['issue-place'],
'passport_expiration'     =&gt; date( 'Y-m-d', strtotime( $passport['expiration'] ) ),
'passport_updatedby'      =&gt; $_SESSION['user_id'],
'passport_updated'        =&gt; date( 'Y-m-d H:i:s', time() ),
];

$this-&gt;db-&gt;flush_cache();
$passportUpdated =
$this-&gt;db-&gt;where([
'passport_applicant'  =&gt; $applicantId
])
-&gt;update( 'applicant_passport', $passportData);

//Update educational background
$educationData = [
'education_mba'				=&gt; $education['mba'],
'education_mba_course'		=&gt; $education['mba-course'],
'education_mba_year'		=&gt; $education['mba-year'],
'education_college'			=&gt; $education['college'],
'education_college_skills'	=&gt; $education['college-skills'],
'education_college_year'	=&gt; $education['college-year'],
'education_others'			=&gt; $education['others'],
'education_others_year'		=&gt; $education['others-year'],
'education_highschool'		=&gt; $education['highschool'],
'education_highschool_year'	=&gt; $education['highschool-year'],
'education_updatedby'		=&gt; $_SESSION['user_id'],
'education_updated'			=&gt; date( 'Y-m-d H:i:s', time() ),
];

$this-&gt;db-&gt;flush_cache();
$educationUpdated =
$this-&gt;db-&gt;where([
'education_applicant' =&gt; $applicantId,
])
-&gt;update( 'applicant_education', $educationData );


//Remove unselected work experiences
foreach ( $applicant['experiences'] as $experienceId =&gt; $experience ) {
if ( ! isset( $oldWorkExperiences['company'][$experienceId] ) ) {
$workExperiencesRemove[] = $experienceId;
continue;
}

$workExperiencesUpdate = [
'experience_applicant'  =&gt; $applicantId,
'experience_company'    =&gt; $oldWorkExperiences['company'][$experienceId],
'experience_position'   =&gt; $oldWorkExperiences['experience_position'][$experienceId],
'experience_salary'     =&gt; $oldWorkExperiences['experience_salary'][$experienceId],
'experience_country'    =&gt; $oldWorkExperiences['country'][$experienceId],


'experience_from'       =&gt; $oldWorkExperiences['from'][$experienceId],
'experience_to'         =&gt; $oldWorkExperiences['to'][$experienceId],
'experience_years'      =&gt; $oldWorkExperiences['years'][$experienceId],
'experience_createdby'  =&gt; $_SESSION['user_id'],
'experience_updatedby'  =&gt; $_SESSION['user_id'],
'experience_created'    =&gt; date( 'Y-m-d H:i:s', time() ),
'experience_updated'    =&gt; date( 'Y-m-d H:i:s', time() ),
];

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where([
'experience_id' =&gt; $experienceId,
])-&gt;update( 'applicant_experiences', $workExperiencesUpdate );
}

if ( count( $workExperiencesRemove ) &gt; 0) {
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where_in( 'experience_id', $workExperiencesRemove )
-&gt;where([
'experience_applicant' =&gt; $applicantId,
])
-&gt;delete( 'applicant_experiences' );
}

//Add new work experiences
if ( isset( $newWorkExperiences['company'] ) ) {
for ( $i = 0; $i &lt; count( $newWorkExperiences['company'] ); $i++ ) {				
if ( empty( $newWorkExperiences['company'][$i] ) ) {
continue;
}

$workExperiencesData[] = [
'experience_applicant'	=&gt; $applicantId,
'experience_company'	=&gt; $newWorkExperiences['company'][$i],
'experience_position'	=&gt; $newWorkExperiences['experience_position'][$i],
'experience_salary'		=&gt; $newWorkExperiences['experience_salary'][$i],
'experience_country'    =&gt; $newWorkExperiences['country'][$i],


'experience_from'		=&gt; $newWorkExperiences['from'][$i],
'experience_to'			=&gt; $newWorkExperiences['to'][$i],
'experience_years'		=&gt; $newWorkExperiences['years'][$i],
'experience_createdby'	=&gt; $_SESSION['user_id'],
'experience_updatedby'	=&gt; $_SESSION['user_id'],
'experience_created'	=&gt; date( 'Y-m-d H:i:s', time() ),
'experience_updated'	=&gt; date( 'Y-m-d H:i:s', time() ),
];
}

if ( count ( $workExperiencesData ) &gt; 0 ) {
$experienceInserted = $this-&gt;db-&gt;insert_batch('applicant_experiences', $workExperiencesData);
}
}


//Get the updated applicant record
$applicant = $this-&gt;getApplicantById( $applicantId );


//Rollback if transaction fails
if ( ! $this-&gt;db-&gt;trans_status() || ! $applicantUpdated || ! $passportUpdated || ! $educationUpdated) {
$this-&gt;db-&gt;trans_rollback();
return false;
} 

$this-&gt;endProcess();

//Commit transaction
$this-&gt;db-&gt;trans_commit();	

return $applicant;
} 

public function updateApplicantCertificates( $applicantId )
{
$applicant = $this-&gt;getApplicantById( $applicantId );
$post      = $_POST['applicant'];

$certificate     =
$certificateData = [];

$certificate     = $post['certificate'];

//Start Transaction
$this-&gt;db-&gt;trans_begin();

$certificateData = [
'certificate_applicant'          =&gt; $applicantId,
'certificate_tesda'              =&gt; isset( $certificate['tesda'] ),
'certificate_info_sheet'         =&gt; isset( $certificate['info-sheet'] ),
'certificate_authenticated'      =&gt; isset( $certificate['authenticated'] ),
// 'red_ribbon_file_date'			 =&gt; date( 'Y-m-d H:i:s', strtotime( $certificate['red-filed-date'] ) ),
//'red_ribbon_expired_date'		 =&gt; date( 'Y-m-d H:i:s', strtotime( $certificate['red-expired-date'] ) ),
'certificate_authenticated_nbi'  =&gt; isset( $certificate['authenticated-nbi'] ),
'nbi_expired_date'				 =&gt; date( 'Y-m-d H:i:s', strtotime( $certificate['nbi-expired-date'] ) ),
'certificate_insurance'          =&gt; $certificate['insurance'],
'insurance_no'			         =&gt; $certificate['insurance-no'],
'certificate_medical_clinic'     =&gt; $certificate['medical-clinic'],
'certificate_medical_exam_date'  =&gt; date( 'Y-m-d H:i:s', strtotime( $certificate['medical-exam-date'] ) ),
'certificate_medical_result'     =&gt; $certificate['medical-result'],
'certificate_medical_remarks'    =&gt; $certificate['medical-remarks'],
'certificate_medical_expiration' =&gt; date( 'Y-m-d H:i:s', strtotime( $certificate['medical-expiration'] ) ),
'certificate_pdos'               =&gt; isset( $certificate['pdos'] ),
'certificate_pt_result'          =&gt; $certificate['pt-result'],
'certificate_pt_result_date'     =&gt; date( 'Y-m-d H:i:s', strtotime( $certificate['pt-result-date'] ) ),
'certificate_philhealth'         =&gt; isset( $certificate['philhealth'] ),
'certificate_m1b'                =&gt; isset( $certificate['m1b'] ),
'certificate_tor'                =&gt; $certificate['tor'] ,
'certificate_prc_cert'           =&gt; $certificate['prc_cert'] ,
'certificate_prc_id'             =&gt; $certificate['prc_id'] ,
'certificate_prc_rating'         =&gt; $certificate['prc_rating'],
'certificate_coe'                =&gt; $certificate['coe'] ,
'certificate_bc'                 =&gt; $certificate['bc'] ,
'certificate_mc'                 =&gt; $certificate['mc'] ,
'applicant_certificate_no_marriage' =&gt; date( 'Y-m-d H:i:s', strtotime( $certificate['no_marriage'] ) ),
'certificate_saudi_id'           =&gt; $certificate['saudi_ids'] ,
'certificate_prc_take'           =&gt; $certificate['prc_take'],	
'certificate_ksa'         	 	 =&gt; $certificate['ksa'] ,
'certificate_haad'          	 =&gt; $certificate['haad'] ,
'certificate_qatar'          	 =&gt; $certificate['qatar'] ,
'certificate_nclex'          	 =&gt; $certificate['nclex'] ,
'certificate_nclex_exam'          	 =&gt; $certificate['nclex_exam'] ,
'certificate_ielts'          	 =&gt; $certificate['ielts'] ,
'certificate_ielts_exam'         =&gt; $certificate['ielts_exam'] ,
'certificate_ielts_overall'      =&gt; $certificate['ielts_overall'] ,
'certificate_cgfns_exam'      =&gt; $certificate['cgfns_exam'] ,
'certificate_cgfns'          	 =&gt; $certificate['cgfns'] ,
'certificate_cgfns_id'          	 =&gt; $certificate['cgfns_id'] ,
'certificate_vsh'          	 =&gt; $certificate['vsh_exam'] ,
'certificate_dha'          	 =&gt; $certificate['dha'] ,
'certificate_mmr'          	 =&gt; $certificate['mmr'] ,
'medical_fit'          	 =&gt; $certificate['medical_fit'] ,

'omma'          	 =&gt; $certificate['omma'] ,
'omma_date'          	 =&gt;  date( 'Y-m-d', strtotime( $certificate['omma_date']  ) ),
'swab'          	 =&gt; $certificate['swab'] ,
'swab_date'          	 =&gt;  date( 'Y-m-d', strtotime( $certificate['swab_date'] ) ) ,
'polio'          	 =&gt; $certificate['polio'] ,
'polio_date'          	 =&gt;  date( 'Y-m-d', strtotime( $certificate['polio_date'] ) ) ,

'certificate_tesda_date'     =&gt; $certificate['tesda_date'] ,
'certificate_tesda_release'     =&gt; $certificate['tesda_release'] ,
'certificate_tesda_assest'     =&gt; $certificate['certificate_tesda_assest'] ,
'certificate_tesda_name'     =&gt; $certificate['tesda_name'] ,
'certificate_pdos_date'      =&gt; $certificate['pdos_date'] ,
'certificate_pdos_no'      =&gt; $certificate['pdos_no'] ,
'fra_pdos'      =&gt; $certificate['fra_pdos'] ,
'owwa_number'                =&gt; $certificate['owwanumber'] ,
'certificate_owwa'           =&gt; isset( $certificate['owwa'] ),
'certificate_owwa_from'      =&gt; $certificate['owwafrom'] ,
'certificate_owwa_file'      =&gt; $certificate['owwafrom'] ,
'certificate_owwa_to'     	 =&gt; $certificate['owwato'] ,
'localflight'     	 =&gt; $certificate['localflight'] ,
'localflight1'     	 =&gt; $certificate['localflight1'] ,
'localflight2'     	 =&gt; $certificate['localflight2'] ,
'certificate_updatedby'          =&gt; $_SESSION['user_id'],
'certificate_updated'            =&gt; date( 'Y-m-d H:i:s', time() ),        
];
$this-&gt;db-&gt;flush_cache();
$certificateUpdated =
$this-&gt;db-&gt;where([
'certificate_applicant' =&gt; $applicantId,
])-&gt;update( 'applicant_certificate', $certificateData );

//Get the updated applicant record
$applicant = $this-&gt;getApplicantById( $applicantId );

//Rollback if transaction fails
if ( ! $this-&gt;db-&gt;trans_status() || ! $certificateUpdated) {
$this-&gt;db-&gt;trans_rollback();
return false;
} 

$this-&gt;endProcess();

//Commit transaction
$this-&gt;db-&gt;trans_commit();	

return $applicant;
}






public function updateApplicantRequirements( $applicantId )
{
	

	
	
	
	
	
	
$applicant = $this-&gt;getApplicantById( $applicantId );
$post      = $_POST['applicant'];

$requirement      =
$requirementsData = [];

$requirement      = $post['requirement'];

//Start Transaction
$this-&gt;db-&gt;trans_begin();

$requirementsData = [
'requirement_applicant'           =&gt; $applicantId,
'requirement_trade_test'          =&gt; isset( $requirement['trade-test'] ),
'requirement_trade_remarks'		  =&gt; $requirement['tesda-remarks'],
'requirement_picture_status'      =&gt; $requirement['picture-status'],
//'requirement_coe'                 =&gt; isset( $requirement['coe'] ),
'requirement_school_records'      =&gt; $requirement['school-records'],
'requirement_visa'                =&gt; isset( $requirement['visa'] ),            
'requirement_visa_date'           =&gt; date( 'Y-m-d', strtotime( $requirement['visa-date'] ) ),
'requirement_visa_stamp'           =&gt; date( 'Y-m-d', strtotime( $requirement['visa-stamp'] ) ),
'requirement_visa_release_date'   =&gt; date( 'Y-m-d', strtotime( $requirement['visa-release-date'] ) ),
'requirement_visa_expiration'     =&gt; date( 'Y-m-d', strtotime( $requirement['visa-expiration'] ) ),
'requirement_oec_number'          =&gt; $requirement['oec-number'],
'requirement_oec_submission_date' =&gt; date( 'Y-m-d', strtotime( $requirement['oec-submission-date'] ) ),
'requirement_oec_release_date'    =&gt; date( 'Y-m-d', strtotime( $requirement['oec-release-date'] ) ),
'requirement_owwa_certificate'    =&gt; $requirement['owwa-certificate'],
'requirement_owwa_schedule'       =&gt; date( 'Y-m-d', strtotime( $requirement['owwa-schedule'] ) ),
'requirement_contract'            =&gt; date( 'Y-m-d', strtotime( $requirement['contract'] ) ),
'requirement_mofa'                =&gt; $requirement['mofa'],
'requirement_job_offer'           =&gt; $requirement['job-offer'],
'requirement_job_received'           =&gt; date( 'Y-m-d', strtotime( $requirement['jo-received'] ) ),
'requirement_job_accepted'           =&gt; date( 'Y-m-d', strtotime( $requirement['jo-accept'] ) ),
'offer_letter'			          =&gt; $requirement['offer-letter'],
'requirement_ticket'              =&gt; $requirement['ticket'],
'ticket_no'			              =&gt; $requirement['ticket-no'],
'covidme'			              =&gt; $requirement['covidme'],
'covid_name'			              =&gt; $requirement['covid_name'],
'covid_date'			              =&gt; $requirement['covid_date'],
'covid_date2'			              =&gt; $requirement['covid_date2'],
'covid_loc'			              =&gt; $requirement['covid_loc'],
'covid_yellow'			              =&gt; $requirement['covid_yellow'],
'covid_cert'			              =&gt; $requirement['covid_cert'],
'covidb1'			              =&gt; $requirement['covidb1'],
'covidb2'			              =&gt; $requirement['covidb2'],
'covidb3'			              =&gt; $requirement['covidb3'],

'flight_date'			          =&gt; date( 'Y-m-d', strtotime( $requirement['flight-date'] ) ),
'ticket_remarks'			      =&gt; $requirement['flight-remarks'],
'requirement_offer_salary'        =&gt; $requirement['offer-salary'],
'requirement_remarks'             =&gt; $requirement['remarks'],
'requirement_visa_no'             =&gt; $requirement['visa-no'],
'requirement_visa_category'        =&gt; $requirement['visa-category'],
'applicant_requirement_visaremarks'        =&gt; $requirement['visaremarks'],
'stamped_kuw'        =&gt; $requirement['stamped_kuw'],
'applicant_requirement_ecode'        =&gt; $requirement['ecode'],
'applicant_requirement_paid'        =&gt; $requirement['paid'],
'applicant_requirement_rfp'        =&gt; $requirement['rfp'],
'transnum'        =&gt; $requirement['transnum'],
'ticket_plus'        =&gt; $requirement['ticket_plus'],
'applicant_requirement_lastpage'        =&gt; $requirement['lastpage'],
'applicant_requirement_mol'        =&gt; $requirement['mol'],
'applicant_requirement_peos'        =&gt; $requirement['peos'],
'applicant_requirement_peosd'        =&gt; $requirement['peosd'],
'applicant_requirement_ereg'        =&gt; $requirement['ereg'],
'applicant_requirement_eregd'        =&gt; $requirement['eregd'],
'applicant_requirement_kawala'        =&gt; $requirement['kawala'],
'applicant_requirement_oec_expired'  =&gt; $requirement['oecexpired'],
'requirement_contract_sign'  =&gt; $requirement['sign'],
'vfs'  =&gt; $requirement['vfs'],
'requirement_musaned_encoded'			          =&gt; date( 'Y-m-d', strtotime( $requirement['requirement_musaned_encoded'] ) ),
'requirement_musaned_approved'			          =&gt; date( 'Y-m-d', strtotime( $requirement['requirement_musaned_approved'] ) ),
'requirement_musaned_sign'			          =&gt; date( 'Y-m-d', strtotime( $requirement['requirement_musaned_sign'] ) ),

'requirement_updatedby'           	=&gt; $_SESSION['user_id'],
'requirement_updated'             	=&gt; date( 'Y-m-d H:i:s', time() ),
'visa_duration'						=&gt; $requirement['visa_duration'],

];





$this-&gt;db-&gt;flush_cache();
$requirementsUpdated =
$this-&gt;db-&gt;where([
'requirement_applicant' =&gt; $applicantId,
])-&gt;update( 'applicant_requirement', $requirementsData );

//Update applicant job offer
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where([
'applicant_id' =&gt; $applicantId,
])-&gt;update( 'applicant', [ 'applicant_job' =&gt; $requirement['job-offer'] ] );



//Update applicant job offer
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where([
'applicant_id' =&gt; $applicantId,
])-&gt;update( 'applicant', [ 'applicant_fb' =&gt; $requirement['applicant_fb'] ] );


//Update applicant job offer
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where([
'applicant_id' =&gt; $applicantId,
])-&gt;update( 'applicant', [ 'applicant_employer_idno' =&gt; $requirement['applicant_employer_idno'] ] );



//Update applicant job offer
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where([
'applicant_id' =&gt; $applicantId,
])-&gt;update( 'applicant', [ 'applicant_employer_address' =&gt; $requirement['applicant_employer_address'] ] );

//Update applicant job offer
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where([
'applicant_id' =&gt; $applicantId,
])-&gt;update( 'applicant', [ 'applicant_employer_number' =&gt; $requirement['employer_number'] ] );



//Update applicant job offer
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where([
'applicant_id' =&gt; $applicantId,
])-&gt;update( 'applicant', [ 'sub_employer' =&gt; $requirement['sub_employer'] ] );




//createBilling
$billFine = true;
// if ( ! $applicant['requirement_job_offer'] && $requirement['job-offer'] &gt; 0 ) {
// 	$this-&gt;load-&gt;model( 'm_billing' );
// 	$billFine = ( new m_billing )-&gt;createBilling( $applicantId );
// }

if ( $requirement['job-offer'] &gt; 0 ) {

$this-&gt;load-&gt;model( 'm_billing' );

if (  ! ( new m_billing )-&gt;hasBilling( $applicantId ) ) {
$billFine = ( new m_billing )-&gt;createBilling( $applicantId );
}
}		

//Get the updated applicant record
$applicant = $this-&gt;getApplicantById( $applicantId );

//Rollback if transaction fails
if ( ! $this-&gt;db-&gt;trans_status() || ! $requirementsUpdated || ! $billFine ) {
$this-&gt;db-&gt;trans_rollback();
return false;
} 

$this-&gt;endProcess();

//Commit transaction
$this-&gt;db-&gt;trans_commit();	

return $applicant;
}

public function uploadApplicantFile( $applicantId, $file )
{
$post = $_POST['applicant']['file'];

ini_set('memory_limit', '100M');
ini_set('post_max_size', '100M');
ini_set('upload_max_filesize', '100M');

switch ( $file['error'] ) {
case UPLOAD_ERR_OK:

break;

case UPLOAD_ERR_NO_FILE:  

Message::addWarning('No file sent');
return false;   

case UPLOAD_ERR_INI_SIZE:
case UPLOAD_ERR_FORM_SIZE:

Message::addWarning('Exceeded filesize limit');
return false;  

default:

Message::addWarning('Unknown errors occur.');
return false;
}

$fileName = time().'-'.$file['name'];

$uploadDir     = __DIR__.'/../../files/applicant/uploaded/';
$applicantDir  = $uploadDir . str_pad( $applicantId, 7, '0', STR_PAD_LEFT ) . '/';
$applicantPath = 'files/applicant/uploaded/'.str_pad( $applicantId, 7, '0', STR_PAD_LEFT ) . '/';

if ( ! is_dir( $applicantDir ) ) {
mkdir( $applicantDir, 0777, true );
}

//Make directory rewritable
chmod( $applicantDir , 0777);

$uploaded = move_uploaded_file( $file['tmp_name'], $applicantDir . $fileName );

if ( ! $uploaded ) {
Message::addWarning('File cannot be upload. Please check the file.');
return false;
}

$fileData = [
'file_applicant' =&gt; $applicantId,
'file_name'      =&gt; $post['name'],
'file_type'      =&gt; $post['type'],
'file_size'      =&gt; $file['size'],
'file_mime'      =&gt; $file['type'],
'file_path'      =&gt; $applicantPath . $fileName,
'file_status'    =&gt; 1,
'file_createdby' =&gt; $_SESSION['user_id'],
'file_created'   =&gt; date( 'Y-m-d H:i:s', time() ),
];

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;insert( 'applicant_files', $fileData );
$fileId = $this-&gt;db-&gt;insert_id();

$file = $this-&gt;getApplicantFileById( $fileId );

return $file;
}

public function updateApplicantStatus( $applicantId )
{
$applicant = $this-&gt;getApplicantById( $applicantId );
$post      = $_POST['applicant'];

$log       =
$logData   = [];

$log      = $post['status'];

$logInserted = $this-&gt;addLog( $log['remarks'], $applicantId, $log['employer'], $log['status'], date( 'Y-m-d', strtotime( $log['date'] ) ) ,$log['repat_date']);

//Update applicant status
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where([
'applicant_id' =&gt; $applicantId,
])
-&gt;update( 'applicant', [
'applicant_employer' =&gt; $log['employer'],
'applicant_status'   =&gt; $log['status'],
'sub_status'	     =&gt; $log['substatus'],
'applicantNumber'    =&gt; $log['applicant-no'],
'applicant_remarks'    =&gt; $log['remarks'],
'optional_statuses_id'  =&gt; $log['optionStatus'],
'sub_employer'    =&gt; $log['sub_employer'],
'applicant_employer_number'   =&gt; $log['employer_number'],
'applicant_employer_address'   =&gt; $log['applicant_employer_address'],
'applicant_employer_idno'   =&gt; $log['applicant_employer_idno'],
'applicant_fb'   =&gt; $log['applicant_fb'],
'is_repat'    =&gt; isset($log['is_repat']) ? $log['is_repat'] : "",
'repat_date'    =&gt; isset($log['repat_date']) ? $log['repat_date'] : "",
]);

switch ( $log['status'] ) {

case $this-&gt;status['Reserved']:

//Start Transaction
$this-&gt;db-&gt;trans_begin();

//Delete previous selected record
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where([
'reservation_applicant' =&gt; $applicantId,
])-&gt;delete( 'employer_reservation' );

$selectionData = [
'reservation_employer'   =&gt; $log['employer'],
'reservation_applicant'  =&gt; $applicantId,
'reservation_expiration' =&gt; date( 'Y-m-d', strtotime( ' +'.self::RESERVED_DAYS_EXPIRATION.' days' ) ),
'reservation_status'     =&gt; 1,
'reservation_remarks'    =&gt; '',
'reservation_date'       =&gt; fdate( 'Y-m-d', $log['date'] ),
'reservation_createdby'  =&gt; $_SESSION['user_id'],
'reservation_updatedby'  =&gt; $_SESSION['user_id'],
'reservation_updated'    =&gt; date( 'Y-m-d H:i:s', time() ),
'reservation_created'    =&gt; date( 'Y-m-d H:i:s', time() ),
];

$this-&gt;db-&gt;flush_cache();
$selectionInserted = $this-&gt;db-&gt;insert( 'employer_reservation', $selectionData );

//Commit transaction
$this-&gt;db-&gt;trans_commit();

break;

case $this-&gt;status['Selected']:

//Start Transaction
$this-&gt;db-&gt;trans_begin();

//Delete previous selected record
$this-&gt;db-&gt;flush_cache();
$selected = $this-&gt;db-&gt;get_where('employer_selected', [
'selected_applicant' =&gt; $applicantId,
])-&gt;row_array();

if ( empty( $selected ) ) {

$selectedData = [
'selected_employer'   =&gt; $log['employer'],
'selected_applicant'  =&gt; $applicantId,
'selected_date'       =&gt; fdate( 'Y-m-d', $log['date'] ),
'selected_remarks'    =&gt; $log['remarks'],
'selected_createdby'  =&gt; $_SESSION['user_id'] ,
'selected_updatedby'  =&gt; $_SESSION['user_id'] ,
'selected_updated'    =&gt; date( 'Y-m-d H:i:s', time() ),
'selected_created'    =&gt; date( 'Y-m-d H:i:s', time() ),
];

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;insert( 'employer_selected', $selectedData );

} else {

$selectedData = [
'selected_employer'   =&gt; $log['employer'],
'selected_date'       =&gt; fdate( 'Y-m-d', $log['date'] ),
'selected_remarks'    =&gt; $log['remarks'],
'selected_updatedby'  =&gt; $_SESSION['user_id'] ,
'selected_updated'    =&gt; date( 'Y-m-d H:i:s', time() ),
];

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where([
'selected_id' =&gt; $selected['selected_id'],
])
-&gt;update( 'employer_selected', $selectedData );

}

//Rollback if transaction fails
if ( ! $this-&gt;db-&gt;trans_status() ) {
$this-&gt;db-&gt;trans_rollback();
return false;
}

//Commit transaction
$this-&gt;db-&gt;trans_commit();

break;

case $this-&gt;status['Deployed']:

$this-&gt;db-&gt;flush_cache();

//Add to deployed list
$deployedData = [
'deployed_applicant'  =&gt; $applicantId,
'deployed_employer'   =&gt; $applicant['applicant_employer'],
'deployed_job'        =&gt; $applicant['applicant_job'],
'deployed_country'    =&gt; $applicant['applicant_preferred_country'],
'deployed_position'   =&gt; $applicant['job_position'],
'deployed_salary'     =&gt; (float) $applicant['applicant_expected_salary'],
'deployed_remarks'    =&gt; 'Applicant has been added to deployed list.',
'deployed_date'       =&gt; fdate( 'Y-m-d', $log['date'] ),
'deployed_createdby'  =&gt; $_SESSION['user_id'],
'deployed_updatedby'  =&gt; $_SESSION['user_id'],
'deployed_created'    =&gt; date( 'Y-m-d H:i:s', time() ),
'deployed_updated'    =&gt; date( 'Y-m-d H:i:s', time() ),
];

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;insert( 'deployed', $deployedData);

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;set('`job_occupied`', '`job_occupied` + 1', FALSE)
-&gt;where([
'job_id' =&gt; $applicant['applicant_job'],
])-&gt;update( 'job' );

//create Commission for recruitment agent
$this-&gt;load-&gt;model( 'm_commission_recruitment_agent' );
( new m_commission_recruitment_agent )-&gt;createCommission( $applicantId );

if ( $applicant['applicant_employer'] ) {
$this-&gt;load-&gt;model( 'm_commission_marketing_agency' );
( new m_commission_marketing_agency )-&gt;createCommission( $applicant['applicant_employer'], $applicantId );

$this-&gt;load-&gt;model( 'm_commission_marketing_agent' );
( new m_commission_marketing_agent )-&gt;createCommission( $applicant['applicant_employer'], $applicantId );
}

break;
}

$applicant = [];

if ( $logInserted ) {
$applicant = $this-&gt;getApplicantById( $applicantId );
}

return $applicant;
}

public function reserveApplicant( $applicantId, $employerId )
{
$selectionData = $applicantData = [];

$selectionData = [
'reservation_employer'   =&gt; $employerId,
'reservation_applicant'  =&gt; $applicantId,
'reservation_expiration' =&gt; date( 'Y-m-d', strtotime( ' +'.self::RESERVED_DAYS_EXPIRATION.' days' ) ),
'reservation_status'     =&gt; 1,
'reservation_remarks'    =&gt; '',
'reservation_date'       =&gt; date( 'Y-m-d', time() ),
'reservation_createdby'  =&gt; $_SESSION['employer']['user']['user_id'],
'reservation_updatedby'  =&gt; $_SESSION['employer']['user']['user_id'],
'reservation_updated'    =&gt; date( 'Y-m-d H:i:s', time() ),
'reservation_created'    =&gt; date( 'Y-m-d H:i:s', time() ),
];

$this-&gt;db-&gt;flush_cache();
$selectionInserted = $this-&gt;db-&gt;insert( 'employer_reservation', $selectionData );

//$logInserted = $this-&gt;addLog( $log['remarks'], $applicantId, $log['employer'], $log['status'], date( 'Y-m-d', strtotime( $log['date'] ) ) );

$applicantData = [
'applicant_status'   =&gt; $this-&gt;status['Reserved'],
'applicant_employer' =&gt; $employerId,
'applicant_job'      =&gt; 0,
];

$this-&gt;db-&gt;flush_cache();
$applicantUpdated =
$this-&gt;db-&gt;where([
'applicant_id' =&gt; $applicantId,
])
-&gt;update( 'applicant', $applicantData);

if ( $selectionInserted && $applicantUpdated ) {
$this-&gt;addLog('Applicant was reserved', $applicantId, $employerId, $this-&gt;status['Reserved']);
return true;
}

return false;
}

public function delete_multipleLineup( $applicantId){
$this-&gt;db-&gt;delete('multiple_lineups', array('applicant_id' =&gt; $applicantId));  
}

public function extendReserveApplicant( $reservationId, $daysToExtend = self::RESERVED_DAYS_EXPIRATION, $remarks = '' )
{
$reservationData = [
'reservation_expiration' =&gt; date( 'Y-m-d', strtotime( ' +'.$daysToExtend.' days' ) ),
'reservation_remarks'    =&gt; $remarks,
'reservation_updatedby'  =&gt; $_SESSION['user_id'],
'reservation_updated'    =&gt; date( 'Y-m-d', time() ),
];

$this-&gt;db-&gt;flush_cache();
$reserveUpdated = 
$this-&gt;db-&gt;where([
'reservation_id' =&gt; $reservationId,
])-&gt;update( 'employer_reservation', $reservationData );

return $reserveUpdated;
}

public function unReserveApplicant( $applicantId, $employerId )
{
//Delete reservation entry
$this-&gt;db-&gt;flush_cache();
$selectionDeleted = 
$this-&gt;db-&gt;where([
'reservation_employer'  =&gt; $employerId,
'reservation_applicant' =&gt; $applicantId,
])-&gt;delete( 'employer_reservation' );

//Revert applicant status 
$applicantData = [
'applicant_status'   =&gt; $this-&gt;status['Available'],
'applicant_employer' =&gt; 0,
'applicant_job'       =&gt; 0,
];

$this-&gt;db-&gt;flush_cache();
$applicantUpdated = 
$this-&gt;db-&gt;where([
'applicant_id' =&gt; $applicantId,
])-&gt;update( 'applicant', $applicantData );

$this-&gt;addLog('Unreserved the applicant.', $applicantId, $employerId, $this-&gt;status['Available'], date( 'Y-m-d', time() ) );

return $selectionDeleted && $applicantUpdated;
}

public function selectApplicant( $applicantId, $employerId, $remarks = '' )
{
$selectedData = $applicantData = [];

$this-&gt;db-&gt;flush_cache();
$selected = 
$this-&gt;db-&gt;get_where( 'employer_selected', [ 
'selected_applicant' =&gt; $applicantId,
'selected_employer'  =&gt; $employerId,
]);

if ( empty( $selected ) ) { 
return true;
}

$selectedData = [
'selected_employer'   =&gt; $employerId,
'selected_applicant'  =&gt; $applicantId,
'selected_remarks'    =&gt; $remarks,
'selected_createdby'  =&gt; isset( $_SESSION['employer']['user'] ) 
? $_SESSION['employer']['user']['user_id'] 
: $_SESSION['user_id'],
'selected_updatedby'  =&gt; isset( $_SESSION['employer']['user'] ) 
? $_SESSION['employer']['user']['user_id'] 
: $_SESSION['user_id'],
'selected_updated'    =&gt; date( 'Y-m-d H:i:s', time() ),
'selected_created'    =&gt; date( 'Y-m-d H:i:s', time() ),
];

$this-&gt;db-&gt;flush_cache();
$selectedInserted = $this-&gt;db-&gt;insert( 'employer_selected', $selectedData );

$applicantData = [
'applicant_status' =&gt; $this-&gt;status['Selected'],
];

$this-&gt;db-&gt;flush_cache();
$applicantUpdated =
$this-&gt;db-&gt;where([
'applicant_id' =&gt; $applicantId,
])
-&gt;update( 'applicant', $applicantData);

return $selectedInserted && $applicantUpdated;
}

public function unSelectApplicant( $applicantId, $employerId )
{
//Remove selection entry
$this-&gt;db-&gt;flush_cache();
$selectionDeleted = 
$this-&gt;db-&gt;where([
'selected_employer'  =&gt; $employerId,
'selected_applicant' =&gt; $applicantId,
])-&gt;delete( 'employer_selected' );

$applicantData = [
'applicant_status' =&gt; $this-&gt;status['Reserved'],
'applicant_job'    =&gt; 0,
];

$this-&gt;db-&gt;flush_cache();
$applicantUpdated =
$this-&gt;db-&gt;where([
'applicant_id' =&gt; $applicantId,
])-&gt;update( 'applicant', $applicantData );

return $applicantUpdated;
}

public function isReserved( $applicantId, $employerId )
{
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;select()
-&gt;from( 'applicant a' )
-&gt;join( 'employer_reservation er', 'er.reservation_applicant = a.applicant_id' )
-&gt;where([
'er.reservation_employer'  =&gt; $employerId,
'er.reservation_applicant' =&gt; $applicantId,
'er.reservation_status'    =&gt; 1,
'a.applicant_status'       =&gt; $this-&gt;status['Reserved'],
]);

return $this-&gt;db-&gt;count_all_results() &gt; 0;
}

public function archiveApplicant( $applicantId )
{
$strQuery = "INSERT INTO `archive_applicant` SELECT *,? FROM `applicant` WHERE `applicant_id` = ?";
/*
$this-&gt;db-&gt;flush_cache();
$archived = 
$this-&gt;db-&gt;query( $strQuery, [
$_SESSION['user_id'],
$applicantId,
]);
*/
$this-&gt;db-&gt;flush_cache();
$deleted = $this-&gt;db-&gt;where([
'applicant_id' =&gt; $applicantId,
])-&gt;delete( 'applicant');

return /*$archived &&*/ $deleted;
}

public function archiveApplicantFile( $applicantId, $fileId )
{
$file = $this-&gt;getApplicantFileById( $fileId );

if ( empty( $file ) ) {
Message::addInfo( 'File is not longer available.' );
return false;
}

$filePath         = $file['file_path'];
$fileName         = pathinfo( $file['file_path'], PATHINFO_BASENAME );
$applicantFolder  = end( explode( '/', dirname( $file['file_path'] ) ) );
$applicantDir     = __DIR__.'/../../files/archive/applicant/uploaded/'.$applicantFolder.'/';

//Make rewritable directory
if ( ! is_dir( $applicantDir ) ) {
mkdir( $applicantDir, 0777, true );
}

//Make directory rewritable
chmod( $applicantDir , 0777);

$archived = rename( $file['file_path'], $applicantDir . $fileName );

if ( ! $archived ) {
Message::addWarning('File is unaccessible. Please contact your administrator.');
return false;
}

$this-&gt;db-&gt;flush_cache();
$fileUpdated = 
$this-&gt;db-&gt;where([
'file_id' =&gt; $fileId,
])-&gt;update( 'applicant_files', [ 'file_status' =&gt; 0 ] );

return $fileUpdated;
}

public function moveToAvailable( $applicantIds, $options = [] )
{
//Delete from reservation
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where_in( 'reservation_applicant', $applicantIds )
-&gt;delete( 'employer_reservation' );

//Delete from selected
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where_in( 'selected_applicant', $applicantIds )
-&gt;delete( 'employer_selected' );

foreach ( $applicantIds as $applicantId ) {
$this-&gt;clearApplicantBilling( $applicantId );
}

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where_in( 'applicant_id', $applicantIds );

$this-&gt;setDBQueryOptions( $options );

$this-&gt;db-&gt;update( 'applicant', [
'applicant_status'   =&gt; $this-&gt;status['Available'],
'applicant_employer' =&gt; 0,
'applicant_job'      =&gt; 0,
]);

$affectedApplicants = $this-&gt;db-&gt;affected_rows(); 

foreach ( $applicantIds as $applicantId ) {
$this-&gt;addLog('Applicant has been moved back to list of available.', $applicantId, 0, 1, date( 'Y-m-d', time() ) );
}

return $affectedApplicants;
}

public function clearApplicantBilling( $applicantId )
{
//Get bill
$this-&gt;db-&gt;from( 'bill' )
-&gt;where([
'bill_applicant' =&gt; $applicantId,
]);

$bill = $this-&gt;db-&gt;get()-&gt;row_array();

if ( empty( $bill ) ) {
return false;
}

//Get all ORs
$ors = [];


$this-&gt;db-&gt;from( 'or' )
-&gt;where([
'or_applicant' =&gt; $applicantId,
]);

$orRows = $this-&gt;db-&gt;get()-&gt;result_array();
$orRows = $this-&gt;indexArray( $orRows, 'or_number' );
$ors    = array_merge( $ors, array_keys( $orRows ) );


$this-&gt;db-&gt;from( 'bill_payment_applicant' )
-&gt;where([
'payment_bill'      =&gt; $bill['bill_id'],
'payment_applicant' =&gt; $applicantId,
]);

$orRows = $this-&gt;db-&gt;get()-&gt;result_array();
$orRows = $this-&gt;indexArray( $orRows, 'payment_or' );
$ors    = array_merge( $ors, array_keys( $orRows ) );

$this-&gt;db-&gt;from( 'paidall_employer_applicants' )
-&gt;where([
'paidall_bill'      =&gt; $bill['bill_id'],
'paidall_applicant' =&gt; $applicantId,
]);

$orRows = $this-&gt;db-&gt;get()-&gt;result_array();
$orRows = $this-&gt;indexArray( $orRows, 'paidall_or' );
$ors    = array_merge( $ors, array_keys( $orRows ) );


$this-&gt;db-&gt;from( 'payment_employer_detail' )
-&gt;where([
'detail_bill'      =&gt; $bill['bill_id'],
'detail_applicant' =&gt; $applicantId,
]);

$orRows = $this-&gt;db-&gt;get()-&gt;result_array();
$orRows = $this-&gt;indexArray( $orRows, 'detail_or' );
$ors    = array_merge( $ors, array_keys( $orRows ) );


$this-&gt;db-&gt;from( 'payment_worker_detail' )
-&gt;where([
'detail_bill'      =&gt; $bill['bill_id'],
'detail_applicant' =&gt; $applicantId,
]);

$orRows = $this-&gt;db-&gt;get()-&gt;result_array();
$orRows = $this-&gt;indexArray( $orRows, 'detail_or' );
$ors    = array_merge( $ors, array_keys( $orRows ) );


$orsFiltered = [];
foreach ($ors as $or) {
if ( ! in_array( $or , $orsFiltered) ) {
$orsFiltered[] = $or;	
}    		
}

//Delete transaction history
$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where([
'bill_id' =&gt; $bill['bill_id'],
])
-&gt;delete( 'bill' );

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where([
'detail_bill' =&gt; $bill['bill_id'],
])
-&gt;delete( 'bill_detail' );

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where([
'payment_bill' =&gt; $bill['bill_id'],
])
-&gt;delete( 'bill_payment_applicant' );


$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where([
'detail_bill' =&gt; $bill['bill_id'],
])
-&gt;delete( 'payment_worker_detail' );

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where([
'detail_bill' =&gt; $bill['bill_id'],
])
-&gt;delete( 'payment_employer_detail' );


$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where_in( 'payment_or', $orsFiltered )
-&gt;delete( 'bill_payment_employer' );

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where_in( 'paidall_or', $orsFiltered )
-&gt;delete( 'paidall_employer_applicants' );

$this-&gt;db-&gt;flush_cache();
$this-&gt;db-&gt;where_in( 'or_number', $orsFiltered )
-&gt;delete( 'or' );

return true;
}

/* Protected Methods
-------------------------------*/
protected function uploadPhoto( $applicantId, $file )
{
$dirDestination = DIR_UPLOADS.'applicant'.DIRECTORY_SEPARATOR;

$fileName = time().'-'.$applicantId.'.'.pathinfo( $file['name'], PATHINFO_EXTENSION );

if ( ! is_writable( $dirDestination ) ) {
chmod( $dirDestination, 0777);	
}	

$uploaded = move_uploaded_file( $file['tmp_name'], $dirDestination.$fileName );

if ( $uploaded ) {
return $fileName;
}

return false;
}

protected function endProcess()
{
if (isset($_SESSION['post']['admin']['applicants/add'])) {
unset($_SESSION['post']['admin']['applicants/add']);
}

if (isset($_SESSION['post']['admin']['applicants/review'])) {
unset($_SESSION['post']['admin']['applicants/review']);
}

if (isset($_SESSION['post']['public']['applicants/registration'])) {
unset($_SESSION['post']['public']['applicants/registration']);
}

return $this;		
}

/* Private Methods
-------------------------------*/
private function rawApplicantPassport( array $elements = [] )
{
$passportData = [
'passport_applicant'   =&gt; null,
'passport_number'      =&gt; null,
'passport_issue'       =&gt; null,
'passport_issue_place' =&gt; null,
'passport_expiration'  =&gt; null,
'passport_createdby'   =&gt; isset( $_SESSION['user_id'] )
  ? $_SESSION['user_id']
  : 0,
'passport_updatedby'   =&gt; isset( $_SESSION['user_id'] )
  ? $_SESSION['user_id']
  : 0,
'passport_created'     =&gt; date( 'Y-m-d H:i:s', time() ),
'passport_updated'     =&gt; date( 'Y-m-d H:i:s', time() ),
];

$passportData = array_merge( $passportData, $elements );

return $passportData;
}

private function rawApplicantVisa( array $elements = [] )
{
$visaData = [
'visa_applicant'   =&gt; null,
'visa_date'        =&gt; null,
'visa_release'     =&gt; null,
'visa_expiration'  =&gt; null,
'visa_status'      =&gt; null,
'visa_createdby'   =&gt; isset( $_SESSION['user_id'] )
? $_SESSION['user_id']
: 0,
'visa_updatedby'   =&gt; isset( $_SESSION['user_id'] )
? $_SESSION['user_id']
: 0,
'visa_created'     =&gt; date( 'Y-m-d H:i:s', time() ),
'visa_updated'     =&gt; date( 'Y-m-d H:i:s', time() ),
];

$visaData = array_merge( $visaData, $elements );

return $visaData;
}

private function rawApplicantCertificate( array $elements = [] )
{
$certificateData = [
'certificate_applicant'           =&gt; null,
'certificate_tesda'               =&gt; null,
'certificate_info_sheet'          =&gt; null,
'certificate_authenticated'       =&gt; null,
'certificate_authenticated_nbi'   =&gt; null,
'certificate_insurance'           =&gt; null,
'certificate_medical_clinic'      =&gt; null,
'certificate_medical_exam_date'   =&gt; null,
'certificate_medical_remarks'     =&gt; null,
'certificate_medical_expiration'  =&gt; null,
'certificate_pdos'                =&gt; null,

'certificate_philhealth'          =&gt; null,
'certificate_m1b'                 =&gt; null,
'certificate_createdby'           =&gt; isset( $_SESSION['user_id'] )
			 ? $_SESSION['user_id']
			 : 0,
'certificate_updatedby'           =&gt; isset( $_SESSION['user_id'] )
			 ? $_SESSION['user_id']
			 : 0,
'certificate_created'             =&gt; date( 'Y-m-d H:i:s', time() ),
'certificate_updated'             =&gt; date( 'Y-m-d H:i:s', time() ),            
];

$certificateData = array_merge( $certificateData, $elements );

return $certificateData;
}

private function rawApplicantRequirements( array $elements = [] )
{
$requirementsData = [
'requirement_applicant'           =&gt; null,
'requirement_visa'                =&gt; null,
'requirement_visa_date'           =&gt; null,
'requirement_visa_release_date'   =&gt; null,
'requirement_visa_expiration'     =&gt; null,
'requirement_oec_number'          =&gt; null,
'requirement_oec_submission_date' =&gt; null,
'requirement_oec_release_date'    =&gt; null,
'requirement_owwa_certificate'    =&gt; null,
'requirement_owwa_schedule'       =&gt; null,
'requirement_mofa'                =&gt; null,
'requirement_job_offer'           =&gt; null,
'requirement_remarks'             =&gt; null,
'requirement_createdby'           =&gt; isset( $_SESSION['user_id'] )
			 ? $_SESSION['user_id']
			 : 0,
'requirement_updatedby'           =&gt; isset( $_SESSION['user_id'] )
			 ? $_SESSION['user_id']
			 : 0,
'requirement_created'             =&gt; date( 'Y-m-d H:i:s', time() ),
'requirement_updated'             =&gt; date( 'Y-m-d H:i:s', time() ),            
];

$requirementsData = array_merge( $requirementsData, $elements );

return $requirementsData;
}

public function cyd_get_applicant_requirement(){
$return = array();
//requirement_oec_number search
$this-&gt;db-&gt;flush_cache();
$query = $this-&gt;db-&gt;get('applicant_requirement');
$results = $query-&gt;result();

foreach($results as $per_result) {
$return[$per_result-&gt;requirement_applicant] = $per_result;
}
return $return;
}

public function cyd_get_all_sub_position(){
$array_return = array();
$position_array_return = array();

$this-&gt;db-&gt;flush_cache();
$position_query = $this-&gt;db-&gt;get('position');
$position_results = $position_query-&gt;result();
foreach ($position_results as $position_value) {
$position_array_return[$position_value-&gt;position_id] = $position_value-&gt;position_name;
}

$this-&gt;db-&gt;flush_cache();
$query = $this-&gt;db-&gt;get('applicant_preferred_positions');
$results = $query-&gt;result();

foreach ($results as $value) {
if(!isset($array_return[$value-&gt;position_applicant]))
$array_return[$value-&gt;position_applicant] = ' ';

if(isset($position_array_return[$value-&gt;position_position])){
if(strlen($array_return[$value-&gt;position_applicant]) &lt; 4)
$array_return[$value-&gt;position_applicant] .= $position_array_return[$value-&gt;position_position];
else
$array_return[$value-&gt;position_applicant] .= ', '.$position_array_return[$value-&gt;position_position];
}

}
return $array_return;
}

//add all application table to an array
public function cyd_get_applicants_raw(){
$applicant_array_return = array();
$applicant_query = $this-&gt;db-&gt;get('applicant');
$applicant_results = $applicant_query-&gt;result();
foreach ($applicant_results as $applicant_value) {
$applicant_array_return[$applicant_value-&gt;applicant_id] = $applicant_value;
}
return $applicant_array_return;
}

public function cyd_get_applicant_certificate_raw(){
$applicant_array_return = array();
$applicant_query = $this-&gt;db-&gt;get('applicant_certificate');
$applicant_results = $applicant_query-&gt;result();
foreach ($applicant_results as $applicant_value) {
$applicant_array_return[$applicant_value-&gt;certificate_applicant] = $applicant_value;
}
return $applicant_array_return;
}

public function cyd_applicant_requirement_raw(){
$applicant_array_return = array();
$applicant_query = $this-&gt;db-&gt;get('applicant_requirement');
$applicant_results = $applicant_query-&gt;result();
foreach ($applicant_results as $applicant_value) {
$applicant_array_return[$applicant_value-&gt;requirement_applicant] = $applicant_value;
}
return $applicant_array_return;
}

public function denyHit($applicantId){

$data = array(
'hit_status' =&gt; 'cleared',
'hit_date' =&gt; (date('Y')+1).'-0'.rand(1,9).'-01'
);

$this-&gt;db-&gt;where('applicant_id', $applicantId);
$this-&gt;db-&gt;update('applicant', $data); 

return 'Applicants Hit Denied';
}

public function allTrainingBranches(){

$query = $this-&gt;db-&gt;get('training_branches');
$allbranch = $query-&gt;result_array();
$result = [];
foreach ($allbranch as $branch) {
$result[$branch['id']] = $branch['branch_name'];
}
return $result;
}

public function get_skill_cyd($applicant_id){

$query = $this-&gt;db-&gt;get_where('applicant_skills_cyds', array('applicant_id' =&gt; $applicant_id));
return $query-&gt;result();
}
}
