<?php 
include 'calendar/db.php';
//vfs Release
$vfs = "SELECT 
count(hid) as fefe,hearings.* FROM
hearings

and   hdate NOT IN ('1970-01-01','0000-00-00','') GROUP BY vfs";
$vfs1 = $conn->query($vfs);
while($vfs2 = $vfs1->fetch_assoc() ){

$flightrowdate .="{
    title: 'HEARINGS (".$vfs2['fefe'].")',
    url: 'myreport/my-vfs1.php?dateme=".$vfs2['hid']."&&user=".$_GET['user']."',
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

  
 a[href*="my-flig"]{
  background-color: #98FB98;
}

 a[href*="my-medical"]{
  background-color: #FFD700;
}

 a[href*="my-oec"]{
  background-color: #FFEFD5;
}
a[href*="my-owwa"]{
  background-color: #FFB6C1;
}

a[href*="my-tesda"]{
  background-color: #B0E0E6;
}


</style>
</head>
<body>

<div style="clear:both;height:20px"></div>
<h2 style="font-size:22px;text-align:center;position:absolute;margin-left:300px"><?php echo date('M-d-Y', strtotime($date1)); ?></h2>
<div style="clear:both;height:20px"></div>


<div style="clear:both;height:10px"></div>
<div style="clear:both;height:10px"></div>
 <div id='calendar' style="position:absolute;width:1000px;margin-left:300px"></div>
  
  
  
  
  
  
  
  
  
  
</body>
</html>