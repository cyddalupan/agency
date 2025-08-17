<style>
body {
    font-size:14px !important;
    font-family:calibri,Arial, Helvetica, sans-serif;
	line-height:150%;
	color:black;
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
  
    text-align:left !important;
    	color:black;
		text-transform:uppercase;
	
}
table th {
   
    text-transform: uppercase;
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
    font-size:14px;
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

textarea{
    width: 100%;
    color: #FFF;
    background: transparent;
    border: none;
    outline: none;
}
</style>

<body>
 

<div class="gradient" style="text-align:center;float: right; width: 100%;margin-top:-25pt">
    <img src="{banner}" style="width: 100%; " width="90%"/>
</div>



<div class="gradient" style="float: left; width:  margin-bottom: 0pt;width: 70% ">
<h2 style="color:black">{lname} , {fname} {mname}</h2>
<p style="margin-top:-15pt">   <span style="margin-right:20px;">Position Applied.<span class=""> {position_name}</span></span> </p>
</div>

<div class="gradient" style="float: right; margin-bottom: 0pt;width: 25% ">
    <img src="{profile_picture}" style="float:right; height:110px;BORDER:1PX SOLID BLACK;width:250px "  />
</div>
  


<table cellspacing="0" cellpadding="1">
<tr >
<th style="color:black;width:150px;text-align:left;font-size:22px" colspan="3">
<b>PERSONAL DATA  </b></th>
</tr>
 <div class="clearfix"></div>  <div class="clearfix"></div> 

<tr >
<th style="width:250px">Age:</th>
<td>{applicant_age}</td>
</tr>

<tr >
<th  style="">Gender:</th>
<td>{applicant_gender}</td>
</tr>


<tr >
<th  style="">Date of Birth:</th>
<td>{applicant_birthdate}</td>
</tr>

<tr >
<th style="">Civil Status:</th>
<td>{applicant_civil_status}</td>
</tr>

<tr >
<th style="">Height:</th>
<td>{applicant_height}</td>
</tr>


<tr >
<th style="">Weight:</th>
<td>{applicant_weight}</td>
</tr>



<tr >
<th  style="">NATIONALITY:</th>
<td>{applicant_nationality}</td>
</tr>


<tr >
<th style="">Religion:</th>
<td>{applicant_religion}</td>
</tr>


</table>
    



<div class="clearfix"></div>




<table cellspacing="0" cellpadding="1">
<tr >
<th style="color:black;width:150px;text-align:left;font-size:22px" colspan="3">
<b>WORK EXPERIENCE </b></th>
</tr>
</table>
 <div class="clearfix"></div>

{work_experiences}
  
</div>
<div class="clearfix"></div>



<DIV style="page-break-after:always"></DIV>
 
<table cellspacing="0" cellpadding="1">
<tr >
<th style="color:black;width:150px;text-align:left;font-size:22px" colspan="3">
<b>EDUCATION</b></th>
</tr>
</table>
 {education}

<div class="clearfix"></div>


<tr >
<th style="color:black;width:150px;text-align:left;font-size:22px" colspan="3">
<b>TRAININGS/SEMINARS:</b></th>
</tr>
</table>


 {trainineme}

<div class="clearfix"></div>


<div class="" style="clear: both; margin: 0; padding: 0pt; "></div>
<DIV style="page-break-after:always"></DIV>
{documents}