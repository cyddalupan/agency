
<?php 
$con = mysql_connect("localhost","property_888web","Eclipse888!");
mysql_set_charset ( "latin1_swedish_ci", $con );
if (!$con)
{
die('Could not connect: ' . mysql_error());
}
mysql_select_db("property_accounting", $con);

if(isset($_POST['app_save'])){
$values="";
foreach ($_POST['appnumber'] as $lkey => $lvalue) {
//$values .= "$lkey:$lvalue;";

$ifexist=mysql_query("SELECT * FROM email_address2 where appnumber='".$_POST['appnumber'][$lkey]."'");
$ifexistjud=mysql_num_rows($ifexist);
$field_to_insert = array( 
//'date_applied' => $_POST['date_applied'][$lkey],
'date_medical' => $_POST['date_medical'][$lkey],

);
if($ifexistjud>=1){
$ids=$_POST['appnumber'][$lkey];
insert_data('email_address2',$field_to_insert,$ids);
}else{
insert_data('email_address2',$field_to_insert);
}


}
echo '<div style="width:98%;padding:5px;background:green;color:white;">';	
echo "Successfully Updated";
echo "</div>";	

}



if(isset($_POST['exp'])){
$users = array_slice($_POST, 0, -1);
//var_dump($_POST);
insert_data('Expenses',$users);
$sql1="Select * From Expenses where exappnumber= LAST_INSERT_ID()";
echo '
<div class="alert alert-success">
<button type="button" class="close" data-dismiss="alert">×</button>
<strong>Successfully Added New Expenses!</strong>
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
$sql .= " WHERE appnumber = '$id'".$exp;
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
		$sql .= " WHERE appnumber = '$id'";
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





