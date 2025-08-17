<style>
body {
    font-size:11px !important;
    font-family:Arial, Helvetica, sans-serif;
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
    padding:3px !important;
    border: 1px solid #363434;
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
    margin-top:10px; 
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
<div class="gradient" style="text-align:center">
    <img src="{banner}" style="width: 100%; margin-top: -35pt; " width="90%" height="120" />
</div>

<div class="gradient" style="float: LEFT; width: 20%; margin-bottom: 0pt; ">
    <img src="{profile_picture}" style="float:right; height:140px; " />
</div>



<div class="" style="float: right; width: 60%; margin-bottom: 2em; ">
    <table cellspacing="0" cellpadding="1">
        <tr >
            <th width="40%">POST APPLIED FOR:</th>
            <td>{position_name} </td>
			<td><span style="float:right;margin-left:50px">الوظيفه</span></td>
        </tr>
      
		 <tr>
            <th>CODE:</th>
            <td colspan="2">{applicantNumber}</td>
		
        </tr>
		
		 <tr>
            <th style="text-align:center">LAST NAME</th>
            <th style="text-align:center">FIRST NAME</th>
		    <th style="text-align:center">MIDDLE NAME</th>
		</tr>
		
		<tr>
             <td style="text-align:center">{lname}</td>
			 <td style="text-align:center">{fname}</td>
			 <td style="text-align:center">{mname}</td>
		</tr>
		
		
    </table>
</div>

<div class="clearfix"></div>

<div class="" style="float:left; width: 47%; margin-bottom: 2pt; padding:0px; ">
   
    <div>
        <p style="font-size:14px;color:red;text-align:center">Skills Checklist</p>
		  <p style="font-size:14px;color:red;text-align:center">المهارات </p>
        <table cellspacing="0" cellpadding="1">
            <tr >
                <td>LANGUAGES</td>
				<td>اللغات</td>
            <tr >
			 <tr >
                <td>{applicant_languages}</td>
				<td></td>
            <tr>
			

        </table>
    </div>
	
	<div>
		
		<table cellspacing="0" cellpadding="1">
		 <tr >
                <td>House Work</td>
				<td>لاعمال المنزلية</td>
            <tr >
			<tr >
				<td>{applicant_other_skills}</td>
				<td></td>
				
			<tr >
		</table>
	</div>
			
		<div class="" style=" ">
			
			<p style="font-size:14px;color:red;text-align:center">WORK EXPERIENCE</p>
			<p style="font-size:14px;color:red;text-align:center">بالعمل الخبرة</p>
			{work_experiences}
		</div>

		  <div>
	<p style="font-size:14px;color:red;text-align:center">Passport Details  بيانات جواز السفر</p>
				<table cellspacing="0" cellpadding="1">
					<tr >
						<th width="40%">NUMBER:</th>
						<td>{passport_number}</td>
						<td>رقم الجواز</td>
					</tr>
					<tr >
						<th>DATE OF ISSUE:</th>
						<td>{passport_issue}</td>
						<td>تاريخ الاصدار</td>
					</tr>
					
					<tr >
						<th>DATE OF EXP.</th>
						<td>{passport_expiration}</td>
						<td>تاريخ الانتهاء</td>
					</tr>
					<tr >
						<th width="40%">PLACE OF ISSUED:</th>
						<td>{passport_issue_place}</td>
						<td>محل الاصدار</td>
					</tr>
				</table> 
			</div>

   
</div>





<div class="" style="float:right; width: 47%; margin-left:10px; margin-bottom: 2pt; ">
{whole_body}
 <div>
         <table cellspacing="0" cellpadding="1">
            <tr >
                <th width="45%">NATIONALITY:</th>
                <td>{applicant_nationality}</td>
				<td>الجنسية</td>
            </tr>
            <tr >
                <th>RELIGION/BELIEF:</th>
                <td>{applicant_religion}</td>
				<td></td>
            </tr>
            <tr >
                <th>DATE OF BIRTH:</th>
                <td>{applicant_birthdate}</td>
				<td>تاريخ الميلاد</td>
            </tr>
            <tr >
                <th>SEX:</th>
                <td>{applicant_gender}</td>
				<td></td>
            </tr>
            <tr >
                <th>AGE:</th>
                <td>{applicant_age}</td>
				<td>العمر</td>
            </tr>
        
            <tr >
                <th>CIVIL STATUS:</th>
                <td>{applicant_civil_status}</td>
				<td>لحالة الاجتماعيه</td>
            </tr>
            <tr >
                <th>WEIGHT:</th>
                <td>{applicant_weight}</td>
				<td>الوزن</td>
            </tr>
            <tr >
                <th>HEIGHT:</th>
                <td>{applicant_height}</td>
				<td>الطول</td>
				
            </tr>
			  <tr >
                <th>EDUCATION:</th>
                <td colspan="2">{edu}</td>
				
            </tr>
			
			  <tr >
                <th>CONTACT NUMBER:</th>
                <td colspan="2">{contact}</td>
				
            </tr>
			 
			
            <tr >
                <th>ADDRESS:</th>
                <td colspan="2">{applicant_address}</td>
            </tr>
			
			  <tr >
                <th>NAME OF NEXT KIN:</th>
                 <td colspan="2">{applicant_incase_name}</td>
            </tr>
			
			
			
			  <tr >
                <th>ADDRESS OF NEXT KIN:</th>
                 <td colspan="2">{applicant_incase_address}</td>
				
            </tr>
			
			
			
			  <tr >
                <th>CONTACT OF NEXT KIN:</th>
                <td colspan="2">{applicant_incase_contact}</td>
            </tr>
			
			
			
        </table>
    </div>
</div>


<div class="" style="clear: both; margin: 0pt; padding: 0pt; "></div>
<DIV style="page-break-after:always"></DIV>
{documents}

