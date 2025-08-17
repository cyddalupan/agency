<?php
$timezone = "Asia/Kuala_Lumpur";
if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);
$date2=date('Y-m-d');
	





if(isset($_POST['updatedata'])){
$values="";
foreach ($_POST['applicant_id'] as $lkey => $lvalue) {
//$values .= "$lkey:$lvalue;";

$ifexist=mysql_query("SELECT * FROM  applicant_certificate where certificate_applicant='".$_POST['applicant_id'][$lkey]."'");
$ifexistjud=mysql_num_rows($ifexist);
$field_to_insert = array( 
	'certificate_applicant' => $_POST['applicant_id'][$lkey],
	'certificate_tesda_date' => $_POST['certificate_tesda_date'][$lkey],
	'certificate_owwa_to' => $_POST['certificate_owwa_to'][$lkey],
	'certificate_medical_remarks' => $_POST['certificate_medical_remarks'][$lkey],
	'certificate_goback' => $_POST['certificate_goback'][$lkey],
	'certificate_lineup_remarks' => $_POST['certificate_lineup_remarks'][$lkey],
	'certificate_pre_remarks' => $_POST['certificate_pre_remarks'][$lkey],
	'certificate_process_remarks' => $_POST['certificate_process_remarks'][$lkey],
	'certificate_visa_remarks' => $_POST['certificate_visa_remarks'][$lkey],

);
if($ifexistjud>=1){
$ids=$_POST['applicant_id'][$lkey];
insert_data('applicant_certificate',$field_to_insert,$ids);
}else{
insert_data('applicant_certificate',$field_to_insert);
}


$ifexist1=mysql_query("SELECT * FROM  applicant_requirement where requirement_applicant='".$_POST['applicant_id'][$lkey]."'");
$ifexistjud1=mysql_num_rows($ifexist1);
$field_to_insert1 = array( 
	'requirement_applicant' => $_POST['applicant_id'][$lkey],
	'requirement_contract' => $_POST['requirement_contract'][$lkey],
	'requirement_contract_sign' => $_POST['requirement_contract_sign'][$lkey],
	'vfs' => $_POST['vfs'][$lkey],
	'requirement_visa_stamp' => $_POST['requirement_visa_stamp'][$lkey],
	'requirement_oec_release_date' => $_POST['requirement_oec_release_date'][$lkey],
	'requirement_exp' => $_POST['requirement_exp'][$lkey],
);
if($ifexistjud1>=1){
$ids1=$_POST['applicant_id'][$lkey];
insert_data3('applicant_requirement',$field_to_insert1,$ids1);
}else{
insert_data3('applicant_requirement',$field_to_insert1);
}





}
echo '<div style="width:28%;padding:3px;background:green;color:white;;margin-bottom:2px;margin-top:-5px">';	
echo "Successfully Updated";
echo "</div>";	

}
		


function insert_data( $table, $fields, $id = '', $exp = '' ){
	if ($id) {
		# update existing record
		# safer to reference the index on update
		$sql = "UPDATE $table SET ";
		foreach ($fields as $lkey => $lvalue) {
			$sql .= "$lkey = '$lvalue', ";
		}
		$sql = substr($sql,0,-2); // remove last comma
		$sql .= " WHERE certificate_applicant = '$id'".$exp;
		mysql_query($sql) or die(mysql_error());
	} else {
		$sql = "INSERT INTO $table (";
		$values = ' VALUES (';
		foreach ($fields as $lkey => $lvalue) {
			$sql .= "$lkey,";
			 $values .= "'$lvalue',";
		}
			
		$sql = substr($sql,0,-1); // remove last comma
		$values = substr($values,0,-1); // remove last comma	
		$sql .= ') ' . $values . ') ';
		mysql_query($sql) or die(mysql_error());
	}
}





function insert_data3( $table, $fields, $id = '', $exp = '' ){
	if ($id) {
		# update existing record
		# safer to reference the index on update
		$sql = "UPDATE $table SET ";
		foreach ($fields as $lkey => $lvalue) {
			$sql .= "$lkey = '$lvalue', ";
		}
		$sql = substr($sql,0,-2); // remove last comma
		$sql .= " WHERE requirement_applicant = '$id'".$exp;
		mysql_query($sql) or die(mysql_error());
	} else {
		$sql = "INSERT INTO $table (";
		$values = ' VALUES (';
		foreach ($fields as $lkey => $lvalue) {
			$sql .= "$lkey,";
			 $values .= "'$lvalue',";
		}
			
		$sql = substr($sql,0,-1); // remove last comma
		$values = substr($values,0,-1); // remove last comma	
		$sql .= ') ' . $values . ') ';
		mysql_query($sql) or die(mysql_error());
	}
}

function insert_data4( $table, $fields, $id = '', $exp = '' ){
	if ($id) {
		# update existing record
		# safer to reference the index on update
		$sql = "UPDATE $table SET ";
		foreach ($fields as $lkey => $lvalue) {
			$sql .= "$lkey = '$lvalue', ";
		}
		$sql = substr($sql,0,-2); // remove last comma
		$sql .= " WHERE requirement_applicant = '$id'".$exp;
		mysql_query($sql) or die(mysql_error());
	} else {
		$sql = "INSERT INTO $table (";
		$values = ' VALUES (';
		foreach ($fields as $lkey => $lvalue) {
			$sql .= "$lkey,";
			 $values .= "'$lvalue',";
		}
			
		$sql = substr($sql,0,-1); // remove last comma
		$values = substr($values,0,-1); // remove last comma	
		$sql .= ') ' . $values . ') ';
		mysql_query($sql) or die(mysql_error());
	}
}



?>



