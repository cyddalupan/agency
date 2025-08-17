<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Print OR#<?php echo $or['or_number'];  ?></title>
<style>
#receipt {
	font-family:Gotham, "Helvetica Neue", Helvetica, Arial, sans-serif;
	border:1px solid #666;
	width:600px;
	padding:20px;
}

#receipt .header {
	margin-bottom:20px;
}

#receipt .header .company {
	float:left;
	width:350px;
}

#receipt .header .or-number {
	float:right;
	width:200px;
	font-size:120%;
}

#receipt .header .or-number .number {
	color:#f03;
	font-weight:bold;
}

#receipt .header .company img {
	width:100%;
}

#receipt .header2 {
	margin-bottom:20px;
}

#receipt .official-receipt {
	float:left;
	width:200px;
	text-decoration:underline;
}

#receipt .date {
	width:150px;
	float:right; 
}

#receipt .body .line-statement {
	margin-bottom:10px;
}

.clearfix {
	clear:both;
}

</style>
</head>
<body>

<div id="receipt">
	<div class="header">
    	<div class="company">
        	<img src="<?php echo $app->getPath()['images']; ?>ics-banner.png" alt="STEPUP-MANPOWER">
        </div>
        
        <div class="or-number" align="right">
        	No: <span class="number">2012312</span>
        </div>
        
        <div class="clearfix"></div>
    </div>
    
    <div class="header2">
        <div class="official-receipt">
            <p><strong>OFFICIAL RECEIPT</strong></p>
        </div>
        
        <div class="date" align="right">
            <p>Date <span style="text-decoration:underline">&nbsp;&nbsp;<?php echo $or['date']; ?>&nbsp;&nbsp;</span></p>
        </div>
        
        <div class="clearfix"></div>
    </div>
    
    <div class="body">
        <div class="line-statement">
        	<div style="width:20%; display:inline-block">Received by:&nbsp;</div><div style="width:90%; border-bottom:1px solid #000; width:80%; display:inline-block">
            <?php echo $or['received-by']; ?>
            </div>
        </div>
        <div  class="line-statement">
	        <div style="width:20%; display:inline-block">Amount:&nbsp;</div><div style="width:90%; border-bottom:1px solid #000; width:80%; display:inline-block">
            <?php echo $or['amount']; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>