<script type="text/javascript">
function resizeIframe(obj) {
obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
}
</script>
<?php
$mylink="https://thegrandmidoriortigas.info/agency/";
?>

<div ng-controller="dashboard">
<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
<ul class="breadcrumb">
<li  class="active">
<i class="fa fa-home"></i> Home 
</li>
</ul>
</div>
<!-- /Page Breadcrumb -->

<!-- Page Header -->
<div class="page-header position-relative">
<div class="header-title">
<h1>Dashboard</h1>
</div>
<!--Header Buttons-->
<div class="header-buttons">
<a class="sidebar-toggler" href="#">
<i class="fa fa-arrows-h"></i>
</a>
<a class="refresh" id="refresh-toggler" href="#">
<i class="fa fa-refresh"></i>
</a>
<a class="fullscreen" id="fullscreen-toggler" href="#">
<i class="fa fa-arrows-alt"></i>
</a>
</div>
<!--Header Buttons End-->
</div>
<!-- /Page Header -->

<!-- Page Body -->
<div class="page-body page-<?php echo $app->getTemplate(); ?>">
<?php $app->renderAlerts(); ?> 

<?php //include'db.php';?>
<?php //include'flow.php';?>


<style>
@keyframes blink {
0% {
opacity: 1;
}

50% {
opacity: 0;
}

100% {
opacity: 1;
}
}

.blinking-text {
text-align: center;
margin-top: 2%;
font-size: 28px;
color: red;
animation: blink 1s infinite;
}
</style>	





<div class="horizontal-space"></div>

  <div class="blinking-text">
        Check Reminders!
    </div>
</br></br>

<iframe src="<?=$mylink?>others/index.php" style="width:100%;margin:auto 0 ;height:1000px"></iframe>











<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [27]) ): ?> 
<iframe src="https://recruitment-portal.net/everlast/others/pop_medical.php?medical=2" style="width:100%;margin:auto 0 ;height:1000px"></iframe>
<?php endif; ?>

<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [28]) ): ?> 
<iframe src="https://recruitment-portal.net/everlast/others/pop_tesda.php?medical=2" style="width:100%;margin:auto 0 ;height:1000px"></iframe>
<?php endif; ?>

<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [29]) ): ?> 
<iframe src="https://recruitment-portal.net/everlast/others/pop_wait.php?imy=<?=$_SESSION['admin']['user']['userassign']?>" style="width:100%;margin:auto 0 ;height:1000px"></iframe>
<?php endif; ?>

<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [31]) ): ?> 
<iframe src="https://recruitment-portal.net/everlast/others/pop_bc.php?user=<?=$_SESSION['admin']['user']['user_id']?>" style="width:100%;margin:auto 0 ;height:1000px"></iframe>
<?php endif; ?>   

<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [17]) ): ?> 
<iframe src="https://recruitment-portal.net/everlast/others/pop_branch.php?imy=<?=$_SESSION['admin']['user']['branch_id']?>&&user=<?=$_SESSION['admin']['user']['user_id']?>" style="width:100%;margin:auto 0 ;height:1000px"></iframe>
<?php endif; ?> 



<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [33]) ): ?> 
<iframe src="https://recruitment-portal.net/everlast/others/interview.php?fil=1&&nm=For Interview" style="width:100%;margin:auto 0 ;height:1000px"></iframe>
<?php endif; ?> 

<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [32]) ): ?> 
<h1>Active Complaints</h1>

<?php endif; ?> 












 
    <?php  
    $data=111;
      if ($data==111) { ?> 
      
         }   
        <?php  }  ?> 
        
        
        
        
      <?php 
    if ($data==1) {  ?>  
    
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
    
    
    <script>
    $(document).ready(function(){
    $("#myModal").modal('show');
    });
    </script>
    
    
    
    
    <style>
    @media (min-width: 1200px) {
    .container {
    max-width: none;
    }
    } 
    </style>
    
    
    
    <div id="myModal" class="modal fade" style="width:98%;position:absolute">
    <div class="modal-dialog" style="width:98%;position:absolute">
    <div class="modal-content" style="width:98%;position:absolute">
    <div class="modal-header" style="width:98%;position:absolute">
    <h3 class="modal-title"></h3>
    
    </div>
    <div class="modal-body">
    <?= $data?>
    
    <?php if ( in_array( $_SESSION['admin']['user']['user_type'], [27]) ): ?> 
    <iframe src="https://recruitment-portal.net/everlast/others/pop_medical.php?medical=2" style="width:100%;margin:auto 0 ;height:1000px"></iframe>
    <?php endif; ?>
    
    <?php if ( in_array( $_SESSION['admin']['user']['user_type'], [28]) ): ?> 
    <iframe src="https://recruitment-portal.net/everlast/others/pop_tesda.php?medical=2" style="width:100%;margin:auto 0 ;height:1000px"></iframe>
    <?php endif; ?>
    
    <?php if ( in_array( $_SESSION['admin']['user']['user_type'], [29]) ): ?> 
    <iframe src="https://recruitment-portal.net/everlast/others/pop_wait.php?imy=<?=$_SESSION['admin']['user']['userassign']?>" style="width:100%;margin:auto 0 ;height:1000px"></iframe>
    <?php endif; ?>
    
    <?php if ( in_array( $_SESSION['admin']['user']['user_type'], [31]) ): ?> 
    <iframe src="https://recruitment-portal.net/everlast/others/pop_bc.php?user=<?=$_SESSION['admin']['user']['user_id']?>" style="width:100%;margin:auto 0 ;height:1000px"></iframe>
    <?php endif; ?>   
    
    <?php if ( in_array( $_SESSION['admin']['user']['user_type'], [17]) ): ?> 
    <iframe src="https://recruitment-portal.net/everlast/others/pop_branch.php?imy=<?=$_SESSION['admin']['user']['branch_id']?>&&user=<?=$_SESSION['admin']['user']['user_id']?>" style="width:100%;margin:auto 0 ;height:1000px"></iframe>
    <?php endif; ?> 
    <?php if ( in_array( $_SESSION['admin']['user']['user_type'], [4]) ): ?> 
    <iframe src="https://recruitment-portal.net/everlast/others/pop_booking.php?imy=<?=$_SESSION['admin']['user']['branch_id']?>&&user=<?=$_SESSION['admin']['user']['user_id']?>" style="width:100%;margin:auto 0 ;height:1000px"></iframe>
    <?php endif; ?> 
    
    
    <?php if ( in_array( $_SESSION['admin']['user']['user_type'], [33]) ): ?> 
    <iframe src="https://recruitment-portal.net/everlast/others/interview.php?fil=1&&nm=For Interview" style="width:100%;margin:auto 0 ;height:1000px"></iframe>
    <?php endif; ?> 
    
    <?php if ( in_array( $_SESSION['admin']['user']['user_type'], [32]) ): ?> 
    <h1>Active Complaints</h1>
    
    <?php endif; ?> 
    
    
    
    </div>
    </div>
    </div>
    
    
    </div>
    <?php  }  ?> 









</div>
<!-- /Page Body -->
</div>