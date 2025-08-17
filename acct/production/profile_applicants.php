<?php include'inc/header1.php';
include'tracking.php';
include'inc/update.php';
$app = mysql_query("SELECT * FROM email_address2 where app_id=".$_GET['appid']." ");
$row=mysql_fetch_array($app);

$CERT = mysql_query("SELECT * FROM applicant_certificate where app_id=".$_GET['appid']." ");
$certrow=mysql_fetch_array($CERT);

$REQ = mysql_query("SELECT * FROM applicant_requirement where app_id=".$_GET['appid']." ");
$reqrow=mysql_fetch_array($REQ);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);

$app3 = mysql_query("SELECT * FROM medical where app_id=".$_GET['appid']." ");
$row2=mysql_fetch_array($app3);

?>

<body class="nav-md" style="background:#F5F5F5">



<!-- page content -->
<div class="right_col" role="main">
<div class="">


<div class="clearfix"></div>

<div class="row" style="width:90%;margin:0 auto;margin-top:20px;margin-bottom:20px">
<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">

<div class="x_content">
<div class="col-md-3 col-sm-12 col-xs-12">  
<h5>NAME</h5>     
<h2 style="color:Red"><?=$row['lname']?>, <?=$row['name']?> <?=$row['mname']?></h2>    
</DIV>
<div class="col-md-3 col-sm-12 col-xs-12">    
<h5>Country</h5>   
<h2><?=$row['country']?></h2>    
</DIV>
<div class="col-md-3 col-sm-12 col-xs-12">      
<h5>Agent</h5>  
<h2 style="color:green"><?=$row1['agent_first']?> <?=$row1['agent_last']?></h2>    
</DIV>

<div class="col-sm-2 invoice-col">

<a href="cert/pdos.php?app_id=<?=$_GET['appid']?>" style="COLOR:blue;font-size:12px" target="_blank">Print PDOS</a>

</div>

<DIV style="clear:both;height:30px"></DIV>


<div class="" role="tabpanel" data-example-id="togglable-tabs">
<ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
<li role="presentation" class="active"><a href="#tab_content1" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Profile</a>
</li>
<li role="presentation" class=""><a href="#tab_medical" role="tab" id="profile-tab" data-toggle="tab" aria-expanded="false">Medical</a>
</li>

<li role="presentation" class=""><a href="#tab_req" role="tab" id="profile-tab1" data-toggle="tab" aria-expanded="false">Requirements</a>
</li>

<li role="presentation" class=""><a href="#tab_process" role="tab" id="profile-tab2" data-toggle="tab" aria-expanded="false">Processing</a>
</li>

<li role="presentation" class=""><a href="#tab_statuss" role="tab" id="profile-tab3" data-toggle="tab" aria-expanded="false">Status</a>
</li>


</ul>
<div id="myTabContent" class="tab-content">
<div role="tabpanel" class="tab-pane fade active in" id="tab_content1" aria-labelledby="home-tab">

<?php include'form/profile.php';?>

</div>

--
<div role="tabpanel" class="tab-pane fade" id="tab_medical" aria-labelledby="profile-tab">
<?php include'form/medical.php';?>
</div>


<div role="tabpanel" class="tab-pane fade" id="tab_req" aria-labelledby="profile-tab1">
<?php include'form/req.php';?>
</div>


<div role="tabpanel" class="tab-pane fade" id="tab_statuss" aria-labelledby="profile-tab3">
<?php include'form/status.php';?>
</div>


<div role="tabpanel" class="tab-pane fade" id="tab_process" aria-labelledby="profile-tab2">
<?php include'form/process.php';?>
</div>



</div>
</div>


</div>
</div>
</div>
</div>
</div>
</div>
<!-- /page content -->

<!-- footer content -->



<!-- jQuery -->
<script src="../vendors/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- FastClick -->
<script src="../vendors/fastclick/lib/fastclick.js"></script>
<!-- NProgress -->
<script src="../vendors/nprogress/nprogress.js"></script>
<!-- morris.js -->
<script src="../vendors/raphael/raphael.min.js"></script>
<script src="../vendors/morris.js/morris.min.js"></script>
<!-- bootstrap-progressbar -->
<script src="../vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
<!-- bootstrap-daterangepicker -->
<script src="../vendors/moment/min/moment.min.js"></script>
<script src="../vendors/bootstrap-daterangepicker/daterangepicker.js"></script>

<!-- Custom Theme Scripts -->
<script src="../build/js/custom.min.js"></script>

</body>
</html>