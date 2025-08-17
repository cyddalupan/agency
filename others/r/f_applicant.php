<?php
$timezone = "Asia/Kuala_Lumpur";
if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);
$date2=date('Y-m-d');




if(isset($_POST['updatedata2'])){
$values="";
foreach ($_POST['exp_id'] as $lkey => $lvalue) {
//$values .= "$lkey:$lvalue;";

$ifexist=mysql_query("SELECT * FROM  email_address4 where exp_id='".$_POST['exp_id'][$lkey]."'");
$ifexistjud=mysql_num_rows($ifexist);
$field_to_insert = array( 
	'exp_id' => $_POST['exp_id'][$lkey],
	'request_payment' => $_POST['request_payment'][$lkey],

	
);
if($ifexistjud>=1){
$ids=$_POST['exp_id'][$lkey];
insert_data('email_address4',$field_to_insert,$ids);
}else{
insert_data('email_address4',$field_to_insert);
}


}
echo '<div style="width:20%;padding:5px;background:green;color:white;">';	
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
		$sql .= " WHERE exp_id = '$id'".$exp;
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



