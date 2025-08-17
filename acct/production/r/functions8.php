<?php
$timezone = "Asia/Kuala_Lumpur";
if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);
$date2=date('Y-m-d');



if(isset($_POST['add_exp'])){
$users = array_slice($_POST, 0, -1);
//var_dump($_POST);
insert_data('email_address',$users);
$sql1="Select * From email_address where e_id= LAST_INSERT_ID()";
echo '
<div class="alert alert-success">
<button type="button" class="close" data-dismiss="alert">×</button>
<strong>Successfully Added New!</strong>
</div>';
}




if(isset($_POST['updatedata'])){
$values="";
foreach ($_POST['applicant_id'] as $lkey => $lvalue) {
//$values .= "$lkey:$lvalue;";

$ifexist=mysql_query("SELECT * FROM  applicant where applicant_id='".$_POST['applicant_id'][$lkey]."'");
$ifexistjud=mysql_num_rows($ifexist);
$field_to_insert = array( 
	'applicant_id' => $_POST['applicant_id'][$lkey],
	'incc' => $_POST['incc'][$lkey],
	
);
if($ifexistjud>=1){
$ids=$_POST['applicant_id'][$lkey];
insert_data('applicant',$field_to_insert,$ids);
}else{
insert_data('applicant',$field_to_insert);
}


}
echo '<div style="width:98%;padding:5px;background:green;color:white;">';	
echo "Successfully Updated";
echo "</div>";	

}



if(isset($_POST['updatedata1'])){
$values="";
foreach ($_POST['applicant_id'] as $lkey => $lvalue) {
//$values .= "$lkey:$lvalue;";

$ifexist=mysql_query("SELECT * FROM  applicant where applicant_id='".$_POST['applicant_id'][$lkey]."'");
$ifexistjud=mysql_num_rows($ifexist);
$field_to_insert = array( 
	'applicant_id' => $_POST['applicant_id'][$lkey],
	'applicant_paid' => $_POST['applicant_paid'][$lkey],
	'fra_deployed' => $_POST['fra_deployed'][$lkey],
	'fra_visa' => $_POST['fra_visa'][$lkey],
	'fra_ftw' => $_POST['fra_ftw'][$lkey],
	'fra_sent' => $_POST['fra_sent'][$lkey],
	'fra_before' => $_POST['fra_before'][$lkey],
	
);
if($ifexistjud>=1){
$ids=$_POST['applicant_id'][$lkey];
insert_data('applicant',$field_to_insert,$ids);
}else{
insert_data('applicant',$field_to_insert);
}


}
echo '<div style="width:98%;padding:5px;background:green;color:white;">';	
echo "Successfully Updated...";
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
		$sql .= " WHERE applicant_id = '$id'".$exp;
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



