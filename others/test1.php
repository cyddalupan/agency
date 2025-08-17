<div class="col-sm-2">
<div class="form-group">
<label>User/R.O</label>
<select name="applicant[userassign]" class="form-control" required>
<option value="" selected >-Select Branch-</option>
<?php
$dbhost = 'localhost';
$dbuser = 'abbaphil_jinhel';
$dbpass = '67PtE01S4KL4hKIqUQyoP2aKhDwud7BV';
$dbname = 'abbaphil_maanyag';
$conn = mysqli_connect($dbhost, $dbuser, $dbpass,$dbname);



$app = "SELECT *  FROM user";
$row1 = $conn->query($app);
$flightrowdate="";


echo'<option value="">Select User</option>';
while($applicantrow2= $row1->fetch_assoc() ){

echo'<option value="'.$applicantrow2["user_name"].'">'.$applicantrow2["user_fullname"].'  - '.$applicantrow2["user_name "].'</option>';

}

?>
</select>

</div>
</div>
