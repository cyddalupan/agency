<?php
include 'db.php';
if(isset($_POST["Import"])){
		

		echo $filename=$_FILES["file"]["tmp_name"];
		

		 if($_FILES["file"]["size"] > 0)
		 {

		  	$file = fopen($filename, "r");
	         while (($emapData = fgetcsv($file, 50000, ",")) !== FALSE)
	         {
	   
//It wiil insert a row to our  email_address table from our csv file`
$sql = "INSERT into  applicant (`applicant_first`, `applicant_middle`, `applicant_last`, `applicant_contacts`, `fb_link`, `sub_employer`, `applicant_birthdate`, `applicant_age`, `applicant_address`, `applicant_remarks`, `applicant_remarks1`, `applicant_remarks2`, `applicant_remarks3` ,`singil`,`applicant_id`)
values('$emapData[0]','$emapData[1]','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[5]','$emapData[6]','$emapData[7]','$emapData[8]','$emapData[11]','$emapData[12]','$emapData[14]','$emapData[10]',1,'$emapData[15]')";



//$sql = "INSERT into  applicant (`applicant_first`,`applicant_remarks1`,`applicant_remarks_3`,`checkmet`)
//values('$emapData[1]','$emapData[2]','$emapData[3]',1)";

//$sql = "INSERT into  applicants_others (`applicant_id`)
//values('$emapData[15]')";

//$sql = "INSERT into   applicant_certificate (`certificate_applicant`)
//values('$emapData[15]')";


//$sql = "INSERT into   applicant_education (`education_applicant`)
//values('$emapData[15]')";

//$sql = "INSERT into   applicant_passport (`passport_applicant`)
//values('$emapData[15]')";

//$sql = "INSERT into    applicant_requirement (`requirement_applicant`,`flight_date`)
//values('$emapData[15]','$emapData[9]')";

//$sql = "INSERT into   applicant_skills_cyds (`applicant_id`)
//values('$emapData[15]')";

//$sql = "INSERT into    deployed (`deployed_date`,`deployed_status`,`deployed_country`)
//values('$emapData[9]','$emapData[29]','$emapData[30]')";



//we are using mysql_query function. it returns a resource on true else False on error
$result = mysqli_query( $conn, $sql );
if(! $result )
{
echo "<script type=\"text/javascript\">
alert(\"Invalid File:Please Upload CSV File.\");
window.location = \"applicant.php\"
</script>";

}

	         }
	         fclose($file);
	         //throws a message if data successfully imported to mysql database from excel file
	         echo "<script type=\"text/javascript\">
						alert(\"CSV File has been successfully Imported.\");
						window.location = \"applicant.php\"
					</script>";
	        
			 

			 //close of connection
			mysqli_close($conn); 
				
		 	
			
		 }
	}	 
?>		 