<?php
session_start();
if(isset($_SESSION['hotel'])){
header("location:index.php");
}
?>
<style>
input[type=text], select {
  width: 100%;
  padding: 12px 20px;
  margin: 8px 0;
  display: inline-block;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}

input[type=submit] {
  width: 100%;
  background-color: #4CAF50;
  color: white;
  padding: 14px 20px;
  margin: 8px 0;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

input[type=submit]:hover {
  background-color: #45a049;
}

div {
  border-radius: 5px;
  background-color: #f2f2f2;
  padding: 20px;
  width:30%;
  margin:0 auto;
}
</style>



<?php include'loginnow.php';?>




<div>
  <form action=""  method="post" action="">
    <label for="fname" style="color:blue">Confrim Username (Authenticate)</label>
    <input type="text" id="fname" name="username" placeholder="">

    
  
    <input type="submit" name="Submits" value="Continue...">
  </form>
</div>


</body>
</html>