<?php
$con=mysql_connect('localhost','property_marin','67PtE01S4KL4hKIqUQyoP2aKhDwud7BV')or die ('cannot connect');
mysql_select_db('abbaphil_jinhel',$con);



$applicant11111111 = mysql_query("SELECT * FROM applicant where applicant_employer = ".$_GET['q']."  ");
echo'<option value="">Select Applicant</option>';			
while($applicantrow23=mysql_fetch_array($applicant11111111))
{
echo'<option value="'.$applicantrow23["applicant_id"].'">'.$applicantrow23["applicant_last"].'   '.$applicantrow23["applicant_first"].'</option>';

}


?>