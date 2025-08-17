<?php 
include 'calendar/db.php';
    //  echo 'Connected successfully';
      //mysqli_close($conn);
$display = "";
         if (isset($_POST['submit'])) {
                

                $sql = "INSERT INTO `calendar`(`sub_title`, `description`, `sub_date`, `status`,link) 
                        VALUES ('".$_POST['title']."','".$_POST['description']."','".$_POST['daterecord']."',1,'".$_POST['link']."')";
                if ($conn->query($sql) === TRUE) {
                       $display = '<div class="alert alert-success" role="alert">New record created successfully</div>';
                } else {
                     $display = '<div class="alert alert-danger" role="alert">Error: ' . $sql . '</div>' . $conn->error;
                }

                $conn->close();
         }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Calendar</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
</head>
<body>


		<div class="row">
		
<div style="margin:0 auto;margin-top:0px;text-align:center">
<a href="index.php" style="color:red;font-size:16px ;text-decoration: underline;">My Schedule</a> | 
<a href="request.php" style="color:blue;font-size:16px ;text-decoration: none;">Payment Request</a> | 
<a href="b_account.php"  style="color:blue;font-size:16px; text-decoration: none;">Summary Reports</a>
</div>
<div style="clear:both;height:10px"></div>


		
		<div class="container">
			<h2 style="font-size:22px;text-align:center">Add Schedule</h2>
		<div class="col-md-4"> 
		<?php 
		echo  $display ;
		?>

		<form method="post">
		<div class="form-group">

		Date :<br><input type='date' name="daterecord" class="form-control" required="" /><br>
		Title :<br> <input type="text" name="title" required=""></textarea><br>
		Link :<br> <input type="text" name="link" ></textarea><br>
		Description :<br> <textarea name="description" style="width: 340px" required=""></textarea><br>
		</div>
		<input type="submit" name="submit" value="Submit">
		</form>
		</div>  

		</div>
		</div>
   

    <script type="text/javascript">
            $(function () {
                $('#datetimepicker1').datetimepicker();
            });
        </script>


<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

</body>
</html>

