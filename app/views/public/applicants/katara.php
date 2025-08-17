<style>
body {
    font-size:11px !important;
    font-family:calibri,Arial, Helvetica, sans-serif;
	line-height:150%;
}
img {
    width:100% !important;
}
table {
    width:100% !important;
	border-collapse: collapse;
}
table th, table td {
    font-weight:normal !important;
    padding:2px !important;
    border: 1px solid black;
    text-align:left !important;
	text-transform:uppercase;
	
}
table th {
   font-weight:bold;
}
table td {
    padding-left:10px;
	line-height:150%;
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
    clear:both; 
    margin-top:1px; 
    margin-bottom:0px;
    width:100% !important;
}
span.ref-no {
    text-decoration:underline; 
    padding-bottom:5px; 
    margin-right:30px;
}
</style>

<body>



<div class="gradient" style="text-align:center;border-bottom:0px solid black;padding:2px">
    <img src="{banner}" style="width: 80%; margin-top: -40pt; "  height="140" />
</div>


  
<div class="clearfix"></div>


<div class="clearfix"></div>
<div class="" style="width: 100%;margin-top: -28pt;">
    <table cellspacing="0">
        
        <tr>
			<td style="text-align:LEFT;color:black;width:120px;font-weight:bold;background:#DAA520;font-size:13px">Full Name <div class="clearfix"></div>  </td>
		    <td style="text-align:LEFT;font-size:13px;border-right:0px;border-LEFT:0px;width:200px;" colspan="2">{fname} {mname} {lname}</td>
		<td style="text-align:LEFT;color:black;width:110px;font-weight:bold;background:#DAA520">Position <div class="clearfix"></div>   </td>
		    <td style="text-align:LEFT;font-size:13px;border-right:1px solid black;border-LEFT:0px;font-size:13px" colspan="2">{position_name}</td>
		</tr>
        
        
	 </table>
</div>

<div class="" style="float:right; width: 35%; margin-bottom: 2pt; ">
{whole_body}
 </div>
 
 <div class="" style="float:left; width: 60%;argin-bottom: 2pt; ">

	<table cellspacing="0" cellpadding="1">
            
			<tr >
                <th style="color:#b33c00;width:100px">Monthly Salary:</th>
                <td>400 {currency}</td>
				<td style="color:#b33c00"></td>
            </tr>
				<tr >
                <th style="color:#b33c00;width:100px">Mobile #:</th>
                <td> {contact}</td>
				<td style="color:#b33c00"></td>
            </tr>
			
			
			<tr >
                <th style="color:#b33c00">Contract Period:</th>
                <td>2 Years</td>
				<td style="color:#b33c00"> </td>
            </tr>
			
			<tr >
                <th  style="color:#b33c00">Passport #:</th>
                <td>{passport_number}</td>
				<td style="color:#b33c00"></td>
            </tr>
			
			<tr >
                <th style="color:black;width:150px;background:#DAA520;text-align:center;font-size:12px" colspan="3">
				Next of Kin</th>
            </tr>
			
			
			
			<tr >
                <th  style="color:#b33c00">Name:</th>
                <td>{applicant_incase_name}</td>
				<td style="color:#b33c00"></td>
            </tr>
			
			<tr >
                <th style="color:#b33c00">Contact #:</th>
                <td>{applicant_incase_contact}</td>
				<td style="color:#b33c00"></td>
            </tr>
			
			<tr >
                <th  style="color:#b33c00">Address:</th>
                <td>{applicant_incase_address}</td>
				<td style="color:#b33c00"> </td>
            
			
			
			
			<tr >
                <th style="color:black;width:150px;background:#DAA520;text-align:center;font-size:12px" colspan="3">
				Personal Details                               </th>
            </tr>
			
			<tr >
                <th  style="color:#b33c00">NATIONALITY:</th>
                <td>{applicant_nationality}</td>
				<td style="color:#b33c00"></td>
            </tr>
			
			<tr >
                <th style="color:#b33c00">Address:</th>
                <td>{applicant_address}</td>
				<td style="color:#b33c00"></td>
            </tr>
			
			<tr >
                <th  style="color:#b33c00">Date of Birth:</th>
                <td>{applicant_birthdate}</td>
				<td style="color:#b33c00"></td>
            </tr>
            
            	<tr >
                <th style="color:#b33c00;width:100px">Age:</th>
                <td>{applicant_age}</td>
				<td style="color:#b33c00"></td>
            </tr>
			
				<tr >
                <th style="color:#b33c00;width:100px">Religion:</th>
                <td>{applicant_religion}</td>
				<td style="color:#b33c00"></td>
            </tr>
            
            
            	<tr >
                <th style="color:#b33c00;width:100px">Height:</th>
                <td>{applicant_height} (FEET)</td>
				<td style="color:#b33c00"></td>
            </tr>
            
            
            
            
            	<tr >
                <th style="color:#b33c00;width:100px">Weight:</th>
                <td>{applicant_weight} (KG)</td>
				<td style="color:#b33c00"></td>
            </tr>
            
            
			
			<tr >
                <th style="color:#b33c00">Marital Status:</th>
                <td>{applicant_civil_status}</td>
				<td style="color:#b33c00"></td>
            </tr>
			
			<tr >
                <th  style="color:#b33c00">No. of Children:</th>
                <td>{applicant_children}</td>
				<td style="color:#b33c00"></td>
            </tr>
			
		
			
			<tr >
                <th style="color:#b33c00">EDUCATION: </th>
                <td>{edu}</td>
				<td style="color:#b33c00"> </td>
            </tr>
		
		</table>
		
	
</div>
 
<div class="clearfix"></div>

 <h3 style="background:#DAA520;padding:3px;margin-top: -20pt;font-size:12x " >Previous employment(Abroad):</h3>
{work_experiences}

 
 <div class="" style="width: 100%;margin-top: -10pt; "> 
	
	
	
<div class="clearfix"></div>
<h3  style="background:#DAA520;padding:3px ;font-size:12x">KNOWLEDGE OF LANGUAGES:</h3>
<table cellspacing="0" cellpadding="1" style="margin-top: -2pt; ">
<tr >
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11X;text-transform: uppercase;font-weight:bold">LANGUAGE</td>
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:12PX;text-transform: uppercase;font-weight:bold"></td>
</tr>

<tr >
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:12PX;text-transform: uppercase;font-weight:bold">ENGLISH</td>
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:12PXtext-transform: uppercase;font-weight:bold">{write_e}</td>
</tr>

<tr >
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:12PX;text-transform: uppercase;font-weight:bold">ARABIC</td>
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:12PXtext-transform: uppercase;font-weight:bold">{write_a}</td>
</tr>

</TABLE>
         
            
            
            
            
			
			
	
	
	<table cellspacing="0" cellpadding="1">
	    
	    	<tr >
                <td colspan="6" STYLE="TEXT-ALIGN:left;font-size:12x;background:#DAA520;font-weight:bold;padding:3px"> SKILLS & EXPERIENCE</td>
				
			</tr>
            
			<tr >
                <th style="color:#b33c00;width:150px">Ironing:</th>
                <td>{ironing}</td>
				<td style="color:#b33c00"></td>
				
				<th style="color:#b33c00;width:150px">Baby Sitting:</th>
                <td>{baby_sitting}</td>
				<td style="color:#b33c00"></td>
				
            </tr>
			
				<tr >
                <th style="color:#b33c00;width:150px">cooking:</th>
                <td>{cooking}</td>
				<td style="color:#b33c00"></td>
				
				<th style="color:#b33c00;width:150px">Washing:</th>
                <td>{washing}</td>
				<td style="color:#b33c00"></td>
            </tr>
			
			
				<tr >
                <th style="color:#b33c00;width:150px">Arabic Cooking :</th>
                <td>{arabic_cooking}</td>
				<td style="color:#b33c00"></td>
				
				<th style="color:#b33c00;width:150px">Tutoring:</th>
                <td>{tutoring}</td>
				<td style="color:#b33c00"></td>
				
            </tr>
			
			<tr >
                <th style="color:#b33c00;width:150px">Sewing:</th>
                <td>{sewing}</td>
				<td style="color:#b33c00"></td>
				
				<th style="color:#b33c00;width:150px">Cleaning:</th>
                <td>{cleaning}</td>
				<td style="color:#b33c00"></td>
				
            </tr>
			
			
		
            
	</table>
<div class="clearfix"></div>
<textarea style="border:0px solid black;width:700px;height:100px;background:white;margin-top: -20pt;">{applicant_remarks_3}</textarea>			
</div>


<DIV style="page-break-after:always"></DIV>	
{documents}
<table cellspacing="0" cellpadding="1" style="text-transform: uppercase;FONT-SIZE:12PX;margin-top: -15pt;">
<tr >
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;text-transform: uppercase"> NAME OF WORKER</td>
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;WIDTH:450PX;text-transform: uppercase">{fname} {mname} {lname}</td>
</tr>	

<tr >
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;text-transform: uppercase">POSITION</td>
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;WIDTH:450PX;text-transform: uppercase">HOUSEHOLD SERVICES WORKERS</td>
</tr>	


<tr >
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;text-transform: uppercase"> ADDRESS</td>
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;WIDTH:450PX;text-transform: uppercase">{applicant_address}</td>
</tr>


<tr >
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;text-transform: uppercase">CIVIL STATUS</td>
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;WIDTH:450PX;text-transform: uppercase">{applicant_civil_status}</td>
</tr>	
	

<tr >
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;text-transform: uppercase"> HAVE KIDS</td>
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;WIDTH:450PX;text-transform: uppercase">{applicant_children}</td>
</tr>	

<tr >
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;text-transform: uppercase"> CONTACT #</td>
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;WIDTH:450PX;text-transform: uppercase"> {contact}</td>
</tr>	


<tr >
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;text-transform: uppercase"> PASSPORT #</td>
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;WIDTH:450PX;text-transform: uppercase">{passport_number}</td>
</tr>	

<tr >
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;text-transform: uppercase"> DATE OF ISSUE</td>
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;WIDTH:450PX;text-transform: uppercase">{passport_issue}</td>
</tr>	

<tr >
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;text-transform: uppercase"> PLACE OF ISSUE</td>
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;WIDTH:450PX;text-transform: uppercase">{passport_place}</td>
</tr>	


<tr >
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;text-transform: uppercase"> NAME OF NOK</td>
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;WIDTH:450PX;text-transform: uppercase">{applicant_incase_name}</td>
</tr>	


<tr >
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;text-transform: uppercase"> CONTACT NUMBER OF NOK</td>
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;WIDTH:450PX;text-transform: uppercase">{applicant_incase_contact}</td>
</tr>	

<tr >
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;text-transform: uppercase"> LANGUAGE SPOKEN</td>
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:11px;WIDTH:450PX;text-transform: uppercase">{applicant_languages}</td>
</tr>	


<tr >
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:12PX;text-transform: uppercase">RELIGION</td>
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:12PX;WIDTH:450PX;text-transform: uppercase">{applicant_religion}</td>
</tr>	


<tr >
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:12PX;text-transform: uppercase"> EMPLOYMENT STATUS</td>
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:12PX;WIDTH:450PX;text-transform: uppercase">{applicant_ex}</td>
</tr>	

<tr >
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:12PX;COLOR:rED;text-transform: uppercase"> SKILLS / CAN COOK/ TAKE CARE OF BABY AND DISABLED PERSON (PLEASE SPECIFIY DETAILS IF AGREE THE APPLICANT)</td>
<td colspan="6" STYLE="TEXT-ALIGN:LEFT;font-size:12PX;WIDTH:450PX;text-transform: uppercase">{applicant_remarks1}</td>
</tr>	


	
</table>	
	
