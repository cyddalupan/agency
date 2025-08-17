<?php 
$con = mysql_connect("localhost","abbaphil_jinhel","67PtE01S4KL4hKIqUQyoP2aKhDwud7BV");
mysql_set_charset ( "latin1_swedish_ci", $con );
if (!$con)
{
die('Could not connect: ' . mysql_error());
}
mysql_select_db("abbaphil_four", $con);

if(isset($_POST['update_profile'])){
echo '<div style="width:100%;padding:5px;background:green;color:white;">';			
//$new_patient = array_slice($_POST, 0, -3);
//var_dump($new_patient);
//insert_data('cases',$new_patient,$_POST['case_id']);
echo "Successfully Updated";
echo "</div>";

$insert=mysql_query("UPDATE table_name
SET timesched = ".$_POST['timesched'].",
passsched = ".$_POST['passsched'].",
locsched = ".$_POST['locsched']."
WHERE applicant_id=".$_POST['applicant_id']."");

}



//----------------------------------------------END OF URINE EXAM---------------
if(isset($_POST['add_applicants'])){
$users = array_slice($_POST, 0, -1);
//var_dump($_POST);
insert_data('cases',$users);
$sql1="Select * From cases where case_id= LAST_INSERT_ID()";
$get_id=mysql_fetch_array(mysql_query($sql1));
			
//$case_id=$get_id['case_id'];
//$insert=mysql_query("insert into history (case_id,remarks2,updateby,dateadded) values
//(".$case_id.",'','".$_POST['updateby']."','".$_POST['date_added']."')");

header('location:111.php?success=Added!');

echo '
<div class="alert alert-success">
<button type="button" class="close" data-dismiss="alert">×</button>
<strong>Successfully Added New Case! '.$_GET['success'].'</strong>
</div>';

}




function select_db($table,$fields,$id="",$exp=""){
if($id){
$id=" where ".$id;
}
$sql = "Select ".$fields." from ".$table.$id." ".$exp;
$rows=mysql_query($sql);
return $rows;
}

//delete data
function delete_db($table,$id){
$sql = "DELETE from ".$table." ".$id;
mysql_query($sql) or die(mysql_error());
}

//insert data 1
function insert_data( $table, $fields, $id = '', $exp = '' ){
if ($id) {
# update existing record
# safer to reference the index on update
$sql = "UPDATE $table SET ";
foreach ($fields as $lkey => $lvalue) {
$sql .= "$lkey = '$lvalue', ";
}
$sql = substr($sql,0,-2); // remove last comma
$sql .= " WHERE case_id = '$id'".$exp;
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

function insert_data2( $table, $fields, $id = '' ){
		# update existing record
		# safer to reference the index on update
		$sql = "UPDATE $table SET ";
		foreach ($fields as $lkey => $lvalue) {
			$sql .= "$lkey = '$lvalue', ";
		}
		$sql = substr($sql,0,-2); // remove last comma
		$sql .= " WHERE case_id = '$id'";
		mysql_query($sql) or die(mysql_error());
}



//split sentence
function sentence_case($string) {
$sentences = preg_split('/([.?!]+)/ ', $string, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
$new_string = '';
foreach ($sentences as $key => $sentence) {
$new_string .= ($key & 1) == 0?
ucfirst(strtolower(trim($sentence))) :
$sentence.' ';
}
return trim($new_string);
} 

?>





