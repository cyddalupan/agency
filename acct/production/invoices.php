<?php include'db.php';
$employer = mysql_query("SELECT * FROM employer where employer_id=".$_GET['emp_id']." ");
$employer1=mysql_fetch_array($employer);
$count=1;
?>

<style>
#wrapper{
font-family: Arial, Helvetica, sans-serif;
border:0px solid black;
width:1100px;
margin:0 auto ;
min-height:500px;
padding:6px;
color:black;

}
h1{
text-align:center;
margin:0 auto;
}
#customers {
font-family: Arial, Helvetica, sans-serif;
border-collapse: collapse;
width: 90%;
font-size:18px;
margin:0 auto
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 6px;
}
#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: CENTER;
  color: BLACK;
}

input {

   border:none;
    outline-width: 0;
color:black;
width:100px;
width:250px;
padding:3px;
font-size:18px;
font-weight:bold;
}
</style>



<div id="wrapper">

		<img src="logojin.png" style="width:80%;margin-left:80px;margin-top:-10px">
		<h1>Billing Invoice</h1>
		
		
		
		<div style="clear:both;HEIGHT:15PX"></div>
			<table id="customers">
			
			<tr>
			<td>Company Name:</td>
			<td STYLE="width:350px"><b><?=$employer1['employer_name']?></B></td>
			<td>Invoice No:</td>
			<td><b><input type="text" value="" style="border:1px solid #F8F8FF"></B></td>
			</tr>
			<tr>
			<td>ATTN:</td>
			<td><b><?=$employer1['employer_contact_person']?></B></td>
			<td>DATE:</td>
			<td><b> <input type="date" value="" style="width:300px"></B></B></td>
			</tr>
			
			<tr>
			<td>ADDRESS:</td>
			<td><b><?=$employer1['employer_address']?></B></td>
			<td>EMAIL ADDRESS:</td>
			<td><b> <?=$employer1['employer_email']?></B></td>
			</tr>
			
			</table>
			
			
				<div style="clear:both;HEIGHT:35PX"></div>
				
<?php
$app = mysql_query("SELECT * FROM applicant
WHERE applicant_paid!=1
AND   applicant_status NOT IN (25,1,21,14,22)  
AND applicant_employer=".$_GET['emp_id']."
AND (fra_ftw!=0 OR fra_visa!=0 OR fra_deployed!=0 OR fra_sent!=0 OR fra_before!=0) ORDER BY applicant_id desc 
");

$counting = mysql_query("SELECT count(applicant_source) as countme FROM  applicant WHERE applicant_paid!=1
AND   applicant_status NOT IN (25,1,21,14,22)  
AND applicant_employer=".$_GET['emp_id']."
AND (fra_ftw!=0 OR fra_visa!=0 OR fra_deployed!=0 OR fra_sent!=0 OR fra_before!=0) ");
$counting1 = mysql_fetch_array($counting);
?>


				

		<table id="customers">
		<tr>
		<th>Particulars</th>
		<th>Amount in USD</th>
	    <th>Status</th>
		</tr>
	
		<tr>
		<td COLSPAN="3"><input type="text" value="" style="border:1px solid #F8F8FF;font-size:18px;padding:4px;width:600px" ></td>
		</tr>
<?PHP
	


while($row=mysql_fetch_array($app))
{

$received = mysql_query("SELECT sum(fra_amount) as totalamount FROM  liq_fra 
where  app_id=".$row['applicant_id']." ");
$received1=mysql_fetch_array($received);

$totalpay=($row["fra_ftw"]+$row["fra_visa"]+$row["fra_deployed"]+$row["fra_sent"]+$row["fra_before"])-$received1["totalamount"];




$grandtotal=$totalpay+$grandtotal;


?>	
		
		
		
		<tr>
		<td STYLE="padding-left:50px;font-size:16px;width:400px"><?=$count?>. <b><?=$row["applicant_last"]?> , <?=$row["applicant_first"]?> <?=$row["applicant_middle"]?></b> </td>
		<td STYLE="padding-left:50px;font-size:16px;TEXT-ALIGN:center;width:190px"><b>$ <?=number_format($totalpay,2)?></B></td>
		<td ><input type="text" value="" style="border:1px solid #F8F8FF;font-size:18px;padding:4px;width:280px" ></td>
		</tr>
	

<?PHP
$count++;
}
$count1=$count++;
?>		
		
		
		
		
		
		
		
		<tr>
		    <td  STYLE="TEXT-ALIGN:RIGHT;FONT-SIZE:26px;FONT-WEIGHT:BOLD">NET AMOUNT IN USD </td>
		<td  STYLE="TEXT-ALIGN:right;FONT-SIZE:16PX;FONT-WEIGHT:BOLD;font-size:26px;">$ <?=number_format($grandtotal,2)?></td>
		 <td  STYLE="TEXT-ALIGN:RIGHT;FONT-SIZE:26px;FONT-WEIGHT:BOLD"> </td>
		</tr>
		</table>
		
<div style="clear:both;HEIGHT:25PX"></div>		

<style>
#customers1 {
  width: 100%;
  border: 0px solid;
}
</style>


		
	    <table id="customers1" STYLE="BORDER:NONE">
	
		<tr>
		 <td  STYLE="TEXT-ALIGN:RIGHT;FONT-SIZE:16PX;FONT-WEIGHT:BOLD">AMOUNT IN WORDS: </td>
		<td COLSPAN="2"><input type="text" value="" style="border:1px solid #F8F8FF;font-size:18px;padding:4px;width:700px" ></td>
		</tr>	
		
		</table>	
		
		
		
<div style="clear:both;HEIGHT:35PX"></div>

<P Style="font-weight:bold;padding-left:50px">NOTE: KINDY REMIT YOUR BANK ACCOUNT DETAILS MENTIONED BELOW:<P>

			<table id="customers">
			
			<tr>
			<td>ACCOUNT NAME:</td>
			<td><b>JINHEL INTERNATIONAL RECRUITMENT CORPORATION</B></td>
			</tr>
			
				<tr>
			<td>ACCOUNT NUMBER:</td>
			<td><b>111160237819</B></td>
			</tr>
			
			
				<tr>
			<td>BANK NAME:</td>
			<td><b>PHILIPPINE NATIONAL BANK (PNB)</B></td>
			</tr>
			
				<tr>
			<td>DEPOSITORY BRANCH :</td>
			<td><b>1111 MAKATI-ALLIED BANK CENTER</B></td>
			</tr>

			<tr>
			<td>ISSUING BANK:</td>
			<td><b>1111 MAKATI ALLIED BANK CENTER</B></td>
			</tr>
		
			<tr>
			<td>BANK ADDRESS:</td>
			<td><b>AYALA AVENUE MAKATI, MANILA</B></td>
			</tr>
			
				<tr>
			<td>SWIFT CODE NO:</td>
			<td><b>PNBMPHMM</B></td>
			</tr>
			
			
	
			</table>
<div style="clear:both;HEIGHT:45PX"></div>
<P Style="font-weight:bold;padding-left:50px">PREPARED BY: THESSIE ABIERA<P>
<div style="clear:both;HEIGHT:35PX"></div>


<div style="clear:both;HEIGHT:35PX"></div>
	
</div>
<P STYLE="TEXT-ALIGN:CENTER"> Telephone No. 401-3671 / 384 -1711 / 468 - 6327  Fax No. 551 - 5709</P>
<P STYLE="TEXT-ALIGN:CENTER"> Email Address: <i style="color:blue"> jinhel2004@yahoo.com / loretaochoa@yahoo.com</i></P>