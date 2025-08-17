<?php
$ip2= gethostbyaddr($_SERVER['REMOTE_ADDR']);
$timezone = "Asia/Kuala_Lumpur";
if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);
$date3=date('Y-m-d');
?>
<?php 
include'../db2.php';

if(isset($_POST['blood_submit'])){
		echo '<div style="margin-top:10%;margin-left:60%;
			position:absolute;padding:5px;background:green;color:white;">';
			$field_to_insert = array_slice($_POST, 0, -1);
			//var_dump($new_patient);
			
			$ifexist=mysql_query("SELECT * FROM blood where p_id='".$_POST['p_id']."'");
			$ifexistjud=mysql_num_rows($ifexist);
		
			if($ifexistjud>=1){
				$ids=$_POST['p_id'];
				insert_data('blood',$field_to_insert,$ids);
			}else{
				insert_data('blood',$field_to_insert);
			}
			echo "Patient is Successfully Updated (BLOOD CHEM)";
		
			echo "</div>";
}


if(isset($_POST['urine_submit'])){
		echo '<div style="margin-top:10%;margin-left:60%;
			position:absolute;padding:5px;background:green;color:white;">';
			$field_to_insert = array_slice($_POST, 0, -1);
			//var_dump($new_patient);
			
			$ifexist=mysql_query("SELECT * FROM urine where p_id='".$_POST['p_id']."'");
			$ifexistjud=mysql_num_rows($ifexist);
		
			if($ifexistjud>=1){
				$ids=$_POST['p_id'];
				insert_data('urine',$field_to_insert,$ids);
			}else{
				insert_data('urine',$field_to_insert);
			}
			echo "Patient is Successfully Updated (URINE)";
		
			echo "</div>";
}



if(isset($_POST['feca_submit'])){
		echo '<div style="margin-top:10%;margin-left:60%;
			position:absolute;padding:5px;background:green;color:white;">';
			$field_to_insert = array_slice($_POST, 0, -1);
			//var_dump($new_patient);
			
			$ifexist=mysql_query("SELECT * FROM feca where p_id='".$_POST['p_id']."'");
			$ifexistjud=mysql_num_rows($ifexist);
		
			if($ifexistjud>=1){
				$ids=$_POST['p_id'];
				insert_data('feca',$field_to_insert,$ids);
			}else{
				insert_data('feca',$field_to_insert);
			}
			echo "Patient is Successfully Updated";
		
			echo "</div>";
}


if(isset($_POST['delete_agency'])){
	foreach ($_POST['delall'] as $delall) {
	  mysql_query("delete from agency WHERE id = ".$delall)
	  or die ("cannot delete data");
	   
	  }
	echo"Succesfully Deleted";
}
if(isset($_POST['xray'])){
		echo '<div style="margin-top:10%;margin-left:60%;
			position:absolute;padding:5px;background:green;color:white;">';
			$field_to_insert = array_slice($_POST, 0, -1);
			//var_dump($new_patient);
			
			$ifexist=mysql_query("SELECT * FROM xray_ecg_lab where p_id='".$_POST['p_id']."'");
			$ifexistjud=mysql_num_rows($ifexist);
		
			if($ifexistjud>=1){
				$ids=$_POST['p_id'];
				insert_data('xray_ecg_lab',$field_to_insert,$ids);
			}else{
				insert_data('xray_ecg_lab',$field_to_insert);
			}
			echo "Patient is Successfully Updated";
$insert=mysql_query("insert into x_ray (p_id,x_ray,remarks,datexam) values
('".$_POST['p_id']."','".$_POST['chest_exam']."','".$_POST['chest_desc']."','".$date3."')");			
			echo "</div>";
		}
if(isset($_POST['rem'])){
	echo '<div style="margin-top:10%;margin-left:64%;
			position:absolute;padding:5px;background:green;color:white;">';
		
			$new_patient = array_slice($_POST, 0, -1);
			//var_dump($new_patient);
			insert_data('p_information',$new_patient,$_POST['p_id']);
			echo "Successfully Updated";
			echo "</div>";
		}
//payments status
if(isset($_POST['cashiers_update'])){
			echo '<div style="width:100%;padding:5px;background:green;color:white;">';			
			$new_patient = array_slice($_POST, 0, -1);
			//var_dump($new_patient);
			insert_data('p_information',$new_patient,$_POST['p_id']);
			echo "Successfully Updated";
			echo "</div>";
		}
//cashier
if(isset($_POST['cashiers'])){
			echo '<div style="width:100%;padding:5px;background:green;color:white;">';			
			$user = array_slice($_POST, 0, -1);
			//var_dump($_POST);
			insert_data('cashier',$user);
			$sql1="Select * From cashier where id= LAST_INSERT_ID()";
			mysql_query($sql1);
			echo "Packages Added";
			echo "</div>";
		}
// audio
	if(isset($_POST['audios'])){
	echo '<div style="margin-top:10%;margin-left:68%;
			position:absolute;padding:5px;background:green;color:white;">';
	
	$field_to_insert = array_slice($_POST, 0, -1);
			//var_dump($new_patient);
			
			$ifexist=mysql_query("SELECT * FROM audio where p_id='".$_POST['p_id']."'");
			$ifexistjud=mysql_num_rows($ifexist);
		
			if($ifexistjud>=1){
				$ids=$_POST['p_id'];
				insert_data('audio',$field_to_insert,$ids);
			}else{
				insert_data('audio',$field_to_insert);
			}
			echo "Patient Audio is Successfully Updated";
			echo "</div>";
		}
// Package
if(isset($_POST['package'])){
			echo '<div style="width:100%;padding:5px;background:green;color:white;">';			
			$user = array_slice($_POST, 0, -1);
			//var_dump($_POST);
			insert_data('payments_desc',$user);
			$sql1="Select * From payments_desc where id= LAST_INSERT_ID()";
			mysql_query($sql);
			echo "New Package Added";
			echo "</div>";
		}
// new agency
if(isset($_POST['agencys'])){
			echo '<div style="width:100%;padding:5px;background:green;color:white;">';			
			$user = array_slice($_POST, 0, -1);
			//var_dump($_POST);
			insert_data('agency',$user);
			$sql1="Select * From agency where id= LAST_INSERT_ID()";
			mysql_query($sql);
			echo "New Agency Added";
			echo "</div>";
		}
// LABORATORY EXAM SAVE/UPDATE----------------------------------------------------
    	if(isset($_POST['save_update'])){
			$values="";
			foreach ($_POST['p_id'] as $lkey => $lvalue) {
				//$values .= "$lkey:$lvalue;";
				
				$ifexist=mysql_query("SELECT * FROM xray_ecg_lab where p_id='".$_POST['p_id'][$lkey]."'");
				$ifexistjud=mysql_num_rows($ifexist);
				$field_to_insert = array( 
								'p_id' => $_POST['p_id'][$lkey],
								'xray_no' => $_POST['lab_no'][$lkey],
								'hbsag' => $_POST['HBSAG'][$lkey],
								'hiv' => $_POST['HIV'][$lkey],
								'vdrl' => $_POST['VDRL'][$lkey],
								'pt' => $_POST['PT'][$lkey],
							);
				if($ifexistjud>=1){
					$ids=$_POST['p_id'][$lkey];
						insert_data('xray_ecg_lab',$field_to_insert,$ids);
					}else{
						insert_data('xray_ecg_lab',$field_to_insert);
				}
				
				
			}
			echo '<div style="width:98%;padding:5px;background:green;color:white;">';	
				echo "LABORATORY RESULT is Successfully Updated";
			echo "</div>";	
					
		}
//----------------------------------------------END OF LABORATORY EXAM-----------------------------------------------------------


if(isset($_POST['delete'])){
	foreach ($_POST['delall'] as $delall) {
	  mysql_query("delete from p_information WHERE p_id = ".$delall)
	  or die ("cannot delete data");
	   
	  }
	echo"Succesfully Deleted";
}

if(isset($_POST['deletex'])){
	foreach ($_POST['delall'] as $delall) {
	  mysql_query("delete from x_ray WHERE id = ".$delall)
	  or die ("cannot delete data");
	   
	  }
	echo"Succesfully Deleted";
}
if(isset($_POST['delete_user'])){
	foreach ($_POST['delall'] as $delall) {
	  mysql_query("delete from users WHERE id = ".$delall)
	  or die ("cannot delete data");
	   
	  }
	echo"Succesfully Deleted";
}

if(isset($_POST['user'])){
			echo '<div style="width:100%;padding:5px;background:green;color:white;">';			
			$user = array_slice($_POST, 0, -1);
			//var_dump($_POST);
			insert_data('users',$user);
			$sql1="Select * From users where id= LAST_INSERT_ID()";
			mysql_query($sql);
			echo "New User Added";
			echo "</div>";
		}
if(isset($_POST['patiend_add'])){




			echo '<div style="margin-top:10%;margin-left:68%;
			position:absolute;padding:5px;background:green;color:white;">';
			$month=$_POST['month'];
			$day=$_POST['day'];
			$year=$_POST['year'];
			$birthday_date = $year."-".$month."-".$day; 
				$c = date('Y-m-d'); //today is 2011-10-04 
			$timestamp = strtotime($c);
			$timestamp += 60*60*24; // one day increment
			$current_date = date("Y-m-d", $timestamp);
			$diff_in_mill_seconds = strtotime($current_date) - strtotime($birthday_date); 
			$age = floor($diff_in_mill_seconds / (365.2425 *60*60*24)); //365.2425 is the no. of days in a year.  
			
			
			
			unset($_POST['month']);		
			unset($_POST['day']);
			unset($_POST['year']);
			$new_patient = array_slice($_POST, 0, -1);
			//var_dump($_POST);
			insert_data('p_information',$new_patient);
			$sql1="Select * From p_information where id= LAST_INSERT_ID()";
			$get_id=mysql_fetch_array(mysql_query($sql1));
			$p_id=$get_id['id'];
			$fname=substr("".$_POST['fname']."",0,1);
			$lname=substr("".$_POST['lname']."",0,1);
			$access="".$fname."".$lname."".$p_id."";
			$sql="UPDATE p_information SET p_id ='".$p_id."',bday='".$year."-".$month."-".$day."',
			age='".$age."',pusername='".$access."',ppass='welcome1' WHERE id = LAST_INSERT_ID()";
			mysql_query($sql);
			
				//header('location:patients.php?success=Added!');
				echo $access;
			
			echo "</div>";
		}

if(isset($_POST['update_p'])){
			$new_patient = array_slice($_POST, 0, -1);
			//var_dump($new_patient);
			insert_data('p_information',$new_patient,$_POST['p_id']);
			$birthday_date =$_POST['bday']; 
			$c = date('Y-m-d'); //today is 2011-10-04 
			$timestamp = strtotime($c);
			$timestamp += 60*60*24; // one day increment
			$current_date = date("Y-m-d", $timestamp);
			$diff_in_mill_seconds = strtotime($current_date) - strtotime($birthday_date); 
			$age = floor($diff_in_mill_seconds / (365.2425 *60*60*24)); //365.2425 is the no. of days in a year.  
			$sql="UPDATE p_information SET age='".$age."',bday='".$birthday_date."' WHERE p_id =".$_POST['p_id'];
			mysql_query($sql);
		}
		
		
if(isset($_POST['lab'])){
		echo '<div style="margin-top:10%;margin-left:60%;
			position:absolute;padding:5px;background:green;color:white;">';
			$field_to_insert = array_slice($_POST, 0, -1);
			//var_dump($new_patient);
			
			$ifexist=mysql_query("SELECT * FROM xray_ecg_lab where p_id='".$_POST['p_id']."'");
			$ifexistjud=mysql_num_rows($ifexist);
		
			if($ifexistjud>=1){
				$ids=$_POST['p_id'];
				insert_data('xray_ecg_lab',$field_to_insert,$ids);
			}else{
				insert_data('xray_ecg_lab',$field_to_insert);
			}
			echo "Patient is Successfully Updated";
$insert=mysql_query("insert into x_ray (p_id,x_ray,remarks,datexam) values
('".$_POST['p_id']."','".$_POST['chest_exam']."','".$_POST['chest_desc']."','".$date3."')");			
			echo "</div>";
		}
		
	if(isset($_POST['physical2'])){
echo '<div style="margin-top:10%;margin-left:60%;
			position:absolute;padding:5px;background:green;color:white;">';	
			
			
			$field_to_insert = array_slice($_POST, 0, -1);
			//var_dump($new_patient);
			
			$ifexist=mysql_query("SELECT * FROM physical_2 where p_id='".$_POST['p_id']."'");
			$ifexistjud=mysql_num_rows($ifexist);
		
			if($ifexistjud>=1){
				$ids=$_POST['p_id'];
				insert_data('physical_2',$field_to_insert,$ids);
			}else{
				insert_data('physical_2',$field_to_insert);
			}
			echo "Patient is Successfully Updated";
			echo "</div>";
		}	

if(isset($_POST['save-medical-history'])){
echo '<div style="margin-top:10%;margin-left:60%;
			position:absolute;padding:5px;background:green;color:white;">';	

$new_patient = array_slice($_POST, 0, -2);
			//var_dump($new_patient);
		
			$values="";
				foreach ($new_patient as $lkey => $lvalue) {
					$values .= "$lkey:$lvalue;";
					 
				}
	
			$field_to_insert = array( 
								'p_id' => $_POST['p_id'],
								'exam_name' => $values,
								'exam_type' => "medicalhistory",
								'dateadded' => date('m-d-y')
							);
			//$ifexist=select_db('patient_result_exam','p_id='.$_POST['p_id']);
			
			$ifexist=mysql_query("SELECT * FROM patient_result_exam where p_id='".$_POST['p_id']."' and exam_type='medicalhistory'");
			$ifexistjud=mysql_num_rows($ifexist);
		
			if($ifexistjud>=1){
				$ids=$_POST['p_id'];
				insert_data('patient_result_exam',$field_to_insert,$ids,"and exam_type='medicalhistory'");
			}else{
				insert_data('patient_result_exam',$field_to_insert);
			}
		
			echo "Patient Medical History is Successfully Updated";
			echo "</div>";
		}
		
		if(isset($_POST['save-physical-exam'])){
echo '<div style="margin-top:10%;margin-left:60%;
			position:absolute;padding:5px;background:green;color:white;">';	
		$new_patient = array_slice($_POST, 0, -2);
			//var_dump($new_patient);
		
			$values="";
				foreach ($new_patient as $lkey => $lvalue) {
					$values .= "$lkey:$lvalue;";
					 
				}
	
			$field_to_insert = array( 
								'p_id' => $_POST['p_id'],
								'exam_name' => $values,
								'exam_type' => "physicalexam",
								'dateadded' => date('m-d-y')
							);
							
			$ifexist=mysql_query("SELECT * FROM patient_result_exam where p_id='".$_POST['p_id']."' and exam_type='physicalexam'");
			$ifexistjud=mysql_num_rows($ifexist);
		
			if($ifexistjud>=1){
				$ids=$_POST['p_id'];
				insert_data('patient_result_exam',$field_to_insert,$ids, "and exam_type='physicalexam'");
			}else{
				insert_data('patient_result_exam',$field_to_insert);
			}
			echo "Patient Physical Examination is Successfully Updated";
			echo "</div>";
		}

//select a listing with the group of company

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
		$sql .= " WHERE p_id = '$id'".$exp;
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

//insert data 2
function insert_data2( $table, $fields, $id = '' ){
		# update existing record
		# safer to reference the index on update
		$sql = "UPDATE $table SET ";
		foreach ($fields as $lkey => $lvalue) {
			$sql .= "$lkey = '$lvalue', ";
		}
		$sql = substr($sql,0,-2); // remove last comma
		$sql .= " WHERE p_id = '$id'";
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




  <?php
if(isset($_POST['save_update2'])){
$values="";
foreach ($_POST['id'] as $lkey => $lvalue) {

$sql=mysql_query("Select * From cashier where id=".$lvalue);
$row=mysql_fetch_array($sql);
if($row['status']!=$_POST['payment_status'][$lkey]){
$sql="UPDATE cashier SET status='".$_POST['payment_status'][$lkey]."',dateadded='".date('Y-d-m')."' where id =".$lvalue;
mysql_query($sql) or die(mysql_error());
}


}

echo '<div style="width:100%;padding:5px;background:green;color:white;">';		
echo "Packages are Successfully Updated";
			echo "</div>";
}

?>
<?php 
if(isset($_POST['disbursements_save'])){
			echo '<div style="width:100%;padding:5px;background:green;color:white;">';			
			$new_patient = array_slice($_POST, 0, -1);
			//var_dump($new_patient);
			insert_data('disbursements',$new_patient);
			echo "Successfully Added";
			echo "</div>";
		}
?>
<?php
//PRINT YES
if(isset($_POST['print'])){
		foreach ($_POST['delall'] as $delall) {
	
	  mysql_query("update p_information SET print_status='yes' 
	  WHERE p_id = ".$delall)
	  or die ("cannot update");
	 }
	 echo"print now!";
	 }
?>