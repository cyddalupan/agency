<?php


echo'<div style="background:#eee;color:red;padding:8px;width:340px;margin-left:30px;text-align:center">';
require_once 'PHPMailers/class.phpmailer.php';
		$mail = new PHPMailer();
		// Now you only need to add the necessary stuff

		// HTML body

		$body = "</pre>
		<div>";
		$body .= " 
		";
		$body .= "
		";
		
		$body .= "<p>Message: <p>Hi,</p>
		<p>Name : ".$_GET['name']."</p>
		<p>Password : welcomefcec</p>
		<p>Email : ".$_GET['email']."</p>
		<p>login NOW : <a href='http://fcec-inc.com/system/applicant_system/applicant-login'>LOGIN NOW!<A></p>";
		$body .= "</div>" ;

		// And the absolute required configurations for sending HTML with attachement

		$mail->AddAddress("".$_GET['email']."", "Account FCEC APPLICANT");
		$mail->Subject = "Name ".$_POST['name']."";
		$mail->MsgHTML($body);
		$mail->AddAttachment("phpmailer.gif");
		if(!$mail->Send()) {
		echo "There was an error sending the message";
		exit;
		}
		echo "Message was sent successfully";
echo'</div>';

?>

