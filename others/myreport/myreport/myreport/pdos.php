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
text-align: center;
background-color: #DCDCDC;
color: black;
font-size:14px;
}
</style>
</head>
<body>
<?PHP
include'../tracking.php';
include'../db.php';
?>






<p>

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


<form id="demo-form2"  method="post">
<table style="width:500px;border:none">
	<tr>
		<td style="border:none">Date From: <input type="date"  class="form-control" name="datefrom" required  value="<?=$_POST['datefrom']?>" STYLE="WIDTH:150PX;padding:3px"></td>
		<td style="border:none">Date To: <input type="date"  class="form-control" name="dateto" required   value="<?=$_POST['dateto']?>"  STYLE="WIDTH:150PX;padding:3px"></td>
		<td style="border:none">
		Country:
<select class="form-control" required name="country"  required style="width:150px">
<option value="">-Select Country-</option>
<option value="5">KUW</option>
<option value="7">KSA</option>
<option value="11">QAT</option>
</select>
</td>
		
		
		<td style="border:none"><button type="submit" class="btn btn-info"  name="filter" >Filter</button></td>
	</tr>
</table>
</form>



<a id="downloadLink" onclick="exportF(this)" style="color:Red">SAVE MY WORK</a>


<table id="customers" >
<tr>
<td colspan="18"><h2 style="text-align:center">Department of Labor and Employment</h2></td>
<tr>

<tr>
<td colspan="18"><h1 style="text-align:center">Overseas Workers Welfare Administration</h1></td>
<tr>
<tr>
<td colspan="18"><h2 style="text-align:center">Pre-Departure Orientation Seminar (PDOS) Report Form</h2></td>
<tr>
<tr>
<td colspan="18"><h4 style="text-align:center">As of <?php echo date('d-F-Y', strtotime($date1)); ?></h4></td>
<tr>

<tr>
<th style="width:3%">#</th>

<th>Date Applied</th>
<th>Control No.</th>
<th>Certificate No.</th>

<th>Last Name</th>
<th>First Name</th>
<th>Middle Name</th>
<th>Civil Status</th>
<th>Gender</th>
<th>Age</th>
<th>Date of Birth</th>
<th>Job Classification</th>
<th>Date of Supplemental PDOS (Day 1)</th>
<th>Date of PDO at OWWA (Day 1)</th>

<th>Remarks</th>
<th>Country</th>

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



group by applicant_id 
order by applicant_date_applied desc
LIMIT 20");


if(isset($_POST['filter']))
{	
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
WHERE  certificate_pdos_date between '".$_POST['datefrom']."' AND '".$_POST['dateto']."'
AND applicant_preferred_country=".$_POST['country']."
group by applicant_id
order by certificate_pdos_date desc ");
}



while($row=mysql_fetch_array($fit))
{

$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);
$position = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);


echo'<tr>';

echo'<td>'.$count.'</td>';

echo'<td>'; echo date('Y-M-d', strtotime($row['applicant_date_applied']));echo'</td>';
echo'<td>'.$row["certificate_pdos_no"].'</td>';
echo'<td>'.$row["owwa_number"].'</td>';

echo'<td><b>'.$row["applicant_last"].'</b></td>';
echo'<td><b>'.$row["applicant_first"].'</b></td>';
echo'<td><b>'.$row["applicant_middle"].'</b></td>';
echo'<td><b>'.$row["applicant_civil_status"].'</b></td>';
echo'<td><b>FEMALE</b></td>';
echo'<td>'.$row["applicant_age"].'</td>'; 
echo'<td>'; echo date('Y-M-d', strtotime($row['applicant_birthdate']));echo'</td>';	
echo'<td>DW</td>';

IF($row['certificate_pdos_date']=='1970-01-01' || $row['certificate_pdos_date']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo"<td>".$row['certificate_pdos_date']."</td>"; }


IF($row['certificate_owwa_to']=='1970-01-01' || $row['certificate_owwa_to']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo"<td>".$row['certificate_owwa_to']."</td>"; }

echo'<td>
<a href="'.$review.''.$row['applicant_id'].'"  target="_blank">
<button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:9px">'.$row["status"].'</button></a></td>';
echo'<td>'.$countryrow["country_name"].'</td>';

echo'</tr>';
$count++;

}
ECHO'</table>';
	
?>		

  
</div>

</body>
</html>
