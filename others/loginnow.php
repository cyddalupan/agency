
<?php 
include 'db.php';
if(isset($_POST['Submits'])){
		$myusername=$_POST['username'];
		$sql="SELECT * FROM  user
		LEFT JOIN employer
		ON user.user_id=employer.employer_user 
		 WHERE user_name='".$myusername."'";
		$result=mysql_query($sql);
		$row=mysql_fetch_array($result);
		// Mysql_num_row is counting table row
		$count=mysql_num_rows($result);
		// If result matched $myusername and $mypassword, table row must be 1 row

		if($count==1){

		// Register $myusername, $mypassword and redirect to file "index.php"
		$_SESSION['hotel'] = $myusername;
		$_SESSION['user_type']=$row['user_type'];
		$_SESSION['employer_id']=$row['employer_id'];
			$_SESSION['user_fullname']=$row['user_fullname'];
		echo '<script type="text/javascript">
		<!--
		window.location = "all-applicants.php"
		//-->
		</script>
		';
		}
	
		
		else {
		echo "<center><p style='color:red;font-size:12px'>Wrong Username </p></center>";
		
		}

}
?>