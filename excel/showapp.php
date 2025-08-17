<?php
$con=mysql_connect('localhost','property_888web','Eclipse888!')or die ('cannot connect');
mysql_select_db('property_yl2',$con);



$applicant1 = mysql_query("SELECT * FROM subject_name where wel_id = ".$_GET['q']."  ");
echo'<option value="">Select Remarks</option>';
echo'<option value="0">Other</option>';
while($applicantrow=mysql_fetch_array($applicant1))
{
echo'<option value="'.$applicantrow["well_id"].'">'.$applicantrow["well_name"].'</option>';

}


?>