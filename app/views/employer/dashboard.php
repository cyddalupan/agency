
<div class="row">
	<div class="col-md-12 col-sm-12">
		<h2><a href="#">Dashboard</a></h2>
	</div> 
</div>





</br> </br>


<form id="demo-form2" method="post" action="https://recruitment-portal.net/1stnorthern/employer/dashboard">

<div class="input-group">
  <div class="form-outline">
    
	<input type="text" placeholder="first name , last name , fra name" class="form-control" name="mydata" required>

    <label class="form-label" for="form1" STYLE="COLOR:BLACK;FONT-SIZE:">Search Applicant FIRST NAME OR LAST NAME</label>
  </div>
<div class="col-md-6 col-sm-12 col-xs-12 form-group">

<button type="submit" class="btn btn-info"  name="Search1" >Search Applicant</button>

</div>
</div>


</form> 



<table class="table table-hover table-striped table-bordered" style="margin-left:-210px" id="customers">
<thead>
<tr>
<th >#</th>
<th ></th>
<th  style="background:yellow;font-weight:bold">CONTRACT </br>BATCH NO.</th>
<th  style="background:yellow;font-weight:bold" >WAKALA</th>

<th  style="background:yellow;font-weight:bold">Name</th>
<th  style="background:yellow;font-weight:bold">POSITION</th>
<th  style="background:yellow;font-weight:bold">PASSPORT NUMBER</th>
<th  style="background:yellow;font-weight:bold">AGE</th>


<th  style="background:yellow;font-weight:bold">FIRST TIMER /  EX-ABROAD</th>
<th  style="background:yellow;font-weight:bold">RELIGION</th>
<th  style="background:yellow;font-weight:bold">DATE OF MEDICAL</th>
<th  style="background:yellow;font-weight:bold">MEDICAL EXPIRATION</th>
<th  style="background:yellow;font-weight:bold">MEDICAL STATUS</th>
<th  style="background:yellow;font-weight:bold">VFS TASHEEL</th>
<th  style="background:yellow;font-weight:bold">TESDA</th>
<th  style="background:yellow;font-weight:bold">OWWA</th>
<th  style="background:yellow;font-weight:bold">OEC</th>
<th  style="background:yellow;font-weight:bold">VISA STAMPING</th>
<th  style="background:#1E90FF;font-weight:bold;color:white">CONTRACT RECEIVED </th>
<th  style="background:#1E90FF;font-weight:bold;color:white">NAME OF EMPLOYER</th>
<th  style="background:#1E90FF;font-weight:bold;color:white">EMPLOYER CONTACT NUMBER </th>
<th  style="background:#1E90FF;font-weight:bold;color:white">	VISA NUMBER</th>
<th  style="background:#1E90FF;font-weight:bold;color:white">ID NUMBER </th>
<th  style="background:#1E90FF;font-weight:bold;color:white"> 	DEPLOYMENT DATE </th>
<th  style="background:#1E90FF;font-weight:bold;color:white"> 	CURRENT STATUS </th>	
</tr>
</thead>
<tbody>

<?php


$dbhost = 'localhost';
$dbuser = 'abbaphil_jinhel';
$dbpass = '67PtE01S4KL4hKIqUQyoP2aKhDwud7BV';
$dbname = 'abbaphil_north';
$conn = mysqli_connect($dbhost, $dbuser, $dbpass,$dbname);



?>   		
<?php
$counter=1;
	
if(isset($_POST['Search1']))
{

	
	$app = "SELECT *  FROM applicant 

	LEFT JOIN  statuses
	ON applicant.applicant_status = statuses.number
	
		LEFT JOIN  	applicant_certificate
	ON applicant.applicant_id = applicant_certificate.certificate_applicant
	
	
	LEFT JOIN  	applicant_requirement
	ON applicant.applicant_id =applicant_requirement.requirement_applicant
	
	LEFT JOIN  	employer
	ON applicant.applicant_employer =employer.employer_id

	LEFT JOIN  	recruitment_agent
	ON applicant.applicant_source =recruitment_agent.agent_id	
	
		LEFT JOIN  applicant_passport
		ON applicant.applicant_id =	applicant_passport.passport_applicant	
	
	LEFT JOIN  position
		ON applicant.applicant_preferred_position =	position.position_id	
	
	
	where
	applicant_employer=".$_SESSION['employer']['user']['employer_id']."
	and applicant_first LIKE '%".$_POST['mydata']."%'
	or applicant_last LIKE '%".$_POST['mydata']."%'
	order by applicant_updated desc";
	$row1 = $conn->query($app);
	$flightrowdate="";
	
	


}

if(isset($_POST['Search1']))
{

while($row= $row1->fetch_assoc() ){

			 		

echo"<td>".$counter."</td>";
echo'<td>
	 <a target="_blank"  href="https://recruitment-portal.net/maanyag/employer/applicants/resume/00000000'.$row['applicant_id'].'" class="btn btn-danger btn-sm"></i> View</a>
					 </td>';
					 
					 
echo"<td>".$row['requirement_remarks']."</td>";
echo"<td>".$row['applicant_requirement_kawala']."</td>";
echo"<td><a target='_blank'  href='#' STYLE='color:black;font-weight:bold;font-size:16px'>
".$row['applicant_first']."  ".$row['applicant_last']."</a></td>";


echo'<td>'.$row["position_name"].'</td>';
echo'<td>'.$row["passport_number"].'</td>';
echo'<td>'.$row["applicant_age"].'</td>';
echo'<td>'.$row["applicant_ex"].'</td>';
echo'<td>'.$row["applicant_religion"].'</td>';

IF($row['certificate_medical_exam_date']=='1970-01-01' || $row['certificate_medical_exam_date']=='0000-00-00' 
|| $row['certificate_medical_exam_date']=='' || $row['certificate_medical_exam_date']==null) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row['certificate_medical_exam_date']));echo'</td>';}

IF($row['certificate_medical_expiration']=='1970-01-01' || $row['certificate_medical_expiration']=='0000-00-00' 
|| $row['certificate_medical_expiration']=='' || $row['certificate_medical_expiration']==null) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row['certificate_medical_expiration']));echo'</td>';}


echo'<td>'.$row["certificate_medical_result"].'</td>';


IF($row['vfs']=='1970-01-01' || $row['vfs']=='0000-00-00' || $row['vfs']=='' || $row['vfs']==null) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row['vfs']));echo'</td>';}

IF($row['certificate_tesda_release']=='1970-01-01' || $row['certificate_tesda_release']=='0000-00-00' 
|| $row['certificate_tesda_release']=='' || $row['certificate_tesda_release']==null) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row['certificate_tesda_release']));echo'</td>';}

IF($row['certificate_owwa_to']=='1970-01-01' || $row['certificate_owwa_to']=='0000-00-00' 
|| $row['certificate_owwa_to']=='' || $row['certificate_owwa_to']==null) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row['certificate_owwa_to']));echo'</td>';}


IF($row['requirement_oec_release_date']=='1970-01-01' || $row['requirement_oec_release_date']=='0000-00-00' 
|| $row['requirement_oec_release_date']=='' || $row['requirement_oec_release_date']==null) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row['requirement_oec_release_date']));echo'</td>';}


IF($row['requirement_visa_stamp']=='1970-01-01' || $row['requirement_visa_stamp']=='0000-00-00' 
|| $row['requirement_visa_stamp']=='' || $row['requirement_visa_stamp']==null) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row['requirement_visa_stamp']));echo'</td>';}


IF($row['requirement_contract']=='1970-01-01' || $row['requirement_contract']=='0000-00-00' 
|| $row['requirement_contract']=='' || $row['requirement_contract']==null) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row['requirement_contract']));echo'</td>';}



echo"<td>".$row['sub_employer']."</td>";
echo"<td>".$row['applicant_employer_number']."</td>";
echo"<td>".$row['requirement_visa_no']."</td>";
echo"<td>".$row['applicant_employer_idno']."</td>";

IF($row['flight_date']=='1970-01-01' || $row['flight_date']=='0000-00-00' 
|| $row['flight_date']=='' || $row['flight_date']==null) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row['flight_date']));echo'</td>';}


echo"<td>".$row['statusText']."</td>";



echo"</tr>";
$counter++;
}				
}
?>	

</tbody>
</table>	