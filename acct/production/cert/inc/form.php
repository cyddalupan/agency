<?php
	include'../tracking.php';
	include'validation.php';
	include'db.php';
?>



<style type="text/css">
<!--
.ed{
border-style:solid;
border-width:thin;
border-color:#00CCFF;
padding:5px;
margin-bottom: 4px;
}
#button1{
text-align:center;
font-family:Arial, Helvetica, sans-serif;
border-style:solid;
border-width:thin;
border-color:#00CCFF;
padding:5px;
background-color:#00CCFF;
height: 34px;
}
-->
</style>
<!--sa input that accept number only-->
<SCRIPT language=Javascript>
      <!--
      function isNumberKey(evt)
      {
         var charCode = (evt.which) ? evt.which : event.keyCode
         if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;

}
      //-->
   </SCRIPT>

<style>
	#formclass input{ width:300px;padding:3px;}
	#formclass select{ width:310px;padding:3px;}
</style>

<script language="javascript">
function changeTextSimple(idElement){
document.getElementById.('element'+idElement).innerHTML ='<input type="text" name="bday" value="hernan" />';
}</script>
<div style="height:520px;" id="formclass">
<form action="patients.php" method="post" enctype="multipart/form-data" name="addfood" onsubmit="return validateForm()">

<input type="text" name="p_id" value="<?=date('mdy')?>">
<h3>New Patient Information (m/d/y) - <?=date('m/d/y')?></h3>
<hr>
<table style="width:400px;float:left;">
<tr>
<td>Date Exam</td>
<td><input type="text"  name="dateadded" value="<?=$date1?>"></td>
</tr>

<tr>
<td>Medical Type</td>
<td>
<select name="pack" required>
	<option value="">-----------</option>
	<option>FULL MEDICAL</option>
	<option>PT ONLY</option>
	<option>X RAY ONLY</option>	
	<option>X RAY HBSAG</option>
	<option>VACCINE</option>	
	<option>DRUG TEST</option>
	<option>HIV</option>	
</select>
</td>
</tr>


<tr>
<td>OFW/LOCAL</td>
<td>
<select name="ofw_local">
	<option>OFW</option>
	<option>LOCAL</option>	
</select>
</td>
</tr>

<tr>
<td>Last Name</td>
<td><input type="text" name="lname" /></td>
</tr>
<tr>
<td>First Name</td>
<td><input type="text" name="fname"  /></td>
</tr>
<tr>
<td>Middle Name</td>
<td><input type="text" name="mname" /></td>
</tr>
<tr>
<td>Sex</td>
<td>
<select name="sex">
	<option>MALE</option>
	<option>FEMALE</option>
	
</select>
</td>
</tr>
<tr>
<td>Marital Staus</td>
<td>
<select name="marital">
	<option>SINGLE</option>
	<option>MARRIED</option>
	<option>SEPARATED</option>
	<option>WIDOWED</option>
	
</select>
</td>
<tr>

<tr>
<td>Type</td>
<td>
<select name="status_app">
	<option>LANDBASED</option>
	<option>SEABASED</option>
</select>
</td>
<tr>

<td>Citizenship</td>
<td><input type="text" name="citizenship" value="FILIPINO" /></td>
</tr>
<tr>
<td>Religion</td>
<td>
<input type="text" name="religion" value="CATHOLIC" required/>
</td>
</tr>

<tr>
<td>Passport #</td>
<td><input type="text" name="ppt" /></td>
</tr>

<tr>
<td>SEAMAN'S BOOK NO.</td>
<td><input type="text" name="seaman_book" /></td>
</tr>

<tr>
<td>Address</td>
<td>
<textarea name="address" style="width:300px; height:50px" ></textarea>
</td>

</tr>
<tr>
<td>Birthday</td>
<td><input type="hidden" name="bday" id="bday" />
        <select name="month" style="width:80px" onchange="changeTextSimple(bday)" required>
        <option value="">------</option>
        <option value="1">JANUARY</option> 
		<option value="2">FEBRUARY</option> 
		<option value="3">MARCH</option>
		<option value="4">APRIL</option> 
		<option value="5">MAY</option>
         <option value="6">JUNE</option> 
		 <option value="7">JULY</option>
		 <option value="8">AUGUST</option>
		 <option value="9">SEPTEMBER</option> 
		 <option value="10">OCTOBER</option> 
		 <option value="11">NOVEMBER</option> 
		 <option value="12">DECEMBER</option>
        </select>
           <select name="day" style="width:80px" required>
        <option value=""> --day--</option>
        <option>1</option> <option>2</option> <option>3</option> <option>4</option> <option>5</option>
         <option>6</option> <option>7</option> <option>8</option> <option>9</optione> <option>10</option> <option>11</option> <option>12</option>
         <option>13</option> <option>14</option> <option>15</option> <option>16</option> <option>17</option> <option>18</option> <option>19</option>
         <option>20</option> <option>21</option> <option>22</option> <option>23</option> <option>24</option> <option>25</option> <option>26</option>
         <option>27</option> <option>28</option> <option>29</option> <option>30</option> <option>31</option> 
        </select>
           <select name="year" style="width:80px" required>
        <option value="">--year--</option>
    <?php 
		$year = date("Y"); // get the year part of the current date
		
		$iii=0;
		while($iii!=80){
		$nextYear = $year - $iii;
		
	?>
        <option><?=$nextYear?></option>
        <?php $iii++; }?>
       </select> 
</td>
</tr>
<input type="hidden" name="age" />

<tr>



<tr>
<td>Agency name</td>
<?php
$usertype = mysql_query("SELECT * FROM agency ORDER BY name asc");
?>

<td><select name="agency">
<OPTION value="">---------------------------------</option>
<?php
while($rows=mysql_fetch_array($usertype))
{
echo'<option value="'.$rows["name"].'">'.$rows["name"].'</option>';
}
?>
</select>
</td>
</tr>
<tr>
<td>Country Destination</td>
<td><input type="text" name="country" /></td>
</tr>

<tr>
<td>Jobdescription</td>
<td><input type="text" name="jobdesc" /></td>
</tr>
<tr>
<td>Type</td>
<td>
<select name="type_work">
	<option>OTHERS</option>
	<option>DECK</option>
	<option>ENGINE</option>
	<option>STEWARD</option>

	
</select>
</td>
</tr>


<tr>
<td colspan="1"></td>
<td><input type="submit" id="button" name="patiend_add" value="ADD PATIENT" style="padding:4px; font-size:16px;width:310px;"/></td>
</tr>
</table>

</form>
</div>
