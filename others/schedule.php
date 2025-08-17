<?php 
  include 'calendar/db.php';
//for flights
$flight = "SELECT count(flight_date) as fefe, applicant_requirement.*  FROM applicant_requirement group by flight_date ";
$flightresult = $conn->query($flight);
$flightrowdate="";
while($flightrow = $flightresult->fetch_assoc() ){

            $flightrowdate .="{
            title: 'For Flights (".$flightrow['fefe'].")',
            url: 'flight.php?flightdate=".$flightrow['flight_date']."',
            start: '".$flightrow['flight_date']."'
            },";
}


  
//OEC RELEASE
$oec = "SELECT count(requirement_oec_release_date) as fefe, applicant_requirement.*  FROM applicant_requirement group by requirement_oec_release_date ";
$oecresult = $conn->query($oec);
$oecrowdate="";
while($oecrow = $oecresult->fetch_assoc() ){

$flightrowdate .="{
    title: 'OEC Release (".$oecrow['fefe'].")',
    url: 'oec.php?oecdate=".$oecrow['requirement_oec_release_date']."',
    start: '".$oecrow['requirement_oec_release_date']."'
    },";
}

//For medical
$medr = "SELECT count(certificate_medical_exam_date) as fefe, applicant_certificate.* FROM applicant_certificate group by certificate_medical_exam_date ";
$medrresult = $conn->query($medr);
$medrowdate="";
while($medrow = $medrresult->fetch_assoc() ){

$flightrowdate .="{
    title: 'For Medical (".$medrow['fefe'].")',
    url: 'medical.php?meddate=".$medrow['certificate_medical_exam_date']."',
    start: '".$medrow['certificate_medical_exam_date']."'
    },";
}

//Tesda Schedule
$tesdar = "SELECT count(certificate_tesda_date) as fefe, applicant_certificate.* FROM applicant_certificate group by certificate_tesda_date ";
$tesdaresult = $conn->query($tesdar);
while($tesdatoday = $tesdaresult->fetch_assoc() ){

$flightrowdate .="{
    title: 'For Tesda (".$tesdatoday['fefe'].")',
    url: 'medical.php?meddate=".$tesdatoday['certificate_tesda_date']."',
    start: '".$tesdatoday['certificate_tesda_date']."'
    },";
}


//Tesda Release
$tesdarelease = "SELECT count(certificate_tesda_release) as fefe, applicant_certificate.* FROM applicant_certificate group by certificate_tesda_release ";
$tesdaresultrelease = $conn->query($tesdarelease);
while($tesdarelease = $tesdaresultrelease->fetch_assoc() ){

$flightrowdate .="{
    title: 'Tesda  Release(".$tesdarelease['fefe'].")',
    url: 'medical.php?meddate=".$tesdarelease['certificate_tesda_release']."',
    start: '".$tesdarelease['certificate_tesda_release']."'
    },";
}



//result SET
$flightrowdate = substr($flightrowdate, 0, -1);
 
 
?>



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


<h2 style="font-size:28px;text-align:center;font-weight:bold;color:black">Schedule Management</h2>
<div style="clear:both;height:20px"></div>


<div style="clear:both;height:10px"></div>
<div style="clear:both;height:10px"></div>
  <div id='calendar'></div>
  
  
  
  
  
  
  
  
  
