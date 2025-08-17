<?php 
include'db.php';
$apps =mysql_query("SELECT * FROM 	applicant 

LEFT JOIN  	applicant_certificate
ON applicant.applicant_id = applicant_certificate.certificate_applicant

where applicant.applicant_id=".$_GET['pid']."");
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
font-size:22px;
font-weight:bold;
font-family:	Times New Roman;	
letter-spacing: 2px;
}

table{
margin:0 auto;	
}
table td{
font-size:15px;	
padding:1px;
font-family:	Times New Roman;
}
</style>

<div id="wrapper">

<div id="header"> 
<img src="skyhigh.png" style="width:100%;margin-top:1px;HEIGHT:140PX"/> 

<h2 style="margin:-15px:">SUPPLEMENTAL PRE-DEPARTURE ORIENTATION SEMINAR FOR</h2>
<h2 style="margin-top:-16px">HOUSEHOLD SERVICE WORKERS BOUND FOR SAUDI ARABIA</h2>
<div id="clear" style="height:5px"></div>
<h1>CERTIFICATE OF ATTENDANCE</h1>
</br></br>
</div>

<div style="width:900px;border:0px solid black;float:left">	

<?php
  $str =$info['certificate_pdos_no'];
  
    $str1 =  ltrim($str, '19-');
  
?>

<P STYLE="text-align:center;font-size:16px;margin-top:-15px">NO. <?=$info['certificate_pdos_no']?> </P>
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
		
		
		</tr>

</table>

<div style="clear:both; height:60px;"></div>
<img src="sig.png" style="position:absolute;margin-left:340px;margin-top:-60px"/> 

<P STYLE="text-align:center;font-size:15px;font-weight:bold;">ROSALYN T. OMPOC</P>
<P STYLE="text-align:center;font-size:15px;margin-top:-15px">Printed Name and Signature</P>
<P STYLE="text-align:center;font-size:15px;margin-top:-15px">PRESIDENT / CEO</P>


</div>
<div style="clear:both; height:1px;"></div>
</div>
<div style="clear:both; height:2px;"></div>









