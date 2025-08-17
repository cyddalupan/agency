<?php
session_start();
if(!isset($_SESSION['admin']['user'])){
header("location:noaccess.php");
}
include 'calendar/db.php';
include'tracking.php';
//for flights
$flight = "SELECT count(flight_date) as fefe,
applicant_requirement.* 
, applicant.* 
, recruitment_agent.* 
FROM applicant_requirement


LEFT JOIN  	applicant
ON applicant_requirement.requirement_applicant =applicant.applicant_id

LEFT JOIN  	recruitment_agent
ON applicant.applicant_source =recruitment_agent.agent_id

where branch_type=".$_GET['imy']."

 group by flight_date ";
$flightresult = $conn->query($flight);
$flightrowdate="";
while($flightrow = $flightresult->fetch_assoc() ){

            $flightrowdate .="{
         
		   title: 'Flights (".$flightrow['fefe'].")',
	   
            url: 'myreport/my2-flight.php?flightdate=".$flightrow['flight_date']."&&imy=".$_GET['imy']."',
            start: '".$flightrow['flight_date']."'
            },";
}


  
//OEC RELEASE
$oec = "SELECT count(requirement_oec_release_date) as fefe
,applicant_requirement.* 
, applicant.* 
, recruitment_agent.* 
FROM applicant_requirement


LEFT JOIN  	applicant
ON applicant_requirement.requirement_applicant =applicant.applicant_id

LEFT JOIN  	recruitment_agent
ON applicant.applicant_source =recruitment_agent.agent_id
where branch_type=".$_GET['imy']."
group by requirement_oec_release_date ";
$oecresult = $conn->query($oec);
$oecrowdate="";
while($oecrow = $oecresult->fetch_assoc() ){

$flightrowdate .="{
    title: 'OEC (".$oecrow['fefe'].")',
    url: 'myreport/my2-oec.php?dateme=".$oecrow['requirement_oec_release_date']."&&imy=".$_GET['imy']."',
    start: '".$oecrow['requirement_oec_release_date']."'
    },";
}



//vfs Release
$vfs = "SELECT 
count(vfs) as fefe,applicant_requirement.* 
, applicant.* 
, recruitment_agent.* 
FROM applicant_requirement


LEFT JOIN  	applicant
ON applicant_requirement.requirement_applicant =applicant.applicant_id

LEFT JOIN  	recruitment_agent
ON applicant.applicant_source =recruitment_agent.agent_id
where branch_type=".$_GET['imy']."
AND vfs NOT IN ('1970-01-01','0000-00-00','') GROUP BY vfs";
$vfs1 = $conn->query($vfs);
while($vfs2 = $vfs1->fetch_assoc() ){

$flightrowdate .="{
    title: 'BIO(".$vfs2['fefe'].")',
    url: 'myreport/my2-vfs.php?dateme=".$vfs2['vfs']."&&imy=".$_GET['imy']."',
    start: '".$vfs2['vfs']."'
    },";
}





//For medical
$medr = "SELECT count(certificate_medical_exam_date) as fefe,applicant_certificate.* 
, applicant.* 
, recruitment_agent.* 
FROM applicant_certificate


LEFT JOIN  	applicant
ON applicant_certificate.certificate_applicant =applicant.applicant_id

LEFT JOIN  	recruitment_agent
ON applicant.applicant_source =recruitment_agent.agent_id
where branch_type=".$_GET['imy']."

group by certificate_medical_exam_date ";
$medrresult = $conn->query($medr);
$medrowdate="";
while($medrow = $medrresult->fetch_assoc() ){

$flightrowdate .="{
    title: 'Medical (".$medrow['fefe'].")',
    url: 'myreport/my2-medical.php?dateme=".$medrow['certificate_medical_exam_date']."&&imy=".$_GET['imy']."',
    start: '".$medrow['certificate_medical_exam_date']."'
    },";
}




//Tesda Schedule
$tesdar = "SELECT count(certificate_tesda_date) as fefe,applicant_certificate.* 
, applicant.* 
, recruitment_agent.* 
FROM applicant_certificate


LEFT JOIN  	applicant
ON applicant_certificate.certificate_applicant =applicant.applicant_id

LEFT JOIN  	recruitment_agent
ON applicant.applicant_source =recruitment_agent.agent_id
where branch_type=".$_GET['imy']."

AND  certificate_tesda_date  NOT IN ('1970-01-01','0000-00-00','')
group by certificate_tesda_date ";
$tesdaresult = $conn->query($tesdar);
while($tesdatoday = $tesdaresult->fetch_assoc() ){

$flightrowdate .="{
    title: 'Tesda (".$tesdatoday['fefe'].")',
    url: 'myreport/my2-tesda.php?dateme=".$tesdatoday['certificate_tesda_date']."&&imy=".$_GET['imy']."',
    start: '".$tesdatoday['certificate_tesda_date']."'
    },";
}







//OWWA Release
$owwa = "SELECT 
count(certificate_applicant) as fefe,
applicant.applicant_id as adonis,applicant_certificate.* 
, applicant.* 
, recruitment_agent.* 
FROM applicant_certificate


LEFT JOIN  	applicant
ON applicant_certificate.certificate_applicant =applicant.applicant_id

LEFT JOIN  	recruitment_agent
ON applicant.applicant_source =recruitment_agent.agent_id
where branch_type=".$_GET['imy']."
and  certificate_owwa_from NOT IN ('1970-01-01','0000-00-00','') GROUP BY certificate_owwa_from";
$owwa1 = $conn->query($owwa);
while($owwa2 = $owwa1->fetch_assoc() ){

$flightrowdate .="{
    title: 'OWWA(".$owwa2['fefe'].")',
    url: 'myreport/my2-owwa.php?dateme=".$owwa2['certificate_owwa_from']."&&imy=".$_GET['imy']."',
    start: '".$owwa2['certificate_owwa_from']."'
    },";
}



//start swab 2ND
$swab = "SELECT 
count(swab_date) as fefe,
applicant.applicant_id as adonis,applicant_certificate.* 
, applicant.* 
, recruitment_agent.* 
FROM applicant_certificate


LEFT JOIN  	applicant
ON applicant_certificate.certificate_applicant =applicant.applicant_id

LEFT JOIN  	recruitment_agent
ON applicant.applicant_source =recruitment_agent.agent_id
WHERE branch_type=".$_GET['imy']." and  swab_date  NOT IN ('1970-01-01','0000-00-00','')
group by swab_date ";
$swab1= $conn->query($swab);
while($swab2 = $swab1->fetch_assoc() ){

$flightrowdate .="{
    title: 'SWAB(".$swab2['fefe'].")',
    url: 'myreport/my-swab.php?dateme=".$swab2['swab_date']."&&imy=".$_GET['imy']."',
    start: '".$swab2['swab_date']."'
    },";
}


//local flight 
$assest = "SELECT 
count(certificate_tesda_assest) as fefe,
applicant.applicant_id as adonis,applicant_certificate.* 
, applicant.* 
, recruitment_agent.* 
FROM applicant_certificate


LEFT JOIN  	applicant
ON applicant_certificate.certificate_applicant =applicant.applicant_id

LEFT JOIN  	recruitment_agent
ON applicant.applicant_source =recruitment_agent.agent_id
WHERE branch_type=".$_GET['imy']." and  certificate_tesda_assest NOT IN ('1970-01-01','0000-00-00','') GROUP BY certificate_tesda_assest";
$assest1 = $conn->query($assest);
while($assest2 = $assest1->fetch_assoc() ){

$flightrowdate .="{
    title: 'Tesda Assestment(".$assest2['fefe'].")',
    url: 'myreport/my2-assestment.php?dateme=".$assest2['certificate_tesda_assest']."&&imy=".$_GET['imy']."',
    start: '".$assest2['certificate_tesda_assest']."'
    },";
}




//start local flight 
$local = "SELECT 
count(localflight) as fefe,
applicant.applicant_id as adonis,applicant_certificate.* 
, applicant.* 
, recruitment_agent.* 
FROM applicant_certificate


LEFT JOIN  	applicant
ON applicant_certificate.certificate_applicant =applicant.applicant_id

LEFT JOIN  	recruitment_agent
ON applicant.applicant_source =recruitment_agent.agent_id
WHERE branch_type=".$_GET['imy']." and  localflight NOT IN ('1970-01-01','0000-00-00','') GROUP BY localflight";
$local1 = $conn->query($local);
while($local12 = $local1->fetch_assoc() ){

$flightrowdate .="{
    title: 'L Flight(".$local12['fefe'].")',
    url: 'myreport/my2-local.php?dateme=".$local12['localflight']."&&imy=".$_GET['imy']."',
    start: '".$local12['localflight']."'
    },";
}



//result SET
$flightrowdate = substr($flightrowdate, 0, -1);
 
 
?>

<!DOCTYPE html>
<html>
<head>
	<title>Scheduling System</title>
	<link href='calendar/packages/core/main.css' rel='stylesheet' />
<link href='calendar/packages/daygrid/main.css' rel='stylesheet' />
<script src='calendar/packages/core/main.js'></script>
<script src='calendar/packages/interaction/main.js'></script>
<script src='calendar/packages/daygrid/main.js'></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">

<script>

  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
      plugins: [ 'interaction', 'dayGrid' ],
     
      editable: false,
      eventLimit: true, // allow "more" link when too many events
     events: [<?php echo $flightrowdate ;?>] ,
	  

    });

    calendar.render();
  });

</script>
<style>

  body {
    margin: 40px 10px;
    padding: 0;
    font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
    font-size: 14px;
  }

  #calendar {
    max-width: 1150px;
    margin: 0 auto;
  }
  
  .fc-content{
	
	font-size:14px;	 
	text-align:center;
	color:black;
	font-weight:bold;
	
  }
  
a[href*="vfs"]{
  background-color: #87CEFA;
}

  
 a[href*="my2-flig"]{
  background-color: #98FB98;
}

 a[href*="my2-medical"]{
  background-color: #FFD700;
}

 a[href*="my2-oec"]{
  background-color: #FFEFD5;
}
a[href*="my2-owwa"]{
  background-color: #FFB6C1;
}

a[href*="my2-tesda"]{
  background-color: #B0E0E6;
}
a[href*="local"]{
  background-color: #FFE4E1;
}
a[href*="assest"]{
  background-color: #FF69B4;

}
a[href*="swab"]{
  background-color: #B0C4DE;

}


</style>
</head>
<body>

<div style="float:right;width:120px;border:1px solid black;padding:5px; background-color: #FFD700;TEXT-ALIGN:center"> MEDICAL</div>
<div style="float:right;width:120px;border:1px solid black;padding:5px; background-color: #B0E0E6;TEXT-ALIGN:center"> TESDA</div>
<div style="float:right;width:120px;border:1px solid black;padding:5px; background-color: #FFB6C1;TEXT-ALIGN:center"> OWWA</div>
<div style="float:right;width:120px;border:1px solid black;padding:5px; background-color: #87CEFA;TEXT-ALIGN:center"> VFS</div>
<div style="float:right;width:120px;border:1px solid black;padding:5px; background-color: #FFEFD5;TEXT-ALIGN:center"> OEC</div>
<div style="float:right;width:120px;border:1px solid black;padding:5px; background-color: #98FB98;TEXT-ALIGN:center"> FLIGHTS</div>
<div style="float:right;width:140px;border:1px solid black;padding:2px; background-color: #FFE4E1;TEXT-ALIGN:center"> LOCAL FLIGHTS</div>
<div style="float:right;width:160px;border:1px solid black;padding:2px; background-color: #FF69B4;TEXT-ALIGN:center"> TESDA ASSESTMENT</div>
<div style="float:right;width:140px;border:1px solid black;padding:2px; background-color: #B0C4DE;TEXT-ALIGN:center"> SWAB</div>

<div style="margin:0 auto;margin-top:-30px;text-align:center">



<a href="dashboard.php" style="color:red;font-size:16px ;text-decoration: underline;">Dashboard</a> 
</div>
<div style="clear:both;height:10px"></div>
<h2 style="font-size:22px;text-align:center">Schedule Management</h2>

<div style="clear:both;height:20px"></div>


<div style="clear:both;height:10px"></div>
<div style="clear:both;height:10px"></div>
<h4 style="text-align:center;FONT-SIZE:20PX"> <?php echo date('M-d-Y', strtotime($date1)); ?></p></h4>
  <div id='calendar'></div>
  
  
  
  
  
  
  
  
  
  
</body>
</html>