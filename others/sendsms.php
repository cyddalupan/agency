<form action="" method="post" style="width:500px;margin:150px">
<p style="color:red"> CHANGE NUMBER OR DETAILS IF TO RECEIVED SMS</p>

contact
<input type="text" name="mobile" value="09953269012">
</br></br>

name
<input type="text" name="mobile1" value="Adonis">
</br></br>
message
<textarea name="notification_message"> HI FTW APPLICANT</textarea>

</br></br>

<a href="delete_methode_link" onclick="return confirm('Are you sure you want to Sent SMS?');">		
<button type="submit" class="btn btn-success btn-sm"  name="sentcv" style="border:BORDER 2PX SOLID black;color:black;border:1px solid black">Sent SMS</button>
</a>

</form>

<?php
if(isset($_POST['sentcv'])){

function gw_send_sms($user,$pass,$sms_from,$sms_to,$sms_msg)
{
$query_string =
"api2.aspx?apiusername=".$user."&apipassword=".$pass;
$query_string .=
"&senderid=".rawurlencode($sms_from)."&mobileno=".rawurlencode($sms_to);
$query_string .=
"&message=".rawurlencode(stripslashes($sms_msg)) . "&languagetype=1";
$url =
"http://gateway80.onewaysms.ph/".$query_string;
$fd = @implode ('', file ($url));
if ($fd)
{
if ($fd > 0) {
Print("MT ID : " . $fd);
$ok = "success";
}
else {
print("Please refer to API on Error : " . $fd);
$ok = "fail";
}
}
else
{
// no contact with gateway
$ok = "no contact with gateway ";
}
return $ok;
}
Print("Sending to one way sms " . gw_send_sms("APIWF72T9DFKO",
"APIWF72T9DFKOWF72T", "INFO", "".$_POST['mobile']."", "Hi ".$_POST['mobile1'].", ".$_POST['notification_message'].""));
}
?>