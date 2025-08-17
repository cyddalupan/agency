<?php include'inc/header1.php';
include'inc/expenses.php';
include'styles.php';
include'tracking.php';
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.8.1/css/bootstrap-select.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.8.1/js/bootstrap-select.js"></script>


<style>
input[type="checkbox"] {
   width: 20px;
            height: 20px;
}
</style>


<body class="nav-md">
<div class="container body">
<div class="main_container">
<div class="col-md-3 left_col">
<div class="left_col scroll-view">
<div class="navbar nav_title" style="border: 0;">
<a href="index.html" class="site_title"> <span><?php include'title.php';?></span></a>
</div>

<div class="clearfix"></div>

<!-- menu profile quick info -->
<div class="profile clearfix">
<div class="profile_pic">
<img src="images/img.jpg" alt="..." class="img-circle profile_img">
</div>
<div class="profile_info">
<span>Welcome,</span>
<h2><?=$_SESSION['user_fullname']?></h2>
</div>
</div>
<!-- /menu profile quick info -->

<br />

<!-- sidebar menu -->
<div id="sidebar-menu" class="main_menu_side hidden-print main_menu">

<div class="menu_section">
<?php include'inc/menu.php';?>	
</div>
</div>
<!-- /sidebar menu -->

<!-- /menu footer buttons -->
<div class="sidebar-footer hidden-small">
<a data-toggle="tooltip" data-placement="top" title="Settings">
<span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
</a>
<a data-toggle="tooltip" data-placement="top" title="FullScreen">
<span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
</a>
<a data-toggle="tooltip" data-placement="top" title="Lock">
<span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
</a>
<a data-toggle="tooltip" data-placement="top" title="Logout" href="login.html">
<span class="glyphicon glyphicon-off" aria-hidden="true"></span>
</a>
</div>
<!-- /menu footer buttons -->
</div>
</div>

<!-- top navigation -->
<div class="top_nav">
<div class="nav_menu">
<nav>
<div class="nav toggle">
<a id="menu_toggle"><i class="fa fa-bars"></i></a>
</div>
<ul class="nav navbar-nav navbar-right">
<li class="">
<a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
<img src="images/img.jpg" alt="">
<span class=" fa fa-angle-down"></span>
</a>
<ul class="dropdown-menu dropdown-usermenu pull-right">

</li>
<li><a href="logout.php"><i class="fa fa-sign-out pull-right"></i> Log Out</a></li>
</ul>
</li>


</ul>
</li>
</ul>
</nav>
</div>
</div>
<!-- /top navigation -->

<!-- page content -->
<div class="right_col" role="main">

<h3>BIND AGENT </h3>









<?php
if($_SESSION['user_name']=="lowela" || $_SESSION['user_name']=="marie" || $_SESSION['user_name']=="superadmin88" || $_SESSION['user_name']=="lj" ){
?>
<?PHP } ?>  



<div class="row">

<div class="col-md-12 col-xs-12">
<div class="x_panel">

<div class="x_content">

<form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post">

<div class="form-group">
<div class="col-md-4 col-sm-4 col-xs-4 form-group">
<input type="text" placeholder="search agent" class="form-control" name="search1" >
</div>

<div class="col-md-6 col-sm-12 col-xs-12 form-group">
<button type="submit" class="btn btn-info"  name="SearchME" >Search Agent</button>

</div>
</form>  

<div class="clearfix"></div><div class="clearfix"></div>
 </br></br></br>
 
 
 
<div class="row">

<div class="col-md-3 col-xs-3">

<?php
include'db.php';

?>


<form action="" method="post" action="">
<?php

echo'<select  class="selectpicker" single data-live-search="true"  name="agent_id"  style="width:200px">
<option></option>';

$usertype = mysql_query("SELECT * FROM recruitment_agent  ORDER BY agent_first asc");

while($rows=mysql_fetch_array($usertype))

{
echo'<option value="'.$rows["agent_id"].'"> '.$rows["agent_first"].' '.$rows["agent_last"].'</option>';
}
echo'</optgroup>';



ECHO'</select>';
?>

<a href="delete_methode_link" onclick="return confirm('Are you sure you want to Save?');">		
<button type="submit" class="btn btn-warning btn-sm pull-right"  name="updateapproval1" style="border:BORDER 2PX SOLID black;color:white">Bind Data</button>
</a>


</div>
</div>




<div class="clearfix"></div><div class="clearfix"></div>	

<style>
#example-checkbox table td,th{
font-size:11px;	
}
</style>

<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>
<th >#</th>
<th >NAME</th>
<th >AGENT</th>
<th >FINAL AGENT</th>
<th ></th>
<th ></th>
</tr>
</thead>
<tbody>
<?php	



if(isset($_POST['updateapproval1'])){
foreach ($_POST['delall'] as $delall) {
        $app =mysql_query("UPDATE applicant
        SET  applicant_source =".$_POST['agent_id']."
        where applicant_id= ".$delall)

or die ("cannot Update data");
}
 echo"<p style='color:white;BACKGROUND:#DC143C;padding:10px;FONT-SIZE:14PX;WIDTH:400px'>Succesfully UPDATE agent ..</p>";
            echo $delall;
    
}






$app = mysql_query("SELECT *
FROM applicant
where singil=1
group by applicant_remarks
order by applicant_id asc");



if(isset($_POST['SearchME']))
{

$app = mysql_query("SELECT *
FROM applicant
where
singil=1
AND applicant_remarks LIKE '%".$_POST['search1']."%' ");

}

while($row=mysql_fetch_array($app))
{

$countmein = mysql_query("SELECT count(applicant_id) as alls,applicant.* FROM applicant
where  applicant_remarks='".$row['applicant_remarks']."'
AND singil=1");
$countmein1 = mysql_fetch_array($countmein);


$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$row1=mysql_fetch_array($app1);



echo'<tr style="">';
echo'<td><input type="checkbox" name="delall[]" value="'.$row['applicant_id'].'"> '.$row['applicant_id'].'</td>';

echo'<td>'.$row["applicant_first"].' '.$row["applicant_middle"].'  '.$row["applicant_last"].'</td>';
echo'<td>'.$row["applicant_remarks"].'</td>';
echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$row1["agent_first"].' '.$row1["agent_last"].' - '.$row["applicant_source"].'</td>';

echo'<td>'.$countmein1["alls"].'</td>';
echo'<td>'.$row["singil"].'</td>';
echo'</tr>';

$totalme=$total_reim+$totalme;
}
?>
</tbody>


</table>

</form>	



<div class="ln_solid"></div>

</div>
</div>
</div>  

</div>			


<!-- /footer content -->
</div>
</div>
<!-- jQuery -->
<script src="../vendors/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Skycons -->
<script src="../vendors/skycons/skycons.js"></script>

<!-- Custom Theme Scripts -->
<script src="../build/js/custom.min.js"></script>

</body>





<!-- Propeller Data table js-->
<script type="text/javascript" language="javascript" src="jsdatatables.js"></script>
<script>
//Propeller Customised Javascript code 
$(document).ready(function() {
$('#example-checkbox').DataTable({
responsive: false,
columnDefs: [ {
orderable: false,
className: 'select-checkbox',
targets:0,
} ],
select: {
style: 'multi',
selector: 'td:first-child'
},
order: [ 4, 'desc' ],
bFilter: true,
bLengthChange: true,
pagingType: "simple",
"paging": true,
"searching": true,
"language": {
"info": " _START_ - _END_ of _TOTAL_ ",
"sLengthMenu": "<span class='custom-select-title'>Rows per page:</span> <span class='custom-select'> _MENU_ </span>",
"sSearch": "",
"sSearchPlaceholder": "Search",
"paginate": {
"sNext": " ",
"sPrevious": " "
},
},
dom:
"<'row'<'col-sm-12'><'search-paper pmd-textfield'f>>" +
"<'custom-select-info'<'custom-select-item'><'custom-select-action'>>" +
"<'row'<'col-sm-12'tr>>" +
"<'pmd-card-footer' <'pmd-datatable-pagination' l i p>>",
});

/// Select value
$('.custom-select-info').hide();

$('#example-checkbox tbody').on( 'click', 'tr', function () {
if ( $(this).hasClass('selected') ) {
var rowinfo = $(this).closest('.dataTables_wrapper').find('.select-info').text();
$(this).closest('.dataTables_wrapper').find('.custom-select-info .custom-select-item').text(rowinfo);
if ($(this).closest('.dataTables_wrapper').find('.custom-select-info .custom-select-item').text() != null){
$(this).closest('.dataTables_wrapper').find('.custom-select-info').show();
//show delet button
} else{
$(this).closest('.dataTables_wrapper').find('.custom-select-info').hide();
}
}
else {
var rowinfo = $(this).closest('.dataTables_wrapper').find('.select-info').text();
$(this).closest('.dataTables_wrapper').find('.custom-select-info .custom-select-item').text(rowinfo);
}
if($('#example-checkbox').find('.selected').length == 0){
$(this).closest('.dataTables_wrapper').find('.custom-select-info').hide();
}
} );
$("div.data-table-title").html('');
$(".custom-select-action").html('<button class="btn btn-sm pmd-btn-fab pmd-btn-flat pmd-ripple-effect btn-primary" type="button"><i class="material-icons pmd-sm">delete</i></button><button class="btn btn-sm pmd-btn-fab pmd-btn-flat pmd-ripple-effect btn-primary" type="button"><i class="material-icons pmd-sm">more_vert</button>');

} );
</script>


