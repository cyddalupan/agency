<?php
include 'db.php';
if(isset($_POST["Import"])){
		

		echo $filename=$_FILES["file"]["tmp_name"];
		

		 if($_FILES["file"]["size"] > 0)
		 {

		  	$file = fopen($filename, "r");
	         while (($emapData = fgetcsv($file, 50000, ",")) !== FALSE)
	         {
	$mydate=date('Y-m-d', strtotime($emapData[0])) ;   
//It wiil insert a row to our  email_address table from our csv file`
$sql = "INSERT into  email_address6 (`agent_id`, `date1`,`currency`,`amount1`, `account`, `particular`, `remarks2`, `liqu`, `amount`)
values('$emapData[3]','$emapData[5]','$emapData[5]','$emapData[7]','$emapData[9]','$emapData[10]','$emapData[12]','$emapData[13]','$emapData[14]')";



//we are using mysql_query function. it returns a resource on true else False on error
$result = mysqli_query( $conn, $sql );
if(! $result )
{
echo "<script type=\"text/javascript\">
alert(\"Invalid File:Please Upload CSV File.\");
window.location = \"paymentagent.php\"
</script>";

}

	         }
	         fclose($file);
	         //throws a message if data successfully imported to mysql database from excel file
	         echo "<script type=\"text/javascript\">
						alert(\"CSV File has been successfully Imported.\");
						window.location = \"paymentagent.php\"
					</script>";
	        
			 

			 //close of connection
			mysqli_close($conn); 
				
		 	
			
		 }
	}	 
?>		 