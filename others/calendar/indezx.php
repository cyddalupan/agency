<?php 
  include 'db.php';

  $sql = "SELECT * FROM calendar";
  $result = $conn->query($sql);
$datefield="";
  while($row = $result->fetch_assoc() ){

    $datefield .="{
          title: '".$row['sub_title']."',
          url: '".$row['link']."',
          start: '".$row['sub_date']."'
        },";
  }

  $datefield = substr($datefield, 0, -1);
  //echo  $datefield;
?>

<!DOCTYPE html>
<html>
<head>
	<title>Scheduling System</title>
	<link href='packages/core/main.css' rel='stylesheet' />
<link href='packages/daygrid/main.css' rel='stylesheet' />
<script src='packages/core/main.js'></script>
<script src='packages/interaction/main.js'></script>
<script src='packages/daygrid/main.js'></script>
<script>

  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
      plugins: [ 'interaction', 'dayGrid' ],
     
      editable: false,
      eventLimit: true, // allow "more" link when too many events
      events: [<?php echo $datefield ?>]
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
    max-width: 900px;
    margin: 0 auto;
  }

</style>
</head>
<body>
  <div id='calendar'></div>
</body>
</html>