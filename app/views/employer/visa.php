<!DOCTYPE html>
<html>
<head>
<style>
* {
    box-sizing: border-box;
}

input[type=text], select, textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    resize: vertical;
}

label {
    padding: 12px 12px 12px 0;
    display: inline-block;
}

input[type=submit] {
    background-color: #4CAF50;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    float: right;
}

input[type=submit]:hover {
    background-color: #45a049;
}

.container {
    border-radius: 5px;
    background-color: #f2f2f2;
    padding: 20px;
	border:1px solid black;
}

.col-25 {
    float: left;
    width: 25%;
    margin-top: 6px;
}

.col-75 {
    float: left;
    width: 75%;
    margin-top: 6px;
}

/* Clear floats after the columns */
.row:after {
    content: "";
    display: table;
    clear: both;
}

/* Responsive layout - when the screen is less than 600px wide, make the two columns stack on top of each other instead of next to each other */
@media screen and (max-width: 600px) {
    .col-25, .col-75, input[type=submit] {
        width: 100%;
        margin-top: 0;
    }
}
</style>
</head>
<body>
<?php
$con = mysql_connect("localhost","iwebfram_land","^1PCS)XlQ{Hx");
mysql_set_charset ( "latin1_swedish_ci", $con );
if (!$con)
{
die('Could not connect: ' . mysql_error());
}
mysql_select_db("iwebfram_landbase", $con);


if(isset($_POST['lab'])){

echo'<div style="margin-top:10%;margin-left:60%;
position:absolute;padding:5px;background:green;color:white;">';	

$insert=mysql_query("update applicant_passport SET employer_remarks='".$_POST['employer_remarks']."' 
WHERE passport_applicant=".$_POST['applicant_id']." ");			

echo "Successfully Save";
echo "</div>";
}
$newapplicant=mysql_query("select * from applicant_passport where passport_id=".$applicant['applicant_id']."");
$row=mysql_fetch_array($newapplicant);
	


$app = mysql_query("SELECT * FROM applicant where applicant_id=".$_GET['appid']." ");
$row=mysql_fetch_array($app);

$CERT = mysql_query("SELECT * FROM applicant_certificate where applicant_id=".$_GET['appid']." ");
$certrow=mysql_fetch_array($CERT);

$REQ = mysql_query("SELECT * FROM applicant_requirement where applicant_id=".$_GET['appid']." ");
$reqrow=mysql_fetch_array($REQ);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$row1=mysql_fetch_array($app1);

$pass = mysql_query("SELECT * FROM applicant_passport where applicant_id=".$_GET['appid']." ");
$passport=mysql_fetch_array($pass);


$files = mysql_query("SELECT * FROM applicant_files where applicant_id=".$_GET['appid']."  AND file_type!=''");
$filesrow=mysql_fetch_array($files);
?>






<div class="container" style="width:70%;margin:0 auto">
<h2 style="color:Red">VISA/APPLICAT REMARKS</h2>
  <form method="post" action="">
    <div class="row">
      <div class="col-25">
        <label for="fname">Name</label>
      </div>
        <div class="col-75" style="color:blue;font-size:22px">
        <?=$row['applicant_first']?> <?=$row['applicant_middle']?>
      </div>
    </div>
    <div class="row">
      <div class="col-25">
        <label for="lname">Position</label>
      </div>
              <div class="col-75" style="color:blue;font-size:22px">
        <?=$row['position']?>
      </div>
    </div>
	
		<div class="row">
		<div class="col-25">
		<label for="lname">Visa #</label>
		</div>
		<div class="col-75">
		<input type="text" id="lname" name="visa"o value="<?=$row['visa']?>">
		</div>
		</div>


		<div class="row">
		<div class="col-25">
		<label for="lname">Sponsor Name</label>
		</div>
		<div class="col-75">
		<input type="text" id="lname" name="visa"o value="<?=$row['visa']?>">
		</div>
		</div>
		
				<div class="row">
		<div class="col-25">
		<label for="lname">Sponsor Contact Number</label>
		</div>
		<div class="col-75">
		<input type="text" id="lname" name="visa"o value="<?=$row['visa']?>">
		</div>
		</div>




		
 
    <div class="row">
      <div class="col-25">
        <label for="subject">Remarks</label>
      </div>
      <div class="col-75">
        <textarea id="subject" name="subject" placeholder="Write something.." style="height:150px"></textarea>
      </div>
    </div>
    <div class="row">
      <input type="submit" id="lab" name="lab" value="Submit">
    </div>
  </form>
</div>

</body>
</html>
