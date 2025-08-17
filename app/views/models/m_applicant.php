<?php //-->
use \Application\Message as Message;

/*
* This file is part a custom application package.
* (c) 2014 Clemente Qui���ones Jr. <clemquinones@gmail.com>
* (c) 2015 Cyd Dalupan <cydmdalupan@gmail.com>
*/

/**
* Core Knowledge of all pages
*
* @author     Clemente Qui���ones Jr. <clemquinones@gmail.com>
* @author     Cyd Dalupan <cydmdalupan@gmail.com>
* @version    1.0.0
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class m_applicant extends MY_Model {
/* Constants
-------------------------------*/
const RESERVED_DAYS_EXPIRATION = 7; //7-days, update also on the MySQL trigger

/* Public Properties
-------------------------------*/    
/* Protected Properties
-------------------------------*/

public $status = [
'New'     => 10,
'For Interview'  => 11,
'Passporting'    => 15,
'Pending Medical'  => 7, 
'For QVC'      => 6, 
'Available'      => 0,
'Line Up'        => 5, //interview
'Reserved'       => 2,
'Pre-Selected'   => 3,
'Selected'       => 4,
'For Booking International'    => 12,      
 'For Deployment' => 8,
'Deployed'       => 9,
'For QVC Appt'       => 10,
//'Failed Interview'    => 20,
'For Medical'    => 13,
'Unfit'    => 21,
'Repat'    => 22,
'Cancelled'      => 1,
'A to A'      => 14,
'Backout'      => 25,
'Transmittal'      => 26,
'For Contract Signing'      => 27,
'For OWWA'      => 28,
'Owwa Reschedule'      => 29,
'FOR OEC'      => 30,
'OEC Released'      => 31,
'Visa Stamped'      => 32,
'for OEC'      => 33,
'Visa Released'      => 35,
'Re Booking'      => 36,
'Re Booking'      => 36,
'For Visa Stamping'      => 37,
'For PDOS'      => 38,
'For Line Up'      => 39,
'For Biometric Appointment'      => 40,
'For Biometric'      => 41,
'For Tesda'      => 42,
'WAITING VISA (UAE)'      => 43,
/* id 16 to 19 is used on  training status */
];

public $statusText = [
10 => 'New',
15 => 'Passporting',
7 => 'Pending Medical',
0 => 'Available',
5 => 'Line Up',
2 => 'Reserved',
//3 => 'Pre-Selected',
4 => 'Selected',
6 => 'For QVC',
12 => 'For Booking International',
8 => 'For Deployment',
9 => 'Deployed',
10 => 'For QVC Appt',  
11 => 'For Interview',
13 => 'For Medical',
//16 => 'Enrolled to training',
//17 => 'Started training',
//18 => 'Failed Training',
//19 => 'Graduate Training',
//20 => 'Failed Interview',
21 => 'Unfit',
22 => 'Repat',
1 => 'Cancelled',
14 => 'A to A',
25 => 'Backout',
26 => 'Transmittal', 
27 => 'For Contract Signing', 
28 => 'For OWWA', 
29 => 'Owwa Reschedule', 
30 => 'OEC Filed', 
31 => 'OEC Released',   
32 => 'Visa Stamped', 
35 => 'Visa Received', 
33 => 'FOR OEC	',  
36 => 'Re Booking', 
37 => 'For Visa Stamping',  
38 => 'For PDOS',
39 => 'For Line Up',  
40 => 'For Biometric Appointment', 
41 => 'For Biometric', 
42 => 'For Tesda',  
43 => 'WAITING VISA (UAE)',       
];

public $statusColors = [
0 => 'default',
1 => 'danger',
2 => 'primary',
3 => 'default',
4 => 'info',
5 => 'info',
6 => 'primary',
7 => 'danger',
8 => 'warning',
9 => 'success',    
10 => 'info',     
11 => 'primary',     
12 => 'warning',
//13 => 'info',
14 => 'default',
15 => 'danger',	
//16 => 'primary',
//17 => 'default',
//18 => 'success',
//19 => 'info',
//20 => 'danger',
21 => 'danger',
22 => 'default',
25 => 'danger',
26 => 'default',
27 => 'primary',
28 => 'default',
29 => 'default',
30 => 'default',
31 => 'warning',
32 => 'warning',
33 => 'default',
35 => 'warning',
36 => 'warning',
37 => 'warning',
38 => 'warning',
39 => 'info',
40 => 'warning',
41 => 'warning',
42 => 'info',
43 => 'warning',
];




public $fileTypes = [   
'Whole Body Picture' => 'Whole Body Picture',
'Resume'             => 'Resume/CV',
'Passport'           => 'Passport',
'Visa'               => 'Visa',
'OEC'               => 'OEC',
'NBI'               => 'NBI',
'OWWA'               => 'OWWA',
'VISA STAMPED'        => 'VISA STAMPED',
'PDOS'               => 'PDOS',
'TESDA'               => 'TESDA',
'OMA'               => 'OMA',
'CONTRACT'               => 'CONTRACT',
'MEDICAL'               => 'MEDICAL',
'INSURANCE'               => 'INSURANCE',
'TRADE TEST'               => 'TRADE TEST',
'INFOSHEET'               => 'INFOSHEET',
'TICKET'               => 'TICKET',
'MOFA'               => 'MOFA',
'E-Reg'               => 'E-Reg',
'PEOS'               => 'PEOS',
'QVC Appointment'               => 'QVC Appointment',
'EPP'               => 'EPP',
'BOQ Yellow Card'               => 'BOQ Yellow Card',
'Doc 1'              => 'Docs 1',
'Doc 2'              => 'Docs 2',
'Doc 3'              => 'Docs 3',
'Doc 4'              => 'Docs 4',
'Doc 5'              => 'Docs 5',
'Doc 6'              => 'Docs 6',
'Doc 7'              => 'Docs 7',
'Doc 8'              => 'Docs 8',
'Other'              => 'Other',
'Agency Files 1'      => 'Agency Files 1',
'Agency Files 2'      => 'Agency Files 2',
'Agency Files 3'      => 'Agency Files 3',
'Agency Files 4'      => 'Agency Files 4',
'Agency Files 5'      => 'Agency Files 5',
'Agency Files 3'      => 'Agency Files 6'

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
if (isset($search['position']) &&  $search['position'] > 0 ) {
//get Subposition
$this->db->flush_cache();
$query = $this->db->get_where('applicant_preferred_positions', array('position_position' =>  $search['position']));
$result = $query->result();
foreach ($result as $positions_value) {
$all_apid_sub_pos[] =$positions_value->position_applicant.'"';
}
}

if ( ! empty( $search['q'] ) ) {

//requirement_oec_number search
$this->db->flush_cache();
$query = $this->db->get_where('applicant_requirement', array('requirement_oec_number' => $search['q']), 1);
$result = $query->result();
if(isset($result[0]->requirement_applicant))
$requirement_id = $result[0]->requirement_applicant;
else
$requirement_id = 0;

//insurance_no search
$this->db->flush_cache();
$query = $this->db->get_where('applicant_certificate', array('insurance_no' => $search['q']), 1);
$result = $query->result();
if(isset($result[0]->certificate_applicant))
$certificate_id = $result[0]->certificate_applicant;
else
$certificate_id = 0;

//ticket_no search
$this->db->flush_cache();
$query = $this->db->get_where('applicant_requirement', array('ticket_no	' => $search['q']), 1);
$result = $query->result();
if(isset($result[0]->requirement_applicant))
$ticket_no_id = $result[0]->requirement_applicant;
else
$ticket_no_id = 0;

//ticket_no search
$this->db->flush_cache();
$query = $this->db->get_where('applicant', array('applicantNumber' => $search['q']), 1);
$result = $query->result();
if(isset($result[0]->applicant_id))
$applicantNumber_id = $result[0]->applicant_id;
else
$applicantNumber_id = 0;



}

$this->db->flush_cache();
$this->db->select( 'a.*' )
->from('applicant_view a')
->join( 'position p', 'p.position_id = a.applicant_preferred_position', 'left' );

if ( ! empty( $search['q'] ) ) {
$this->db->where('(
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

if (isset($search['country']) && $search['country'] > 0 ) {
$this->db->where([
'applicant_preferred_country' => $search['country']
]);
}

if (isset($search['status']) && $search['status'] != 111 ) {
$this->db->where([
'applicant_status' => $search['status']
]);
}

if (isset($search['position']) && $search['position'] > 0 ) {
$this->db->where([
'applicant_preferred_position' => $search['position']
]);
$this->db->or_where_in('a.applicant_id',$all_apid_sub_pos);
}

if (isset($search['employer']) &&  $search['employer'] > 0 ) {
$this->db->where([
'employer_id' => $search['employer']
]);
}

if ( ! empty( $search['gender'] ) ) {
$this->db->where([
'applicant_gender' => $search['gender']
]);
}

if (isset($search['age']['from']) && $search['age']['from'] > 0 ) {
$this->db->where( 'applicant_age BETWEEN '.(int) $search['age']['from'].' AND '.(int) $search['age']['to'],
null, false );
}

if (isset($search['salary']['from']) && $search['salary']['from'] > 0 ) {
$this->db->where( 'applicant_expected_salary BETWEEN '.
(float) $search['salary']['from'].' AND '.(float) $search['salary']['to'], 
null, false );
}

if ( isset( $search['date-applied']['from'], $search['date-applied']['to'] ) 
&& date('Y-m-d', strtotime( $search['date-applied']['from'] )) != date('Y-m-d', strtotime(null))
&& date('Y-m-d', strtotime( $search['date-applied']['to'] )) != date('Y-m-d', strtotime(null))
) {

$dateFrom = date('Y-m-d', strtotime( $search['date-applied']['from'] ));
$dateTo   = date('Y-m-d', strtotime( $search['date-applied']['to'] ));

$this->db->where( 'DATE(applicant_date_applied) BETWEEN \''.$dateFrom.'\' AND \''.$dateTo.'\'',
null, false );
}
$this->db->query('SET SQL_BIG_SELECTS=1');
$this->db->group_by( 'a.applicant_id' );

$applicants = $this->db->get()->result_array();
// dd($this->db->last_query());

return $applicants;
}

public function searchApplicantsCount()
{
$search = $_GET['search'];

$all_apid_sub_pos = '';
if (isset($search['position']) && $search['position'] > 0 ) {
//get Subposition
$this->db->flush_cache();
$query = $this->db->get_where('applicant_preferred_positions', array('position_position' =>  $search['position']));
$result = $query->result();
foreach ($result as $positions_value) {
$all_apid_sub_pos[] =$positions_value->position_applicant.'"';
}
}

if ( ! empty( $search['q'] ) ) {

//requirement_oec_number search
$this->db->flush_cache();
$query = $this->db->get_where('applicant_requirement', array('requirement_oec_number' => $search['q']), 1);
$result = $query->result();
if(isset($result[0]->requirement_applicant))
$requirement_id = $result[0]->requirement_applicant;
else
$requirement_id = 0;

//insurance_no search
$this->db->flush_cache();
$query = $this->db->get_where('applicant_certificate', array('insurance_no' => $search['q']), 1);
$result = $query->result();
if(isset($result[0]->certificate_applicant))
$certificate_id = $result[0]->certificate_applicant;
else
$certificate_id = 0;

//ticket_no search
$this->db->flush_cache();
$query = $this->db->get_where('applicant_requirement', array('ticket_no	' => $search['q']), 1);
$result = $query->result();
if(isset($result[0]->requirement_applicant))
$ticket_no_id = $result[0]->requirement_applicant;
else
$ticket_no_id = 0;

//ticket_no search
$this->db->flush_cache();
$query = $this->db->get_where('applicant', array('applicantNumber' => $search['q']), 1);
$result = $query->result();
if(isset($result[0]->applicant_id))
$applicantNumber_id = $result[0]->applicant_id;
else
$applicantNumber_id = 0;



}

$this->db->flush_cache();
$this->db->select( 'a.*' )
->from('applicant_view a')
->join( 'position p', 'p.position_id = a.applicant_preferred_position', 'left' );

if ( ! empty( $search['q'] ) ) {
$this->db->where('(
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

if (isset($search['country']) && $search['country'] > 0 ) {
$this->db->where([
'applicant_preferred_country' => $search['country']
]);
}

if (isset($search['position']) && $search['position'] > 0 ) {
$this->db->where([
'applicant_preferred_position' => $search['position']
]);
$this->db->or_where_in('a.applicant_id',$all_apid_sub_pos);
}

if (isset($search['employer']) && $search['employer'] > 0 ) {
$this->db->where([
'employer_id' => $search['employer']
]);
}

if ( ! empty( $search['gender'] ) ) {
$this->db->where([
'applicant_gender' => $search['gender']
]);
}

if (isset($search['age']['from']) && $search['age']['from'] > 0 ) {
$this->db->where( 'applicant_age BETWEEN '.(int) $search['age']['from'].' AND '.(int) $search['age']['to'],
null, false );
}

if (isset($search['salary']['from']) && $search['salary']['from'] > 0 ) {
$this->db->where( 'applicant_expected_salary BETWEEN '.
(float) $search['salary']['from'].' AND '.(float) $search['salary']['to'], 
null, false );
}

if ( isset( $search['date-applied']['from'], $search['date-applied']['to'] ) 
&& date('Y-m-d', strtotime( $search['date-applied']['from'] )) != date('Y-m-d', strtotime(null))
&& date('Y-m-d', strtotime( $search['date-applied']['to'] )) != date('Y-m-d', strtotime(null))
) {

$dateFrom = date('Y-m-d', strtotime( $search['date-applied']['from'] ));
$dateTo   = date('Y-m-d', strtotime( $search['date-applied']['to'] ));

$this->db->where( 'DATE(applicant_date_applied) BETWEEN \''.$dateFrom.'\' AND \''.$dateTo.'\'',
null, false );
}
$this->db->query('SET SQL_BIG_SELECTS=1');
$this->db->group_by( 'a.applicant_id' );

$applicants = $this->db->get()->result_array();
// dd($this->db->last_query());

return count($applicants);
}


public function getApplicantById( $applicantId )
{	
//Get Applicant Info
$this->db->flush_cache();
$this->db->from( 'applicant_view' )
->where([
'applicant_id'	=> $applicantId,
]);
$this->db->query('SET SQL_BIG_SELECTS=1');
$applicant               = $this->db->get()->row_array();
$workExperiences         = $this->getApplicantWorkExperiences( $applicantId );
$otherPreferredPositions = $this->getApplicantOtherPreferredPositions( $applicantId );
$otherPreferredCountries = $this->getApplicantOtherPreferredCountries( $applicantId );

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
$query = $this->db->get_where('applicant_certificate', array('certificate_applicant' => $applicantId));
$result =  $query->result();
return $result[0];
}

function getApplicantRawById( $applicantId ){
$query = $this->db->get_where(' applicant', array('applicant_id' => $applicantId));
$result =  $query->result();
return $result[0];
}

function getApplicantRequirementsById( $applicantId ){
$query = $this->db->get_where('applicant_requirement', array('requirement_applicant' => $applicantId));
$result =  $query->result();
return $result[0];
}
function getApplicantPassById( $applicantId ){
$query = $this->db->get_where('applicant_passport', array('passport_applicant' => $applicantId));
$result =  $query->result();
return $result[0];
}

	
public function getCurrencyById($applicantId){
$this->db->from( 'applicant' )
->where([
'applicant_id'	=> $applicantId,
]);
$applicant = $this->db->get()->row_array();
return $applicant['currency'];
}

//For admin/applicants/send_applicants
public function getApplicantsByIds( $applicantIds )
{
$this->db->flush_cache();
$this->db->from( 'applicant' )
->where_in('applicant_id', $applicantIds);
$this->db->query('SET SQL_BIG_SELECTS=1');
$applicants = $this->db->get()->result_array();

return $applicants;
}

public function lineUpApplicants( $applicantIds, $employerId )
{
$this->db->flush_cache();
$this->db->where_in( 'applicant_id', $applicantIds )
->update( 'applicant', [
'applicant_status'   => $this->status['Line Up'],
'applicant_employer' => $employerId,
]);
foreach ($applicantIds as $applicantId) {
$logInserted = $this->addLog( 'Send Applicant', $applicantId, $employerId, $this->status['Line Up'], date( 'Y-m-d', time() ) );
}	
}



public function getApplicants( $options = [], $limit = 0, $offset = 0, $sort = ['applicant_updated', 'DESC'])
{
if($_SESSION['admin']['user']['user_type'] == 9){
$user_rs = $this->db->get_where('user', array('team_lead_id' => $_SESSION['admin']['user']['user_id']));
foreach ($user_rs->result() as $key => $value) {
$users_rs_id[] = $value->user_id;
}

$this->db->flush_cache();
$this->db->where_in('employer_user',$users_rs_id);
$employers_all = $this->db->get('employer')->result_array();
foreach ($employers_all as $key => $value) {
$employers_id[] = $value['employer_id'];
}
}

if($_SESSION['admin']['user']['user_type'] == 10){
$employers = $this->db->get_where('employer', array('rs_id' => $_SESSION['admin']['user']['user_id']));
foreach ($employers->result() as $key => $value) {
$employers_id[] = $value->employer_id;
}
}

$this->db->flush_cache();
$this->db->from( 'applicant_view' ); 

if(($_SESSION['admin']['user']['user_type'] == 10) || ($_SESSION['admin']['user']['user_type'] == 9)){
$this->db->where_in('applicant_employer',$employers_id);
}

if(isset($_GET['skill'])){
$this->db->where('applicant_position_type',$_GET['skill']);
}

//For Selected
$this->db->join( 'employer_selected', 'selected_employer = applicant_employer AND selected_applicant = applicant_id', 'left' );

//For Deployed
$this->db->join( 'deployed', 'deployed_employer = applicant_employer AND deployed_applicant = applicant_id', 'left' );

$this->setDBQueryOptions( $options )
->setDBQueryRange( $limit, $offset )
->setDBQueryOrders( $sort );
$this->db->query('SET SQL_BIG_SELECTS=1'); 
$applicants = $this->db->get()->result_array();

return $this->indexArray( $applicants, 'applicant_id' );
}

public function getApplicantsCount( $options = [] )
{
if($_SESSION['admin']['user']['user_type'] == 9){
$user_rs = $this->db->get_where('user', array('team_lead_id' => $_SESSION['admin']['user']['user_id']));
foreach ($user_rs->result() as $key => $value) {
$users_rs_id[] = $value->user_id;
}

$this->db->flush_cache();
$this->db->where_in('employer_user',$users_rs_id);
$employers_all = $this->db->get('employer')->result_array();
foreach ($employers_all as $key => $value) {
$employers_id[] = $value['employer_id'];
}
}

if($_SESSION['admin']['user']['user_type'] == 10){
$employers = $this->db->get_where('employer', array('rs_id' => $_SESSION['admin']['user']['user_id']));
foreach ($employers->result() as $key => $value) {
$employers_id[] = $value->employer_id;
}
}


$this->db->flush_cache();
$this->db->from( 'applicant_view' ); 

if(($_SESSION['admin']['user']['user_type'] == 10) || ($_SESSION['admin']['user']['user_type'] == 9)){
$this->db->where_in('applicant_employer',$employers_id);
}

$this->setDBQueryOptions( $options );

$applicants = $this->db->count_all_results();

return $applicants;
}

public function cyd_get_multiple_employer( $applicant_id = [] )
{
$this->db->flush_cache();
$this->db->from( 'multiple_lineups' )->where([
'applicant_id' => $applicant_id,
]);
$this->db->group_by('applicant_employer');
$lineup_ids = $this->db->get()->result_array();

$result = '';
foreach ($lineup_ids as $lineup_id) {
$this->db->flush_cache();
$this->db->from( 'employer' )->where([
'employer_id' => $lineup_id['applicant_employer'],
]);
$tbl_employer = $this->db->get()->result_array();
$result .= $tbl_employer[0]['employer_name'].', ';
}
return $result;
}

public function getPreSelected( $options = [], $limit = 0, $offset = 0, $sort = ['reservation_expiration', 'ASC'])
{
$this->db->flush_cache();

$this->db->from( 'applicant_view' )
->join( 'applicant_log', 'log_applicant = applicant_id' )

->where([
'applicant_status' => $this->status['Pre-Selected'],
]);

$this->setDBQueryOptions( $options )
->setDBQueryRange( $limit, $offset )
->setDBQueryOrders( $sort );
$this->db->query('SET SQL_BIG_SELECTS=1'); 
$applicants = $this->db->get()->result_array();

return $this->indexArray( $applicants, 'applicant_id' );
}
public function getPreSelectedCount( $options = [] )
{
$this->db->flush_cache();

$this->db->from( 'applicant_view' )
->join( 'applicant_log', 'log_applicant = applicant_id' )
->where([
'applicant_status' => $this->status['Pre-Selected'],
]);

$this->setDBQueryOptions( $options );
$this->db->query('SET SQL_BIG_SELECTS=1'); 
$applicants = $this->db->count_all_results();

return $applicants;
}

public function getForBooking( $options = [], $limit = 0, $offset = 0, $sort = ['applicant_id', 'DESC'])
{
$this->db->flush_cache();

$this->db->from( 'applicant_view' )
->join( 'applicant_log', 'log_applicant = applicant_id' )

->where([
'applicant_status' => $this->status['Deployment'],
]);

$this->setDBQueryOptions( $options )
->setDBQueryRange( $limit, $offset )
->setDBQueryOrders( $sort );
$this->db->query('SET SQL_BIG_SELECTS=1'); 
$applicants = $this->db->get()->result_array();

return $this->indexArray( $applicants, 'applicant_id' );
}
public function getForBookingCount( $options = [] )
{
$this->db->flush_cache();

$this->db->from( 'applicant_view' )
->join( 'applicant_log', 'log_applicant = applicant_id' )
->where([
'applicant_status' => $this->status['Deployment'],
]);

$this->setDBQueryOptions( $options );
$this->db->query('SET SQL_BIG_SELECTS=1'); 
$applicants = $this->db->count_all_results();

return $applicants;
}

public function getReservedApplicants( $options = [], $limit = 0, $offset = 0, $sort = ['reservation_expiration', 'ASC'])
{
$this->db->flush_cache();

$this->db->from( 'applicant_view' )
->join( 'employer_reservation', 'reservation_applicant = applicant_id' )
->where([
'applicant_status' => $this->status['Reserved'],
]);

$this->setDBQueryOptions( $options )
->setDBQueryRange( $limit, $offset )
->setDBQueryOrders( $sort );
$this->db->query('SET SQL_BIG_SELECTS=1'); 
$applicants = $this->db->get()->result_array();

return $this->indexArray( $applicants, 'applicant_id' );
}
public function getReservedApplicantsCount( $options = [] )
{
$this->db->flush_cache();

$this->db->from( 'applicant_view' )
->join( 'employer_reservation', 'reservation_applicant = applicant_id' )
->where([
'applicant_status' => $this->status['Reserved'],
]);

$this->setDBQueryOptions( $options );
$this->db->query('SET SQL_BIG_SELECTS=1'); 
$applicants = $this->db->count_all_results();

return $applicants;
}

public function getExpiredReservedApplicants( $options = [], $limit = 0, $offset = 0, $sort = [ 'reservation_expiration', 'ASC' ] )
{
$this->db->flush_cache();

$this->db->from( 'applicant_view' )
->join( 'employer_reservation', 'reservation_applicant = applicant_id' )
->where([
'applicant_status'          => $this->status['Reserved'],
'reservation_expiration <=' => date( 'Y-m-d', time() ),
'reservation_expiration >'  => DATE_EMPTY,
]);

$this->setDBQueryOptions( $options )
->setDBQueryRange( $limit, $offset )
->setDBQueryOrders( $sort );
$this->db->query('SET SQL_BIG_SELECTS=1'); 
$applicants = $this->db->get()->result_array();

return $this->indexArray( $applicants, 'applicant_id' );
}

public function getExpiredReservedApplicantsCount( $options = [] )
{
$this->db->flush_cache();
$this->db->from( 'applicant_view' )
->join( 'employer_reservation', 'reservation_applicant = applicant_id' )
->where([
'applicant_status'          => $this->status['Reserved'],
'reservation_expiration <=' => date( 'Y-m-d', time() ),
'reservation_expiration >'  => DATE_EMPTY,
]);

$this->setDBQueryOptions( $options );

$applicants = $this->db->count_all_results();

return $applicants; 
}

public function getExpiredMedicalApplicants( $options = [], $limit = 0, $offset = 0, $sort = [ 'certificate_medical_expiration', 'ASC' ] )
{
$this->db->flush_cache();
$this->db->from( 'applicant_view' )
->where([
'certificate_medical_clinic !='     => '',
'certificate_medical_expiration <=' => date( 'Y-m-d', time() + 60*60*24*14 ),
'certificate_medical_expiration >'  => DATE_EMPTY,
]);
$notinclude = array(1,7,9,22,21);
$this->db->where_not_in('applicant_status',$notinclude);

$this->setDBQueryOptions( $options )
->setDBQueryRange( $limit, $offset )
->setDBQueryOrders( $sort );
$this->db->query('SET SQL_BIG_SELECTS=1');
$applicants = $this->db->get()->result_array();

return $this->indexArray( $applicants, 'applicant_id' );
}

public function getExpiredMedicalApplicantsCount( $options = [] )
{
$this->db->flush_cache();
$this->db->from( 'applicant_view' )
->where([
'certificate_medical_clinic !='     => '',
'certificate_medical_expiration <=' => date( 'Y-m-d', time() + 60*60*24*14 ),
'certificate_medical_expiration >'  => DATE_EMPTY,
]);
$notinclude = array(1,7,9,22,21);
$this->db->where_not_in('applicant_status',$notinclude);

$this->setDBQueryOptions( $options );

$applicants = $this->db->count_all_results();

return $applicants; 
}

public function getExpiredVisaApplicants( $options = [], $limit = 0, $offset = 0, $sort = [ 'requirement_visa_expiration', 'ASC' ] )
{
$this->db->flush_cache();
$this->db->from( 'applicant_view' )
->where([
'requirement_visa'               => 1,
'requirement_visa_expiration <=' => date( 'Y-m-d', time() + 60*60*24*30 ),
'requirement_visa_expiration >'  => DATE_EMPTY,
]);

$notinclude = array(1,7,9,22,21);
$this->db->where_not_in('applicant_status',$notinclude);

$this->setDBQueryOptions( $options )
->setDBQueryRange( $limit, $offset )
->setDBQueryOrders( $sort );
$this->db->query('SET SQL_BIG_SELECTS=1');
$applicants = $this->db->get()->result_array();

return $this->indexArray( $applicants, 'applicant_id' );
}

public function getExpiredVisaApplicantsCount( $options = [] )
{
$this->db->flush_cache();
$this->db->from( 'applicant_view' )
->where([
'requirement_visa'               => 1,
'requirement_visa_expiration <=' => date( 'Y-m-d', time() + 60*60*24*30 ),
]);

$notinclude = array(1,7,9,22,21);
$this->db->where_not_in('applicant_status',$notinclude);

$this->setDBQueryOptions( $options );

$applicants = $this->db->count_all_results();

return $applicants; 
}

public function getExpiredPassportsApplicants( $options = [], $limit = 0, $offset = 0, $sort = [ 'passport_expiration', 'ASC' ] )
{
$this->db->flush_cache();
$this->db->from( 'applicant_view' )
// ->where_in( 'applicant_status', [
//     $this->m_applicant->status['Selected'],
//     $this->m_applicant->status['Reserved'],
//     $this->m_applicant->status['For Deployment'],
// ])
->where([
'passport_number !='     => '',
'passport_expiration <=' => date( 'Y-m-d', time() + 60*60*24*30*8 ),
'passport_expiration >'  => DATE_EMPTY,
]);

$notinclude = array(1,7,9,22,21);
$this->db->where_not_in('applicant_status',$notinclude);

$this->setDBQueryOptions( $options )
->setDBQueryRange( $limit, $offset )
->setDBQueryOrders( $sort );
$this->db->query('SET SQL_BIG_SELECTS=1');
$applicants = $this->db->get()->result_array();

return $this->indexArray( $applicants, 'applicant_id' );
}

public function getExpiredPassportsApplicantsCount( $options = [] )
{
$this->db->flush_cache();
$this->db->from( 'applicant_view' )
// ->where_in( 'applicant_status', [
//     $this->m_applicant->status['Selected'],
//     $this->m_applicant->status['Reserved'],
//     $this->m_applicant->status['For Deployment'],
// ])
->where([
'passport_number !='     => null,
'passport_number !='     => '',
'passport_expiration <=' => date( 'Y-m-d', time() + 60*60*24*30*8 ),
'passport_expiration >'  => DATE_EMPTY,
]);

$notinclude = array(1,7,9,22,21);
$this->db->where_not_in('applicant_status',$notinclude);

$this->setDBQueryOptions( $options );

$applicants = $this->db->count_all_results();

return $applicants; 
}




public function getLineUpApplicants( $options = [], $limit = 0, $offset = 0, $sort = ['applicant_updated', 'DESC'] )
{
$this->db->flush_cache();
$this->db->from( 'applicant_view' )
->where([
'applicant_status'     => $this->status['Line Up'],
]);

$this->setDBQueryOptions( $options )
->setDBQueryRange( $limit, $offset )
->setDBQueryOrders( $sort );
$this->db->query('SET SQL_BIG_SELECTS=1');
$applicants = $this->db->get()->result_array();

return $this->indexArray( $applicants, 'applicant_id' );
} 

public function cyd_getLineUpApplicants( $options = [], $limit = 0, $offset = 0, $sort = ['applicant_updated', 'DESC'] )
{
$this->db->flush_cache();
$this->db->select('*');
$this->db->from('applicant_view');
$this->db->where([
'applicant_status' => $this->status['Line Up'],
'applicant_employer' => $options['where'][0]['applicant_employer'],
]);
$this->setDBQueryRange( $limit, $offset )
->setDBQueryOrders( $sort );
$this->db->query('SET SQL_BIG_SELECTS=1');

$applicants = $this->db->get()->result_array();

return $this->indexArray( $applicants, 'applicant_id' );
} 

public function getLineUpApplicantsCount( $options = [] )
{
$this->db->flush_cache();
$this->db->from( 'applicant_view' )
->join( 'employer_selected', 'selected_applicant = applicant_id' )
->where([
'applicant_status'     => $this->status['Line Up'],
]);

$this->setDBQueryOptions( $options );

$applicants = $this->db->count_all_results();

return $applicants;
}










public function getSelectedApplicants( $options = [], $limit = 0, $offset = 0, $sort = ['applicant_updated', 'DESC'] )
{
$this->db->flush_cache();
$this->db->from( 'applicant_view' )
->join( 'employer_selected', 'selected_applicant = applicant_id' )
->where([
'applicant_status'     => $this->status['Selected'],
]);

$this->setDBQueryOptions( $options )
->setDBQueryRange( $limit, $offset )
->setDBQueryOrders( $sort );
$this->db->query('SET SQL_BIG_SELECTS=1');
$applicants = $this->db->get()->result_array();

return $this->indexArray( $applicants, 'applicant_id' );
} 

public function getSelectedApplicantsCount( $options = [] )
{
$this->db->flush_cache();
$this->db->from( 'applicant_view' )
->join( 'employer_selected', 'selected_applicant = applicant_id' )
->where([
'applicant_status'     => $this->status['Selected'],
]);

$this->setDBQueryOptions( $options );

$applicants = $this->db->count_all_results();

return $applicants;
}

public function getDeployedApplicants( $options = [], $limit = 0, $offset = 0, $sort = ['applicant_updated', 'DESC'] )
{
$this->db->flush_cache();
$this->db->from( 'applicant_view' )
->join( 'deployed', 'deployed_applicant = applicant_id', 'inner' )
->where([
'applicant_status'     => $this->status['Deployed'],
]);

$this->setDBQueryOptions( $options )
->setDBQueryRange( $limit, $offset )
->setDBQueryOrders( $sort );
$this->db->query('SET SQL_BIG_SELECTS=1');
$applicants = $this->db->get()->result_array();

return $this->indexArray( $applicants, 'applicant_id' );
} 

public function getDeployedApplicantsCount( $options = [] )
{
$this->db->flush_cache();
$this->db->from( 'applicant_view' )
->join( 'deployed', 'deployed_applicant = applicant_id', 'inner' )
->where([
'applicant_status'     => $this->status['Deployed'],
]);

$this->setDBQueryOptions( $options );

$applicants = $this->db->count_all_results();

return $applicants;
}

public function getApplicantWorkExperiences( $applicantId )
{
//Get Work Experiences
$this->db->flush_cache();
$this->db->from('applicant_experiences')
->where([
'experience_applicant'	=> $applicantId,
]);
$this->db->query('SET SQL_BIG_SELECTS=1');
$experiences = $this->db->get()->result_array();

return $this->indexArray( $experiences, 'experience_id' );
}


public function getApplicantWorktrain( $applicantId )
{
//Get Work training
$this->db->flush_cache();
$this->db->from('appliocant_train')
->where([
'm_app'	=> $applicantId,
]);
$this->db->query('SET SQL_BIG_SELECTS=1');
$experiencestraining = $this->db->get()->result_array();

return $this->indexArray( $experiencestraining, 't_id' );
}




public function getApplicantOtherPreferredPositions( $applicantId )
{
$this->db->flush_cache();
$this->db->select( 'p.*' )
->from('position p')
->join('applicant_preferred_positions pp', 'pp.position_position = p.position_id')
->where([
'pp.position_applicant' => $applicantId,
]);
$this->db->query('SET SQL_BIG_SELECTS=1');
$positions = $this->db->get()->result_array();

return $this->indexArray( $positions, 'position_id' );
}

public function getApplicantOtherPreferredCountries( $applicantId )
{
$this->db->flush_cache();
$this->db->select( 'c.*' )
->from('country c')
->join('applicant_preferred_countries pc', 'pc.country_country = c.country_id')
->where([
'pc.country_applicant' => $applicantId,
]);
$this->db->query('SET SQL_BIG_SELECTS=1');
$countries = $this->db->get()->result_array();

return $this->indexArray( $countries, 'country_id' );
}

public function getApplicantFiles( $applicantId, $options = [], $limit = 0, $offset = 0 )
{
$this->db->flush_cache();
$this->db->from( 'applicant_files' )
->join( 'user', 'user_id = file_createdby' )
->where([
'file_applicant' => $applicantId,
'file_status'    => 1,
]);

$this->setDBQueryOptions( $options )
->setDBQueryRange( $limit, $offset );

$this->db->order_by( 'file_created', 'DESC' );
$this->db->query('SET SQL_BIG_SELECTS=1');
$files = $this->db->get()->result_array();

return $this->indexArray( $files, 'file_type' );
}

public function getApplicantFileByType( $applicantId, $type )
{
$this->db->flush_cache();
$this->db->from('applicant_files')
->where([
'file_applicant' => $applicantId,
'file_type'      => $type,
]);
$this->db->query('SET SQL_BIG_SELECTS=1');
$file = $this->db->get()->row_array();

return $file;
}

public function getApplicantFileById( $fileId )
{
$this->db->flush_cache();
$this->db->from( 'applicant_files' )
// ->join( 'user', 'user_id = file_createdby' )
->where([
'file_id' => $fileId,
]);

$file = $this->db->get()->row_array();

return $file;
}

public function getApplicantLogs( $applicantId, $options = [], $limit = 0, $offset = 0, $sort = [ 'log_created', 'DESC' ] )
{
$this->db->flush_cache();
$this->db->from( 'applicants_logs_view' )
->where([
'log_applicant' => $applicantId,
]);

$this->setDBQueryOptions( $options )
->setDBQueryRange( $limit, $offset )
->setDBQueryOrders( $sort );
$this->db->query('SET SQL_BIG_SELECTS=1');
$logs = $this->db->get()->result_array();

return $this->indexArray( $logs, 'log_id' );

}


public function getStatus( $applicantId )
{
$this->db->flush_cache();
$this->db->from( 'applicant_view' )
->where([
'applicant_id' => $applicantId,
]);
$this->db->query('SET SQL_BIG_SELECTS=1');
$applicant = $this->db->get()->row_array();

$response = [
'employer' => $applicant['applicant_employer'],
'country'  => $applicant['country_name'],
'status'   => $applicant['applicant_status'],
'date'     => date( 'Y-m-d', time() ),
'remarks'  => null,
];

$this->db->flush_cache();
$this->db->from( 'applicant_log' )
->where([
'log_applicant' => $applicantId,
'log_status'    => $applicant['applicant_status'],
])
->order_by( 'log_created', 'DESC' )
->limit(1);
$this->db->query('SET SQL_BIG_SELECTS=1');
$log = $this->db->get()->row_array();

if ( ! empty( $log ) ) {

$response = array_merge( $response, [
'status'   => $log['log_status'],
'date'     => date( 'Y-m-d', strtotime( $log['log_date'] ) ),
'remarks'  => $log['log_remarks'],
]);
}

return $response;
}

public function getAllLogs( $options = [], $limit = 0, $offset = 0, $sort = [ 'log_created', 'DESC' ] )
{
$this->db->from( 'applicants_logs_view' );

$this->setDBQueryOptions( $options )
->setDBQueryRange( $limit, $offset )
->setDBQueryOrders( $sort );
$this->db->query('SET SQL_BIG_SELECTS=1');				
$logs = $this->db->get()->result_array();

return $this->indexArray( $logs, 'log_id' );
}

public function getCounters()
{
$this->db->query('SET SQL_BIG_SELECTS=1');
$counter = [];

for ( $m = 6; $m >= 0; $m-- ) {

$month    = date('Y-F', strtotime('-'.$m.' months'));
$dateFrom = date( 'Y-m-01', strtotime( '-'.$m.' months' ) );
$dateTo   = date( 'Y-m-t', strtotime( '-'.$m.' months' ) );

$this->db->flush_cache();
$this->db->from( 'applicant' )
->where( "`applicant_date_applied` BETWEEN '".$dateFrom."' AND '".$dateTo."' ", null, false);

$counter['applied'][$month] = $this->db->count_all_results();

$this->db->flush_cache();
$this->db->from( 'applicant_view' );
$this->db->where([
'applicant_status' => $this->status['Deployed'],
]);
$this->db->join( 'deployed', 'deployed_applicant = applicant_id' );
$this->db->where(
"DATE(deployed_date) BETWEEN '".$dateFrom."' AND '".$dateTo."'",
null, false);
$this->db->where("deployed_id = (SELECT deployed_id FROM deployed WHERE deployed_applicant = applicant_id ORDER BY deployed_created DESC LIMIT 1)", null, false);

$counter['deployed'][$month] = $this->db->count_all_results();

/*
$this->db->from( 'applicant_log' )
->join( 'applicant', 'applicant_id = log_applicant' )
->where( "`log_created` BETWEEN '".$dateFrom."' AND '".$dateTo."' ", null, false)
->where([
'log_status'       => $this->status['Deployed'],
]);
$counter['deployed'][$month] = $this->db->count_all_results();
*/

$this->db->flush_cache();
$this->db->from( 'applicant_log' )
->where( "`log_created` BETWEEN '".$dateFrom."' AND '".$dateTo."' ", null, false)
->where([
'log_status'       => $this->status['Reserved'],
]);

$counter['reserved'][$month] = $this->db->count_all_results();

$this->db->flush_cache();
$this->db->from( 'applicant_log' )
->where( "`log_created` BETWEEN '".$dateFrom."' AND '".$dateTo."' ", null, false)
->where([
'log_status'       => $this->status['Selected'],
]);

$counter['Selected'][$month] = $this->db->count_all_results();

$this->db->flush_cache();
$this->db->from( 'applicant_log' )
->where( "`log_created` BETWEEN '".$dateFrom."' AND '".$dateTo."' ", null, false)
->where([
'log_status'       => $this->status['Deployment'],
]);

$counter['booking'][$month] = $this->db->count_all_results();
}

$response = [];

foreach ( $counter as $status => $months ) {
foreach ( $counter[$status] as $month => $count ) {
$response[$month][$status] = $count;
}
}

return $response;
}

public function getBalance( $applicantId )
{
//order by created desc
$this->db->flush_cache();

$this->db->from( 'bill' )
->where([
'bill_applicant' => $applicantId,
'bill_status'    => 0,
]);

$bill = $this->db->get()->row_array();

if ( empty( $bill ) ) {
return 0.0;
}

$balance = $bill['bill_applicant_deposit'] > $bill['bill_applicant_cost'] ? 0 : $bill['bill_applicant_cost'] - $bill['bill_applicant_deposit'];

return $balance;
}

public function addApplicant()
{	
$applicant          = $_POST['applicant'];

$basic				= 
$passport			=
$visa               =
$education			=
$experiences		=
$applicantData		= 
$preferredPositions =
$preferredCountries =
$passportData		=
$educationData		=		
$experiencesData	=
$visaData		    =
$certificateData    = 
$requirementData    =
$logData            = [];

$basic 				= $applicant['basic'];
$passport 			= $applicant['passport'];
$education 			= $applicant['education'];
$experiences 		= isset( $applicant['work-experience'] ) ? $applicant['work-experience'] : [];
$newWorkExperiences = $experiences;

//Start Transaction
$this->db->trans_begin();

if(!isset($applicant['currency']))
$applicant['currency'] = 'PHP';

//Applicant

$applicantData = [
'applicant_first'			   => ucwords( $basic['first'] ),
'applicant_middle'			   => ucwords( $basic['middle'] ),
'applicant_last'		 	   => ucwords( $basic['last'] ),
//'applicant_suffix'		 	=> $basic['suffix'],
'applicant_birthdate'		   => date('Y-m-d', strtotime( $basic['birthdate'] ) ),
'applicant_age'				   => 0, //This will compute automatically be the db trigger
'applicant_gender'			   => $basic['gender'],
'applicant_contacts'           => $basic['contacts'],
'applicant_contacts_2'         => $basic['contacts2'],
'applicant_contacts_3'         => $basic['contacts3'],
'applicant_address'			   => $basic['address'],
'applicant_email'			   => $basic['email'],
'applicant_nationality'		   => $basic['nationality'],
'applicant_civil_status'	   => $basic['status'],
'applicant_religion'		   => $basic['religion'],
'applicant_languages'		   => $basic['languages'],
'applicant_height'			   => $basic['height'],
'applicant_weight'			   => $basic['weight'],
'applicant_position_type'	   => $applicant['type'],
'currency'  				   => $applicant['currency'],
'applicant_mothers' 		   => $basic['applicant_mothers'],
'applicant_children' 		   => $basic['applicant_children'],
'applicant_incase_name' 		=> $basic['applicant_incase_name'],
'applicant_incase_relation' 	=> $basic['applicant_incase_relation'],
'applicant_incase_contact' 		   => $basic['applicant_incase_contact'],
'applicant_incase_address' 		   => $basic['applicant_incase_address'],
'applicant_employer'			   => $applicant['employer'],


'applicant_preferred_position' => $applicant['preferred-position'],
'training_branches_id' => $applicant['training-branch'],
'applicant_expected_salary'    => $applicant['expected-salary'],
'applicant_preferred_country'  => $applicant['preferred-country'],
'applicant_other_skills'       => $applicant['other-skills'],
'applicant_cv'				   => '',
'applicant_photo'			   => '',
'applicant_status'			   => $applicant['status-applicant'], //Available, Not Selected
'applicant_ex'			   => $applicant['applicant_ex'], //Available, Not Selected
'applicant_ppt_pay'			   => $applicant['applicant_ppt_pay'], //Available, Not Selected
'applicant_ppt_stat'			   => $applicant['applicant_ppt_stat'], //Available, Not Selected
'applicant_source'			   => $applicant['source'],
'applicant_remarks'			   => $applicant['remarks'],
'applicant_date_applied'       => date( 'Y-m-d', strtotime( $applicant['date-applied'] ) ),
'applicant_createdby'		   => isset( $_SESSION['admin']['user']['user_id'] )
		  ? $_SESSION['admin']['user']['user_id']
		  : 0,
'applicant_updatedby'		   => isset( $_SESSION['admin']['user']['user_id'] )
		  ? $_SESSION['admin']['user']['user_id']
		  : 0,
'applicant_created'			   => date('Y-m-d H:i:s', time()),
'applicant_updated'			   => date('Y-m-d H:i:s', time()),
];

$this->db->flush_cache();
$applicantInserted	= $this->db->insert('applicant', $applicantData);
$applicantId 		= $this->db->insert_id();

//applicant_skills_cyd
if(!isset($applicant['is_ironing'])) $applicant['is_ironing'] = 0;
if(!isset($applicant['is_cooking'])) $applicant['is_cooking'] = 0;
if(!isset($applicant['is_sewing'])) $applicant['is_sewing'] = 0;
if(!isset($applicant['is_computer'])) $applicant['is_computer'] = 0;
if(!isset($applicant['is_arabic_cooking'])) $applicant['is_arabic_cooking'] = 0;
if(!isset($applicant['is_baby_sitting'])) $applicant['is_baby_sitting'] = 0;
if(!isset($applicant['is_children_care'])) $applicant['is_children_care'] = 0;
if(!isset($applicant['is_tutoring'])) $applicant['is_tutoring'] = 0;
if(!isset($applicant['is_cleaning'])) $applicant['is_cleaning'] = 0;
if(!isset($applicant['is_washing'])) $applicant['is_washing'] = 0;
if(!isset($applicant['is_manicure'])) $applicant['is_manicure'] = 0;
if(!isset($applicant['is_massage'])) $applicant['is_massage'] = 0;
if(!isset($applicant['is_blower'])) $applicant['is_blower'] = 0;
if(!isset($applicant['is_coloring'])) $applicant['is_coloring'] = 0;
if(!isset($applicant['write_e'])) $applicant['write_e'] = 0;
if(!isset($applicant['read_e'])) $applicant['read_e'] = 0;
if(!isset($applicant['speak_e'])) $applicant['speak_e'] = 0;
if(!isset($applicant['write_a'])) $applicant['write_a'] = 0;
if(!isset($applicant['read_a'])) $applicant['read_a'] = 0;
if(!isset($applicant['speak_a'])) $applicant['speak_a'] = 0;


$skill_cyd = [
'applicant_id'		=> $applicantId,
'ironing'			=> $applicant['is_ironing'],
'cooking'			=> $applicant['is_cooking'],
'sewing'			=> $applicant['is_sewing'],
'computer'			=> $applicant['is_computer'],
'arabic_cooking'	=> $applicant['is_arabic_cooking'],
'baby_sitting'		=> $applicant['is_baby_sitting'],
'children_care'		=> $applicant['is_children_care'],
'tutoring'			=> $applicant['is_tutoring'],
'cleaning'			=> $applicant['is_cleaning'],
'washing'			=> $applicant['is_washing'],
'manicure'			=> $applicant['is_manicure'],
'massage'			=> $applicant['is_massage'],
'blower'			=> $applicant['is_blower'],
'coloring'			=> $applicant['is_coloring'],
'write_e'			=> $applicant['write_e'],
'read_e'			=> $applicant['read_e'],
'speak_e'			=> $applicant['speak_e'],
'write_a'			=> $applicant['write_a'],
'read_a'			=> $applicant['read_a'],
'speak_a'			=> $applicant['speak_a'],

'created_at'		=> date('Y-m-d H:i:s', time()),
'updated_at'		=> date('Y-m-d H:i:s', time()),
];

//Upload photo
if ( isset( $_FILES['applicant']['name']['photo'] ) ) {
$file = [
'name'     => $_FILES['applicant']['name']['photo'],
'type'     => $_FILES['applicant']['type']['photo'],
'tmp_name' => $_FILES['applicant']['tmp_name']['photo'],
'error'    => $_FILES['applicant']['error']['photo'],
'size'     => $_FILES['applicant']['size']['photo'],
];

if ( ! $file['error'] ) {
$filePath = $this->uploadPhoto( $applicantId, $file );

$this->db->flush_cache();
$this->db->where([
'applicant_id' => $applicantId,
])->update( 'applicant', [
'applicant_photo' => $filePath,
]);
}
}

//Create applicant slug
$this->db->flush_cache();
$this->db->where([
'applicant_id' => $applicantId,
])->update( 'applicant', [ 
'applicant_slug' => str_pad( $applicantId, 10, '0', STR_PAD_LEFT ).'/'
.strSlug( $basic['first'].' '.$basic['middle'].' '.$basic['last'] ) 
]);

//Other preferred positions
if ( isset( $applicant['other-preferred-positions'] ) && count( $applicant['other-preferred-positions'] ) > 0 ) {
foreach( $applicant['other-preferred-positions'] as $position) {
$preferredPositions[] = [
'position_applicant'   => $applicantId,
'position_position'    => $position,
'position_createdby'   => isset( $_SESSION['admin']['user']['user_id'] )
		  ? $_SESSION['admin']['user']['user_id']
		  : 0,
'position_created'     => date( 'Y-m-d H:i:s', time() ),
];
}

if ( count( $preferredPositions ) > 0 ) {
$this->db->flush_cache();
$this->db->insert_batch( 'applicant_preferred_positions', $preferredPositions );
}
}

//Other preferred countries
if ( isset( $applicant['other-preferred-countries'] ) && count( $applicant['other-preferred-countries'] ) > 0 ) {
foreach( $applicant['other-preferred-countries'] as $country) {
$preferredCountries[] = [
'country_applicant'  => $applicantId,
'country_country'    => $country,
'country_createdby'  => isset( $_SESSION['admin']['user']['user_id'] )
		? $_SESSION['admin']['user']['user_id']
		: 0,
'country_created'    => date( 'Y-m-d H:i:s', time() ),
];
}

if ( count( $preferredCountries ) > 0 ) {
$this->db->flush_cache();
$this->db->insert_batch( 'applicant_preferred_countries', $preferredCountries );
}
}

//Passport
$passportData = [
'passport_applicant'	=> $applicantId,
'passport_number'		=> $passport['number'],
'passport_issue'        => date( 'Y-m-d', strtotime( $passport['issue'] ) ),
'passport_issue_place'  => $passport['issue-place'],
'passport_expiration'	=> date('Y-m-d', strtotime( $passport['expiration'] ) ),
'passport_createdby'	=> isset( $_SESSION['admin']['user']['user_id'] )
   ? $_SESSION['admin']['user']['user_id']
   : 0,
'passport_updatedby'	=> isset( $_SESSION['admin']['user']['user_id'] )
   ? $_SESSION['admin']['user']['user_id']
   : 0,
'passport_created'		=> date( 'Y-m-d H:i:s', time() ),
'passport_updated'		=> date( 'Y-m-d H:i:s', time() ),
];

$this->db->flush_cache();
$passportInserted 	= $this->db->insert( 'applicant_passport', $passportData );
$passportId 		= $this->db->insert_id();

//Education
if ( $applicantId && isset( $applicant['education'] ) ) {
$educationData = [
'education_applicant'		=> $applicantId,
'education_mba'				=> $education['mba'],
'education_mba_course'		=> $education['mba-course'],
'education_mba_year'		=> $education['mba-year'],
'education_college'			=> $education['college'],
'education_college_skills'	=> $education['college-skills'],
'education_college_year'	=> $education['college-year'],
'education_others'			=> $education['others'],
'education_others_year'		=> $education['others-year'],
'education_highschool'		=> $education['highschool'],
'education_highschool_year'	=> $education['highschool-year'],
'education_createdby'		=> isset( $_SESSION['admin']['user']['user_id'] )
		   ? $_SESSION['admin']['user']['user_id']
		   : 0,
'education_updatedby'		=> isset( $_SESSION['admin']['user']['user_id'] )
		   ? $_SESSION['admin']['user']['user_id']
		   : 0,
'education_created'			=> date('Y-m-d H:i:s', time()),
'education_updated'			=> date('Y-m-d H:i:s', time()),
];

$this->db->flush_cache();
$educationInserted 	= $this->db->insert('applicant_education', $educationData);
$educationId 		= $this->db->insert_id();
}

$this->db->flush_cache();
$this->db->insert('applicant_skills_cyds', $skill_cyd);

//Work Experience
if ( $applicantId && isset( $applicant['work-experience'] ) ) {
for ( $i = 0; $i < count( $experiences['company'] ); $i++ ) {
if ( empty( $experiences['company'][$i] ) ) {
continue;
}
$experiencesData[] = [
'experience_applicant'	=> $applicantId,
'experience_company'	=> $newWorkExperiences['company'][$i],
'experience_position'	=> $newWorkExperiences['experience_position'][$i],
'experience_salary'		=> $newWorkExperiences['experience_salary'][$i],
'experience_country'    => $newWorkExperiences['country'][$i],

'hospital_level'    	=> $newWorkExperiences['hospital_level'][$i],
'bed_capacity'    		=> $newWorkExperiences['bed_capacity'][$i],
'reasonOfLeaving'    	=> $newWorkExperiences['reasonOfLeaving'][$i],
'salary'    			=> $newWorkExperiences['salary'][$i],
'typeOfResidence'    	=> $newWorkExperiences['typeOfResidence'][$i],
'nationality'    		=> $newWorkExperiences['nationality'][$i],
'NoFamilyMembers'    	=> $newWorkExperiences['NoFamilyMembers'][$i],
'extraExperience10'    	=> $newWorkExperiences['extraExperience10'][$i],
'extraExperience11'    	=> $newWorkExperiences['extraExperience11'][$i],
'extraExperience12'    	=> $newWorkExperiences['extraExperience12'][$i],

'experience_from'		=> $newWorkExperiences['from'][$i],
'experience_to'			=> $newWorkExperiences['to'][$i],
'experience_years'		=> $newWorkExperiences['years'][$i],
'experience_createdby'	=> $_SESSION['admin']['user']['user_id'],
'experience_updatedby'	=> $_SESSION['admin']['user']['user_id'],
'experience_created'	=> date( 'Y-m-d H:i:s', time() ),
'experience_updated'	=> date( 'Y-m-d H:i:s', time() ),
];
}

if ( count( $experiencesData ) > 0 ) {
$this->db->flush_cache();
$experienceInserted = $this->db->insert_batch('applicant_experiences', $experiencesData);
}
}

//Visa
//       $visaData = $this->rawApplicantVisa([ 'visa_applicant' => $applicantId]);
// $this->db->flush_cache();
// $visaInserted = $this->db->insert( 'applicant_visa', $visaData);
// $visaId       = $this->db->insert_id();

//Certificates
$certificateData     = $this->rawApplicantCertificate( [ 'certificate_applicant' => $applicantId ] );
$this->db->flush_cache();
$certificateInserted = $this->db->insert( 'applicant_certificate', $certificateData);
$certificateId       = $this->db->insert_id();

//Requirements
$requirementData     = $this->rawApplicantRequirements( [ 'requirement_applicant' => $applicantId ] );
$this->db->flush_cache();
$requirementInserted = $this->db->insert( 'applicant_requirement', $requirementData);
$requirementId       = $this->db->insert_id();

$applicant = $this->getApplicantById( $applicantId );

//Rollback if transaction fails
if ( ! $this->db->trans_status() || ! $applicantInserted) {
$this->db->trans_rollback();
return false;
} 

$this->endProcess();

$this->addLog('Applicant has been registered.', $applicantId, 0, $this->status['Available'], date( 'Y-m-d', time() ) );

//Commit transaction
$this->db->trans_commit();	

return $applicant;
}

public function changePhoto( $applicantId )
{
$file = [
'name'     => $_FILES['applicant']['name']['photo'],
'type'     => $_FILES['applicant']['type']['photo'],
'tmp_name' => $_FILES['applicant']['tmp_name']['photo'],
'error'    => $_FILES['applicant']['error']['photo'],
'size'     => $_FILES['applicant']['size']['photo'],
];

if ( ! $file['error'] ) {
$filePath = $this->uploadPhoto( $applicantId, $file );

$this->db->flush_cache();
$this->db->where([
'applicant_id' => $applicantId,
])->update( 'applicant', [
'applicant_photo' => $filePath,
]);

return true;
}

return false;	
}

public function addLog( $remarks, $applicantId, $employerId, $status = null, $date = null ,$repat_date = '0000-00-00' )
{
$applicant =
$logData   = [];

//Start Transaction
$this->db->trans_begin();

//Get applicant info
$applicant = 
$this->db->from( 'applicant' )
->where([
'applicant_id' => $applicantId,
])
->get()->row_array();

//Prefer log data

$cyd_get_id = $this->cyd_get_id();      
if(!isset($cyd_get_id)){
$cyd_get_id = 0;
}

$logData = [
'log_applicant'  => $applicantId,
'log_employer'   => $employerId,
'log_status'     => is_null( $status ) ? $applicant['applicant_status'] : $status,
'log_country'    => $applicant['applicant_preferred_country'],
'repat_date'     => $repat_date,
'log_date'       => is_null( $date ) ? date( 'Y-m-d', time() ) : $date,
'log_remarks'    => $remarks,
'log_createdby'  => $cyd_get_id,
'log_created'    => date( 'Y-m-d H:i:s', time() ),
];

//Insert
$this->db->flush_cache();
$this->db->insert( 'applicant_log', $logData );

//Rollback if transaction fails
if ( ! $this->db->trans_status() ) {
$this->db->trans_rollback();
return false;
}

//Commit transaction
$this->db->trans_commit();

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
$applicant = $this->getApplicantById( $applicantId );

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
//$this->db->trans_begin();

//Update applicant profile

if(!isset($post['type']))$post['type'] = '';

$applicantData = [
'applicant_first'			   => ucwords( $basic['first'] ),
'applicant_middle'			   => ucwords( $basic['middle'] ),
'applicant_last'		 	   => ucwords( $basic['last'] ),
'applicant_birthdate'          => date('Y-m-d', strtotime( $basic['birthdate'] ) ),
'applicant_age'				   => 0, //This will compute automatically be the db trigger
'applicant_gender'			   => $basic['gender'],
'applicant_contacts'           => $basic['contacts'],
'applicant_contacts_2'           => $basic['contacts2'],
'applicant_contacts_3'           => $basic['contacts3'],
'applicant_address'			   => $basic['address'],
'applicant_email'			   => $basic['email'],
'applicant_nationality'		   => $basic['nationality'],
'applicant_civil_status'	   => $basic['status'],
'applicant_religion'		   => $basic['religion'],
'applicant_languages'		   => $basic['languages'],
'applicant_height'			   => $basic['height'],
'applicant_weight'			   => $basic['weight'],
'applicant_preferred_position' => $post['preferred-position'],
'currency' 					   => $post['currency'],
'applicant_children'		   => $basic['children'],
'applicant_mothers'			   => $basic['applicant_mothers'],
'applicant_expected_salary'    => $post['expected-salary'],
'applicant_preferred_country'  => $post['preferred-country'],
'applicant_other_skills'       => $post['other-skills'],
'applicant_source'			   => $post['source'],
'applicant_position_type'		=> $post['type'],
'other_source'			   => $basic['other_source'],
'applicant_date_interview'	=>  $basic['date-interview'],
'applicant_remarks1'	=>  	$basic['remarks1'],
'applicant_remarks_3'	=>  	$basic['remarks_3'],
'applicant_jobs'	=>  	$basic['applicant_jobs'],
'applicant_by_interview'	=>  $basic['date-by'],
'applicant_ex'	=>  $basic['applicant_ex'],
'applicant_ppt_stat'	=>  $basic['applicant_ppt_stat'],
'applicant_ppt_pay'	=>  $basic['applicant_ppt_pay'],
'applicant_incase_name'		 => $basic['applicant_incase_name'],
'applicant_incase_relation'	 => $basic['applicant_incase_relation'],
'applicant_incase_contact'	 => $basic['applicant_incase_contact'],
'applicant_incase_address'	 => $basic['applicant_incase_address'],
'applicant_remarks'            => $post['remarks'],
'applicant_slug'               => str_pad( $applicantId, 10, '0', STR_PAD_LEFT )
		 .'/'.strSlug( $basic['first'].' '.$basic['middle'].' '.$basic['last'] ),
'applicant_date_applied'       => date( 'Y-m-d', strtotime( $post['date-applied'] ) ),
'applicant_updatedby'          => $_SESSION['admin']['user']['user_id'],
'applicant_updated'            => date('Y-m-d H:i:s', time()),
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
'applicant_id'			=> $applicantId,
'ironing'				=> $post['is_ironing'],
'cooking'				=> $post['is_cooking'],
'sewing'				=> $post['is_sewing'],
'computer'				=> $post['is_computer'],
'arabic_cooking'		=> $post['is_arabic_cooking'],
'baby_sitting'			=> $post['is_baby_sitting'],
'children_care'			=> $post['is_children_care'],
'tutoring'				=> $post['is_tutoring'],
'cleaning'				=> $post['is_cleaning'],
'washing'				=> $post['is_washing'],
'manicure'			=> $post['is_manicure'],
'massage'			=> $post['is_massage'],
'blower'			=> $post['is_blower'],
'coloring'			=> $post['is_coloring'],
'write_e'			=> $post['write_e'],
'read_e'			=> $post['read_e'],
'speak_e'			=> $post['speak_e'],
'write_a'			=> $post['write_a'],
'read_a'			=> $post['read_a'],
'speak_a'			=> $post['speak_a'],
'updated_at'			=> date( 'Y-m-d H:i:s', time() ),
];

$this->db->flush_cache();
$this->db->where('applicant_id', $applicantId);
$this->db->from('applicant_skills_cyds');
$count_skill_cyd = $this->db->count_all_results();

if($count_skill_cyd == 0){
$this->db->insert('applicant_skills_cyds', $skill_cyd_data);
}else{
$this->db->where([
'applicant_id' => $applicantId,
])
->update( 'applicant_skills_cyds', $skill_cyd_data );
}


if(isset($post['training-branch'])) 	$applicantData['training_branches_id'] 	= $post['training-branch'];
if(isset($post['training-status'])) 	$applicantData['applicant_status'] 	= $post['training-status'];
if(isset($post['training-start'])) 		$applicantData['start_training_at'] 	= $post['training-start'];
if(isset($post['training-end'])) 		$applicantData['end_training_at'] 		= $post['training-end'];
if(isset($post['training-remarks'])) 	$applicantData['training_remarks'] 	= $post['training-remarks'];

$this->db->flush_cache();
$applicantUpdated =
$this->db->where([
'applicant_id' => $applicantId,
])
->update( 'applicant', $applicantData );

//Remove preferred positions
foreach ( $applicant['other-preferred-positions'] as $position ) {
if ( ! in_array( $position['position_id'], $preferredPositions ) ) {
$preferredPositionsRemove[] = $position['position_id'];
continue;
}
}

if ( count( $preferredPositionsRemove ) > 0 ) {
$this->db->flush_cache();
$this->db->where_in( 'position_position', $preferredPositionsRemove )
->where([
'position_applicant' => $applicantId,
])
->delete( 'applicant_preferred_positions' );
}

//Add preferred Positions
foreach ( $preferredPositions as $positionId ) {
if ( ! array_key_exists( $positionId, $applicant['other-preferred-positions'] ) ) {
$preferredPositionsData[] = [
'position_applicant'   => $applicantId,
'position_position'    => $positionId,
'position_createdby'   => $_SESSION['admin']['user']['user_id'],
'position_created'     => date( 'Y-m-d H:i:s', time() ),
];
}
}

if ( count( $preferredPositionsData ) > 0 ) {
$this->db->flush_cache();
$this->db->insert_batch( 'applicant_preferred_positions', $preferredPositionsData );
}

//Remove preferred countries
foreach ( $applicant['other-preferred-countries'] as $country ) {
if ( ! in_array( $country['country_id'], $preferredCountries ) ) {
$preferredCountriesRemove[] = $country['country_id'];
continue;
}
}

if ( count( $preferredCountriesRemove ) > 0) {
$this->db->flush_cache();
$this->db->where_in( 'country_country', $preferredCountriesRemove )
->where([
'country_applicant' => $applicantId,
])
->delete( 'applicant_preferred_countries' );
}
//Add preferred Countries
foreach ( $preferredCountries as $countryId ) {
if ( ! array_key_exists( $countryId, $applicant['other-preferred-countries'] ) ) {
$preferredCountriesData[] = [
'country_applicant'  => $applicantId,
'country_country'    => $countryId,
'country_createdby'  => $_SESSION['admin']['user']['user_id'],
'country_created'    => date( 'Y-m-d H:i:s', time() ),
];
}
}

if ( count( $preferredCountriesData ) > 0 ) {
$this->db->flush_cache();
$this->db->insert_batch( 'applicant_preferred_countries', $preferredCountriesData );
}

//Update passport
$passportData = [
'passport_number'         => $passport['number'],
'passport_issue'          => date( 'Y-m-d', strtotime( $passport['issue'] ) ),
'passport_issue_place'    => $passport['issue-place'],
'passport_expiration'     => date( 'Y-m-d', strtotime( $passport['expiration'] ) ),
'passport_updatedby'      => $_SESSION['admin']['user']['user_id'],
'passport_updated'        => date( 'Y-m-d H:i:s', time() ),
];

$this->db->flush_cache();
$passportUpdated =
$this->db->where([
'passport_applicant'  => $applicantId
])
->update( 'applicant_passport', $passportData);

//Update educational background
$educationData = [
'education_mba'				=> $education['mba'],
'education_mba_course'		=> $education['mba-course'],
'education_mba_year'		=> $education['mba-year'],
'education_college'			=> $education['college'],
'education_college_skills'	=> $education['college-skills'],
'education_college_year'	=> $education['college-year'],
'education_others'			=> $education['others'],
'education_others_year'		=> $education['others-year'],
'education_highschool'		=> $education['highschool'],
'education_highschool_year'	=> $education['highschool-year'],
'education_updatedby'		=> $_SESSION['admin']['user']['user_id'],
'education_updated'			=> date( 'Y-m-d H:i:s', time() ),
];

$this->db->flush_cache();
$educationUpdated =
$this->db->where([
'education_applicant' => $applicantId,
])
->update( 'applicant_education', $educationData );


//Remove unselected work experiences
foreach ( $applicant['experiences'] as $experienceId => $experience ) {
if ( ! isset( $oldWorkExperiences['company'][$experienceId] ) ) {
$workExperiencesRemove[] = $experienceId;
continue;
}

$workExperiencesUpdate = [
'experience_applicant'  => $applicantId,
'experience_company'    => $oldWorkExperiences['company'][$experienceId],
'experience_position'   => $oldWorkExperiences['experience_position'][$experienceId],
'experience_salary'     => $oldWorkExperiences['experience_salary'][$experienceId],
'experience_country'    => $oldWorkExperiences['country'][$experienceId],

'hospital_level'    	=> $oldWorkExperiences['hospital_level'][$experienceId],
'bed_capacity'    		=> $oldWorkExperiences['bed_capacity'][$experienceId],
'reasonOfLeaving'    	=> $oldWorkExperiences['reasonOfLeaving'][$experienceId],
'salary'    			=> $oldWorkExperiences['salary'][$experienceId],
'typeOfResidence'    	=> $oldWorkExperiences['typeOfResidence'][$experienceId],
'nationality'    		=> $oldWorkExperiences['nationality'][$experienceId],
'NoFamilyMembers'    	=> $oldWorkExperiences['NoFamilyMembers'][$experienceId],
'extraExperience10'    	=> $oldWorkExperiences['extraExperience10'][$experienceId],
'extraExperience11'    	=> $oldWorkExperiences['extraExperience11'][$experienceId],
'extraExperience12'    	=> $oldWorkExperiences['extraExperience12'][$experienceId],

'experience_from'       => $oldWorkExperiences['from'][$experienceId],
'experience_to'         => $oldWorkExperiences['to'][$experienceId],
'experience_years'      => $oldWorkExperiences['years'][$experienceId],
'experience_createdby'  => $_SESSION['admin']['user']['user_id'],
'experience_updatedby'  => $_SESSION['admin']['user']['user_id'],
'experience_created'    => date( 'Y-m-d H:i:s', time() ),
'experience_updated'    => date( 'Y-m-d H:i:s', time() ),
];

$this->db->flush_cache();
$this->db->where([
'experience_id' => $experienceId,
])->update( 'applicant_experiences', $workExperiencesUpdate );
}

if ( count( $workExperiencesRemove ) > 0) {
$this->db->flush_cache();
$this->db->where_in( 'experience_id', $workExperiencesRemove )
->where([
'experience_applicant' => $applicantId,
])
->delete( 'applicant_experiences' );
}

//Add new work experiences
if ( isset( $newWorkExperiences['company'] ) ) {
for ( $i = 0; $i < count( $newWorkExperiences['company'] ); $i++ ) {				
if ( empty( $newWorkExperiences['company'][$i] ) ) {
continue;
}

$workExperiencesData[] = [
'experience_applicant'	=> $applicantId,
'experience_company'	=> $newWorkExperiences['company'][$i],
'experience_position'	=> $newWorkExperiences['experience_position'][$i],
'experience_salary'		=> $newWorkExperiences['experience_salary'][$i],
'experience_country'    => $newWorkExperiences['country'][$i],

'hospital_level'    	=> $newWorkExperiences['hospital_level'][$i],
'bed_capacity'    		=> $newWorkExperiences['bed_capacity'][$i],
'reasonOfLeaving'    	=> $newWorkExperiences['reasonOfLeaving'][$i],
'salary'    			=> $newWorkExperiences['salary'][$i],
'typeOfResidence'    	=> $newWorkExperiences['typeOfResidence'][$i],
'nationality'    		=> $newWorkExperiences['nationality'][$i],
'NoFamilyMembers'    	=> $newWorkExperiences['NoFamilyMembers'][$i],
'extraExperience10'    	=> $newWorkExperiences['extraExperience10'][$i],
'extraExperience11'    	=> $newWorkExperiences['extraExperience11'][$i],
'extraExperience12'    	=> $newWorkExperiences['extraExperience12'][$i],

'experience_from'		=> $newWorkExperiences['from'][$i],
'experience_to'			=> $newWorkExperiences['to'][$i],
'experience_years'		=> $newWorkExperiences['years'][$i],
'experience_createdby'	=> $_SESSION['admin']['user']['user_id'],
'experience_updatedby'	=> $_SESSION['admin']['user']['user_id'],
'experience_created'	=> date( 'Y-m-d H:i:s', time() ),
'experience_updated'	=> date( 'Y-m-d H:i:s', time() ),
];
}

if ( count ( $workExperiencesData ) > 0 ) {
$experienceInserted = $this->db->insert_batch('applicant_experiences', $workExperiencesData);
}
}


//Get the updated applicant record
$applicant = $this->getApplicantById( $applicantId );


//Rollback if transaction fails
if ( ! $this->db->trans_status() || ! $applicantUpdated || ! $passportUpdated || ! $educationUpdated) {
$this->db->trans_rollback();
return false;
} 

$this->endProcess();

//Commit transaction
$this->db->trans_commit();	

return $applicant;
} 

public function updateApplicantCertificates( $applicantId )
{
$applicant = $this->getApplicantById( $applicantId );
$post      = $_POST['applicant'];

$certificate     =
$certificateData = [];

$certificate     = $post['certificate'];

//Start Transaction
$this->db->trans_begin();

$certificateData = [
'certificate_applicant'          => $applicantId,
'certificate_tesda'              => isset( $certificate['tesda'] ),
'certificate_info_sheet'         => isset( $certificate['info-sheet'] ),
'certificate_authenticated'      => isset( $certificate['authenticated'] ),
// 'red_ribbon_file_date'			 => date( 'Y-m-d H:i:s', strtotime( $certificate['red-filed-date'] ) ),
//'red_ribbon_expired_date'		 => date( 'Y-m-d H:i:s', strtotime( $certificate['red-expired-date'] ) ),
'certificate_authenticated_nbi'  => isset( $certificate['authenticated-nbi'] ),
'nbi_expired_date'				 => date( 'Y-m-d H:i:s', strtotime( $certificate['nbi-expired-date'] ) ),
'certificate_insurance'          => $certificate['insurance'],
'insurance_no'			         => $certificate['insurance-no'],
'certificate_medical_clinic'     => $certificate['medical-clinic'],
'certificate_medical_exam_date'  => date( 'Y-m-d H:i:s', strtotime( $certificate['medical-exam-date'] ) ),
'certificate_medical_result'     => $certificate['medical-result'],
'certificate_medical_remarks'    => $certificate['medical-remarks'],
'certificate_medical_expiration' => date( 'Y-m-d H:i:s', strtotime( $certificate['medical-expiration'] ) ),
'certificate_pdos'               => isset( $certificate['pdos'] ),
'certificate_pt_result'          => $certificate['pt-result'],
'certificate_pt_result_date'     => date( 'Y-m-d H:i:s', strtotime( $certificate['pt-result-date'] ) ),
'certificate_philhealth'         => isset( $certificate['philhealth'] ),
'certificate_m1b'                => isset( $certificate['m1b'] ),
'certificate_tor'                => $certificate['tor'] ,
'certificate_prc_cert'           => $certificate['prc_cert'] ,
'certificate_prc_id'             => $certificate['prc_id'] ,
'certificate_prc_rating'         => $certificate['prc_rating'],
'certificate_coe'                => $certificate['coe'] ,
'certificate_bc'                 => $certificate['bc'] ,
'certificate_mc'                 => $certificate['mc'] ,
'applicant_certificate_no_marriage' => date( 'Y-m-d H:i:s', strtotime( $certificate['no_marriage'] ) ),
'certificate_saudi_id'           => $certificate['saudi_ids'] ,
'certificate_prc_take'           => $certificate['prc_take'],	
'certificate_ksa'         	 	 => $certificate['ksa'] ,
'certificate_haad'          	 => $certificate['haad'] ,
'certificate_qatar'          	 => $certificate['qatar'] ,
'certificate_nclex'          	 => $certificate['nclex'] ,
'certificate_nclex_exam'          	 => $certificate['nclex_exam'] ,
'certificate_ielts'          	 => $certificate['ielts'] ,
'certificate_ielts_exam'         => $certificate['ielts_exam'] ,
'certificate_ielts_overall'      => $certificate['ielts_overall'] ,
'certificate_cgfns_exam'      => $certificate['cgfns_exam'] ,
'certificate_cgfns'          	 => $certificate['cgfns'] ,
'certificate_cgfns_id'          	 => $certificate['cgfns_id'] ,
'certificate_vsh'          	 => $certificate['vsh_exam'] ,
'certificate_dha'          	 => $certificate['dha'] ,
'certificate_mmr'          	 => $certificate['mmr'] ,
'medical_fit'          	 => $certificate['medical_fit'] ,

'omma'          	 => $certificate['omma'] ,
'omma_date'          	 =>  date( 'Y-m-d', strtotime( $certificate['omma_date']  ) ),
'swab'          	 => $certificate['swab'] ,
'swab_date'          	 =>  date( 'Y-m-d', strtotime( $certificate['swab_date'] ) ) ,
'polio'          	 => $certificate['polio'] ,
'polio_date'          	 =>  date( 'Y-m-d', strtotime( $certificate['polio_date'] ) ) ,

'certificate_tesda_date'     => $certificate['tesda_date'] ,
'certificate_tesda_release'     => $certificate['tesda_release'] ,
'certificate_tesda_assest'     => $certificate['certificate_tesda_assest'] ,
'certificate_tesda_name'     => $certificate['tesda_name'] ,
'certificate_pdos_date'      => $certificate['pdos_date'] ,
'certificate_pdos_no'      => $certificate['pdos_no'] ,
'fra_pdos'      => $certificate['fra_pdos'] ,
'owwa_number'                => $certificate['owwanumber'] ,
'certificate_owwa'           => isset( $certificate['owwa'] ),
'certificate_owwa_from'      => $certificate['owwafrom'] ,
'certificate_owwa_file'      => $certificate['owwafrom'] ,
'certificate_owwa_to'     	 => $certificate['owwato'] ,
'localflight'     	 => $certificate['localflight'] ,
'certificate_updatedby'          => $_SESSION['admin']['user']['user_id'],
'certificate_updated'            => date( 'Y-m-d H:i:s', time() ),        
];
$this->db->flush_cache();
$certificateUpdated =
$this->db->where([
'certificate_applicant' => $applicantId,
])->update( 'applicant_certificate', $certificateData );

//Get the updated applicant record
$applicant = $this->getApplicantById( $applicantId );

//Rollback if transaction fails
if ( ! $this->db->trans_status() || ! $certificateUpdated) {
$this->db->trans_rollback();
return false;
} 

$this->endProcess();

//Commit transaction
$this->db->trans_commit();	

return $applicant;
}






public function updateApplicantRequirements( $applicantId )
{
	

	
	
	
	
	
	
$applicant = $this->getApplicantById( $applicantId );
$post      = $_POST['applicant'];

$requirement      =
$requirementsData = [];

$requirement      = $post['requirement'];

//Start Transaction
$this->db->trans_begin();

$requirementsData = [
'requirement_applicant'           => $applicantId,
'requirement_trade_test'          => isset( $requirement['trade-test'] ),
'requirement_trade_remarks'		  => $requirement['tesda-remarks'],
'requirement_picture_status'      => $requirement['picture-status'],
//'requirement_coe'                 => isset( $requirement['coe'] ),
'requirement_school_records'      => $requirement['school-records'],
'requirement_visa'                => isset( $requirement['visa'] ),            
'requirement_visa_date'           => date( 'Y-m-d', strtotime( $requirement['visa-date'] ) ),
'requirement_visa_stamp'           => date( 'Y-m-d', strtotime( $requirement['visa-stamp'] ) ),
'requirement_visa_release_date'   => date( 'Y-m-d', strtotime( $requirement['visa-release-date'] ) ),
'requirement_visa_expiration'     => date( 'Y-m-d', strtotime( $requirement['visa-expiration'] ) ),
'requirement_oec_number'          => $requirement['oec-number'],
'requirement_oec_submission_date' => date( 'Y-m-d', strtotime( $requirement['oec-submission-date'] ) ),
'requirement_oec_release_date'    => date( 'Y-m-d', strtotime( $requirement['oec-release-date'] ) ),
'requirement_owwa_certificate'    => $requirement['owwa-certificate'],
'requirement_owwa_schedule'       => date( 'Y-m-d', strtotime( $requirement['owwa-schedule'] ) ),
'requirement_contract'            => date( 'Y-m-d', strtotime( $requirement['contract'] ) ),
'requirement_mofa'                => $requirement['mofa'],
'requirement_job_offer'           => $requirement['job-offer'],
'requirement_job_received'           => date( 'Y-m-d', strtotime( $requirement['jo-received'] ) ),
'requirement_job_accepted'           => date( 'Y-m-d', strtotime( $requirement['jo-accept'] ) ),
'offer_letter'			          => $requirement['offer-letter'],
'requirement_ticket'              => $requirement['ticket'],
'ticket_no'			              => $requirement['ticket-no'],
'covidme'			              => $requirement['covidme'],
'covid_name'			              => $requirement['covid_name'],
'covid_date'			              => $requirement['covid_date'],
'covid_date2'			              => $requirement['covid_date2'],
'covid_loc'			              => $requirement['covid_loc'],
'covid_yellow'			              => $requirement['covid_yellow'],
'covid_cert'			              => $requirement['covid_cert'],
'flight_date'			          => date( 'Y-m-d', strtotime( $requirement['flight-date'] ) ),
'ticket_remarks'			      => $requirement['flight-remarks'],
'requirement_offer_salary'        => $requirement['offer-salary'],
'requirement_remarks'             => $requirement['remarks'],
'requirement_visa_no'             => $requirement['visa-no'],
'requirement_visa_category'        => $requirement['visa-category'],
'applicant_requirement_visaremarks'        => $requirement['visaremarks'],
'stamped_kuw'        => $requirement['stamped_kuw'],
'applicant_requirement_ecode'        => $requirement['ecode'],
'applicant_requirement_paid'        => $requirement['paid'],
'applicant_requirement_rfp'        => $requirement['rfp'],
'transnum'        => $requirement['transnum'],
'ticket_plus'        => $requirement['ticket_plus'],
'applicant_requirement_lastpage'        => $requirement['lastpage'],
'applicant_requirement_mol'        => $requirement['mol'],
'applicant_requirement_peos'        => $requirement['peos'],
'applicant_requirement_peosd'        => $requirement['peosd'],
'applicant_requirement_ereg'        => $requirement['ereg'],
'applicant_requirement_eregd'        => $requirement['eregd'],
'applicant_requirement_kawala'        => $requirement['kawala'],
'applicant_requirement_oec_expired'  => $requirement['oecexpired'],
'requirement_contract_sign'  => $requirement['sign'],
'vfs'  => $requirement['vfs'],
'requirement_musaned_encoded'			          => date( 'Y-m-d', strtotime( $requirement['requirement_musaned_encoded'] ) ),
'requirement_musaned_approved'			          => date( 'Y-m-d', strtotime( $requirement['requirement_musaned_approved'] ) ),
'requirement_musaned_sign'			          => date( 'Y-m-d', strtotime( $requirement['requirement_musaned_sign'] ) ),

'requirement_updatedby'           	=> $_SESSION['admin']['user']['user_id'],
'requirement_updated'             	=> date( 'Y-m-d H:i:s', time() ),
'visa_duration'						=> $requirement['visa_duration'],

];





$this->db->flush_cache();
$requirementsUpdated =
$this->db->where([
'requirement_applicant' => $applicantId,
])->update( 'applicant_requirement', $requirementsData );

//Update applicant job offer
$this->db->flush_cache();
$this->db->where([
'applicant_id' => $applicantId,
])->update( 'applicant', [ 'applicant_job' => $requirement['job-offer'] ] );



//Update applicant job offer
$this->db->flush_cache();
$this->db->where([
'applicant_id' => $applicantId,
])->update( 'applicant', [ 'applicant_fb' => $requirement['applicant_fb'] ] );


//Update applicant job offer
$this->db->flush_cache();
$this->db->where([
'applicant_id' => $applicantId,
])->update( 'applicant', [ 'applicant_employer_idno' => $requirement['applicant_employer_idno'] ] );



//Update applicant job offer
$this->db->flush_cache();
$this->db->where([
'applicant_id' => $applicantId,
])->update( 'applicant', [ 'applicant_employer_address' => $requirement['applicant_employer_address'] ] );

//Update applicant job offer
$this->db->flush_cache();
$this->db->where([
'applicant_id' => $applicantId,
])->update( 'applicant', [ 'applicant_employer_number' => $requirement['employer_number'] ] );



//Update applicant job offer
$this->db->flush_cache();
$this->db->where([
'applicant_id' => $applicantId,
])->update( 'applicant', [ 'sub_employer' => $requirement['sub_employer'] ] );




//createBilling
$billFine = true;
// if ( ! $applicant['requirement_job_offer'] && $requirement['job-offer'] > 0 ) {
// 	$this->load->model( 'm_billing' );
// 	$billFine = ( new m_billing )->createBilling( $applicantId );
// }

if ( $requirement['job-offer'] > 0 ) {

$this->load->model( 'm_billing' );

if (  ! ( new m_billing )->hasBilling( $applicantId ) ) {
$billFine = ( new m_billing )->createBilling( $applicantId );
}
}		

//Get the updated applicant record
$applicant = $this->getApplicantById( $applicantId );

//Rollback if transaction fails
if ( ! $this->db->trans_status() || ! $requirementsUpdated || ! $billFine ) {
$this->db->trans_rollback();
return false;
} 

$this->endProcess();

//Commit transaction
$this->db->trans_commit();	

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
'file_applicant' => $applicantId,
'file_name'      => $post['name'],
'file_type'      => $post['type'],
'file_size'      => $file['size'],
'file_mime'      => $file['type'],
'file_path'      => $applicantPath . $fileName,
'file_status'    => 1,
'file_createdby' => $_SESSION['admin']['user']['user_id'],
'file_created'   => date( 'Y-m-d H:i:s', time() ),
];

$this->db->flush_cache();
$this->db->insert( 'applicant_files', $fileData );
$fileId = $this->db->insert_id();

$file = $this->getApplicantFileById( $fileId );

return $file;
}

public function updateApplicantStatus( $applicantId )
{
$applicant = $this->getApplicantById( $applicantId );
$post      = $_POST['applicant'];

$log       =
$logData   = [];

$log      = $post['status'];

$logInserted = $this->addLog( $log['remarks'], $applicantId, $log['employer'], $log['status'], date( 'Y-m-d', strtotime( $log['date'] ) ) ,$log['repat_date']);

//Update applicant status
$this->db->flush_cache();
$this->db->where([
'applicant_id' => $applicantId,
])
->update( 'applicant', [
'applicant_employer' => $log['employer'],
'applicant_status'   => $log['status'],
'sub_status'	     => $log['substatus'],
'applicantNumber'    => $log['applicant-no'],
'applicant_remarks'    => $log['remarks'],
'optional_statuses_id'  => $log['optionStatus'],
'sub_employer'    => $log['sub_employer'],
'applicant_employer_number'   => $log['employer_number'],
'applicant_employer_address'   => $log['applicant_employer_address'],
'applicant_employer_idno'   => $log['applicant_employer_idno'],
'applicant_fb'   => $log['applicant_fb'],
'is_repat'    => isset($log['is_repat']) ? $log['is_repat'] : "",
'repat_date'    => isset($log['repat_date']) ? $log['repat_date'] : "",
]);

switch ( $log['status'] ) {

case $this->status['Reserved']:

//Start Transaction
$this->db->trans_begin();

//Delete previous selected record
$this->db->flush_cache();
$this->db->where([
'reservation_applicant' => $applicantId,
])->delete( 'employer_reservation' );

$selectionData = [
'reservation_employer'   => $log['employer'],
'reservation_applicant'  => $applicantId,
'reservation_expiration' => date( 'Y-m-d', strtotime( ' +'.self::RESERVED_DAYS_EXPIRATION.' days' ) ),
'reservation_status'     => 1,
'reservation_remarks'    => '',
'reservation_date'       => fdate( 'Y-m-d', $log['date'] ),
'reservation_createdby'  => $_SESSION['admin']['user']['user_id'],
'reservation_updatedby'  => $_SESSION['admin']['user']['user_id'],
'reservation_updated'    => date( 'Y-m-d H:i:s', time() ),
'reservation_created'    => date( 'Y-m-d H:i:s', time() ),
];

$this->db->flush_cache();
$selectionInserted = $this->db->insert( 'employer_reservation', $selectionData );

//Commit transaction
$this->db->trans_commit();

break;

case $this->status['Selected']:

//Start Transaction
$this->db->trans_begin();

//Delete previous selected record
$this->db->flush_cache();
$selected = $this->db->get_where('employer_selected', [
'selected_applicant' => $applicantId,
])->row_array();

if ( empty( $selected ) ) {

$selectedData = [
'selected_employer'   => $log['employer'],
'selected_applicant'  => $applicantId,
'selected_date'       => fdate( 'Y-m-d', $log['date'] ),
'selected_remarks'    => $log['remarks'],
'selected_createdby'  => $_SESSION['admin']['user']['user_id'] ,
'selected_updatedby'  => $_SESSION['admin']['user']['user_id'] ,
'selected_updated'    => date( 'Y-m-d H:i:s', time() ),
'selected_created'    => date( 'Y-m-d H:i:s', time() ),
];

$this->db->flush_cache();
$this->db->insert( 'employer_selected', $selectedData );

} else {

$selectedData = [
'selected_employer'   => $log['employer'],
'selected_date'       => fdate( 'Y-m-d', $log['date'] ),
'selected_remarks'    => $log['remarks'],
'selected_updatedby'  => $_SESSION['admin']['user']['user_id'] ,
'selected_updated'    => date( 'Y-m-d H:i:s', time() ),
];

$this->db->flush_cache();
$this->db->where([
'selected_id' => $selected['selected_id'],
])
->update( 'employer_selected', $selectedData );

}

//Rollback if transaction fails
if ( ! $this->db->trans_status() ) {
$this->db->trans_rollback();
return false;
}

//Commit transaction
$this->db->trans_commit();

break;

case $this->status['Deployed']:

$this->db->flush_cache();

//Add to deployed list
$deployedData = [
'deployed_applicant'  => $applicantId,
'deployed_employer'   => $applicant['applicant_employer'],
'deployed_job'        => $applicant['applicant_job'],
'deployed_country'    => $applicant['applicant_preferred_country'],
'deployed_position'   => $applicant['job_position'],
'deployed_salary'     => (float) $applicant['applicant_expected_salary'],
'deployed_remarks'    => 'Applicant has been added to deployed list.',
'deployed_date'       => fdate( 'Y-m-d', $log['date'] ),
'deployed_createdby'  => $_SESSION['admin']['user']['user_id'],
'deployed_updatedby'  => $_SESSION['admin']['user']['user_id'],
'deployed_created'    => date( 'Y-m-d H:i:s', time() ),
'deployed_updated'    => date( 'Y-m-d H:i:s', time() ),
];

$this->db->flush_cache();
$this->db->insert( 'deployed', $deployedData);

$this->db->flush_cache();
$this->db->set('`job_occupied`', '`job_occupied` + 1', FALSE)
->where([
'job_id' => $applicant['applicant_job'],
])->update( 'job' );

//create Commission for recruitment agent
$this->load->model( 'm_commission_recruitment_agent' );
( new m_commission_recruitment_agent )->createCommission( $applicantId );

if ( $applicant['applicant_employer'] ) {
$this->load->model( 'm_commission_marketing_agency' );
( new m_commission_marketing_agency )->createCommission( $applicant['applicant_employer'], $applicantId );

$this->load->model( 'm_commission_marketing_agent' );
( new m_commission_marketing_agent )->createCommission( $applicant['applicant_employer'], $applicantId );
}

break;
}

$applicant = [];

if ( $logInserted ) {
$applicant = $this->getApplicantById( $applicantId );
}

return $applicant;
}

public function reserveApplicant( $applicantId, $employerId )
{
$selectionData = $applicantData = [];

$selectionData = [
'reservation_employer'   => $employerId,
'reservation_applicant'  => $applicantId,
'reservation_expiration' => date( 'Y-m-d', strtotime( ' +'.self::RESERVED_DAYS_EXPIRATION.' days' ) ),
'reservation_status'     => 1,
'reservation_remarks'    => '',
'reservation_date'       => date( 'Y-m-d', time() ),
'reservation_createdby'  => $_SESSION['employer']['user']['user_id'],
'reservation_updatedby'  => $_SESSION['employer']['user']['user_id'],
'reservation_updated'    => date( 'Y-m-d H:i:s', time() ),
'reservation_created'    => date( 'Y-m-d H:i:s', time() ),
];

$this->db->flush_cache();
$selectionInserted = $this->db->insert( 'employer_reservation', $selectionData );

//$logInserted = $this->addLog( $log['remarks'], $applicantId, $log['employer'], $log['status'], date( 'Y-m-d', strtotime( $log['date'] ) ) );

$applicantData = [
'applicant_status'   => $this->status['Reserved'],
'applicant_employer' => $employerId,
'applicant_job'      => 0,
];

$this->db->flush_cache();
$applicantUpdated =
$this->db->where([
'applicant_id' => $applicantId,
])
->update( 'applicant', $applicantData);

if ( $selectionInserted && $applicantUpdated ) {
$this->addLog('Applicant was reserved', $applicantId, $employerId, $this->status['Reserved']);
return true;
}

return false;
}

public function delete_multipleLineup( $applicantId){
$this->db->delete('multiple_lineups', array('applicant_id' => $applicantId));  
}

public function extendReserveApplicant( $reservationId, $daysToExtend = self::RESERVED_DAYS_EXPIRATION, $remarks = '' )
{
$reservationData = [
'reservation_expiration' => date( 'Y-m-d', strtotime( ' +'.$daysToExtend.' days' ) ),
'reservation_remarks'    => $remarks,
'reservation_updatedby'  => $_SESSION['admin']['user']['user_id'],
'reservation_updated'    => date( 'Y-m-d', time() ),
];

$this->db->flush_cache();
$reserveUpdated = 
$this->db->where([
'reservation_id' => $reservationId,
])->update( 'employer_reservation', $reservationData );

return $reserveUpdated;
}

public function unReserveApplicant( $applicantId, $employerId )
{
//Delete reservation entry
$this->db->flush_cache();
$selectionDeleted = 
$this->db->where([
'reservation_employer'  => $employerId,
'reservation_applicant' => $applicantId,
])->delete( 'employer_reservation' );

//Revert applicant status 
$applicantData = [
'applicant_status'   => $this->status['Available'],
'applicant_employer' => 0,
'applicant_job'       => 0,
];

$this->db->flush_cache();
$applicantUpdated = 
$this->db->where([
'applicant_id' => $applicantId,
])->update( 'applicant', $applicantData );

$this->addLog('Unreserved the applicant.', $applicantId, $employerId, $this->status['Available'], date( 'Y-m-d', time() ) );

return $selectionDeleted && $applicantUpdated;
}

public function selectApplicant( $applicantId, $employerId, $remarks = '' )
{
$selectedData = $applicantData = [];

$this->db->flush_cache();
$selected = 
$this->db->get_where( 'employer_selected', [ 
'selected_applicant' => $applicantId,
'selected_employer'  => $employerId,
]);

if ( empty( $selected ) ) { 
return true;
}

$selectedData = [
'selected_employer'   => $employerId,
'selected_applicant'  => $applicantId,
'selected_remarks'    => $remarks,
'selected_createdby'  => isset( $_SESSION['employer']['user'] ) 
? $_SESSION['employer']['user']['user_id'] 
: $_SESSION['admin']['user']['user_id'],
'selected_updatedby'  => isset( $_SESSION['employer']['user'] ) 
? $_SESSION['employer']['user']['user_id'] 
: $_SESSION['admin']['user']['user_id'],
'selected_updated'    => date( 'Y-m-d H:i:s', time() ),
'selected_created'    => date( 'Y-m-d H:i:s', time() ),
];

$this->db->flush_cache();
$selectedInserted = $this->db->insert( 'employer_selected', $selectedData );

$applicantData = [
'applicant_status' => $this->status['Selected'],
];

$this->db->flush_cache();
$applicantUpdated =
$this->db->where([
'applicant_id' => $applicantId,
])
->update( 'applicant', $applicantData);

return $selectedInserted && $applicantUpdated;
}

public function unSelectApplicant( $applicantId, $employerId )
{
//Remove selection entry
$this->db->flush_cache();
$selectionDeleted = 
$this->db->where([
'selected_employer'  => $employerId,
'selected_applicant' => $applicantId,
])->delete( 'employer_selected' );

$applicantData = [
'applicant_status' => $this->status['Reserved'],
'applicant_job'    => 0,
];

$this->db->flush_cache();
$applicantUpdated =
$this->db->where([
'applicant_id' => $applicantId,
])->update( 'applicant', $applicantData );

return $applicantUpdated;
}

public function isReserved( $applicantId, $employerId )
{
$this->db->flush_cache();
$this->db->select()
->from( 'applicant a' )
->join( 'employer_reservation er', 'er.reservation_applicant = a.applicant_id' )
->where([
'er.reservation_employer'  => $employerId,
'er.reservation_applicant' => $applicantId,
'er.reservation_status'    => 1,
'a.applicant_status'       => $this->status['Reserved'],
]);

return $this->db->count_all_results() > 0;
}

public function archiveApplicant( $applicantId )
{
$strQuery = "INSERT INTO `archive_applicant` SELECT *,? FROM `applicant` WHERE `applicant_id` = ?";
/*
$this->db->flush_cache();
$archived = 
$this->db->query( $strQuery, [
$_SESSION['admin']['user']['user_id'],
$applicantId,
]);
*/
$this->db->flush_cache();
$deleted = $this->db->where([
'applicant_id' => $applicantId,
])->delete( 'applicant');

return /*$archived &&*/ $deleted;
}

public function archiveApplicantFile( $applicantId, $fileId )
{
$file = $this->getApplicantFileById( $fileId );

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

$this->db->flush_cache();
$fileUpdated = 
$this->db->where([
'file_id' => $fileId,
])->update( 'applicant_files', [ 'file_status' => 0 ] );

return $fileUpdated;
}

public function moveToAvailable( $applicantIds, $options = [] )
{
//Delete from reservation
$this->db->flush_cache();
$this->db->where_in( 'reservation_applicant', $applicantIds )
->delete( 'employer_reservation' );

//Delete from selected
$this->db->flush_cache();
$this->db->where_in( 'selected_applicant', $applicantIds )
->delete( 'employer_selected' );

foreach ( $applicantIds as $applicantId ) {
$this->clearApplicantBilling( $applicantId );
}

$this->db->flush_cache();
$this->db->where_in( 'applicant_id', $applicantIds );

$this->setDBQueryOptions( $options );

$this->db->update( 'applicant', [
'applicant_status'   => $this->status['Available'],
'applicant_employer' => 0,
'applicant_job'      => 0,
]);

$affectedApplicants = $this->db->affected_rows(); 

foreach ( $applicantIds as $applicantId ) {
$this->addLog('Applicant has been moved back to list of available.', $applicantId, 0, 1, date( 'Y-m-d', time() ) );
}

return $affectedApplicants;
}

public function clearApplicantBilling( $applicantId )
{
//Get bill
$this->db->from( 'bill' )
->where([
'bill_applicant' => $applicantId,
]);

$bill = $this->db->get()->row_array();

if ( empty( $bill ) ) {
return false;
}

//Get all ORs
$ors = [];


$this->db->from( 'or' )
->where([
'or_applicant' => $applicantId,
]);

$orRows = $this->db->get()->result_array();
$orRows = $this->indexArray( $orRows, 'or_number' );
$ors    = array_merge( $ors, array_keys( $orRows ) );


$this->db->from( 'bill_payment_applicant' )
->where([
'payment_bill'      => $bill['bill_id'],
'payment_applicant' => $applicantId,
]);

$orRows = $this->db->get()->result_array();
$orRows = $this->indexArray( $orRows, 'payment_or' );
$ors    = array_merge( $ors, array_keys( $orRows ) );

$this->db->from( 'paidall_employer_applicants' )
->where([
'paidall_bill'      => $bill['bill_id'],
'paidall_applicant' => $applicantId,
]);

$orRows = $this->db->get()->result_array();
$orRows = $this->indexArray( $orRows, 'paidall_or' );
$ors    = array_merge( $ors, array_keys( $orRows ) );


$this->db->from( 'payment_employer_detail' )
->where([
'detail_bill'      => $bill['bill_id'],
'detail_applicant' => $applicantId,
]);

$orRows = $this->db->get()->result_array();
$orRows = $this->indexArray( $orRows, 'detail_or' );
$ors    = array_merge( $ors, array_keys( $orRows ) );


$this->db->from( 'payment_worker_detail' )
->where([
'detail_bill'      => $bill['bill_id'],
'detail_applicant' => $applicantId,
]);

$orRows = $this->db->get()->result_array();
$orRows = $this->indexArray( $orRows, 'detail_or' );
$ors    = array_merge( $ors, array_keys( $orRows ) );


$orsFiltered = [];
foreach ($ors as $or) {
if ( ! in_array( $or , $orsFiltered) ) {
$orsFiltered[] = $or;	
}    		
}

//Delete transaction history
$this->db->flush_cache();
$this->db->where([
'bill_id' => $bill['bill_id'],
])
->delete( 'bill' );

$this->db->flush_cache();
$this->db->where([
'detail_bill' => $bill['bill_id'],
])
->delete( 'bill_detail' );

$this->db->flush_cache();
$this->db->where([
'payment_bill' => $bill['bill_id'],
])
->delete( 'bill_payment_applicant' );


$this->db->flush_cache();
$this->db->where([
'detail_bill' => $bill['bill_id'],
])
->delete( 'payment_worker_detail' );

$this->db->flush_cache();
$this->db->where([
'detail_bill' => $bill['bill_id'],
])
->delete( 'payment_employer_detail' );


$this->db->flush_cache();
$this->db->where_in( 'payment_or', $orsFiltered )
->delete( 'bill_payment_employer' );

$this->db->flush_cache();
$this->db->where_in( 'paidall_or', $orsFiltered )
->delete( 'paidall_employer_applicants' );

$this->db->flush_cache();
$this->db->where_in( 'or_number', $orsFiltered )
->delete( 'or' );

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
'passport_applicant'   => null,
'passport_number'      => null,
'passport_issue'       => null,
'passport_issue_place' => null,
'passport_expiration'  => null,
'passport_createdby'   => isset( $_SESSION['admin']['user']['user_id'] )
  ? $_SESSION['admin']['user']['user_id']
  : 0,
'passport_updatedby'   => isset( $_SESSION['admin']['user']['user_id'] )
  ? $_SESSION['admin']['user']['user_id']
  : 0,
'passport_created'     => date( 'Y-m-d H:i:s', time() ),
'passport_updated'     => date( 'Y-m-d H:i:s', time() ),
];

$passportData = array_merge( $passportData, $elements );

return $passportData;
}

private function rawApplicantVisa( array $elements = [] )
{
$visaData = [
'visa_applicant'   => null,
'visa_date'        => null,
'visa_release'     => null,
'visa_expiration'  => null,
'visa_status'      => null,
'visa_createdby'   => isset( $_SESSION['admin']['user']['user_id'] )
? $_SESSION['admin']['user']['user_id']
: 0,
'visa_updatedby'   => isset( $_SESSION['admin']['user']['user_id'] )
? $_SESSION['admin']['user']['user_id']
: 0,
'visa_created'     => date( 'Y-m-d H:i:s', time() ),
'visa_updated'     => date( 'Y-m-d H:i:s', time() ),
];

$visaData = array_merge( $visaData, $elements );

return $visaData;
}

private function rawApplicantCertificate( array $elements = [] )
{
$certificateData = [
'certificate_applicant'           => null,
'certificate_tesda'               => null,
'certificate_info_sheet'          => null,
'certificate_authenticated'       => null,
'certificate_authenticated_nbi'   => null,
'certificate_insurance'           => null,
'certificate_medical_clinic'      => null,
'certificate_medical_exam_date'   => null,
'certificate_medical_remarks'     => null,
'certificate_medical_expiration'  => null,
'certificate_pdos'                => null,

'certificate_philhealth'          => null,
'certificate_m1b'                 => null,
'certificate_createdby'           => isset( $_SESSION['admin']['user']['user_id'] )
			 ? $_SESSION['admin']['user']['user_id']
			 : 0,
'certificate_updatedby'           => isset( $_SESSION['admin']['user']['user_id'] )
			 ? $_SESSION['admin']['user']['user_id']
			 : 0,
'certificate_created'             => date( 'Y-m-d H:i:s', time() ),
'certificate_updated'             => date( 'Y-m-d H:i:s', time() ),            
];

$certificateData = array_merge( $certificateData, $elements );

return $certificateData;
}

private function rawApplicantRequirements( array $elements = [] )
{
$requirementsData = [
'requirement_applicant'           => null,
'requirement_visa'                => null,
'requirement_visa_date'           => null,
'requirement_visa_release_date'   => null,
'requirement_visa_expiration'     => null,
'requirement_oec_number'          => null,
'requirement_oec_submission_date' => null,
'requirement_oec_release_date'    => null,
'requirement_owwa_certificate'    => null,
'requirement_owwa_schedule'       => null,
'requirement_mofa'                => null,
'requirement_job_offer'           => null,
'requirement_remarks'             => null,
'requirement_createdby'           => isset( $_SESSION['admin']['user']['user_id'] )
			 ? $_SESSION['admin']['user']['user_id']
			 : 0,
'requirement_updatedby'           => isset( $_SESSION['admin']['user']['user_id'] )
			 ? $_SESSION['admin']['user']['user_id']
			 : 0,
'requirement_created'             => date( 'Y-m-d H:i:s', time() ),
'requirement_updated'             => date( 'Y-m-d H:i:s', time() ),            
];

$requirementsData = array_merge( $requirementsData, $elements );

return $requirementsData;
}

public function cyd_get_applicant_requirement(){
$return = array();
//requirement_oec_number search
$this->db->flush_cache();
$query = $this->db->get('applicant_requirement');
$results = $query->result();

foreach($results as $per_result) {
$return[$per_result->requirement_applicant] = $per_result;
}
return $return;
}

public function cyd_get_all_sub_position(){
$array_return = array();
$position_array_return = array();

$this->db->flush_cache();
$position_query = $this->db->get('position');
$position_results = $position_query->result();
foreach ($position_results as $position_value) {
$position_array_return[$position_value->position_id] = $position_value->position_name;
}

$this->db->flush_cache();
$query = $this->db->get('applicant_preferred_positions');
$results = $query->result();

foreach ($results as $value) {
if(!isset($array_return[$value->position_applicant]))
$array_return[$value->position_applicant] = ' ';

if(isset($position_array_return[$value->position_position])){
if(strlen($array_return[$value->position_applicant]) < 4)
$array_return[$value->position_applicant] .= $position_array_return[$value->position_position];
else
$array_return[$value->position_applicant] .= ', '.$position_array_return[$value->position_position];
}

}
return $array_return;
}

//add all application table to an array
public function cyd_get_applicants_raw(){
$applicant_array_return = array();
$applicant_query = $this->db->get('applicant');
$applicant_results = $applicant_query->result();
foreach ($applicant_results as $applicant_value) {
$applicant_array_return[$applicant_value->applicant_id] = $applicant_value;
}
return $applicant_array_return;
}

public function cyd_get_applicant_certificate_raw(){
$applicant_array_return = array();
$applicant_query = $this->db->get('applicant_certificate');
$applicant_results = $applicant_query->result();
foreach ($applicant_results as $applicant_value) {
$applicant_array_return[$applicant_value->certificate_applicant] = $applicant_value;
}
return $applicant_array_return;
}

public function cyd_applicant_requirement_raw(){
$applicant_array_return = array();
$applicant_query = $this->db->get('applicant_requirement');
$applicant_results = $applicant_query->result();
foreach ($applicant_results as $applicant_value) {
$applicant_array_return[$applicant_value->requirement_applicant] = $applicant_value;
}
return $applicant_array_return;
}

public function denyHit($applicantId){

$data = array(
'hit_status' => 'cleared',
'hit_date' => (date('Y')+1).'-0'.rand(1,9).'-01'
);

$this->db->where('applicant_id', $applicantId);
$this->db->update('applicant', $data); 

return 'Applicants Hit Denied';
}

public function allTrainingBranches(){

$query = $this->db->get('training_branches');
$allbranch = $query->result_array();
$result = [];
foreach ($allbranch as $branch) {
$result[$branch['id']] = $branch['branch_name'];
}
return $result;
}

public function get_skill_cyd($applicant_id){

$query = $this->db->get_where('applicant_skills_cyds', array('applicant_id' => $applicant_id));
return $query->result();
}
}
