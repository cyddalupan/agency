<?php 

	     $dbhost = 'localhost';
         $dbuser = 'iwebphil_gensan';
         $dbpass = '.adonis132';
         $dbname = 'iwebphil_everlast';
         $conn = mysqli_connect($dbhost, $dbuser, $dbpass,$dbname);
   
         if(! $conn ){
            die('Could not connect: ' . mysqli_error());
         }
?>