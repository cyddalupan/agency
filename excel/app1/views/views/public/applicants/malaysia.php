<style>
body {
    font-size:10px !important;
    font-family:Arial, Helvetica, sans-serif;
	line-height:150%;
	 border-collapse: collapse;


}
img {
    width:100% !important;
}
table {
    width:100% !important;
}
table th, table td {
    font-weight:normal !important;
    padding:4px !important;
    border: 1px solid #7b7a7a;
    text-align:left !important;

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

.myfixed1 { position: absolute; 
    overflow: visible; 
    left: 0; 
    bottom: 0; 
 
    font-family:sans; 
    margin: 0;
}
.blue{
background:#99ccff;
color:black;
padding:8px;
BORDER-bottom:1PX solid black;
BORDER-right:1PX solid black;
}
.blue b{
font-weight:800;
}
.white{
background:#FFF ;
color:black;
padding:8px;
BORDER-bottom:1PX solid black;
BORDER-right:1PX solid black;
}
.white b{
font-weight:800;
}
.clearfix {
    clear:both; 
    margin-top:10px; 
    margin-bottom:0px;
    width:100% !important;
}
span.ref-no {
    text-decoration:underline; 
    padding-bottom:5px; 
    margin-right:30px;
}


h1{
page-break-before: always;
}
.title{
float: left; 
width: 100%;
background:#3399ff;
padding:8px;
BORDER-bottom:1PX solid black;
BORDER-top:1PX solid black;
}
</style>
<DIV style="border:1px solid black;">
<div class="gradient" style="text-align:center">
    <img src="{banner}" style="width: 100%;"/>
</div>
<div class="clearfix" STYLE="clear:both;height:10px"></div>
<div class="title">
    <div style="FONT-SIZE:16PX;color:White;width:500px;border:0px solid black;float: left;">PERSONAL INFORMATION</div>
	<div style="FONT-SIZE:16PX;color:White;width:120px;border:0px solid black;float: left;text-align:right">{ref_no}</div>
</div>
<div class="" style="float: left; width: 77%;min-height:100px;height:!important ">
					<div class="blue" style="float: left;">
						<div style="FONT-SIZE:13PX;float: left;">Full Name: <b>{applicant_name}</b></div>	
					</div>
					<div class="white" style="float: left;">
						<div style="FONT-SIZE:13PX;float: left;">Address: <b>{applicant_address}</b></div>	
					</div>
					<div class="blue" style="float: left;x">
						<div style="FONT-SIZE:13PX;float: left;">Date of Birth: <b>{applicant_birthdate}</b></div>	
					</div>
					{contact}

					<div class="blue" style="float: left;">
						<div style="FONT-SIZE:13PX;float: left;">Nationality: <b>{applicant_nationality}</b></div>	
						
					</div>
	
	
</div>
<div style="float: right; width: 20%; margin-top:0px;height:130px	0px;border:0px solid red;margin-left:-15px;padding:10px;background:#F0F0F0 ;">
				<img src="{profile_picture}" style="float:right; height:130px;width:100% " />
</div>

<!--FULL BOX-->
<div class="" style="float: left; width: 100%;min-height:100px;height:!important ">
		<div class="WHITE" style="float: left;">
			<div style="FONT-SIZE:13PX;float: left;width:30%">Age: <b>{applicant_age}</b></div>	
			<div style="FONT-SIZE:13PX;float: left;width:30%">Height: <b>{applicant_height}</b></div>	
			<div style="FONT-SIZE:13PX;float: left;width:30%">Weight: <b>{applicant_weight}</b></div>	
		</div>

		<div class="blue" style="float: left;">
			<div style="FONT-SIZE:13PX;float: left;width:30%">Religion: <b>{applicant_religion}</b></div>
			<div style="FONT-SIZE:13PX;float: left;width:30%">Passport No: <b>{passport_number}</b></div>
			<div style="FONT-SIZE:13PX;float: left;width:30%">Date of Expiry: <b>{passport_expiration}</b></div>						
		</div>
		
		<div class="white" style="float: left;">
			<div style="FONT-SIZE:13PX;float: left;width:30%">Marital Status: <b>{applicant_civil_status}</b></div>
			<!--STATIC DATA-->
			<div style="FONT-SIZE:13PX;float: left;width:35%">Name of Husband: <b>{partner_husband}</b></div>
			<div style="FONT-SIZE:13PX;float: left;width:25%">Occupation: <b>{partner_occupation}</b></div>	
			<!--END STATIC DATA-->			
		</div>
</div>


<!--half BOX-->
<div class="" style="float: left; width: 50%;min-height:100px;height:!important ">
		<!--STATIC DATA-->
		<div class="blue" style="float: left;">
			<div style="FONT-SIZE:13PX;float: left;width:50%">No of Children: <b>{children_count}</b></div>	
			<div style="FONT-SIZE:13PX;float: left;width:50%">Age(s): <b>{age}</b></div>	
		</div>	
		
		<div class="white" style="float: left;">
			<div style="FONT-SIZE:13PX;float: left;width:50%">Your Position in your family?: <b>{pos_in_fam}</b></div>	
			<div style="FONT-SIZE:13PX;float: left;width:25%">Brother(s): <b>{no_of_bro}</b></div>	
			<div style="FONT-SIZE:13PX;float: left;width:25%">Sister(s): <b>{no_of_sis}</b></div>	
		</div>	
		
		<div class="blue" style="float: left;">
			<div style="FONT-SIZE:13PX;float: left;width:60%">Name of Father:<div class="clearfix" STYLE="clear:both;height:0px"></div> <b>{nam_of_fat}</b></div>	
			<div style="FONT-SIZE:13PX;float: left;width:40%">Occupation:<div class="clearfix" STYLE="clear:both;height:0px"></div> <b>{occ_of_fat}</b></div>	
		</div>
		
		<div class="white" style="float: left;">
			<div style="FONT-SIZE:13PX;float: left;width:60%">Name of Mother:<div class="clearfix" STYLE="clear:both;height:0px"></div> <b>{applicant_mothers}</b></div>	
			<div style="FONT-SIZE:13PX;float: left;width:40%">Occupation: <div class="clearfix" STYLE="clear:both;height:0px"></div><b>{occ_of_mom}</b></div>	
		</div>

		
		<div class="blue" style="float: left;">
			<div style="FONT-SIZE:13PX;float: left;">Who will look after the children when you will be overseas?:
			<div class="clearfix" STYLE="clear:both;height:0px"></div> <b>{relative_name}</b></div>	
		</div>

		<div class="white" style="float: left;">
			<div style="FONT-SIZE:13PX;float: left;">Name and Mobile NO. of relative to contact?:
			<div class="clearfix" STYLE="clear:both;height:0px"></div> <b>{relative_mobile}</b></div>	
		</div>	
			
		<div class="blue" style="float: left;">
			<div style="FONT-SIZE:13PX;float: left;">Skills:
			<div class="clearfix" STYLE="clear:both;height:0px"></div> <b>{applicant_other_skills}</b></div>	
		</div>		
		
		<div class="white" style="float: left;">
			<div style="FONT-SIZE:13PX;float: left;">Expected Salary: <b>{applicant_expected_salary} USD</b></div>	
		</div>			
			
		
		<!--END STATIC DATA-->	
		
</div>
{whole_body}

<div class="clearfix"></div>
</div>
<DIV style="page-break-after:always"></DIV>


<DIV style="border:1px solid black;">
	<div class="title">
		<div style="FONT-SIZE:16PX;color:White;float: left;TEXT-align:center">EDUCATIONAL ATTAINMENT</div>
	</div>
		{education}
<div STyle="clear:both"></div>		
</div>
<div STyle="clear:both;height:20px"></div>		


<DIV style="border:1px solid black;">	
	<div class="title">
		<div style="FONT-SIZE:16PX;color:White;float: left;TEXT-align:center">GENERAL INFORMATION (YES OR NO) </div>
	</div>
	
	<div class="" style="float: left; width: 100%;min-height:100px;height:!important ">
		{gen_info_html}
	</div>
	
<div STyle="clear:both"></div>		
</div>

<div STyle="clear:both;height:20px"></div>		
<DIV style="page-break-after:always"></DIV>
	<!--Start working experience-->		
<div style="border:1px solid black;">	
	<div class="title">
		<div style="FONT-SIZE:16PX;color:White;float: left;TEXT-align:center">WORK EXPERIENCE </div>
	</div>
	{wexp_html}
		
</div>



<div STyle="clear:both;height:20px"></div>	
<DIV style="border:1px solid black;">	
	<div class="title">
		<div style="FONT-SIZE:16PX;color:White;float: left;TEXT-align:center">EMPLOYMENT HISTORY</div>
	</div>
		  {work_experiences}
<div STyle="clear:both"></div>		
</div>
	<!--End working experience-->		



<DIV style="page-break-after:always"></DIV>


	<!--Start working ability-->		
<DIV style="border:1px solid black;">	
	<div class="title">
		<div style="FONT-SIZE:16PX;color:White;float: left;TEXT-align:center">WORKING ABILITY </div>
	</div>
	
	<div class="" style="float: left; width: 100%;min-height:100px;height:!important ">
		<!--STATIC DATA-->
		<div class="white" style="float: left;">
			<div style="FONT-SIZE:13PX;float: left;width:50%;TEXT-ALIGN:CENTER">WORK</div>	
			<div style="FONT-SIZE:13PX;float: left;width:20%;TEXT-ALIGN:CENTER"><b>EXPERIENCED</b></div>
			<div style="FONT-SIZE:13PX;float: left;width:20%;TEXT-ALIGN:CENTER"><b>WILLINGNESS</b></div>			
		</div>
		{wabl_html}
		
	
	</div>
<div STyle="clear:both"></div>		
</div>		
<!--End working ability-->	



	<!--Start future plans-->
<div STyle="clear:both;height:20px"></div>			
<DIV style="border:1px solid black;">	
	<div class="title" STYLE="BORDER:1PX SOLID BLACK;">
		<div style="FONT-SIZE:16PX;color:White;float: left;TEXT-align:center">FUTURE PLANS <P style="margin-top:-3px">Why do you want to go and work overseas? </P></div>
	</div>
	<div class="" style="float: left;BORDER:0PX Solid black;HEIGHT:50PX;PADDING:10PX">
			<div STyle="clear:both;HEIGHT:10PX"></div>			
			<div style="FONT-SIZE:13PX;float: left;width:100%"><b>{future_plans}</b></div>	
	</div>
	
	<div STyle="clear:both;height:20px"></div>	



		
</div>
<div STyle="clear:both;HEIGHT:10PX"></div>
<DIV style="border:0px solid black;">	
<div class="title">
<div style="FONT-SIZE:16PX;color:White;float: left;TEXT-align:center">DECLARATION</P></div>
</div>
<div class="" style="float: left;BORDER:0PX Solid black;HEIGHT:10PX;PADDING:10PX">
<div STyle="clear:both;HEIGHT:1PX"></div>			
<div style="FONT-SIZE:13PX;float: left;width:100%">	
<p>	I <U>{applicant_name}</U> hereby confirms that the information on this form is, to the best of my knowledge , true  and complete . I acknowledge that I have
not with held any materials information which  might preclude me from working overseas. I shall abide by the rules and regulations of <b>Alpha Tomo (P) International Manpower Services, Inc,</b> and work conditions	of my future Employer. I wish to testify tha I want to go and work overseas as Domestic Helper . I further affirm that I shall, to the best of my abilities to complete my Two (2) years contract of my Employment.	<p></div>	
</div>
<p style="width:30%;text-align:center;margin:3px;padding:5px">
<div STyle="clear:both;height:1px"></div>
<div Style="margin-left:50px;margin-top:20px;position:absolute">*** Signed ***</div>	
_________________________________________
<div STyle="clear:both;height:1px"></div>	
Full Name and Signature of Applicants</p>	

<!--End future plans-->	
<div STyle="clear:both;height:25px"></div>	


<!--DOCUMENTS-->	
{documents}
