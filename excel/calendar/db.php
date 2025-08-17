<?php 

	     $dbhost = 'localhost';
         $dbuser = 'property_888web';
         $dbpass = 'Eclipse888!';
         $dbname = 'property_yl2';
         $conn = mysqli_connect($dbhost, $dbuser, $dbpass,$dbname);
   
         if(! $conn ){
            die('Could not connect: ' . mysqli_error());
         }
?>