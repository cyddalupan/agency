<?php 
include 'db.php';
$apps =mysql_query("SELECT applicant.*,	applicant_certificate.*,	employer.*
FROM 	applicant_certificate 
LEFT JOIN applicant
ON applicant_certificate.certificate_id=applicant.applicant_id

LEFT JOIN  	employer
ON applicant.applicant_employer =employer.employer_id


where applicant.applicant_id=".$_GET['pid']."
");
$info=mysql_fetch_array($apps);
?>

<style>
#wrapper{
border:1px solid black;
width:850px;
min-height:270px;
height:!important;
margin: 0 auto;	
padding:2px;
font-size:12px;
font-family:	Times New Roman;
}
#header{
margin:auto 0;	
}
#clear{
clear:both;
height:2px;	
}
h2{
text-align:center;	
font-size:20px;
}
h1{
text-align:center;	
font-size:28px;
font-weight:bold;
font-family:	Times New Roman;	
letter-spacing: 2px;
}

table{
margin:0 auto;	
}
table td{
font-size:16px;	
padding:1px;
font-family:	Times New Roman;
}
</style>

<div id="wrapper">

<div id="header"> 
<img src="pjvbanner.png" style="width:100%;margin-top:1px;"/> 

<h2 style="margin:-15px:">ARABIC LANGUAGE TRAINING COURSE (ALTC) FOR</h2>
<h2 style="margin-top:-16px">DOMESTIC WORKERS Bound for Middle East (Except Kingdom of Saudi Arabia)</h2>
<div id="clear" style="height:10px"></div>
<h1>CERTIFICATE OF ATTENDANCE</h1>
</br></br>
</div>

<div style="width:900px;border:0px solid black;float:left">	
<P STYLE="text-align:center;font-size:16px;margin-top:-15px">NO. <?=$info['owwa_number']?> </P>
<table>
		<tr>
		<td style="width:250px;font-weight:bold">Name of HSW &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  :</td>
		<td STYLE="font-weight:bold"> <?=$info['applicant_first']?> <?=$info['applicant_middle']?> <?=$info['applicant_last']?> </td>
		</tr>

		<tr>
		<td  style="width:250px;font-weight:bold">Specific Occupation  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
		<td STYLE="font-weight:bold"> Household Service Worker</td>
		</tr>


		<tr>
		<td  style="width:300px;font-weight:bold">Name of Employer &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;:</td>
		<td STYLE="font-weight:bold"><?=$info['sub_employer']?> </td>
		</tr>


		<tr>
		<td  style="width:290px;font-weight:bold">Foreign Recruitment Agency  :</td>
		<td STYLE="font-weight:bold"> <?=$info['fra_pdos']?> </td>
		</tr>
		
		
		<tr>
		<td  style="width:250px;font-weight:bold">Date of  Attendance &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
		<?php
		IF($info['certificate_pdos_date']=='1970-01-01' || $info['certificate_pdos_date']=='0000-00-00' || $info['certificate_pdos_date']=='' ) { echo"<td>  </td>"; }
		
		else{  echo'<td STYLE="font-weight:bold">'; echo date('M d,Y', strtotime($info['certificate_pdos_date']));echo'</td>'; }
		?>
		
		
	

</table>

<div style="clear:both; height:5px;"></div> </br>

<p style="margin: 0in; text-indent: 0.5in; line-height: normal; font-size: 12pt; font-family: Calibri, sans-serif;width:800px;margin-left:80px"><i><span style="font-family:'Times New Roman', serif;">This certifies that the above named of HSW has completed the prescribe requirement for the <br> above program, held on&nbsp;&nbsp;</span></i><b  STYLE="border:1px solid black;padding:1px"> 
<?php echo date('M d,Y', strtotime($info['certificate_owwa_to'])); ?></b><i><span style="font-family:'Times New Roman', serif;"> with Certification <b>No. <?=$info['owwa_number']?></b></span></i></p>

<p style="margin: 0in; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family:'Times New Roman', serif;">&nbsp;</span></p>

<p style="margin: 0in; font-size: 12pt; font-family: AGaramond, serif;"><b><u><span style="font-size:11.0pt;font-family:'Times New Roman', serif;">&nbsp;</span></u></b></p>

<p style="margin: 0in; font-size: 12pt; font-family: AGaramond, serif;MARGIN-LEFT:150PX"><b><u><span style="font-size:11.0pt;font-family:'Times New Roman', serif;">MOHAMMAD JAMIL C. YAQUB</span></u></b><b><span style="font-size:11.0pt;font-family:'Times New Roman', serif;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 

<u>DOLORES H. MABALOT</u></span></b></p>

<p style="margin: 0in; font-size: 12pt; font-family: AGaramond, serif;MARGIN-LEFT:130PX">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
&nbsp; &nbsp; &nbsp; &nbsp; Instructor &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Recruitment Supervisor</p>

</div>
<div style="clear:both; height:5px;"></div>
</div>








