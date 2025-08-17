<?php 
include 'db.php';
?>
<!DOCTYPE html>
<html >
  <head>
    <meta charset="UTF-8">
    <title>NEW REPORTS</title>
    
    
    
    
        <style>
      /* NOTE: The styles were added inline because Prefixfree needs access to your styles and they must be inlined if they are on local disk! */
      @import url("http://fonts.googleapis.com/css?family=Open+Sans:400,600,700");
@import url("http://netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.css");
*, *:before, *:after {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body {
  height: 100%;
}

body {
  font: 14px/1 'Open Sans', sans-serif;
  color: #555;
  background: #eee;
}

h1 {
  padding: 50px 0;
  font-weight: 400;
  text-align: center;
}

p {
  margin: 0 0 20px;
  line-height: 1.5;
}

main {
  min-width: 320px;
  max-width: 800px;
  padding: 50px;
  margin: 0 auto;
  background: #fff;
}

section {
  display: none;
  padding: 20px 0 0;
  border-top: 1px solid #ddd;
}

input {
  display: none;
}

label {
  display: inline-block;
  margin: 0 0 -1px;
  padding: 15px 25px;
  font-weight: 600;
  text-align: center;
  color: #bbb;
  border: 1px solid transparent;
}

label:before {
  font-family: fontawesome;
  font-weight: normal;
  margin-right: 10px;
}

label[for*='1']:before {
  content: '\f1cb';
}

label[for*='2']:before {
  content: '\f17d';
}

label[for*='3']:before {
  content: '\f16b';
}

label[for*='4']:before {
  content: '\f1a9';
}

label:hover {
  color: #888;
  cursor: pointer;
}

input:checked + label {
  color: #555;
  border: 1px solid #ddd;
  border-top: 2px solid orange;
  border-bottom: 1px solid #fff;
}

#tab1:checked ~ #content1,
#tab2:checked ~ #content2,
#tab3:checked ~ #content3,
#tab4:checked ~ #content4 {
  display: block;
}

@media screen and (max-width: 650px) {
  label {
    font-size: 0;
  }

  label:before {
    margin: 0;
    font-size: 18px;
  }
}
@media screen and (max-width: 400px) {
  label {
    padding: 15px;
  }
}

    </style>

    
        <script src="js/prefixfree.min.js"></script>

    
  </head>

  <body>

    <h1>LANDBASE HUMAN RESOURCES</h1>

<main>
  
  <input id="tab1" type="radio" name="tabs" checked>
  <label for="tab1">Selected</label>
    

  <input id="tab3" type="radio" name="tabs">
  <label for="tab3">Deployed</label>
    
  <input id="tab4" type="radio" name="tabs">
  <label for="tab4">Encoded</label>
  
  
  <section id="content1">
  
  <div style="clear:both;height:20px"></div>
    	<form action="perprov.php" method="post" target="_blank">
			<div class="col-sm-5">
			<div class="pull-left" >
			<select class="form-control" name="status" style="width:200px;padding:5px;">
				<option value="99">All</option>
				<option value="10">For Review</option>
				<option value="4">Selected</option>
				<option value="12">FOR BOOKING</option>
				<option value="8">FOR DEPLOYMENT</option>
				<option value="1">Cancelled</option>
			</select>
			</div>
			</div>
			
		
			
			
			
			<div style="clear:both;height:20px"></div>
			
			
			<div class="col-sm-5">
			<div class="pull-left" >
			<select class="form-control" name="sub_status" style="width:200px;padding:5px;">
				<option value="1">------------</option>
			 <option>To Undergo Medical</option>
			<option>Medical Under Process</option>
			<option>FIT TO WORK</option>
			<option>Waiting for job Offer</option>
			<option>For job offer Signing</option>
			<option>Waiting For Visa</option>
			<option>Visa in Process</option>
			<option>Visa Approved</option>
			<option>Unfit</option>
			<option>Canceled/backout</option>
			</select>
			</div>
			</div> 
			
			
				<div style="clear:both;height:20px"></div>
			
			<div class="col-sm-5">
			<div class="pull-left" >
			<select class="form-control" name="user" style="width:200px;padding:5px;">
				<option value="999">All User</option>
				<?php
				$usertype = mysql_query("SELECT * FROM user where user_type!=5  ORDER BY user_fullname asc");
				while($rows1=mysql_fetch_array($usertype))
				{
					echo'<option value="'.$rows1["user_id"].'">'.$rows1["user_fullname"].'</option>';
				}
				?>
			</select>
			</div>
			</div> 
			
			<div style="clear:both;height:20px"></div>
			
			<div class="col-sm-5">
			<div class="pull-left" >
			<input type="text" name="keyword" placeholder="Type Keywords"  style="width:200px;padding:5px;display:inline">
			</div>
			</div>
			
			
			
			
			
				<div style="clear:both;height:20px"></div>
			
			<div class="col-sm-5">
			<div class="pull-left" > 
			<button type="submit" class="btn btn-primary" name="addons" style="width:100px;padding:5px;margin-left:100px"> GENERATE</button>
			</div>
			</div> 
		
  </section>
 </form>   
      
  <section id="content3">
		
		<form action="deployed.php" method="post" target="_blank">
			<div class="col-sm-5">
			<div class="pull-left" >
			<select class="form-control" name="status" style="width:200px;padding:5px;">
				<option value="9">Deployed</option>
			</select>
			</div>
			</div> 
			<div style="clear:both;height:20px"></div>
			<input type="text" name="date1">
			<div class="col-sm-5">
			<div class="pull-left" >
					<input type="date" name="date1"  style="width:200px;padding:5px;display:inline">
			</div>
			</div> 
			
			<div style="clear:both;height:20px"></div>
			<div class="col-sm-5">
			<div class="pull-left" >
					<input type="date" name="date2"  style="width:200px;padding:5px;display:inline">
			</div>
			</div> 
			
			
				<div style="clear:both;height:20px"></div>
			
			<div class="col-sm-5">
			<div class="pull-left" >
			<select class="form-control" name="user" style="width:200px;padding:5px;">
				<option value="999">All User</option>
				<?php
				$usertype = mysql_query("SELECT * FROM user where user_type!=5  ORDER BY user_fullname asc");
				while($rows1=mysql_fetch_array($usertype))
				{
					echo'<option value="'.$rows1["user_id"].'">'.$rows1["user_fullname"].'</option>';
				}
				?>
			</select>
			</div>
			</div>
			
		
			
			
			<div style="clear:both;height:20px"></div>
			<div class="col-sm-5">
			<div class="pull-left" > 
			<button type="submit" class="btn btn-primary" name="addons" style="width:100px;padding:5px;margin-left:100px"> GENERATE</button>
			</div>
			</div> 
		
		</form>
  </section>
    
  
  
    <section id="content4">
   	<form action="encoded.php" method="post" target="_blank">
			<div class="col-sm-5">
			<div class="pull-left" >
			<select class="form-control" name="status" style="width:200px;padding:5px;">
				<option value="99">Encoded</option>
			</select>
			</div>
			</div> 
			<div style="clear:both;height:20px"></div>
			<input type="text" name="date1">
			<div class="col-sm-5">
			<div class="pull-left" >
					<input type="date" name="date1"  style="width:200px;padding:5px;display:inline">
			</div>
			</div> 
			
			<div style="clear:both;height:20px"></div>
			<div class="col-sm-5">
			<div class="pull-left" >
					<input type="date" name="date2"  style="width:200px;padding:5px;display:inline">
			</div>
			</div> 
			
			
				<div style="clear:both;height:20px"></div>
			
			<div class="col-sm-5">
			<div class="pull-left" >
			<select class="form-control" name="user" style="width:200px;padding:5px;">
				<option value="999">All User</option>
				<?php
				$usertype = mysql_query("SELECT * FROM user where user_type!=5  ORDER BY user_fullname asc");
				while($rows1=mysql_fetch_array($usertype))
				{
					echo'<option value="'.$rows1["user_id"].'">'.$rows1["user_fullname"].'</option>';
				}
				?>
			</select>
			</div>
			</div>
			
			
			
			
			
			<div style="clear:both;height:20px"></div>
			<div class="col-sm-5">
			<div class="pull-left" > 
			<button type="submit" class="btn btn-primary" name="addons" style="width:100px;padding:5px;margin-left:100px"> GENERATE</button>
			</div>
			</div> 
		
		</form>
  </section>
  
    
</main>
    
    
    
    
    
  </body>
</html>
