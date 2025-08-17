<style>
body {
    font-size:10px !important;
    font-family:calibri
	line-height:150%;
}
img {
    widtd:100% !important;
}
table {
    widtd:100% !important;
	border-collapse: collapse;
}
table td, table td {

    padding:4px !important;
    text-align:left !important;
	font-size:12px;
	text-align:left;
	
}



.gradient {
}
h4 {
    font-family: sans;
    font-weight: normal;
    margin-top: 1em;
    margin-bottom: 0.5em;
}
div {
    padding:3pt;
    margin-bottom: 10pt;
    text-align:left; 
    font-size:11px;
}
.myfixed1 { position: absolute; 
    overflow: visible; 
    left: 0; 
    bottom: 0; 
 
    font-family:sans; 
    margin: 0;
}

.clearfix {
    clear:botd; 
    margin-top:10px; 
    margin-bottom:0px;
    widtd:100% !important;
}
span.ref-no {
    text-decoration:underline; 
    padding-bottom:5px; 
    margin-right:30px;
}
</style>

<body>
<div class="gradient" style="text-align:center;margin-top:-20em">
    <img src="{banner}" style="widtd: 100%" height="130"/>
</div>




<div class="" style="float: left; widtd: 60%; margin-bottom: 2em;marfin-top:-20em ">
    <table cellspacing="0" cellpadding="1">
	    <tr >
           <td style="width:150px;border:none">Date Of Application:</td>
            <td>{applicant_date_applied}</td>
        </tr>
        <tr >
            <td style="width:150px;150px;border:none">Position:</td>
            <td>{position_name}</td>
        </tr>
			<tr>
            <td style="width:150px;border:none">Reffered By:</td>
            <td></td>
        </tr>
      
    </table>
	<div style="float: right; width: 20%;margin-left:200px;MARGIN-TOP:-100PX">
				<img src="{profile_picture}" style="height:130px; " />
</div>
</div>

<div class="clearfix"></div>

<h3>PERSONAL DATA</h3>
<div class="" style="margin-bottom: 12pt;border:1px solid black ">

    <table cellspacing="0" cellpadding="1">
	<tr>
		<td style="width:110px;border:none">Full Name : </td>  
		<td style="width:190px;border:none">{applicant_name}</td>
		<td style="width:90px;border:none">Contact # : </td>  
		<td style="width:180px;border:none">{contact}</td>
	</tr>
	
	<tr>
		<td style="width:110px;border:none">Address : </td>  
		<td style="width:190px;border:none">{applicant_address}</td>
		<td style="width:90px;border:none">Gender : </td>  
		<td style="width:180px">{applicant_gender}</td>
	</tr>
	
	<tr>
		<td style="width:110px;border:none">Date of Birth : </td>  
		<td style="width:190px;border:none">{applicant_birthdate}</td>
		<td style="width:90px;">Religion : </td>  
		<td style="width:180px">{applicant_religion}</td>
	</tr>	
	
	<tr>
		<td style="width:110px;border:none">Age : </td>  
		<td style="width:190px;border:none">{applicant_age}</td>
		<td style="width:90px;">Height : </td>  
		<td style="width:180px">{applicant_height}</td>
	</tr>
	
		<tr>
		<td style="width:110px;border:none">Civil Status : </td>  
		<td style="width:190px;border:none">{applicant_civil_status}</td>
		<td style="width:90px;">Weight : </td>  
		<td style="width:180px">{applicant_weight}</td>
	</tr>
	
		
    </table>
</div>

<div class="clearfix"></div>
<h3>EDUCATIONAL BACKGROUND</h3>
<div class="" style="margin-bottom: 12pt;border:1px solid black ">

   {education}
</div>


<h3>WORK EXPERIENCE</h3>
<div class="" style="margin-bottom: 12pt;border:1px solid black ">

   {work_experiences}
</div>

<h3>OTHER SKILLS</h3>
<div class="" style="margin-bottom: 12pt;border:1px solid black ">

  {applicant_other_skills}
</div>



<h3>PASSPORT DETAILS</h3>

<div class="" style="margin-bottom: 12pt;border:1px solid black ">
    <div>
    
        <table cellspacing="0" cellpadding="1">
            <tr >
                <td widtd="40%">NUMBER:</td>
                <td>{passport_number}</td>
            </tr>
            <tr >
                <td>DATE OF ISSUE:</td>
                <td>{passport_issue}</td>
            </tr>
            <tr >
                <td widtd="40%">PLACE OF ISSUED:</td>
                <td>{passport_issue_place}</td>
            </tr>
            <tr >
                <td>DATE OF EXP.</td>
                <td>{passport_expiration}</td>
            </tr>
        </table> 
    </div>
  	
</div>


<div class="" style="clear: botd; margin: 0pt; padding: 0pt; "></div>
<DIV style="page-break-after:always"></DIV>
{documents}