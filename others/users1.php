<?php
session_start();
if(!isset($_SESSION['admin']['user'])){
header("location:noaccess.php");
}

?>
<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<!-- Head -->
<head>
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
<title></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../assets/styles/admin/bootstrap.min.css" rel="stylesheet" />

</head>

<style>
#shadow {
-webkit-box-shadow: 3px 3px 5px 6px #ccc;  /* Safari 3-4, iOS 4.0.2 - 4.2, Android 2.3+ */
-moz-box-shadow:    3px 3px 5px 6px #ccc;  /* Firefox 3.5 - 3.6 */
box-shadow:         3px 3px 5px 6px #ccc;  /* Opera 10.5, IE 9, Firefox 4+, Chrome 6+, iOS 5 */
}
</style>


<body style="background:white">

<div class="col-md-14 col-sm-14" style="margin-top:-1px">
<div class="row">
<div class="col-md-12">

<div class="clients-page">
<div style="clear:both;height:40px"></div>
<div class="row" style="padding:15px;margin:25px;border:1px solid gray;width:85%" id="shadow">
<h2>Assign Principal Per User</h2>   

<div class="col-md-12" style="margin:20px">
<div class="row">
<?php include'db.php';

if(isset($_POST['savedata111'])){

//$userr=mysql_query("UPDATE  user SET user_emp =".$_POST['user_emp'].",
//password ='".$_POST['password']."',
//user_type =".$_POST['user_type'].",
//acct_pass ='".$_POST['acct_pass']."'
//WHERE user_id =".$_POST['user_id']."");

$insert=mysql_query("REPLACE INTO assign1 (a_employer,a_user)
VALUES (".$_POST['user_emp'].",".$_POST['user_id']." )");



echo '
<div class="alert alert-success">
<button type="button" class="close" data-dismiss="alert">×</button>
<strong>Successfully Save !</strong>
</div>';

}

$userr=mysql_query("SELECT * From  user 
LEFT JOIN employer
ON user.user_emp = employer.employer_id


where user_id=".$_GET['ume']."");
$row=mysql_fetch_array($userr);

?>

<div class="col-md-12"  style=" padding-bottom:20px;">



<div  class="pmd-card pmd-z-depth pmd-card-custom-view">
<div class="table-responsive">




<form class="form-horizontal form-label-left input_mask" method="post" action="" style="font-size:12px">	



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Assign BRANCH</label>
<div class="col-md-3 col-sm-3 col-xs-12">
<select name="user_emp" style="padding:5px;width:300px" required>
<option value="">SELECT BRANCH</option>
<option VALUE="1">PARANAQUE MAIN OFFICE</option>
<option VALUE="2">BACOLOD BRANCH</option>
<option VALUE="3">BUTUAN BRANCH</option>
<option VALUE="4">CEBU BRANCH </option>
<option VALUE="5">CAGAYAN DE ORO BRANCH </option>
<option VALUE="6">DAVAO BRANCH </option>
<option VALUE="7">DIPOLOG BRANCH </option>
<option VALUE="8">DUMAGUETE BRANCH</option>
<option VALUE="9">GENSAN BRANCH</option>
<option VALUE="10">IPIL BRANCH</option>
<option VALUE="11">MAASIN BRANCH</option>
<option VALUE="12">OZAMIZ BRANCH </option>
<option VALUE="13">PAGADIAN BRANCH </option>
<option VALUE="14">SINDANGAN BRANCH</option>
<option VALUE="15">TACLOBAN BRANCH</option>
<option VALUE="16">TAGUM BRANCH</option>
<option VALUE="17">VALENCIA BRANCH</option>
<option VALUE="18">ZAMBOANGA BRANCH</option>
<option VALUE="19"> ROXAS BRANCH</option>

</select>
</div>

<label class="control-label col-md-3 col-sm-3 col-xs-12"></label>
<div class="col-md-3 col-sm-3 col-xs-12">
</div>
</div>










<input type="hidden" class="form-control" name="user_id" value="<?=$_GET['ume']?>">




<div class="form-group">
<div class="col-md-4 col-sm-9 col-xs-12">	
</br>
<button type="submit" class="btn btn-danger pull-right"  name="savedata111" style="padding:10px;background:green;color:white">Save Data</button>
</div>
</div>

</form>	


<div style="clear:both;height:40px"></div>
  <form action="" method="post">    


<table class="table table-hover table-striped table-bordered"  id="example-checkbox" style="width:100%" >
<thead>
<tr>
<th>Principal</th>
<th>Action</th>

</tr>
</thead>
<tbody>



<?php	

if(isset($_POST['delete_orders'])){
foreach ($_POST['delall'] as $delall) {
$app = mysql_query("delete  FROM assign1 where a_id= ".$delall)

or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted</p>";
}

$app = mysql_query("SELECT * FROM   assign1
where a_user=".$_GET['ume']." 
ORDER BY a_id desc");



while($row=mysql_fetch_array($app))
{
 if($row['a_employer']==0){$branchme="N/A";}
if($row['a_employer']==1){$branchme="PARANAQUE MAIN OFFICE";}
if($row['a_employer']==2){$branchme="BACOLOD BRANCH";}
if($row['a_employer']==3){$branchme="BUTUAN BRANCH";}
if($row['a_employer']==4){$branchme="CEBU BRANCH";}
if($row['a_employer']==5){$branchme="CAGAYAN DE ORO BRANCH";}
if($row['a_employer']==6){$branchme="DAVAO BRANCH";}
if($row['a_employer']==7){$branchme="DIPOLOG BRANCH ";}
if($row['a_employer']==8){$branchme="DUMAGUETE BRANCH";}
if($row['a_employer']==9){$branchme="GENSAN BRANCH";}
if($row['a_employer']==10){$branchme="IPIL BRANCH";}
if($row['a_employer']==11){$branchme="MAASIN BRANCH";}
if($row['a_employer']==12){$branchme="OZAMIZ BRANCH";}
if($row['a_employer']==13){$branchme="PAGADIAN BRANCH";}
if($row['a_employer']==14){$branchme="SINDANGAN BRANCH";}
if($row['a_employer']==15){$branchme="TACLOBAN BRANCH";}
if($row['a_employer']==16){$branchme="TAGUM BRANCH";}
if($row['a_employer']==17){$branchme="VALENCIA BRANCH";}
if($row['a_employer']==18){$branchme="ZAMBOANGA BRANCH";}
if($row['a_employer']==19){$branchme="ROXAS BRANCH";}   
    
$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['a_employer']." ");
$emprow=mysql_fetch_array($emp);
echo'<tr>';
echo'<td>'.$branchme.'</td>';
echo "<td><input type='checkbox' name='delall[]' value='".$row['a_id']."' ></td> ";
echo'</tr>';
}
?>
</tbody>
</table>


<div class="row">
<div class="col-5" style="width:100%,float:right">		
<input type="submit" value="Delete" name="delete_orders" style="width:120px;background:Red;padding:10px;margin-left:27px;margin-top:-1px;
color:white;font-size:16px;FLOAt:right;margin-right:10px">
</div>
</div>
</form>	


</br></br></br></br></br>

<h1>ACTIVATE REQUEST PAYMENT</h1>





<?php
if(isset($_POST['saveme'])){
	
	
$app33 = mysql_query("UPDATE user
SET payments='".$_POST['payments']."'
WHERE user_id=".$_GET['ume']." ");

echo"Updated..";	
	
echo '
<div class="alert alert-success">
<button type="button" class="close" data-dismiss="alert">×</button>
<strong>Successfully Save !</strong>
</div>';	
}



$app33 = mysql_query("SELECT * FROM user where user_id=".$_GET['ume']." ");
$row3=mysql_fetch_array($app33);	
?>







</div>
</div>
</div>
</div>
</div>
</div>


</div>
</div>
</div>
</div>



</body>
<!--  /Body -->
</html>


