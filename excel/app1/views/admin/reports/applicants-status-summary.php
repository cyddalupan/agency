<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo $app->getTitle(); ?></title>
	<?php $app->renderStyles(); ?>
</head>
<style>


table {
	border:1px solid #ccc;
	
	background-color: #fff;
		margin-bottom: 0;
	border-collapse: collapse;
	border-spacing: 0;
	width:100%;

}
table tr td {
	padding:3px;
	font-size:11px;
	white-space: nowrap;
	border: 1px solid #ddd;
}


</style>

<?php
if(isset($employer['employer_name']))
    $theEmp = $employer['employer_name'];
else
    $theEmp = 'All';

//get count of applicants depending on status
$statuscountArr['Available'] = 0;
$statuscountArr['For Interview'] = 0;
$statuscountArr['Line Up'] = 0;
$statuscountArr['Qualified'] = 0;
$statuscountArr['Not Qualified'] = 0;
$statuscountArr['Selected'] = 0;
$statuscountArr['For Deployment'] = 0;
$statuscountArr['Deployed'] = 0;
$statuscountArr['Others'] = 0;
$sumTotalApp = 0;
foreach ($applicants as $value) {
    switch ($value['applicant_status']) {
        case 0:
            $statuscountArr['Available']++;
            $sumTotalApp++;
            break;
        case 11:
            $statuscountArr['For Interview']++;
            $sumTotalApp++;
            break;
        case 5:
            $statuscountArr['Line Up']++;
            $sumTotalApp++;
            break;
        case 6:
            $statuscountArr['Qualified']++;
            $sumTotalApp++;
            break;
        case 7:
            $statuscountArr['Not Qualified']++;
            $sumTotalApp++;
            break;
        case 4:
            $statuscountArr['Selected']++;
            $sumTotalApp++;
            break;
        case 8:
            $statuscountArr['For Deployment']++;
            $sumTotalApp++;
            break;
        case 9:
            $statuscountArr['Deployed']++;
            $sumTotalApp++;
            break;
        default:
            $statuscountArr['Others']++;
            $sumTotalApp++;
            break;
    }
}
?>
<body>
	
    <!-- #wrapper -->
    <div id="wrapper1" style="border:2px solid black; max-width: 700px; margin:auto;">
    	<!-- #header -->
    	<div id="header">
        	<h1><?php echo $app->getInfo()['applicationDescription']; ?></h1>
        </div>
    	<!-- endOf: #header -->
        
    	<!-- #header2 -->
        <div id="header2">
        	<h1>Summary <?php echo $app->getTitle(); ?></h1>
        	<p>&nbsp;</p>
        	<p class="date-filter">Date: <?php echo fdate( 'F,d Y', $dateFrom); ?> &minus;&minus; <?php echo fdate( 'F,d Y', $dateTo); ?></p>
        </div>
    	<!-- endOf: #header2 --> 
        <table>
            <tr>
                <th>Status</th>
                <th>Gender</th>
                <th>Position</th>
                <th>Employer</th>
                <th>Age</th>
                <th>No</th>
            </tr>
            <?php //all status needed for Summary
            $statuses = ['Available','For Interview','Line Up','Qualified','Not Qualified','Selected','For Deployment','Deployed','Others'];
            foreach ($statuses  as $status) { ?>
            <tr>
                <td><?php echo $status; ?></td>
                <td><?php echo $post['gender']; ?></td>
                <td><?php echo $podByID ?></td>
                <td><?php echo $theEmp; ?></td>
                <td><?php echo $post['age-from']; ?> - <?php echo $post['age-to']; ?></td>
                <td><?php if(isset($statuscountArr[$status]))echo $statuscountArr[$status];else '0'; ?></td>
            </tr>
            <?php }//end status loop ?>
        </table>
        <p style="text-align: right; color: red; margin-right: 20px; margin-top: 5px;">Total of Applicants = <?php echo $sumTotalApp; ?> </p>
        
    </div>
    <!-- endOf: #wrapper -->

    <div style="clear:both" align="center">
        <p>&nbsp;</p>
        <a href="#" id="btn-print" role="button">Print</a>
    </div>        

    <script src="<?php echo $app->getPath()['scripts']; ?>jquery-2.0.3.min.js"></script>
    <?php $app->renderScripts(); ?>
</body>
</html>