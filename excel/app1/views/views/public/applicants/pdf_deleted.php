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


h1{
page-break-before: always;
}
</style>
<body>
<div class="gradient" style="text-align:center">
    <img src="{banner}" style="width: 100%; " width="90%" height="150" />
</div>

<div class="gradient" style="float: right; width: 20%; margin-bottom: 0pt; ">
    <img src="{profile_picture}" style="float:right; height:140px; " />
</div>

<div class="" style="float: left; width: 60%; margin-bottom: 2pt; ">
    <span style="margin-right:30px;">REF NO.<span class="ref-no">{ref_no}</span></span> <span style="float:right;">DATE. {applicant_date_applied}</span>
</div>

<div class="" style="float: left; width: 60%; margin-bottom: 2em; ">
    <table cellspacing="0" cellpadding="1">
        <tr >
            <th width="40%">POST APPLIED FOR:</th>
            <td>{position_name}</td>
        </tr>
        <tr>
            <th>MONTHLY SALARY OFFER:</th>
            <td>{requirement_offer_salary}</td>
        </tr>
		{contact}
    </table>
</div>

<div class="clearfix"></div>

<div class="" style="margin-bottom: 12pt; ">
    <table cellspacing="0" cellpadding="1">
        <tr >
            <th width="10%">NAME:</th>
            <td>{applicant_name}</td>
        </tr>
    </table>
</div>

<div class="clearfix"></div>

<div class="" style="float:left; width: 47%; margin-bottom: 2pt; padding:0px; ">
    <div>
        <table cellspacing="0" cellpadding="1">
            <tr >
                <th width="40%">NATIONALITY:</th>
                <td>{applicant_nationality}</td>
            </tr>
            <tr >
                <th>RELIGION/BELIEF:</th>
                <td>{applicant_religion}</td>
            </tr>
            <tr >
                <th>DATE OF BIRTH:</th>
                <td>{applicant_birthdate}</td>
            </tr>
            <tr >
                <th>SEX:</th>
                <td>{applicant_gender}</td>
            </tr>
            <tr >
                <th>AGE:</th>
                <td>{applicant_age}</td>
            </tr>
            <tr >
                <th>LIVING TOWN:</th>
                <td>{applicant_address}</td>
            </tr>
            <tr >
                <th>MARITAL STATUS:</th>
                <td>{applicant_civil_status}</td>
            </tr>
            <tr >
                <th>WEIGHT:</th>
                <td>{applicant_weight}</td>
            </tr>
            <tr >
                <th>HEIGHT:</th>
                <td>{applicant_height}</td>
            </tr>
            <tr >
                <th>ADDRESS:</th>
                <td>{applicant_address}</td>
            </tr>
        </table>
    </div>
    <div>
        <p>KNOWLEDGE OF LANGUAGES</p>
        <table cellspacing="0" cellpadding="1">
            <tr >
                <td>{applicant_languages}</td>
            <tr >
        </table>
    </div>
    <div>
        <p>SKILLS</p>
        <table cellspacing="0" cellpadding="1">
            <tr >
                <td>{applicant_other_skills}</td>
            <tr >
        </table>
    </div>
    <div class="" style="margin-bottom: -52pt">
	<p>REMARKS</p>
    {applicant_remarks}
</div>

</div>

<div class="" style="float:right; width: 47%; margin-left:10px; margin-bottom: 2pt; ">
    <div>
        <p>PASSPORT DETAILS</p>
        <table cellspacing="0" cellpadding="1">
            <tr >
                <th width="40%">NUMBER:</th>
                <td>{passport_number}</td>
            </tr>
            <tr >
                <th>DATE OF ISSUE:</th>
                <td>{passport_issue}</td>
            </tr>
            <tr >
                <th width="40%">PLACE OF ISSUED:</th>
                <td>{passport_issue_place}</td>
            </tr>
            <tr >
                <th>DATE OF EXP.</th>
                <td>{passport_expiration}</td>
            </tr>
        </table> 
    </div>
    {whole_body}
</div>

<DIV style="page-break-after:always"></DIV>
<div class="clearfix"></div>
<div class="" style="margin-bottom: -52pt; ">
	<p>EDUCATIONAL</p>
    {education}
</div>
<div class="clearfix"></div>
<div class="" style="margin-bottom: 12pt; ">
    <p>WORK EXPERIENCES</p>
    {work_experiences}
</div>
<DIV style="page-break-after:always"></DIV>
{documents}

<div class="" style="clear: both; margin: 0pt; padding: 0pt; "></div>