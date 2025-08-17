<?php 
include 'calendar/db.php';
//for flights
echo $employerr;
$flight = "SELECT count(flight_date) as fefe, applicant_requirement.* ,applicant_certificate.*,applicant.* FROM
applicant

LEFT JOIN  	applicant_certificate
ON applicant.applicant_id = applicant_certificate.certificate_applicant


LEFT JOIN  	applicant_requirement
ON applicant.applicant_id =applicant_requirement.requirement_applicant


where applicant_employer IN(".$employerr.")
group by flight_date
";
$flightresult = $conn->query($flight);
$flightrowdate="";
while($flightrow = $flightresult->fetch_assoc() ){

            $flightrowdate .="{
            title: 'Flights (".$flightrow['fefe'].")',
            url: 'myreport/my-flight1.php?flightdate=".$flightrow['flight_date']."&&user=".$_SESSION['admin']['user']['user_id']."',
            start: '".$flightrow['flight_date']."'
            },";
}


  
//OEC RELEASE
$oec = "SELECT count(requirement_oec_release_date) as fefe,
applicant_requirement.* ,applicant_certificate.*,applicant.* FROM
applicant

LEFT JOIN  	applicant_certificate
ON applicant.applicant_id = applicant_certificate.certificate_applicant


LEFT JOIN  	applicant_requirement
ON applicant.applicant_id =applicant_requirement.requirement_applicant


where applicant_employer IN(".$employerr.")
group by requirement_oec_release_date ";
$oecresult = $conn->query($oec);
$oecrowdate="";
while($oecrow = $oecresult->fetch_assoc() ){

$flightrowdate .="{
    title: 'OEC RLEASE (".$oecrow['fefe'].")',
    url: 'myreport/my-oec1.php?dateme=".$oecrow['requirement_oec_release_date']."&&user=".$_SESSION['admin']['user']['user_id']."',
    start: '".$oecrow['requirement_oec_release_date']."'
    },";
}





//OWWA Release
$owwa = "SELECT 
count(certificate_applicant) as fefe,
applicant_requirement.* ,applicant_certificate.*,applicant.* FROM
applicant

LEFT JOIN  	applicant_certificate
ON applicant.applicant_id = applicant_certificate.certificate_applicant


LEFT JOIN  	applicant_requirement
ON applicant.applicant_id =applicant_requirement.requirement_applicant

where applicant_employer IN(".$employerr.")

AND  certificate_owwa_from NOT IN ('1970-01-01','0000-00-00','') GROUP BY applicant_id";
$owwa1 = $conn->query($owwa);
while($owwa2 = $owwa1->fetch_assoc() ){

$flightrowdate .="{
    title: 'OWWA Schedule(".$owwa2['fefe'].")',
    url: 'myreport/my-owwa1.php?dateme=".$owwa2['certificate_owwa_from']."&&user=".$_SESSION['admin']['user']['user_id']."',
    start: '".$owwa2['certificate_owwa_from']."'
    },";
}



//Tesda Schedule
$tesdar = "SELECT count(certificate_tesda_date) as fefe, 
applicant_requirement.* ,applicant_certificate.*,applicant.* FROM
applicant

LEFT JOIN  	applicant_certificate
ON applicant.applicant_id = applicant_certificate.certificate_applicant


LEFT JOIN  	applicant_requirement
ON applicant.applicant_id =applicant_requirement.requirement_applicant

where applicant_employer IN(".$employerr.")

AND certificate_tesda_date  NOT IN ('1970-01-01','0000-00-00','')
GROUP BY certificate_tesda_date";
$tesdaresult = $conn->query($tesdar);
while($tesdatoday = $tesdaresult->fetch_assoc() ){

$flightrowdate .="{
    title: 'Tesda (".$tesdatoday['fefe'].")',
    url: 'myreport/my-tesda1.php?dateme=".$tesdatoday['certificate_tesda_date']."&&user=".$_SESSION['admin']['user']['user_id']."',
    start: '".$tesdatoday['certificate_tesda_date']."'
    },";
}




//vfs Release
$vfs = "SELECT 
count(vfs) as fefe,

applicant_requirement.* ,applicant_certificate.*,applicant.* FROM
applicant

LEFT JOIN  	applicant_certificate
ON applicant.applicant_id = applicant_certificate.certificate_applicant


LEFT JOIN  	applicant_requirement
ON applicant.applicant_id =applicant_requirement.requirement_applicant

where applicant_employer IN(".$employerr.")

and   vfs NOT IN ('1970-01-01','0000-00-00','') GROUP BY vfs";
$vfs1 = $conn->query($vfs);
while($vfs2 = $vfs1->fetch_assoc() ){

$flightrowdate .="{
    title: 'BIOMETRICS (".$vfs2['fefe'].")',
    url: 'myreport/my-vfs1.php?dateme=".$vfs2['vfs']."&&user=".$_SESSION['admin']['user']['user_id']."',
    start: '".$vfs2['vfs']."'
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
    max-width: 950px;
    margin: 0 auto;
  }

</style>
</head>
<body>

<body>
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