<?php
$con=mysql_connect('localhost','abbaphil_jinhel','67PtE01S4KL4hKIqUQyoP2aKhDwud7BV')or die ('cannot connect');
mysql_select_db('abbaphil_jinhel',$con);



$showamt = mysql_query("SELECT * FROM set_ where account = '".$_GET['q']."'  ");
			
while($showamt1=mysql_fetch_array($showamt))
{
echo'<option value="'.$showamt1["a_amount"].'">'.$showamt1["a_amount"].'</option>';

}


?>