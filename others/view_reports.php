<?php include'review.php';?>
<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<!-- Head -->
<head>
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
<title></title>

<link href="../assets/styles/admin/bootstrap.min.css" rel="stylesheet" />
<link href="../assets/styles/admin/font-awesome.min.css" rel="stylesheet" />






</head>
<style>
body{
font-family:calibri;    
}

#shadow {
  -webkit-box-shadow: 3px 3px 5px 6px #ccc;  /* Safari 3-4, iOS 4.0.2 - 4.2, Android 2.3+ */
  -moz-box-shadow:    3px 3px 5px 6px #ccc;  /* Firefox 3.5 - 3.6 */
  box-shadow:         3px 3px 5px 6px #ccc;  /* Opera 10.5, IE 9, Firefox 4+, Chrome 6+, iOS 5 */
}
table td input{
color:white;    
}

ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
  overflow: hidden;
  background-color: #000080;
  width:65%;
}

li {
  float: left;
  border-right:1px solid #bbb;
}

li:last-child {
  border-right: none;
}

li a {
  display: block;
  color: white;
  text-align: center;
  padding: 14px 16px;
  text-decoration: none;
}

li a:hover:not(.active) {
  color:white;
}

.active {
  background-color: #1E90FF;
}

</style>


<!-- /Head -->
<!-- Body -->
<body style="background:white">

<div class="col-md-14 col-sm-14" style="margin-top:-1px" >
<div class="row">
<div class="col-md-12">

<div class="clients-page">

<div class="col-sm-2 pull-right" align="center">
</div>

<div style="clear:both;height:30px"></div>



<div class="row" style="padding:15px;margin:25px;border:1px solid gray;width:110%" id="shadow">
<div class="col-md-12" style="">

<?php
include'db.php';

?>




<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h3><span>Reports Applicants / Excel</span></h3>



<ul id="menu">
<li><a  href="view_coordinator.php?user=<?=$_GET['user']?>">Dashboard </a></li>
<li><a  href="view_coordinator-all.php?user=<?=$_GET['user']?>">My Applicant </a></li>
<li><a href="view_line.php?user=<?=$_GET['user']?>">CV Sent</a></li>
<li><a href="view_coordinator1.php?user=<?=$_GET['user']?>">On Process</a></li>
<li><a  href="view_deployed.php?user=<?=$_GET['user']?>">Deployed</a></li>
<li><a   href="view_cancelled.php?user=<?=$_GET['user']?>">Cancelled</a></li>
<li><a class="active" href="view_reports.php?user=<?=$_GET['user']?>">Reports </a></li>
</ul>
<div style="clear:both;height:20px"></div>

</div> 

<div class="row">
<div class="col-md-6"  style=" padding-bottom:20px;">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js">
</script>
<script>
$(document).ready(function(){
    $('#purpose').on('change', function() {
      if ( this.value == '1')
      //.....................^.......
      {
            $("#business").hide();
			$("#onprocess").hide();
			$("#unfit").hide();
			$("#backout").hide();
			$("#deployedme").hide();
			$("#repatme").hide();
			$("#atoa").hide();
			$("#visame").hide();
			$("#contractme").hide();
			$("#ondeployed").hide();
			$("#onbooking").hide();
			$("#cvsend").show();
      }
      else  if ( this.value == '2')
      {
         $("#business").hide();
		 $("#cvsend").hide();
		 $("#unfit").hide();
		 $("#backout").hide();
		 $("#deployedme").hide();
			$("#repatme").hide();
			$("#atoa").hide();
			$("#visame").hide();
			$("#contractme").hide();
			$("#ondeployed").hide();
			$("#onbooking").hide();
        $("#onprocess").show();
      }
	  
	  
	   else  if ( this.value == '3')
      {
        $("#business").hide();
		 $("#cvsend").hide();
		 $("#onprocess").hide();
		 $("#backout").hide();
		 $("#deployedme").hide();
		 $("#atoa").hide();
			$("#repatme").hide();
			$("#visame").hide();
			$("#contractme").hide();	
			$("#ondeployed").hide();
			$("#onbooking").hide();
        $("#unfit").show();
      }
	  
	  
	   else  if ( this.value == '4')
      {
          $("#business").hide();
		 $("#cvsend").hide();
		 $("#onprocess").hide();
		 $("#unfit").hide();
		 $("#deployedme").hide();
			$("#repatme").hide();
			$("#atoa").hide();
			$("#visame").hide();
			$("#contractme").hide();
$("#ondeployed").hide();	
$("#onbooking").hide();		
        $("#backout").show();
      }
	  
	  
	   else  if ( this.value == '5')
      {
            $("#business").hide();
		 $("#cvsend").hide();
		 $("#onprocess").hide();
		 $("#unfit").hide();
		 $("#backout").hide();
		$("#repatme").hide();
		$("#atoa").hide();
			$("#visame").hide();
			$("#contractme").hide();
			$("#ondeployed").hide();
			$("#onbooking").hide();
        $("#deployedme").show();
      }
	  
	  
	  
	  
	   else  if ( this.value == '6')
      {
          $("#business").hide();
		 $("#cvsend").hide();
		 $("#onprocess").hide();
		 $("#unfit").hide();
		 $("#backout").hide();
		$("#deployedme").hide();
        $("#repatme").hide();
			$("#visame").hide();
			$("#contractme").hide();
			$("#ondeployed").hide();
			$("#onbooking").hide();
		$("#atoa").show();
      }
	  
	  
	  
	   else  if ( this.value == '7')
      {
          $("#business").hide();
		 $("#cvsend").hide();
		 $("#onprocess").hide();
		 $("#unfit").hide();
		 $("#backout").hide();
		$("#deployedme").hide();
		$("#atoa").hide();
			$("#visame").hide();
			$("#contractme").hide();
			$("#ondeployed").hide();
			$("#onbooking").hide();
        $("#repatme").show();
      }
	  
	  
	     else  if ( this.value == '8')
      {
          $("#business").hide();
		 $("#cvsend").hide();
		 $("#onprocess").hide();
		 $("#unfit").hide();
		 $("#backout").hide();
		$("#deployedme").hide();
		$("#atoa").hide();
			$("#repatme").hide();
			$("#contractme").hide();
			$("#onbooking").hide();
        $("#visame").show();
      }
	  
	  
	  
	     else  if ( this.value == '9')
      {
          $("#business").hide();
		 $("#cvsend").hide();
		 $("#onprocess").hide();
		 $("#unfit").hide();
		 $("#backout").hide();
		$("#deployedme").hide();
		$("#atoa").hide();
			$("#visame").hide();
			$("#repatme").hide();
			$("#ondeployed").hide();
			$("#onbooking").hide();
        $("#contractme").show();
      }
	  
	  
	     else  if ( this.value == '11')
      {
          $("#business").hide();
		 $("#cvsend").hide();
		 $("#onprocess").hide();
		 $("#unfit").hide();
		 $("#backout").hide();
		$("#deployedme").hide();
		$("#atoa").hide();
			$("#visame").hide();
			$("#repatme").hide();
        $("#contractme").hide();
		$("#onbooking").hide();
		$("#ondeployed").show();
      }
	  
	  
	  	  
	     else  if ( this.value == '10')
      {
          $("#business").hide();
		 $("#cvsend").hide();
		 $("#onprocess").hide();
		 $("#unfit").hide();
		 $("#backout").hide();
		$("#deployedme").hide();
		$("#atoa").hide();
			$("#visame").hide();
			$("#repatme").hide();
        $("#contractme").hide();
		$("#ondeployed").hide();
		$("#onbooking").show();
      }
	  
	  
       else  
      {
        $("#business").hide();
      }
    });
});
</script>
<body>
<select id='purpose' style="border:1px solid black;padding:5px;width:300px">
<option value="0">-Select Reports-</option>
<option value="1">CV SENT</option>
<option value="2">ON PROCESS</option>
<option value="0"> TESDA</option>
<option value="9">CONTRACT</option>
<option value="3">UNFIT</option>
<option value="4">BACKOUT</option>
<option value="10">BOOKING</option>
<option value="5">DEPLOYED</option>
<option value="6">A TO A</option>
<option value="7">REPAT</option>
</select>

</br>




<form method="post" class="form" id="onprocess" style='display:none;'  action="<?=$myreport?>onprocessexcel1.php?user=<?=$_GET['user']?>" 
target="_blank" style="padding:10px;border:1px solid lightgray;margin:2px;WIDTH:200PX;float:left;margin:10px;border:2px solid gray">
<h2>On process</h2>
<div class="row">

</div>

<div class="row">
<div class="col-sm-3">
 Status :
</div>
<div class="col-sm-4">
<div class="input-group">
<select name="status_app" class="form-control" required>
<option value="2">--All--</option>
<option value="1">AVAILABLE</option>
<option value="28">OWWA</option>
<option value="33">OEC FILLING</option>
<option value="30">OEC FILLED</option>
<option value="31">OEC RELEASED</option>
<option value="32">VISA STAMPED</option>
<option value="35">VISA RELEASED</option>
</select>

</div>			    
</div>
</div>


<div class="row">
<div class="col-sm-3">
Sub Status:
</div>
<div class="col-sm-4">
<div class="input-group">
<select name="sub_status" class="form-control" required>
<option value="99999">--All--</option>

<option>Fit to Work</option>
<option>Insurance</option>
<option>Encoding Enjaz</option>
<option>Visa Stamping</option>
<option>Stamped </option>
<option>For Visa Applicantion</option>
<option>Waiting For Visa</option>
<option>Visa in Process</option>
<option>Visa Approved</option>
<option>Visa Stamping</option>
<option>Biometrics Schedule</option>
</select>
</div>			    
</div>
</div>

<div class="row">			
<div class="col-sm-3">
FRA:
</div>
<div class="col-sm-5">
<select name="employer" class="form-control" required>
<option value="99999">-- All --</option>
<?php

$employer = mysql_query("SELECT * FROM   assign
where a_user=".$_GET['user']." 
ORDER BY a_id desc");

$nameArray = array();
while($rowemployer = mysql_fetch_array($employer)) {
// Append to the array
$nameArray[] = $rowemployer['a_employer'];  
}
       
$set_ = mysql_query("SELECT * FROM  employer
where employer_id IN(".implode(",",$nameArray).") 
ORDER BY employer_name asc");


while($setr=mysql_fetch_array($set_))
{
echo'<option value="'.$setr["employer_id"].'">'.$setr["employer_name"].'</option>';
}
?>
</select>
</div>
</div>

</br>


<div class="row">			
<div class="col-sm-3">
AGENT:
</div>
<div class="col-sm-5">
<select name="agent" class="form-control" required>
<option value="99999">-- All --</option>
<?php
$agent1 = mysql_query("SELECT * FROM  recruitment_agent
ORDER BY agent_first asc");
while($agent=mysql_fetch_array($agent1))
{
echo'<option value="'.$agent["agent_id"].'">'.$agent["agent_first"].' '.$agent["agent_last"].'</option>';
}
?>
</select>
</div>
</div>

<div class="modal-footer">
<button class="btn btn-primary" type="submit" >Generate</button>
</div>
</br></br>
</form>







<form method="post" class="form" id="onbooking" style='display:none;'  action="<?=$myreport?>ondeployed.php?book=2&&user=<?=$_GET['user']?>" 
target="_blank" style="padding:10px;border:1px solid lightgray;margin:2px;WIDTH:200PX;float:left;margin:10px;border:2px solid gray">
<h2>BOOKING</h2>
<div class="row">

</div>

<input type="hidden" value="11" name="status_app">



<div class="row">			
<div class="col-sm-3">
FRA:
</div>
<div class="col-sm-5">
<select name="employer" class="form-control" required>
<option value="99999">-- All --</option>
<?php

$employer = mysql_query("SELECT * FROM   assign
where a_user=".$_GET['user']." 
ORDER BY a_id desc");

$nameArray = array();
while($rowemployer = mysql_fetch_array($employer)) {
// Append to the array
$nameArray[] = $rowemployer['a_employer'];  
}
       
$set_ = mysql_query("SELECT * FROM  employer
where employer_id IN(".implode(",",$nameArray).") 
ORDER BY employer_name asc");

while($setr=mysql_fetch_array($set_))
{
echo'<option value="'.$setr["employer_id"].'">'.$setr["employer_name"].'</option>';
}
?>
</select>
</div>
</div>

</br>


<div class="row">			
<div class="col-sm-3">
AGENT:
</div>
<div class="col-sm-5">
<select name="agent" class="form-control" required>
<option value="99999">-- All --</option>
<?php
$agent1 = mysql_query("SELECT * FROM  recruitment_agent
ORDER BY agent_first asc");
while($agent=mysql_fetch_array($agent1))
{
echo'<option value="'.$agent["agent_id"].'">'.$agent["agent_first"].' '.$agent["agent_last"].'</option>';
}
?>
</select>
</div>
</div>

<div class="modal-footer">
<button class="btn btn-primary" type="submit" >Generate</button>
</div>
</br></br>
</form>





<form method="post" class="form" id="ondeployed" style='display:none;'  action="<?=$myreport?>ondeployed.php?book=1&&user=<?=$_GET['user']?>" 
target="_blank" style="padding:10px;border:1px solid lightgray;margin:2px;WIDTH:200PX;float:left;margin:10px;border:2px solid gray">
<h2>DEPLOYMENT</h2>
<div class="row">

</div>

<input type="hidden" value="11" name="status_app">



<div class="row">			
<div class="col-sm-3">
FRA:
</div>
<div class="col-sm-5">
<select name="employer" class="form-control" required>
<option value="99999">-- All --</option>
<?php

$employer = mysql_query("SELECT * FROM   assign
where a_user=".$_GET['user']." 
ORDER BY a_id desc");

$nameArray = array();
while($rowemployer = mysql_fetch_array($employer)) {
// Append to the array
$nameArray[] = $rowemployer['a_employer'];  
}
       
$set_ = mysql_query("SELECT * FROM  employer
where employer_id IN(".implode(",",$nameArray).") 
ORDER BY employer_name asc");


while($setr=mysql_fetch_array($set_))
{
echo'<option value="'.$setr["employer_id"].'">'.$setr["employer_name"].'</option>';
}
?>
</select>
</div>
</div>

</br>


<div class="row">			
<div class="col-sm-3">
AGENT:
</div>
<div class="col-sm-5">
<select name="agent" class="form-control" required>
<option value="99999">-- All --</option>
<?php
$agent1 = mysql_query("SELECT * FROM  recruitment_agent
ORDER BY agent_first asc");
while($agent=mysql_fetch_array($agent1))
{
echo'<option value="'.$agent["agent_id"].'">'.$agent["agent_first"].' '.$agent["agent_last"].'</option>';
}
?>
</select>
</div>
</div>

<div class="modal-footer">
<button class="btn btn-primary" type="submit" >Generate</button>
</div>
</br></br>
</form>







<form method="post" class="form" id="cvsend" style='display:none;'  action="<?=$myreport?>onprocessexcel1.php?user=<?=$_GET['user']?>" 
target="_blank" style="padding:10px;border:1px solid lightgray;margin:2px;WIDTH:200PX;float:left;margin:10px;border:2px solid gray">
<h2>CV SENT</h2>
<div class="row">
</div>
<input type="hidden" value="1" name="status_app">

<div class="row">			
<div class="col-sm-3">
FRA:
</div>
<div class="col-sm-5">
<select name="employer" class="form-control" required>
<option value="99999">-- All --</option>
<?php

$employer = mysql_query("SELECT * FROM   assign
where a_user=".$_GET['user']." 
ORDER BY a_id desc");

$nameArray = array();
while($rowemployer = mysql_fetch_array($employer)) {
// Append to the array
$nameArray[] = $rowemployer['a_employer'];  
}
       
$set_ = mysql_query("SELECT * FROM  employer
where employer_id IN(".implode(",",$nameArray).") 
ORDER BY employer_name asc");


while($setr=mysql_fetch_array($set_))
{
echo'<option value="'.$setr["employer_id"].'">'.$setr["employer_name"].'</option>';
}
?>
</select>
</div>
</div>

</br>


<div class="row">			
<div class="col-sm-3">
AGENT:
</div>
<div class="col-sm-5">
<select name="agent" class="form-control" required>
<option value="99999">-- All --</option>
<?php
$agent1 = mysql_query("SELECT * FROM  recruitment_agent
ORDER BY agent_first asc");
while($agent=mysql_fetch_array($agent1))
{
echo'<option value="'.$agent["agent_id"].'">'.$agent["agent_first"].' '.$agent["agent_last"].'</option>';
}
?>
</select>
</div>
</div>

<div class="modal-footer">
<button class="btn btn-primary" type="submit" >Generate</button>
</div>
</br></br>
</form>






<form method="post" class="form" id="unfit" style='display:none;'  action="<?=$myreport?>onprocessexcel1.php?user=<?=$_GET['user']?>" 
target="_blank" style="padding:10px;border:1px solid lightgray;margin:2px;WIDTH:200PX;float:left;margin:10px;border:2px solid gray">
<h2>UNFIT</h2>
<div class="row">
</div>
<input type="hidden" value="21" name="status_app">
<div class="row" class="col-sm-6">
<div class="col-sm-3">
Date From/To<small><em>(optional)</em></small>:
</div>
<div class="col-sm-4">
<div class="input-group">
<input name="start" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="From" value=""  style="width:150px">
</div>			    
</div>
</br>

<div class="col-sm-4">
<div class="input-group">
<input name="end" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="To" value=""  style="width:150px">
</div>
</div>
</div>


</br></br>
<div class="row">			
<div class="col-sm-3">
AGENT:
</div>
<div class="col-sm-5">
<select name="agent" class="form-control" required>
<option value="99999">-- All --</option>
<?php
$agent1 = mysql_query("SELECT * FROM  recruitment_agent
ORDER BY agent_first asc");
while($agent=mysql_fetch_array($agent1))
{
echo'<option value="'.$agent["agent_id"].'">'.$agent["agent_first"].' '.$agent["agent_last"].'</option>';
}
?>
</select>
</div>
</div>

<div class="modal-footer">
<button class="btn btn-primary" type="submit" >Generate</button>
</div>
</br></br>
</form>




<form method="post" class="form" id="backout" style='display:none;'  action="<?=$myreport?>onprocessexcel1.php?user=<?=$_GET['user']?>" 
target="_blank" style="padding:10px;border:1px solid lightgray;margin:2px;WIDTH:200PX;float:left;margin:10px;border:2px solid gray">
<h2>BACKOUT</h2>
<div class="row">
</div>
<input type="hidden" value="114" name="status_app">
<div class="row" class="col-sm-6">
<div class="col-sm-3">
Date From/To<small><em>(optional)</em></small>:
</div>
<div class="col-sm-4">
<div class="input-group">
<input name="start" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="From" value=""  style="width:150px">
</div>			    
</div>
</br>

<div class="col-sm-4">
<div class="input-group">
<input name="end" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="To" value=""  style="width:150px">
</div>
</div>
</div>


</br></br>
<div class="row">			
<div class="col-sm-3">
AGENT:
</div>
<div class="col-sm-5">
<select name="agent" class="form-control" required>
<option value="99999">-- All --</option>
<?php
$agent1 = mysql_query("SELECT * FROM  recruitment_agent
ORDER BY agent_first asc");
while($agent=mysql_fetch_array($agent1))
{
echo'<option value="'.$agent["agent_id"].'">'.$agent["agent_first"].' '.$agent["agent_last"].'</option>';
}
?>
</select>
</div>
</div>

<div class="modal-footer">
<button class="btn btn-primary" type="submit" >Generate</button>
</div>
</br></br>
</form>









<form method="post" class="form" id="onprocess1" style='display:none;'  action="<?=$myreport?>onprocessexcel1.php?user=<?=$_GET['user']?>" 
target="_blank" style="padding:10px;border:1px solid lightgray;margin:2px;WIDTH:600PX;float:left;margin:10px;border:2px solid gray">
<h2>On process Reports</h2>
<div class="row">

</div>
<div class="row">
<div class="col-sm-3">
Select Status:
</div>
<div class="col-sm-4">
<div class="input-group">
<select name="status_app" class="form-control" required>
<option value="99999">--All--</option>
<option value="1">CV Sent</option>
<option value="2">Selected</option>
<option value="26">Transmittal</option>
<option value="27">Contract Signing</option>
<option value="28">Owwa</option>
<option value="31">oec</option>
<option value="32">Visa</option>
<option value="3">Deployment</option>
</select>

</div>			    
</div>
</div>

</br>

<div class="row">
<div class="col-sm-3">
Sub Status:
</div>
<div class="col-sm-4">
<div class="input-group">
<select name="sub_status" class="form-control" required>
<option value="99999">--All--</option>

<option>Fit to Work</option>
<option>Insurance</option>
<option>Encoding Enjaz</option>
<option>Visa Stamping</option>
<option>Stamped </option>
<option>For Visa Applicantion</option>
<option>Waiting For Visa</option>
<option>Visa in Process</option>
<option>Visa Approved</option>
<option>Visa Stamping</option>
<option>Biometrics Schedule</option>

</select>

</div>			    
</div>
</div>





<div class="row">			
<div class="col-sm-3">
FRA:
</div>
<div class="col-sm-5">
<select name="employer" class="form-control" required>
<option value="99999">-- All --</option>
<?php

$employer = mysql_query("SELECT * FROM   assign
where a_user=".$_GET['user']." 
ORDER BY a_id desc");

$nameArray = array();
while($rowemployer = mysql_fetch_array($employer)) {
// Append to the array
$nameArray[] = $rowemployer['a_employer'];  
}
       
$set_ = mysql_query("SELECT * FROM  employer
where employer_id IN(".implode(",",$nameArray).") 
ORDER BY employer_name asc");


while($setr=mysql_fetch_array($set_))
{
echo'<option value="'.$setr["employer_id"].'">'.$setr["employer_name"].'</option>';
}
?>
</select>
</div>
</div>

</br>


<div class="row">			
<div class="col-sm-3">
AGENT:
</div>
<div class="col-sm-5">
<select name="agent" class="form-control" required>
<option value="99999">-- All --</option>
<?php
$agent1 = mysql_query("SELECT * FROM  recruitment_agent
ORDER BY agent_first asc");
while($agent=mysql_fetch_array($agent1))
{
echo'<option value="'.$agent["agent_id"].'">'.$agent["agent_first"].' '.$agent["agent_last"].'</option>';
}
?>
</select>
</div>
</div>

<div class="modal-footer">
    <button class="btn btn-primary" type="submit" >Generate</button>
</div>
</br></br>
</form>








<form method="post" class="form" id="deployedme" style='display:none;'  action="<?=$myreport?>deployed1.php?user=<?=$_GET['user']?>" target="_blank" style="padding:10px;border:1px solid lightgray;margin:2px;WIDTH:600PX;float:left;margin:10px">
<H2>Deployed Reports</H2>
<div class="row">
<div class="col-sm-3">
Date From/To<small><em>(optional)</em></small>:
</div>
<div class="col-sm-4">
<div class="input-group">
<input name="start" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="From" value="" required>

</div>			    
</div>
<div class="col-sm-4">
<div class="input-group">
<input name="end" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="To" value="" required>

</div>
</div>

</div>

</br></br></br>
<div class="row">			
<div class="col-sm-3">
FRA:
</div>
<div class="col-sm-5">
<select name="employer" class="form-control" required>
<option value="99999">-- All --</option>

<?php

$employer = mysql_query("SELECT * FROM   assign
where a_user=".$_GET['user']." 
ORDER BY a_id desc");

$nameArray = array();
while($rowemployer = mysql_fetch_array($employer)) {
// Append to the array
$nameArray[] = $rowemployer['a_employer'];  
}
       
$set_ = mysql_query("SELECT * FROM  employer
where employer_id IN(".implode(",",$nameArray).") 
ORDER BY employer_name asc");


while($setr=mysql_fetch_array($set_))
{
echo'<option value="'.$setr["employer_id"].'">'.$setr["employer_name"].'</option>';
}
?>
</select>

</div>
</div>



</br>


<div class="row">			
<div class="col-sm-3">
AGENT:
</div>
<div class="col-sm-5">
<select name="agent" class="form-control" required>
<option value="99999">-- All --</option>
<?php
$agent1 = mysql_query("SELECT * FROM  recruitment_agent
ORDER BY agent_first asc");
while($agent=mysql_fetch_array($agent1))
{
echo'<option value="'.$agent["agent_id"].'">'.$agent["agent_first"].' '.$agent["agent_last"].'</option>';
}
?>
</select>
</div>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" type="submit" >Generate</button>
</div>
</br></br>
</form>









<form method="post" class="form" id="repatme" style='display:none;'  action="<?=$myreport?>repat1.php?user=<?=$_GET['user']?>" target="_blank" style="padding:10px;border:1px solid lightgray;margin:2px;WIDTH:600PX;float:left;margin:10px">
<H2>Repat Reports</H2>
<div class="row">

<div class="col-sm-3">
Date From/To<small><em>(optional)</em></small>:
</div>
<div class="col-sm-4">
<div class="input-group">
<input name="start" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="From" value="" required>

</div>			    
</div>
<div class="col-sm-4">
<div class="input-group">
<input name="end" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="To" value="" required>

</div>
</div>

</div>

</br></br></br>
<div class="row">			
<div class="col-sm-3">
FRA:
</div>
<div class="col-sm-5">
<select name="employer" class="form-control" required>
<option value="99999">-- All --</option>

<?php

$employer = mysql_query("SELECT * FROM   assign
where a_user=".$_GET['user']." 
ORDER BY a_id desc");

$nameArray = array();
while($rowemployer = mysql_fetch_array($employer)) {
// Append to the array
$nameArray[] = $rowemployer['a_employer'];  
}
       
$set_ = mysql_query("SELECT * FROM  employer
where employer_id IN(".implode(",",$nameArray).") 
ORDER BY employer_name asc");


while($setr=mysql_fetch_array($set_))
{
echo'<option value="'.$setr["employer_id"].'">'.$setr["employer_name"].'</option>';
}
?>
</select>
</div>
</div>
</br>
<div class="row">			
<div class="col-sm-3">
AGENT:
</div>
<div class="col-sm-5">
<select name="agent" class="form-control" required>
<option value="99999">-- All --</option>
<?php
$agent1 = mysql_query("SELECT * FROM  recruitment_agent
ORDER BY agent_first asc");
while($agent=mysql_fetch_array($agent1))
{
echo'<option value="'.$agent["agent_id"].'">'.$agent["agent_first"].' '.$agent["agent_last"].'</option>';
}
?>
</select>
</div>
</div>



<div class="modal-footer">
    <button class="btn btn-primary" type="submit" >Generate</button>
</div>
</br></br>
</form>







<form method="post" class="form" id="atoa" style='display:none;'  action="<?=$myreport?>atoa1.php?user=<?=$_GET['user']?>" target="_blank" style="padding:10px;border:1px solid lightgray;margin:2px;WIDTH:600PX;float:left;margin:10px">
<H2>A to A Reports</H2>
<div class="row">
<div class="col-sm-3">
Date From/To<small><em>(optional)</em></small>:
</div>
<div class="col-sm-4">
<div class="input-group">
<input name="start" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="From" value="" required>

</div>			    
</div>
<div class="col-sm-4">
<div class="input-group">
<input name="end" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="To" value="" required>

</div>
</div>

</div>

</br></br></br>
<div class="row">			
<div class="col-sm-3">
FRA:
</div>
<div class="col-sm-5">
<select name="employer" class="form-control" required>
<option value="99999">-- All --</option>

<?php

$employer = mysql_query("SELECT * FROM   assign
where a_user=".$_GET['user']." 
ORDER BY a_id desc");

$nameArray = array();
while($rowemployer = mysql_fetch_array($employer)) {
// Append to the array
$nameArray[] = $rowemployer['a_employer'];  
}
       
$set_ = mysql_query("SELECT * FROM  employer
where employer_id IN(".implode(",",$nameArray).") 
ORDER BY employer_name asc");


while($setr=mysql_fetch_array($set_))
{
echo'<option value="'.$setr["employer_id"].'">'.$setr["employer_name"].'</option>';
}
?>
</select>

</div>
</div>



</br>


<div class="row">			
<div class="col-sm-3">
AGENT:
</div>
<div class="col-sm-5">
<select name="agent" class="form-control" required>
<option value="99999">-- All --</option>
<?php
$agent1 = mysql_query("SELECT * FROM  recruitment_agent
ORDER BY agent_first asc");
while($agent=mysql_fetch_array($agent1))
{
echo'<option value="'.$agent["agent_id"].'">'.$agent["agent_first"].' '.$agent["agent_last"].'</option>';
}
?>
</select>
</div>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" type="submit" >Generate</button>
</div>
</br></br>
</form>






<form method="post" class="form" id="visame" style='display:none;'  action="<?=$myreport?>on-visa.php?user=<?=$_GET['user']?>" target="_blank" style="padding:10px;border:1px solid lightgray;margin:2px;WIDTH:600PX;float:left;margin:10px">
<H2>Visa </H2>
<div class="row">

<div class="col-sm-3">
Date From/To<small><em>(optional)</em></small>:
</div>
<div class="col-sm-4">
<div class="input-group">
<input name="start" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="From" value="" >

</div>			    
</div>
<div class="col-sm-4">
<div class="input-group">
<input name="end" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="To" value="" >

</div>
</div>

</div>

</br></br></br>
<div class="row">			
<div class="col-sm-3">
FRA:
</div>
<div class="col-sm-5">
<select name="employer" class="form-control" required>
<option value="99999">-- All --</option>

<?php

$employer = mysql_query("SELECT * FROM   assign
where a_user=".$_GET['user']." 
ORDER BY a_id desc");

$nameArray = array();
while($rowemployer = mysql_fetch_array($employer)) {
// Append to the array
$nameArray[] = $rowemployer['a_employer'];  
}
       
$set_ = mysql_query("SELECT * FROM  employer
where employer_id IN(".implode(",",$nameArray).") 
ORDER BY employer_name asc");


while($setr=mysql_fetch_array($set_))
{
echo'<option value="'.$setr["employer_id"].'">'.$setr["employer_name"].'</option>';
}
?>
</select>



</div>
</div>
</br>
<div class="row">			
<div class="col-sm-3">
Select Visa Stamp / Contract Received
</div>
<div class="col-sm-5">
<select name="visacontract" class="form-control" required>
<option value="1">Visa Stamp</option>


</select>
</div>
</div>

<div class="modal-footer">
    <button class="btn btn-primary" type="submit" >Generate</button>
</div>
</br></br>
</form>




<form method="post" class="form" id="contractme" style='display:none;'  action="<?=$myreport?>on-visa.php?user=<?=$_GET['user']?>" target="_blank" style="padding:10px;border:1px solid lightgray;margin:2px;WIDTH:600PX;float:left;margin:10px">
<H2>Contract Received</H2>
<div class="row">

<div class="col-sm-3">
Date From/To<small><em>(optional)</em></small>:
</div>
<div class="col-sm-4">
<div class="input-group">
<input name="start" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="From" value="" >

</div>			    
</div>
<div class="col-sm-4">
<div class="input-group">
<input name="end" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="To" value="" >

</div>
</div>

</div>

</br></br></br>
<div class="row">			
<div class="col-sm-3">
FRA:
</div>
<div class="col-sm-5">
<select name="employer" class="form-control" required>
<option value="99999">-- All --</option>
<?php

$employer = mysql_query("SELECT * FROM   assign
where a_user=".$_GET['user']." 
ORDER BY a_id desc");

$nameArray = array();
while($rowemployer = mysql_fetch_array($employer)) {
// Append to the array
$nameArray[] = $rowemployer['a_employer'];  
}
       
$set_ = mysql_query("SELECT * FROM  employer
where employer_id IN(".implode(",",$nameArray).") 
ORDER BY employer_name asc");


while($setr=mysql_fetch_array($set_))
{
echo'<option value="'.$setr["employer_id"].'">'.$setr["employer_name"].'</option>';
}
?>
</select>



</div>
</div>
</br>
<div class="row">			
<div class="col-sm-3">
Contract Received
</div>
<div class="col-sm-5">
<select name="visacontract" class="form-control" required>
<option value="2">Contract Received</option>

</select>
</div>
</div>



<div class="modal-footer">
    <button class="btn btn-primary" type="submit" >Generate</button>
</div>
</br></br>
</form>









<div style="clear:both;height:40px"></div>
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


