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
//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`, `chargeto`)
//values('PPT','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[13]','$emapData[14]',3,'PHP',1,'Agent')";



//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`, `chargeto`)
//values('FTW','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[15]','$emapData[16]',3,'PHP',1,'Agent')";


//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`, `chargeto`)
//values('FTW','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[17]','$emapData[18]',3,'PHP',1,'Agent')";


//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`, `chargeto`)
//values('CS','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[19]','$emapData[20]',3,'PHP',1,'Agent')";

//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`, `chargeto`)
//values('DEPLOYED','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[22]','$emapData[23]',3,'PHP',1,'Agent')";


//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`, `chargeto`)
//values('CASH ADVANCE AGENT','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[24]','$emapData[25]',3,'PHP',1,'Agent')";

$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`, `chargeto`)
values('CASH ADVANCE AGENT','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[26]','$emapData[27]',3,'PHP',1,'Agent')";



//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`)
//values('PREMED','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[28]','$emapData[29]',3,'PHP',2)";


//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`)
//values('FULL MED','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[30]','$emapData[31]',3,'PHP',2)";



//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`)
//values('ECG','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[33]','$emapData[34]',3,'PHP',2)";

//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`)
//values('VACCINE ','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[35]','$emapData[36]',3,'PHP',2)";

//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`)
//values('REMEDICAL ','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[41]','$emapData[42]',3,'PHP',2)";



//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`)
//values('PT BEFORE DEPLOYMENT ','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[59]','$emapData[60]',3,'PHP',2)";


//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`)
//values('TESDA ASSESMENT','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[70]','$emapData[71]',3,'PHP',2)";


//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`)
//values('BIOMETRICS','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[72]','$emapData[73]',3,'PHP',2)";

//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`)
//values('ENJAZ','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[76]','2024-06-21',3,'USD',2)";


//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`)
//values('INSURANCE','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[77]','$emapData[78]',3,'USD',2)";


//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`)
//values('OWWA PAYMENT','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[83]','$emapData[84]',3,'PHP',2)";

//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`)
//values('PDOS','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[85]','$emapData[86]',3,'PHP',2)";

//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`)
//values('OEC','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[93]','$emapData[94]',3,'PHP',2)";

//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`)
//values('AIRFARE TICKET','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[95]','$emapData[96]',3,'USD',2)";

//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`)
//values('ADTL EXPENSE','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[101]','$emapData[102]',3,'PHP',2)";


//$sql = "INSERT into  email_address (`account`, `description`,`method_remarks`, `country`, `amount`, `date_ad`, `request_payment`, `currency`, `email_status`)
//values('SPECIAL REMED','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[107]','$emapData[108]',3,'PHP',2)";


//we are using mysql_query function. it returns a resource on true else False on error
$result = mysqli_query( $conn, $sql );
if(! $result )
{
echo "<script type=\"text/javascript\">
alert(\"Invalid File:Please Upload CSV File.\");
window.location = \"agent.php\"
</script>";

}

	         }
	         fclose($file);
	         //throws a message if data successfully imported to mysql database from excel file
	         echo "<script type=\"text/javascript\">
						alert(\"CSV File has been successfully Imported.\");
						window.location = \"agent.php\"
					</script>";
	        
			 

			 //close of connection
			mysqli_close($conn); 
				
		 	
			
		 }
	}	 
?>		 