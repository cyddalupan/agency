
<?php 
include'inc/db.php';






if(isset($_POST['updateamount'])){
$values="";
foreach ($_POST['delall'] as $lkey => $lvalue) {
//$values .= "$lkey:$lvalue;";

$ifexist=mysql_query("SELECT * FROM  e_liq where l_id='".$_POST['delall'][$lkey]."'");
$ifexistjud=mysql_num_rows($ifexist);
$field_to_insert = array( 
	'l_id' => $_POST['delall'][$lkey],

	'l_amount' => $_POST['l_amount'][$lkey],
      'l_remarks' => $_POST['l_remarks'][$lkey],  

);
if($ifexistjud>=1){
$ids=$_POST['delall'][$lkey];
insert_data2('e_liq',$field_to_insert,$ids);
}else{
insert_data2('e_liq',$field_to_insert);
}


}
echo '<div style="width:98%;padding:5px;background:green;color:white;">';	
echo "Successfully Updated!!";
echo "</div>";	

}
	




//with filter duplicate
if(isset($_POST['liq'])){
	

   //NO applicant
	if($count!=9999999 || $count!=0){
	
				$sql="SELECT * FROM e_liq888 WHERE type_applicant='".$_POST['type_applicant']."' AND  l_status=0 
				AND  liq_app=".$_POST['liq_app']." ";
				$result=mysql_query($sql);
				$row=mysql_fetch_array($result);
				$count=mysql_num_rows($result);
				
					
					if($count!=0){	
					echo '
					<div class="alert alert-danger">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<strong>Duplicate Request!</strong>
					</div>';
					}

					
					if($count==0){	
					$users = array_slice($_POST, 0, -1);
					//var_dump($_POST);
					insert_data('e_liq888',$users);
					$sql1="Select * From e_liq888 where l_id= LAST_INSERT_ID()";
					echo '
					<div class="alert alert-success">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<strong>Successfully Added!!!</strong>
					</div>';
					}
					
   }

   
	else{
		$users = array_slice($_POST, 0, -1);
		//var_dump($_POST);
		insert_data('e_liq888',$users);
		$sql1="Select * From e_liq888 where l_id= LAST_INSERT_ID()";
		echo '
		<div class="alert alert-success">
		<button type="button" class="close" data-dismiss="alert">×</button>
		<strong>Successfully Added!!!</strong>
		</div>';
  }
  

}


if(isset($_POST['liq111'])){
$users = array_slice($_POST, 0, -1);
//var_dump($_POST);
insert_data('e_liq',$users);
$sql1="Select * From e_liq where l_id= LAST_INSERT_ID()";
echo '
<div class="alert alert-success">
<button type="button" class="close" data-dismiss="alert">×</button>
<strong>Successfully Added !</strong>
</div>';
}





if(isset($_POST['add_deduction'])){
$users = array_slice($_POST, 0, -1);
//var_dump($_POST);
insert_data(' deduction',$users);
$sql1="Select * From  deduction where 	deduction_id= LAST_INSERT_ID()";
echo '
<div class="alert alert-success">
<button type="button" class="close" data-dismiss="alert">×</button>
<strong>Successfully Added New!</strong>
</div>';
}



if(isset($_POST['addcashin'])){
$users = array_slice($_POST, 0, -1);
//var_dump($_POST);
insert_data('cash_transaction',$users);
$sql1="Select * From cash_transaction where 	cash_id= LAST_INSERT_ID()";
echo '
<div class="alert alert-success">
<button type="button" class="close" data-dismiss="alert">×</button>
<strong>Successfully Added New!</strong>
</div>';
}



if(isset($_POST['add_bannk'])){
$users = array_slice($_POST, 0, -1);
//var_dump($_POST);
insert_data('bank_accounts',$users);
$sql1="Select * From bank_accounts where 	bank_id= LAST_INSERT_ID()";
echo '
<div class="alert alert-success">
<button type="button" class="close" data-dismiss="alert">×</button>
<strong>Successfully Added New!</strong>
</div>';
}








if(isset($_POST['add_fund'])){
$users = array_slice($_POST, 0, -1);
//var_dump($_POST);
insert_data('fund',$users);
$sql1="Select * From fund where fund_id= LAST_INSERT_ID()";
echo '
<div class="alert alert-success">
<button type="button" class="close" data-dismiss="alert">×</button>
<strong>Successfully Added New!</strong>
</div>';
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



if(isset($_POST['update_profile'])){
echo '<div style="width:100%;padding:5px;background:green;color:white;">';			
$new_patient = array_slice($_POST, 0, -1);
//var_dump($new_patient);
insert_data('email_address',$new_patient,$_POST['e_id']);
echo "Successfully Updated";
echo "</div>";
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





