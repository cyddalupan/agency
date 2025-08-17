<?php 
$con=mysql_connect('localhost','property_888web','Eclipse888!')or die ('cannot connect');
mysql_select_db('property_accounting',$con);
$apps =mysql_query("SELECT email_address2.app_id as adonis, applicant_certificate.*,applicant_requirement.*,email_address2.* FROM email_address2
LEFT JOIN applicant_certificate
ON email_address2.app_id=applicant_certificate.app_id
LEFT JOIN 	applicant_requirement
ON email_address2.app_id=applicant_requirement.app_id
where email_address2.app_id=".$_GET['app_id']."");
$info=mysql_fetch_array($apps);
?>

<style>
#wrapper{
border:0px solid black;
width:1020px;
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
		<img src="images/pdos_azizzah.png" style="width:100%;margin-top:-15px;height:150px"/> 
		
		
		<h1>CERTIFICATE OF ATTENDANCE</h1>
		<div id="clear"></div><div id="clear"></div>
		<div id="clear"></div>
	
	
		
			<div style="width:900px;border:0px solid black;float:left">	
<div id="info" STYLE="FONT-SIZE:16PX;text-align:center;width:100%;margin-top:-30px">NO.:  <?=$info['adonis']?> </div>
<div id="clear"></div>
<div id="info">Name of HSW :</div><div id="info2"> <?=$info['name']?> <?=$info['mname']?> <?=$info['lname']?></div>
<div id="clear"></div>
<div id="info">Specific Occupation :</div><div id="info2">  Household Service Worker</div>
<div id="clear"></div>
<div id="info">Name of Employer : </div><div id="info2" style="width:500px"><?=$info['sponsor']?></div>
<div id="clear"></div>
<div id="info">Date of Attendance :</div><div id="info2"> 
<?php
echo date('F j, Y', strtotime($info['certificate_pdos_date']));
 ?>
</div>

			</div>	
			
	</header>

		<div id="clear"></div><div id="clear"></div> </br>
		
		<img src="images/sig.png" style="margin-left:-50px;margin-top:-15px"/> 
</div>

