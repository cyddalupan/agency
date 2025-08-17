<?php
session_start();
if(!isset($_SESSION['staff'])){
header("location:../logout.php");
}
?>
<?php
$con = mysql_connect("localhost","abbaphil_jinhel","67PtE01S4KL4hKIqUQyoP2aKhDwud7BV");
mysql_set_charset ( "latin1_swedish_ci", $con );
if (!$con)
{
die('Could not connect: ' . mysql_error());
}
mysql_select_db("abbaphil_north", $con);
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
	<!-- CSS only -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<!-- JavaScript Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<style>
    input{margin-top:5px;}
    select,input{
    width: 200px;
    margin-right: 5px;
}
</style>
</head>
<body>
<div class="container">
    
<?php
//SAVING
//insert data 1
function insert_data( $table, $fields, $id = '', $exp = '',$val=0 ){
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
    $values = '';

  
        $openval = '(';
        foreach ($fields as $lkey => $lvalue) {
                $sql .= "$lkey,";
                $values .= "'$lvalue[$val]',";
            
        }
        $closeval = ')';
   
    

    $sql = substr($sql,0,-1); // remove last comma
    $values = substr($values,0,-1); // remove last comma	
    $sql .= ') VALUES '.$openval. $values .  $closeval;

   
    mysql_query($sql) or die(mysql_error());
    }
    }

if(isset($_POST['add_exp'])){
    $users = array_slice($_POST, 0, -1);
    $record_count= count($_POST['request_payment']);
    $val=0;
    while($val < $record_count){
        insert_data('email_address',$users,'','',$val);
        $val++;
    }
    

    // $sql1="Select * From email_address where e_id= LAST_INSERT_ID()";
    echo '
    <div class="alert alert-success">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <strong>Successfully Added New!</strong>
    </div>';
    }


//AGENT LIST
$agentlist="";
$usertype = mysql_query("SELECT * FROM recruitment_agent   ORDER BY agent_first asc");
while($rows=mysql_fetch_array($usertype))
{
    $agentlist .='<option value="'.$rows["agent_id"].'"> '.$rows["agent_first"].' '.$rows["agent_last"].'</option>';
}

if($_GET['me']==1) {
			//ACCOUNT LIST
			$accountlist="";
			$set_ = mysql_query("SELECT * FROM set_   ORDER BY account desc");
			while($setr=mysql_fetch_array($set_))
			{
				$accountlist .='<option value="'.$setr["account"].'">'.$setr["account"].'   - '.$setr["a_amount"].'</option>';
			}
}


if($_GET['me']==2) {
			//ACCOUNT LIST
	
				$accountlist .='<option>FTW</option><option>CS</option><option>CS</option><option>DEPLOYED</option><option>PARTIAL AGENT</option><option>FULL AGENT</option>';
			
}



//APPLICANT LIST
$applicantlist="";
$applicant = mysql_query("SELECT * FROM applicant 
LEFT JOIN  	recruitment_agent
ON applicant.applicant_source =recruitment_agent.agent_id
ORDER BY applicant_last asc");
while($rows12222=mysql_fetch_array($applicant))
{
    $applicantlist .='<option value="'.$rows12222["applicant_id"].'">'.$rows12222["applicant_last"].'  '.$rows12222["applicant_middle"].'  '.$rows12222["applicant_first"].' '.$rows12222["applicant_first"].'</option>';
}
?>


</br>
<?php
if($_GET['me']==1) { echo'<a href="../expensesopen.php">Back to Office Expenses</a>'; }
if($_GET['me']==2) { echo'<a href="../agentcom.php">Back to Agent Expenses</a>'; }
?>

<h2>Expenses Entry</h2>

<form method="post">
<div class="field_wrapper">

    <div>
        <select name="request_payment[]" style="padding:5px" required><option value="" disabled selected>-Select Status-</option><option value="0">Pending</option><option value="3">Released</option></select>
        <input type="date" name="date_ad[]" placeholder="Date Transaction"  required>
		<input type="text" name="ref[]" value="" placeholder="Ref #" required>
        <select name="chargeto[]" style="padding:5px"  ><option value="" disabled selected>-Charge To-</option><option>Office</option><option>Agent</option></select>
        <select name="agent_id[]" style="padding:5px"  required><option vaue="0"  selected>Select Agent</option><?php echo $agentlist; ?></select>
        <select name="account[]" required style="padding:5px"  ><option VALUE="" disabled selected>Select Account</option><?php echo $accountlist; ?></select>
		<input type="text" name="amount[]" value="" placeholder="Amount" required/>
		<select name="currency[]" style="padding:5px"><option>PHP</option><option>USD</option></select>
		<input type="text" name="description[]" value="" placeholder="Particular" required/>
		<select class="selectpicker" single data-live-search="true" name="app_id[]" style="padding:5px" required><option VALUE="0">APPLICANT- N/A</option><?php echo $applicantlist;?></select>
		<select name="branch_type[]" style="padding:5px" type="hidden"><option VALUE="14">MAIN MANILA  BRANCH</option><option VALUE="7">URDANETA</option></select>
		<select name="country" style="padding:5px" ><option VALUE="0">N/A</option><option VALUE="5">KUW</option><option VALUE="7">KSA</option><option VALUE="11">QAT</option><option VALUE="12">DUBAI</option></select>
        <a href="javascript:void(0);" class="add_button btn btn-primary "title="Add field">Add</a>
    </div>

</div>
<hr>
<input type="submit" class="btn btn-primary float-end mr-5" name="add_exp" >
</form>
</div>


<script type="text/javascript">
$(document).ready(function(){
    var maxField = 10; //Input fields increment limitation
    var addButton = $('.add_button'); //Add button selector
    var wrapper = $('.field_wrapper'); //Input field wrapper
    var fieldHTML = '<div><hr class="mt-2"><select name="request_payment[]" style="padding:5px" required><option value="" disabled selected>-Select Status-</option><option value="0">Pending</option><option value="3">Released</option></select><input type="date" name="date_ad[]" placeholder="Date Transaction"  required><input type="text" name="ref[]" value="" placeholder="Ref #" required><select name="chargeto[]" style="padding:5px"  ><option value="" disabled selected>-Charge To-</option><option>Agent</option><option>Office</option></select><select name="agent_id[]" style="padding:5px"  required><option vaue="0" selected>Select Agent</option><?php echo $agentlist; ?></select><select name="account[]" required style="padding:5px"  ><option VALUE="" disabled selected>Select Account</option><?php echo $accountlist; ?></select><input type="text" name="amount[]" value="" placeholder="Amount" required/><select name="currency[]" style="padding:5px"><option>PHP</option><option>USD</option></select><input type="text" name="description[]" value="" placeholder="Particular" required/><select class="selectpicker" single data-live-search="true" name="app_id[]" style="padding:5px" required><option VALUE="0">N/A</option><?php echo $applicantlist;?></select><select name="branch_type[]" style="padding:5px" ><option VALUE="14">MAIN MANILA  BRANCH</option><option VALUE="7">URDANETA</option></select><select name="country" style="padding:5px" ><option VALUE="0">N/A</option><option VALUE="5">KUW</option><option VALUE="7">KSA</option><option VALUE="11">QAT</option><option VALUE="12">DUBAI</option></select><a href="javascript:void(0);" class="remove_button btn btn-danger ml-2">Remove</a></div>'; //New input field html 
    var x = 1; //Initial field counter is 1
    
    //Once add button is clicked
    $(addButton).click(function(){
        //Check maximum number of input fields
        if(x < maxField){ 
            x++; //Increment field counter
            $(wrapper).append(fieldHTML); //Add field html
        }
    });
    
    //Once remove button is clicked
    $(wrapper).on('click', '.remove_button', function(e){
        e.preventDefault();
        $(this).parent('div').remove(); //Remove field html
        x--; //Decrement field counter
    });
});
</script>
</body>
</html>