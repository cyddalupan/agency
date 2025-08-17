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
    $applicantlist .='<option value="'.$rows12222["applicant_id"].'">'.$rows12222["applicant_last"].', '.$rows12222["applicant_middle"].' '.$rows12222["applicant_first"].'</option>';
}
?>


</br>
<?php
echo'<a href="../expensesopen.php">Back</a> | '; 
echo'<a href="index.php?me=2">Agent Expenses</a> | ';  
echo'<a href="index.php?me=1">Office & Applicant Expenses</a>  ';
?>

<h2>Expenses Entry</h2>

<form method="post">
<div class="field_wrapper">

    <div>
        <select name="request_payment[]" id="status" style="padding:5px" required><option value="" disabled selected>-Select Status-</option><option value="0">Pending</option><option value="3">Released</option></select>
        <input type="date" name="date_ad[]" placeholder="Date Transaction" id="date_add"  required>
		<input type="text" name="ref[]" value="" placeholder="Voucher #" id="ref" required>
        <select name="chargeto[]" style="padding:5px"  ><option value="" disabled selected>-Charge To-</option><option>Office</option><option>Agent</option></select>
        <select name="agent_id[]" style="padding:5px"  required><option vaue="0"  selected>Select Agent</option><?php echo $agentlist; ?></select>
        <select name="account[]" required style="padding:5px" id="account"  ><option VALUE="" disabled selected>Select Account</option><?php echo $accountlist; ?></select>
		<input type="text" name="amount[]" value="" placeholder="Amount" required/>
		<select name="currency[]" style="padding:5px"><option>PHP</option><option>USD</option></select>
		<input type="text" name="description[]" value="" placeholder="Particular" required/>
		<select class="selectpicker" single data-live-search="true" name="app_id[]" style="padding:5px" required><option VALUE="0">APPLICANT- N/A</option><?php echo $applicantlist;?></select>
		<select name="branch_type[]" style="padding:5px" type="hidden"><option VALUE="1"> MAIN OFFICE</option>
<option VALUE="2">Aklan BRANCH</option>
<option VALUE="3">Calapan BRANCH</option>
<option VALUE="4">Cagayan Valley BRANCH </option>
<option VALUE="5">Davao BRANCH </option>
<option VALUE="6">Leyte BRANCH </option>
<option VALUE="7">Lucena BRANCH </option>
<option VALUE="8">Pangasinan BRANCH</option>
<option VALUE="9">Enrile BRANCH</option></select>
	    <a href="javascript:void(0);" class="add_button btn btn-primary "title="Add field">Add</a>
    </div>

</div>
<hr>
<input type="submit" class="btn btn-primary float-end mr-5" name="add_exp" >
</form>
</div>







<br>

<h2  style="width:80%;margin:0 auto;color:red ">Pending  for checking</h2>

<table  cellspacing="0" id="example-checkbox" style="width:80%;margin:0 auto ">
<thead>
<tr>

<th>#</th>


<th STYLE="">ACTION</th>
<th > Date </th>
<th>Encoded</th>
<th></th>
<th > Branch</th>
<th style="text-align:right">Request</th>

<th >Currency</th>
<th>Amount</th>
<th>#Request</th>
<th>Agent</th>
<th>Applicant</th>
<th >Account</th>
<th >Description</th>
<th >Charge</th>

</tr>
</thead>
<tbody>
<?php	


if(isset($_POST['updateapproval'])){
foreach ($_POST['delall'] as $delall) {
$app = mysql_query("UPDATE email_address
SET  request_payment =".$_POST['mystatus']." 

where e_id= ".$delall)

or die ("cannot Update data");
}
echo"<p style='color:white;BACKGROUND:#DC143C;padding:10px;FONT-SIZE:14PX;WIDTH:400px'>Succesfully UPDATE..</p>";
}


$app = mysql_query("SELECT email_address.*
,applicant.applicant_first
,applicant.applicant_middle
,applicant.applicant_last
FROM email_address
LEFT JOIN  	applicant
ON email_address.app_id = applicant.applicant_id

	
where  account_expense=0
AND request_payment=0
order by e_id desc
limit 1

");


if(isset($_POST['searchme']))
{
$app = mysql_query("SELECT email_address.*
,applicant.applicant_first
,applicant.applicant_middle
,applicant.applicant_last
,applicant.applicant_id
FROM email_address


LEFT JOIN  	applicant
ON email_address.app_id = applicant.applicant_id

where account_expense=0
and request_payment=0
and email_address.currency='".$_POST['currency']."'
and date_ad='".$_POST['date_ad']."'
order by date_ad,e_id desc");
}

while($row=mysql_fetch_array($app))
{

$countmein = mysql_query("SELECT count(app_id) as alls,email_address.* FROM email_address
where  account='".$row['account']."'
AND app_id=".$row['app_id']."
AND request_payment!=4
AND app_id!=0");
$countmein1 = mysql_fetch_array($countmein);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);

$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$row['app_id']." ");
$rowp=mysql_fetch_array($applicant);

$USER = mysql_query("SELECT * FROM  user where user_id=".$row['staff_id']." ");
$rowu=mysql_fetch_array($USER);



$sup = mysql_query("SELECT * FROM  suppliers where sup_id=".$row['sup_id']." ");
$sup1=mysql_fetch_array($sup);


$LIQ = mysql_query("SELECT sum(l_amount) as cashfund FROM  e_liq 
where l_status=0 AND l_e_d=".$row['e_id']."");
$cashliq=mysql_fetch_array($LIQ);
$total_liqq=$cashliq['cashfund']+$row['cash_refund'];
$total_reim=$row['amount'];


if($row["request_payment"]==0){$request='Pending';$rcolor='black';}
if($row["request_payment"]==1){$request='Approved';$rcolor='blue';}
if($row["request_payment"]==2){$request='For Release';$rcolor='orange';}
if($row["request_payment"]==3){$request='Released';$rcolor='green';}
if($row["request_payment"]==4){$request='Decline';$rcolor='red';}
if($row["request_payment"]==5){$request='Return to maker';$rcolor='red';}


$bank = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$row['fund_source_id']." ");
$bank1=mysql_fetch_array($bank);
$bank2 = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$row['account_expense']." ");
$bank22=mysql_fetch_array($bank2);

$source = mysql_query("SELECT * FROM  source_payment where s_id=".$row['fund_source_id']." ");
$source1=mysql_fetch_array($source);



$expense = mysql_query("SELECT SUM(amount) as amount_total ,email_address.* FROM email_address
WHERE account='".$row["account"]."' and currency='PHP' AND request_paymenT IN (3)");
$expense1=mysql_fetch_array($expense);


if	($expense1["amount_total"]==0) {$expnes88="";}
if	($expense1["amount_total"]!=0) {$expnes88=$expense1["amount_total"];}






if($row['branch_type']==0){$branchme="N/A";}
if($row['branch_type']==1){$branchme=" MAIN OFFICE";}
if($row['branch_type']==2){$branchme="Aklan BRANCH";}
if($row['branch_type']==3){$branchme="Calapan BRANCH";}
if($row['branch_type']==4){$branchme="Cagayan BRANCH";}
if($row['branch_type']==5){$branchme="Davao BRANCH";}
if($row['branch_type']==6){$branchme="Leyte BRANCH";}
if($row['branch_type']==7){$branchme="Lucena BRANCH ";}
if($row['branch_type']==8){$branchme="Pangasinan BRANCH";}
if($row['branch_type']==9){$branchme="Enrile BRANCH";}


if($row['tm1']==1){$approved1="Approved Officer";}
if($row['tm2']==1){$approved2="Superadmin";}


if($row['tm2']==2){$approved2="Declined";}
if($row['tm1']==2){$approved1="Declined";}
if($row['tm1']==0){$approved1="Not Approve";}
if($row['tm1']==0){$approved2="";}	

if($row["currency"]=="USD"){$mypeso=$row['amount']*50;}
if($row["currency"]=="PHP"){$mypeso=$row['amount'];}

if($countmein1["alls"]==1){$bgcolor="";}
if($countmein1["alls"]==2){$bgcolor="#FFFF00";}
if($countmein1["alls"]==3){$bgcolor="#FFFF00";}
if($countmein1["alls"]==4){$bgcolor="#FFFF00";}
if($countmein1["alls"]==5){$bgcolor="#FFFF00";}
if($countmein1["alls"]==6){$bgcolor="#FFFF00";}
if($countmein1["alls"]==7){$bgcolor="#FFFF00";}
if($row["requestby"]!=''){$encode=$row["requestby"];}
if($row["requestby"]==''){$encode='globalpro';}


echo'<tr style="background:'.$bgcolor.'">';
echo'<td>'.$row["e_id"].'</td>';

echo'<td><a href="../expenses_edit.php?e_id='.$row["e_id"].'" target="_blank"  STYLE="color:white;background:Red;padding:4px">Review</a>
</td>';

IF($row['date_create']=='1970-01-01' || $row['date_create']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo"<td>".$row['date_create']."</td>"; }
echo'<td>'.$encode.'</td>';
echo'<td><p style="border:1px solid lightgray;padding:2px">'.$approved1.'<p>
<p style="border:1px solid lightblue;padding:2px">'.$approved2.'<p></td>';

echo'<td>'.$branchme.'</td>';

echo'<td style="font-size:12px;color:white;">
<i style="background:'.$rcolor.';padding:2px;margin:2px">'.$request.'</i>
</td>';

echo'<td>'.$row["currency"].'</td>';
echo'<td  style="color:red;font-size:14px;font-weight:bold"> '.number_format($total_reim, 2).'</td>';
echo'<td ><a href="soacopy.php?appid='.$row['app_id'].'"  target="_blank" style="color:black;font-size:16px;font-weight:bold">
'.$countmein1["alls"].'</a></td>';


echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';

echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$row["applicant_first"].' '.$row["applicant_last"].'</td>';

echo'<td>'.$row["account"].'</td>';

echo'<td>'.$row["description"].'</td>';
echo'<td>'.$row["chargeto"].'</td>';

echo'</tr>';

$totalme=$total_reim+$totalme;
}
?>
</tbody>


</table>



<script type="text/javascript">
$(document).ready(function(){
    var maxField = 10; //Input fields increment limitation
    var addButton = $('.add_button'); //Add button selector
    var wrapper = $('.field_wrapper'); //Input field wrapper
   
    
    var x = 1; //Initial field counter is 1
  
    //Once add button is clicked
    $(addButton).click(function(){
        //Check maximum number of input fields
        var ref = $('#ref').val();
        var date_r = $('#date_add').val();
        var status = $('#status').val();
		 var account = $('#account').val();
        var status_val=$( "#status option:selected" ).text();


    var fieldHTML = '<div><hr class="mt-2"><select name="request_payment[]" style="padding:5px" required><option value="'+ status +'" selected>'+ status_val +'</option><option value="0">Pending</option><option value="3">Released</option></select><input type="date" name="date_ad[]" placeholder="Date Transaction" value="'+ date_r +'" required><input type="text" name="ref[]" value="'+ref+'" placeholder="Voucher #" required><select name="chargeto[]" style="padding:5px"  ><option value="" disabled selected>-Charge To-</option><option>Agent</option><option>Office</option></select><select name="agent_id[]" style="padding:5px"  required><option vaue="0" selected>Select Agent</option><?php echo $agentlist; ?></select><select name="account[]" required style="padding:5px"  ><option value="" selected>-Select Account-</option><?php echo $accountlist; ?></select><input type="text" name="amount[]" value="" placeholder="Amount" required/><select name="currency[]" style="padding:5px"><option>PHP</option><option>USD</option></select><input type="text" name="description[]" value="" placeholder="Particular" required/><select class="selectpicker" single data-live-search="true" name="app_id[]" style="padding:5px" required><option VALUE="0">N/A</option><?php echo $applicantlist;?></select><select name="branch_type[]" style="padding:5px" ><option VALUE="1"> MAIN OFFICE</option><option VALUE="2">Aklan BRANCH</option><option VALUE="3">Calapan BRANCH</option><option VALUE="4">Cagayan Valley BRANCH </option><option VALUE="5">Davao BRANCH </option><option VALUE="6">Leyte BRANCH </option><option VALUE="7">Lucena BRANCH </option><option VALUE="8">Pangasinan BRANCH</option><option VALUE="9">Enrile BRANCH</option></select><a href="javascript:void(0);" class="remove_button btn btn-danger ml-2">Remove</a></div>'; //New input field html 

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