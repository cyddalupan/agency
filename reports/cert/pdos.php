<?php 
include '../dbconfig.php';
$apps =mysql_query("SELECT applicant.*,	applicant_certificate.*
FROM 	applicant_certificate 
LEFT JOIN applicant
ON applicant_certificate.certificate_id=applicant.applicant_id
where applicant.applicant_id=".$_GET['pid']."
");
$info=mysql_fetch_array($apps);
?>

<style>
#wrapper{
border:0px solid black;
width:950px;
min-height:630px;
height:!important;
margin: 0 auto;	
padding:5px;
font-size:12px;
font-family:calibri;
}
#header{
margin:auto 0;	
}
#clear{
clear:both;
height:5px;	
}
h2{
text-align:center;	
font-size:24px;
}
h1{
text-align:center;	
font-size:32px;
font-weight:bold;
font-family:Arial Unicode MS;	
letter-spacing: 2px;
}
#info{	
font-size:22px;
width:220px;
border:0px solid black;
text-align:justify;
float:left;
padding:5px;
margin-left:50px;
font-weight:bold;
font-size:25px;
}
#info2{	
font-size:22px;
width:400px;
border:0px solid black;
text-align:left;
float:left;
padding:5px;
padding-left:20px;
font-size:25px;
}
</style>

<div id="wrapper">
	
	<div id="header"> 
		<img src="../images/pdos_azizzah.png" style="width:100%;margin-top:-15px;height:150px"/> 
		
		<h2 style="margin:-15px:">SUPPLEMENTAL PRE-DEPARTURE ORIENTATION SEMINAR FOR</h2>
		<h2 style="margin-top:-16px">HOUSEHOLD SERVICE WORKERS BOUND FOR SAUDI ARABIA</h2>
		
		<h1>CERTIFICATE OF ATTENDANCE</h1>
		<div id="clear"></div><div id="clear"></div>
		<div id="clear"></div>
	
	
		
			<div style="width:900px;border:0px solid black;float:left">	
<div id="info">Name of HSW :</div><div id="info2"> <?=$info['applicant_first']?> <?=$info['applicant_middle']?> <?=$info['applicant_last']?></div>
<div id="info">Specific Occupation :</div><div id="info2">  Household Service Worker</div>
<div id="info">Name of Employer : </div><div id="info2" style="width:500px"><?=$info['sub_employer']?></div>
<div id="info">Date of Attendance :</div><div id="info2"> 
<?php
echo date('F j, Y', strtotime($info['certificate_pdos_date']));
 ?>
</div>

			</div>	
			
	</header>

		<div id="clear"></div><div id="clear"></div>
		
		<img src="../images/sig_azizzah.png" style="float:right;width:320px;margin-left:540px;margin-top:-40px;position:absolute"/> 
</div>

