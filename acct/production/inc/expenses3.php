
<?php 
include'inc/db.php';





if(isset($_POST['add_exp'])){
$users = array_slice($_POST, 0, -1);
//var_dump($_POST);
insert_data('email_address',$users);
$sql1="Select * From email_address where e_id= LAST_INSERT_ID()";
echo '
<div class="alert alert-success">
<button type="button" class="close" data-dismiss="alert">×</button>
<strong>Successfully Added New!!</strong>
</div>';


 $insert=mysql_query("insert into cash_transaction (cash_date,cash_bank_out,cash_bank_in,cash_amount,cash_convertion,bank_remarks,cashadmad,cash_type) values
    ('".$_POST['date_ad']."',17,23,".$_POST['amount'].",1,'".$_POST['description']."',1,2)");

}










if(isset($_POST['add_exp_agent'])){
$users = array_slice($_POST, 0, -3);
//var_dump($_POST);
insert_data('email_address',$users);
$sql1="Select * From email_address where e_id= LAST_INSERT_ID()";

//insert

if($_POST['ticketamount']!="")
{
    $insert=mysql_query("insert into email_address6 (agent_id,amount,particular,date,collect_bank_id,app_id) values
    ('".$_POST['agent_id']."','".$_POST['ticketamount']."','TICKET','".$_POST['date_ad']."',19,'".$_POST['app_id']."')");
}


if($_POST['officededuction']!="")
{
    $insert=mysql_query("insert into email_address6 (agent_id,amount,particular,date,collect_bank_id,app_id) values
    ('".$_POST['agent_id']."','".$_POST['officededuction']."','OFFICE DEDUCTION','".$_POST['date_ad']."',19,'".$_POST['app_id']."')");
}

 

echo '
<div class="alert alert-success">
<button type="button" class="close" data-dismiss="alert">×</button>
<strong>Successfully Added New!</strong>
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
$sql .= " WHERE e_id = '$id'".$exp;
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
		$sql .= " WHERE l_id = '$id'";
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





