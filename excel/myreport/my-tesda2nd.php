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










<h1 style="text-align:center">TESDA 2nd </h1>
<h3 style="text-align:center">As of <?php echo date('d-F-Y', strtotime($_GET["dateme"])); ?></p></h3>
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


<style>
table td{
font-size:12px;
}
table th{
font-size:14px;
}
</style>

<table id="customers" >
<tr>
<td colspan="18"><h2 style="text-align:center"></h2></td>
<tr>

<tr>

<th style="width:3%">#</th>
<th>Status</th>
<th>Date Applied</th>
<th>Applicant Name</th>


<th>FRA</th>
<th>TESDA NAME</th>
<th>TESDA DATE</th>
<th>TESDA TO</th>
<th>TESDA ASSESTMENT</th>
<th>Age</th>
<th>Position</th>
<th>Passport</th>
<th>Country</th>
<th>Agent</th>
<th>Branch</th>
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


where certificate_tesda_release='".$_GET["dateme"]."'
AND certificate_tesda_release  NOT IN ('1970-01-01','0000-00-00','')
group by applicant_id");

while($row=mysql_fetch_array($fit))
{

$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);
$position = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);
if($row['branch_type']==0){$branchme="N/A";}
if($row['branch_type']==7){$branchme="ILO ILO BRANCH";}
if($row['branch_type']==8){$branchme="CDO BRANCH";}
if($row['branch_type']==9){$branchme="DAVAO BRANCH";}
if($row['branch_type']==10){$branchme="BINANGON BRANCH";}
if($row['branch_type']==11){$branchme="CAVITE BRANCH";}
if($row['branch_type']==14){$branchme="MAIN MANILA BRANCH";}
if($row['branch_type']==15){$branchme="TACLOBAN BRANCH";}
if($row['branch_type']==13){$branchme="CEBU BRANCH";}

echo'<tr>';

echo'<td>'.$count.'</td>';
echo'<td>
<a href="'.$review.''.$row['applicant_id'].'"  target="_blank">
<button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:12px">'.$row["status"].'</button></a></td>';
echo'<td>'; echo date('M-d-Y', strtotime($row['applicant_date_applied']));echo'</td>';



echo'<td>'; echo date('M-d-Y', strtotime($row['applicant_date_applied']));echo'</td>';
echo'<td style="font-size:14px;font-weight:bold"><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';

echo'<td>'.$row["employer_name"].'</td>';
echo"<td>".$row['certificate_tesda_name']."</td>";
echo'<td>'; echo date('M-d-Y', strtotime($row['certificate_tesda_date']));echo'</td>';
echo'<td>'; echo date('M-d-Y', strtotime($row['certificate_tesda_release']));echo'</td>';
echo'<td>'; echo date('M-d-Y', strtotime($row['certificate_tesda_assest']));echo'</td>';

echo"<td>".$row['applicant_age']."</td>";
echo"<td>".$position1['position_name']."</td>";
echo'<td>'.$row["passport_number"].'</td>';
echo'<td>'.$countryrow["country_name"].'</td>';
echo'<td>'.$row["agent_first"].' '.$row["agent_last"].'</td>';
echo'<td>'.$branchme.'</td>';
echo'</tr>';
$count++;

}
ECHO'</table>';
	
?>		

  
</div>

</body>
</html>
