<?php include'../review.php';?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {background:#F5F5F5;
FONT-FAMILY:arial;
font-size:12px;
}
#customers {
font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
border-collapse: collapse;
width: 100%;
}

#customers td, #customers th {
border: 1px solid #ddd;
padding: 4px;
}

#customers tr:nth-child(even){background-color: white;}

#customers tr:hover {background-color: white;}

#customers th {
padding-top: 10px;
padding-bottom: 10px;
text-align: left;
background-color: #DCDCDC;
color: black;
}
</style>
</head>
<body>
<?PHP
include'../tracking.php';
include'../db.php';
?>










<h3 style="text-align:center">As of <?php echo date('M-d-Y', strtotime($date1)); ?></p></h3>
<p>
<button onclick="goBack()">Go Back</button>

<script>
function goBack() {
  window.history.back();
}
</script></p>
<div class="row">




<div class="col-md-12 col-sm-12" style="margin-top:-1px;margin:10px;">
<div class="row" style="floar:left;background:white;padding:10px;min-height:100px">


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


<a id="downloadLink" onclick="exportF(this)" style="color:Red">SAVE MY WORK</a>


<table id="customers" >
<tr>
<td colspan="18"><h2 style="text-align:center">UNFIT  </h2></td>
<tr>

<tr>
<th style="width:3%"></th>
<th style="width:3%">#</th>
<th>Status</th>
<th>Date Applied</th>

<th>Applicant Name</th>


<th >CLINIC</th>
<th >EXAM DATE</th>		
<th >RESULT</th>
<th>FRA</th>
<th>Age</th>
<th>Position</th>
<th>Country</th>
<th>Agent</th>
</tr>
<tr>

<?php


$count=1;
$fit =mysql_query("SELECT * FROM applicant
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


				LEFT JOIN 	country
				ON applicant.applicant_preferred_country=country.country_id

where  certificate_medical_result  IN ('UNFIT')
AND applicant_preferred_country=".$_GET["countme"]."
group by applicant_id");

while($row=mysql_fetch_array($fit))
{

$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);
$position = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);


echo'<tr>';
echo'<td></td>';
echo'<td>'.$count.'</td>';
echo'<td>
<a href="'.$review.''.$row['applicant_id'].'"  target="_blank">
<button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:9px">'.$row["status"].'</button></a></td>';


echo'<td>'; echo date('Y-M-d', strtotime($row['applicant_date_applied']));echo'</td>';

echo'<td><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';



echo"<td>".$row['certificate_medical_clinic']."</td>";
echo"<td>".$row['certificate_medical_exam_date']."</td>";
echo"<td>".$row['certificate_medical_result']."</td>";
echo'<td>'.$row["employer_name"].'</td>';
echo"<td>".$row['applicant_age']."</td>";
echo"<td>".$position1['position_name']."</td>";
echo'<td>'.$countryrow["country_name"].'</td>';
echo'<td style="font-size:12px">'.$row["agent_first"].' '.$row["agent_last"].'</td>';
echo'</tr>';
$count++;

}
ECHO'</table>';
	
?>		

  
</div>

</body>
</html>
