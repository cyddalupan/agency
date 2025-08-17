<html>
<head>
<title>Jquery PHP Timeago</title> 
<script type="text/javascript" src="jquery.min.js"></script>
<script type="text/javascript" src="jquery.livequery.js"></script>
<script type="text/javascript" src="jquery.timeago.js"></script>
<script>
$(document).ready(function(){
$("a.timeago").livequery(function() 
{ 
	$(this).timeago(); 
});	
});
</script>
<style>
body{
	font-family: Helvetica,Arial,sans-serif;
	color: #333;
}
#big{font-size:34px}
</style>
</head>
<body>
<?php
$time=time();
$mtime=date("c", $time);
?>	
<div style='text-align:center'>

	<h1>Jquery Timeago PHP</h1>
	

	<br/><br/>
<span id='big'>You opened this page <a href='#' class='timeago' title="<?php echo $mtime; ?>"></a>.</span> <br/>
(This will update every minute. Please wait for it.)
</div>


</body>
</html>