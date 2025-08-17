	
<!DOCTYPE html>
<html>
<!-- Head -->
<head>
<meta http-equiv="Content-Type" content="text/plain; charset=utf-8">
<title></title>

<link href="../assets/styles/admin/bootstrap.min.css" rel="stylesheet" />
<link href="../assets/styles/admin/font-awesome.min.css" rel="stylesheet" />

</head>

<style>

table {
    width:100% !important;
	border-collapse: collapse;
}
table th, table td {
    font-weight:normal !important;
  
    text-align:left !important;
    	color:black;
		text-transform:uppercase;
		font-size:18px;	
		background:none;
	
}
table th {
   
    text-transform: uppercase;
	backgrouund:none;
	
}
table td {
    padding-left:10px;
	line-height:150%;
	backgrouund:none;
}

table {
  border-collapse: collapse;
}





textarea{
    width: 100%;
    color: #FFF;
    background: transparent;
    border: none;
    outline: none;
}



#names{
font-weight:bold;
font-size:30px;	
}
#pos{
font-size:20px;	
}



</style>


<body>
<div class="row" style="margin:0 auto ; width:1100px;border:0px solid black;min-height:500px;padding:10px">

<img src="banner.png" style="width:80%;margin-top:1px;HEIGHT:160PX"/> 
<div style="clear:both; height:20px;"></div>


<?php
include'db.php';
$count=1;	
$app = mysql_query("SELECT * FROM applicant
				LEFT JOIN  statuses
				ON applicant.applicant_status = statuses.number

				LEFT JOIN  	applicant_certificate
				ON applicant.applicant_id = applicant_certificate.certificate_applicant


				LEFT JOIN  	applicant_requirement
				ON applicant.applicant_id =applicant_requirement.requirement_applicant

				LEFT JOIN  	employer
				ON applicant.applicant_employer =employer.employer_id

				LEFT JOIN  	recruitment_agent
				ON applicant.applicant_source =recruitment_agent.agent_id


				LEFT JOIN  applicant_passport
				ON applicant.applicant_id =	applicant_passport.passport_applicant	
where  applicant_id=".$_GET['appid']."");
$info=mysql_fetch_array($app);

$position = mysql_query("SELECT * FROM position where position_id=".$info['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);

$edu = mysql_query("SELECT * FROM  applicant_education where education_applicant=".$info['applicant_id']." ");
$edu1=mysql_fetch_array($edu);

$work = mysql_query("SELECT * FROM applicant_experiences where  experience_applicant=".$_GET['appid']."");

$train = mysql_query("SELECT * FROM appliocant_train where  m_app=".$_GET['appid']."");
$count=mysql_num_rows($train);


$files = mysql_query("SELECT * FROM  applicant_files where  file_applicant=".$_GET['appid']."");


?>

<div class="gradient" style="float: left; width:  margin-bottom: 0pt;width: 70% ">
<h2 style="color:black" id="names"><?=$info['applicant_last']?>,   <?=$info['applicant_first']?> <?=$info['applicant_middle']?> </h2>

<p style="margin-top:-8pt" id="pos">   <span style="margin-right:20px;">Position Applied.<span class=""> <?=$position1['position_name']?></span></span> </p>
</div>
<div style="clear:both; height:20px;"></div>


<div class="gradient" style="float: right; margin-bottom: 0pt;width: 25% ">
    <img src="https://recruitment-portal.net/everlast/files/applicant/<?=$info['applicant_photo']?>" style="float:right; height:210px;BORDER:1PX SOLID BLACK;width:250px;margin-top:-100px "  />
</div>

<table  cellspacing="0" cellpadding="0" style="width:70%">
<tr >
<th style="color:black;width:150px;text-align:left;font-size:22px" colspan="3">
<b>PERSONAL DATA  </b></th>
</tr>
 <div class="clearfix"></div>  <div class="clearfix"></div> 

<tr >
<th style="width:250px">Age:</th>
<td><?=$info['applicant_age']?></td>
</tr>

<tr >
<th  style="">Gender:</th>
<td><?=$info['applicant_gender']?></td>
</tr>


<tr >
<th  style="">Date of Birth:</th>
<td><?=$info['applicant_birthdate']?></td>
</tr>

<tr >
<th style="">Civil Status:</th>
<td> <?=$info['applicant_civil_status']?></td>
</tr>

<tr >
<th style="">Height:</th>
<td> <?=$info['applicant_height']?></td>
</tr>


<tr >
<th style="">Weight:</th>
<td> <?=$info['applicant_weight']?></td>
</tr>



<tr >
<th  style="">NATIONALITY:</th>
<td><?=$info['applicant_nationality']?></td>
</tr>


<tr >
<th style="">Religion:</th>
<td> <?=$info['applicant_religion']?></td>
</tr>


</table>




<div style="clear:both; height:5px;"></div>

<table cellspacing="0" cellpadding="1" style="width:800px">
<tr >
<th style="color:black;width:150px;text-align:left;font-size:22px" colspan="3">
<b>WORK EXPERIENCE </b></th>
</tr>
</table>



<?php
$count=1;
while($work1=mysql_fetch_array($work))
{
?>

<table cellspacing="0" cellpadding="1" style="width:900px;border:0px solid black;height:430px">

<tr >
<th style="">COMPANY :</th>
<td style="font-weight:bold;color:black"> <b><?=$work1['experience_company']?></b></td>
</tr>



<tr >
<th  style="">POSITION : </th>
<td  style="font-weight:bold;color:black"><b><?=$work1['experience_position']?></b></td>
</tr>


<tr >
<th style="">LOCATION : </th>
<td  style="font-weight:bold;color:black"> <b><?=$work1['experience_country']?></b></td>
</tr>

<tr >
<th style="">DURATION : </th>
<td  style="font-weight:bold;color:black"> <b><?php echo''.date('M-d-Y', strtotime($work1['experience_from'])).' - '.date('M-d-Y', strtotime($work1['experience_to'])).'';?></b></td>
</tr>


<tr>
<th style="" colspan="2"></br> Job Description:</th><div style="clear:both; height:20px;"></div>
</tr>

<tr>
<td style="text-align:left"  colspan="2">

 <?php
 $lines = preg_split('/\n|\r/',$work1['salary']);
$Total_lines = count($lines); 
//echo $Total_lines;
if($Total_lines == 1){$lineme="230px"; $mydata='';}
if($Total_lines == 2){$lineme="230px"; $mydata='';}
if($Total_lines == 3){$lineme="230px"; $mydata='';}
if($Total_lines == 4){$lineme="230px";$mydata='';}
if($Total_lines == 5){$lineme="230px";$mydata='';}
if($Total_lines == 6){$lineme="230px";$mydata='';}
if($Total_lines == 7){$lineme="230px";$mydata='';}
if($Total_lines == 8){$lineme="230px";$mydata='';}
if($Total_lines == 9){$lineme="240px";$mydata='';}
if($Total_lines == 10){$lineme="320px";$mydata='<DIV style="page-break-after:always"></DIV>';}
if($Total_lines == 11){$lineme="400px";$mydata='<DIV style="page-break-after:always"></DIV>';}
if($Total_lines == 12){$lineme="400px";$mydata='<DIV style="page-break-after:always"></DIV>';}
if($Total_lines == 13){$lineme="410px";$mydata='<DIV style="page-break-after:always"></DIV>';}

if($Total_lines == 14){$lineme="410px";$mydata='<DIV style="page-break-after:always"></DIV>';}
if($Total_lines == 15){$lineme="420px";$mydata='<DIV style="page-break-after:always"></DIV>';}
if($Total_lines == 16){$lineme="430px";$mydata='<DIV style="page-break-after:always"></DIV>';}
if($Total_lines == 17){$lineme="550px";$mydata='<DIV style="page-break-after:always"></DIV>';}
if($Total_lines == 18){$lineme="600px";$mydata='<DIV style="page-break-after:always"></DIV>';}
if($Total_lines == 19){$lineme="600px"; $mydata='<DIV style="page-break-after:always"></DIV>';}
if($Total_lines == 20){$lineme="600px"; $mydata='<DIV style="page-break-after:always"></DIV>';}

 ?>
  <textarea  style="color:black;font-sie:11px;
  min-height:<?=$lineme?>;padding-left:50px;border:0px solid black;width:100%" ><?=$work1['salary']?></textarea>

</td>
</tr>


</table>

<?php 
echo $mydata;
$count++; }  

?>



<table cellspacing="0" cellpadding="1">
<tr >
<th style="color:black;width:150px;text-align:left;font-size:22px" colspan="3">
<b>EDUCATION</b></th>
</tr>

<tr >
<th style="">EDUCATIONAL LEVEL: </th>
<td> <?=$edu1['education_mba']?></td>
</tr>



<tr >
<th  style="">COURSE:  </th>
<td><?=$edu1['education_mba_course']?></td>
</tr>


<tr >
<th style="">YEAR: </th>
<td> <?=$edu1['education_mba_year']?></td>
</tr>

</table>



<div style="clear:both; height:20px;"></div>

<?php if($count==1){ ?>
<table cellspacing="0" cellpadding="1">



<tr >
<th style="color:black;width:150px;text-align:left;font-size:22px" colspan="3">
<b>TRAININGS/SEMINARS:</b></th>
</tr>



<?php

while($train1=mysql_fetch_array($train))
{
?>



<tr >
<th style="">TRAINING NAME :</th>
<td> <?=$train1['t_name']?></td>
</tr>



<tr >
<th  style="">PROVIDER : </th>
<td><?=$train1['t_provider']?></td>
</tr>


<tr >
<th style="">PLACE : </th>
<td> <?=$train1['t_place']?></td>
</tr>


<tr >
<th style="">DATE ISSUE : </th>

<td> <?php echo''.date('M-d-Y', strtotime($train1['t_issue'])).'';?></td>
</tr>

<tr >
<th style="">DATE EXPIRED : </th>
<td> <?php echo''.date('M-d-Y', strtotime($train1['t_expired'])).'';?></td>
</tr>

<?php } ?>


</table>
<?php } ?>


<?php
while($files1=mysql_fetch_array($files))
{
?>

 <img src="https://recruitment-portal.net/everlast/<?=$files1['file_path']?>" style="width:85%;margin:30px"  />


<?php echo'<DIV style="page-break-after:always"></DIV>'; } ?>

<div style="clear:both; height:50px;"></div>
</div>
</div>



</body>
<!--  /Body -->
</html>
