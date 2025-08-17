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
$sql = "INSERT into  email_address (`account`, `description`,`date_ad`, `amount`, `request_payment`, `currency`)
values('$emapData[2]','','$emapData[3]','$emapData[4]',3,'PHP')";



//we are using mysql_query function. it returns a resource on true else False on error
$result = mysqli_query( $conn, $sql );
if(! $result )
{
echo "<script type=\"text/javascript\">
alert(\"Invalid File:Please Upload CSV File.\");
window.location = \"index.php\"
</script>";

}

	         }
	         fclose($file);
	         //throws a message if data successfully imported to mysql database from excel file
	         echo "<script type=\"text/javascript\">
						alert(\"CSV File has been successfully Imported.\");
						window.location = \"index.php\"
					</script>";
	        
			 

			 //close of connection
			mysqli_close($conn); 
				
		 	
			
		 }
	}	 
?>		 