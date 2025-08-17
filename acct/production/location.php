<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"/>
</head>
<body>
<style> 
body{
background:lightgray;	
}
.box{
margin:20px;
padding:40px;
width:80%;	
background:white;
min-height:300px;
}
select,input{
padding:12px;
width:100%;
font-size:25px;
border:1px solid gray;	
}
table{
width:100%;
}
table td{
padding:15px;
font-size:18px;
}

</style>

<?php
$con=mysql_connect('localhost','iwebphil_chatbot','~(OB.W@uAiCe')or die ('cannot connect');
mysql_select_db('iwebphil_chatbot_test',$con);
?>


<div class="box">

<?php
//echo $_POST['fb_country'];
if ($_POST['fb_country']!='Philippines' || $_POST['fb_country']==''){

?>




<script>
function showstate(str) {

var xhttp;    
if (str == "") {
document.getElementById("showstate").innerHTML = "";
return;
}
xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
if (this.readyState == 4 && this.status == 200) {
document.getElementById("statelist").innerHTML = this.responseText;
}
};
xhttp.open("GET", "showstate.php?q="+str, true);
xhttp.send();
}


function showcity(str) {

var xhttp;    
if (str == "") {
document.getElementById("showcity").innerHTML = "";
return;
}
xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
if (this.readyState == 4 && this.status == 200) {
document.getElementById("citylistp").innerHTML = this.responseText;
}
};
xhttp.open("GET", "showcity.php?q="+str, true);
xhttp.send();
}


</script>

<?php
//echo $_POST['fb_country'];
$country1=mysql_query("SELECT * FROM countries 
where id=".$_POST['fb_country']." ");
$country = mysql_fetch_array($country1);
?>


<form id="form1" name="form1" method="post" action="location-nation.php?idc=<?=$_GET['idc']?>" >
<p><input type='hidden' value='<?=$_GET['idc']?>' name='idmea'/> </p>
<table>

<tr>
<td>Country: </td>	
<td>
<select class="fb_region" name="fb_country" onchange="showstate(this.value)" required>

<option value="<?=$country['id']?>"><?=$country['name']?></option>
<?php
$countries1 = mysql_query("SELECT * FROM  countries ORDER BY id asc");
while($countries=mysql_fetch_array($countries1))
{
echo'<option value="'.$countries["id"].'">'.$countries["name"].'</small></option>';

}	

?>
</select>
</td>
</tr>


<tr>
<td>State: </td>	
<td>
<select class="statelist" name="fb_state"  id="statelist"  onchange="showcity(this.value)" required>

<option value="">Select State</option>
<?PHP

$state11 = mysql_query("SELECT * FROM states where country_id = ".$_POST['fb_country']."  ORDER BY name asc ");
echo'<option value="">Select City/Municipal</option>';			
while($state2=mysql_fetch_array($state11))
{
echo'<option value="'.$state2["id"].'">'.$state2["name"].'</option>';

}

?>
</select>
</td>
</tr>




<tr>
<td>City: </td>	
<td>
<select class="cities"   name="fb_city" id="citylistp" onchange = "showbrgy(this.value)" required>
<option value="">Select City</option>			
</select>
</td> 
</tr>


<tr>
<td>ZIPCODE / POSTCODE: </td>	
<td>
<input type="text" name="fb_zipcode">
<input type="hidden" name="fb_brgy">
<small style="color:red;font-size:18px">If you know your ZIPCODE / POSTCODE please indicate.</small>
</td> 
</tr>





<tr >
<td></td>	
<td colspan="1"><input type="submit" name="nation" class="button" id="button1" value="Next Step" 
style="width:100%;font-size:65px;padding:10px;background:lightblue;color:white;float:right"/> </td>
<tr>



</table>			
</form>

<?php
}
?>










<?php


if ($_POST['fb_country']=='Philippines'){
?>


<script>
function showcity(str) {

var xhttp;    
if (str == "") {
document.getElementById("citylist").innerHTML = "";
return;
}
xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
if (this.readyState == 4 && this.status == 200) {
document.getElementById("citylist").innerHTML = this.responseText;
}
};
xhttp.open("GET", "getcity.php?q="+str, true);
xhttp.send();
}

function showbrgy(str) {

var xhttp;    
if (str == "") {
document.getElementById("brgylist").innerHTML = "";
return;
}
xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
if (this.readyState == 4 && this.status == 200) {
document.getElementById("brgylist").innerHTML = this.responseText;
}
};
xhttp.open("GET", "getbrgy.php?q="+str, true);
xhttp.send();
}


function showprovince(str) {

var xhttp;    
if (str == "") {
document.getElementById("provincelist").innerHTML = "";
return;
}
xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
if (this.readyState == 4 && this.status == 200) {
document.getElementById("provincelist").innerHTML = this.responseText;
}
};
xhttp.open("GET", "getprovince.php?q="+str, true);
xhttp.send();
}
</script>


<form id="form1" name="form1" method="post" action="location-check.php?idc=<?=$_GET['idc']?>" >
<h3>Philippines</h3>
<p><input type='hidden' value='<?=$_GET['idc']?>' name='idmea'/> </p>
<table>



<input type="hidden" name="fb_country" value="PHILIPPINES">
<tr>
<td>Region: </td>	
<td>
<select class="fb_region" name="fb_region" onchange="showprovince(this.value)" required>

<option value="">Select Region</option>
<?php
$region1 = mysql_query("SELECT * FROM  refregion ORDER BY id asc");
while($region=mysql_fetch_array($region1))
{
echo'<option value="'.$region["regCode"].'">'.$region["regDesc"].'</small></option>';

}	

?>
</select>
</td>
</tr>



<tr>
<td>City/Province: </td>	
<td>
<select class="states"  id="provincelist" name="fb_state" onchange="showcity(this.value)" required>


<?php
//$state1 = mysql_query("SELECT provDesc,psgcCode,provCode FROM refprovince ORDER BY provDesc asc");
//while($state=mysql_fetch_array($state1))
//{
//echo'<option value="'.$state["provCode"].'">'.$state["provDesc"].'</small></option>';

//}	

?>
</select>
</td>
</tr>

<tr>
<td>City/Municipal: </td>	
<td>
<select class="cities"   name="fb_city" id="citylist" onchange = "showbrgy(this.value)" required>
<option value="">Select City</option>
<?php
/*	$city1 = mysql_query("SELECT citymunDesc,citymunCode,psgcCode FROM refcitymun ORDER BY citymunDesc asc ");
while($city=mysql_fetch_array($city1))
{
echo'<option value="'.$city["citymunCode"].'">'.$city["citymunDesc"].'-'.$city["citymunCode"].'</small></option>';

}
*/
?>			
</select>
</td> 
</tr>


<tr>
<td>ZIPCODE / POSTCODE: </td>	
<td>
<input type="text" name="fb_zipcode">
</td> 
</tr>



<tr>
<td>Baragay Name: </td>	
<td>
<select name="fb_brgy" id="brgylist">
<OPTION value="">-Select Brgy-</option>
<?php

//$brgysub = mysql_query("SELECT brgyDesc,citymunCode FROM refbrgy order by brgyDesc asc limit 100");
//while($brgy=mysql_fetch_array($brgysub))
//{
//echo'<option value="'.$brgy["brgyDesc"].'"><small>'.$brgy["brgyDesc"].'</small></option>';
//}
?>
</select>
</td>
</tr>

<tr >
<td></td>	
<td colspan="1"><input type="submit" name="manual" class="button" id="button1" value="Next Step" 
style="width:100%;font-size:65px;padding:10px;background:lightblue;color:white;float:right"/> </td>
<tr>



</table>			
</form>

<?php
}
?>

























</div>



<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script> 
<script src="//geodata.solutions/includes/countrystatecity.js"></script>



</body>
</html>

