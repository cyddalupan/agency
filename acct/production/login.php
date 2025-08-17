<?php
session_start();
if(isset($_SESSION['staff'])){
header("location:index.php");
}
?>
<?php 
include 'db.php';
if(isset($_POST['Submits'])){
$myusername=$_POST['username'];
$mypassword=$_POST['password']; 
$myusername = stripslashes($myusername);
$mypassword = stripslashes($mypassword);
$myusername = mysql_real_escape_string($myusername);
$mypassword = mysql_real_escape_string($mypassword);
$sql="SELECT * FROM user WHERE user_name='".$myusername."' AND acct_pass='".$mypassword."' 
";
$result=mysql_query($sql);
$row=mysql_fetch_array($result);
// Mysql_num_row is counting table row
$count=mysql_num_rows($result);
// If result matched $myusername and $mypassword, table row must be 1 row



if($count==1){
// Register $myusername, $mypassword and redirect to file "index.php"
$_SESSION['staff'] = $myusername;
$_SESSION['user_fullname'] = $row['user_name'];
$_SESSION['user_type'] = $row['user_type'];
$_SESSION['user_name'] = $row['user_name'];
$_SESSION['user_id'] = $row['user_id'];
	echo '<script type="text/javascript">
<!--
window.location = "index.php"
//-->
</script>
';
}


else {
echo "<center><p style='color:red;font-size:12px'>Wrong Username or Password</p></center>";
}

}
?>