<?php
$con=mysql_connect('localhost','abbaphil_jinhel','67PtE01S4KL4hKIqUQyoP2aKhDwud7BV')or die ('cannot connect');
mysql_select_db('abbaphil_jinhel',$con);

if($_GET['q']!='8888888'){ $agent="where applicant_source=".$_GET['q'].""; }
if($_GET['q']=='8888888'){ $agent=" "; }


$applicant1 = mysql_query("SELECT * FROM applicant $agent");
echo'<option value="">Select Applicant</option>';
echo'<option value="0">Other</option>';
while($applicantrow=mysql_fetch_array($applicant1))
{
echo'<option value="'.$applicantrow["applicant_id"].'">'.$applicantrow["applicant_last"].'   '.$applicantrow["applicant_first"].'</option>';

}


?>