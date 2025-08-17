<?php
session_start();
if(!isset($_SESSION['admin']['user'])){
header("location:noaccess.php");
}

?>

<?php
$review="https://recruitment-portal.net/manumoti/admin/applicants/review_single/";
$photo="https://recruitment-portal.net/manumoti/files/applicant";
$myreport="https://recruitment-portal.net/manumoti/others/";
?>
<style>
table{
  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  font-size:10px;
}

 td,th {
  border: 1px solid #ddd;
  padding: 5px;
}
th {
padding-top: 10px;
padding-bottom: 10px;
text-align: left;
background-color: #DCDCDC;
color: black;
}
tD{
padding:1px;
}
input{
width:100px;
padding:1px;
font-size:10px;
height:20px;	
}
button{
padding:3px;
font-size:10px;	
}



ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
  overflow: hidden;
  background-color: #333;
}

li {
  float: left;
  border-right:1px solid #bbb;
}

li:last-child {
  border-right: none;
}

li a {
  display: block;
  color: white;
  text-align: center;
  padding: 14px 16px;
  text-decoration: none;
}

li a:hover:not(.active) {
  background-color: #111;
}

.active {
  background-color: #4CAF50;
}


</style>