		<script>
function exportF(elem) {
  var table = document.getElementById("example-checkbox");
  var html = table.outerHTML;
  var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url 
  elem.setAttribute("href", url);
  elem.setAttribute("download", "export.xls"); // Choose the file name
  return false;
}
</script>	


  <style>
table {
  border-spacing: 0;
  width: 70%;
  border: 1px solid #ddd;
}

th {
  cursor: pointer;
}

th, td {
  text-align: left;
  padding: 1px;
  font-size:12px;
}

tr:nth-child(even) {
  background-color: #f2f2f2
}
</style>
 <div class="col-md-12 col-sm-12">
    <div class="row">
     <div class="col-md-12">
        
        <div class="clients-page">
           <?php $app->renderAlerts(); ?>
           <div class="row">
              <div class="col-md-12">
               
                 <div class="hero">
                    <!-- Title. Don't forget the <span> tag -->
                    <h3><span> On Process Applicants</span></h3>
                    <!-- para -->
                 </div> 
                 
                 <div class="row">
                    <div class="col-md-16"  style="padding-bottom:20px;">
                    
                        <div class="table-responsive">
                       


<script>
function exportF(elem) {
  var table = document.getElementById("customers");
  var html = table.outerHTML;
  var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url 
  elem.setAttribute("href", url);
  elem.setAttribute("download", "export.xls"); // Choose the file name
  return false;
}
</script>


					   
<a id="downloadLink" onclick="exportF(this)" style="color:Red">Export to excel</a>
					   

<form id="demo-form2"  form-label-left" method="post"  style="display:none">


<div class="form-group">
<div class="col-md-4 col-sm-4 col-xs-4 form-group">

<input type="text" name="mydata" placeholder="FIRSTNAME,LASTNAME"  Style="width:200px" class="form-control">

</div>

<div class="col-md-6 col-sm-12 col-xs-12 form-group">
<button type="submit" class="btn btn-info"  name="Search" >Search </button>
</div>

</div>
</form>   


					   
					   
  <table class="table table-hover table-striped table-bordered" style="margin-left:-160px" id="customers">
<thead>
<tr>
<th >#</th>
<th ></th>


<th  style="background:#6495ED;font-weight:bold;font-size:12px">Name</th>
<th  style="background:#6495ED;font-weight:bold">POSITION</th>
<th  style="background:#6495ED;font-weight:bold">PASSPORT NUMBER</th>
<th  style="background:#6495ED;font-weight:bold">AGE</th>


<th  style="background:#6495ED;font-weight:bold">FIRST TIMER /EX</th>
<th  style="background:#6495ED;font-weight:bold">MEDICAL STATUS</th>
<th  style="background:#6495ED;font-weight:bold">MEDICAL EXPIRATION</th>

<th  style="background:#00008B;font-weight:bold;color:white">NAME OF EMPLOYER</th>
<th  style="background:#00008B;font-weight:bold;color:white">EMPLOYER CONTACT NUMBER </th>

<th  style="background:#00008B;font-weight:bold;color:white"> 	DEPLOYMENT DATE </th>
<th  style="background:#00008B;font-weight:bold;color:white"> 	CURRENT STATUS </th>	
</tr>
</thead>
<tbody>

<?php


$dbhost = 'localhost';
$dbuser = 'iwebfram_alphato';
$dbpass = 'L@lP3wMuH0wToWkzh';
$dbname = 'iwebfram_workasia';
$conn = mysqli_connect($dbhost, $dbuser, $dbpass,$dbname);
?>   		
<?php
$counter=1;
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
		AND applicant_status NOT IN (9,21,22,1,14,25,5)
		order by requirement_remarks asc";
		$row1 = $conn->query($app);
		$flightrowdate="";

if(isset($_POST['Search']))
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
	AND applicant_status applicant_status NOT IN (9,21,22,1,14,25,5)
	and applicant_first LIKE '%".$_POST['mydata']."%'
	order by applicant_updated desc";
	$row1 = $conn->query($app);

	$flightrowdate="";
	
	


}



while($row= $row1->fetch_assoc() ){

			 		

echo"<td>".$counter."</td>";
echo'<td>
	 <a target="_blank"  href="https://workasia.agency/system/employer/applicants/resume/00000000'.$row['applicant_id'].'" class="btn btn-danger btn-sm"></i> View</a>
					 </td>';
					 

echo"<td><a target='_blank'  href='#' STYLE='color:black;font-weight:bold;font-size:16px'>
".$row['applicant_first']."  ".$row['applicant_last']."</a></td>";


echo'<td>'.$row["position_name"].'</td>';
echo'<td>'.$row["passport_number"].'</td>';
echo'<td>'.$row["applicant_age"].'</td>';
echo'<td>'.$row["applicant_ex"].'</td>';

echo'<td>'.$row["certificate_medical_result"].'</td>';


IF($row['certificate_medical_expiration']=='1970-01-01' || $row['certificate_medical_expiration']=='0000-00-00' 
|| $row['certificate_medical_expiration']=='' || $row['certificate_medical_expiration']==null) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row['certificate_medical_expiration']));echo'</td>';}



echo"<td>".$row['sub_employer']."</td>";
echo"<td>".$row['applicant_employer_number']."</td>";


IF($row['flight_date']=='1970-01-01' || $row['flight_date']=='0000-00-00' 
|| $row['flight_date']=='' || $row['flight_date']==null) { echo"<td></td>"; }
else{ echo'<td>'; echo date('M-d-Y', strtotime($row['flight_date']));echo'</td>';}


echo"<td>".$row['statusText']."</td>";



echo"</tr>";
$counter++;
}				

?>	

</tbody>
</table>	
					   
					   
					   
					   
					   
					   
    						<div style="clear:both;height:40px"></div>
                        </div>
                        
                    </div>
                 </div>
                 
              </div>
           </div>
        </div>
         
        
     </div>
    </div>
  </div>
</div>