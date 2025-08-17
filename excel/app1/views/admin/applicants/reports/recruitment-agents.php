<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo $app->getTitle(); ?></title>
	<?php $app->renderStyles(); ?>
</head>
<body>
	
    <!-- #wrapper -->
    <div id="wrapper">
    
    	<!-- #header -->
    	<div id="header">
        	<h1><?php echo $app->getInfo()['applicationDescription']; ?></h1>
        </div>
    	<!-- endOf: #header -->
        
    	<!-- #header2 -->
        <div id="header2">
        	<h2><?php echo $app->getTitle(); ?></h2>
        </div>
    	<!-- endOf: #header2 -->   
        
        <!-- #content -->
        <div id="content">
        	<table id="applicants">
            	<tbody>
                	<tr>
                    	<th width="1%">#</th>
                        <th>Agent</th>
                        <th>Applicants</th>
                        <th>Remarks</th>
                        <th>Last modified</th>
                    </tr>
                </tbody>
                <tbody>
                    <?php if ( count( $agents ) == 0 ): ?>
                    <tr>
                        <td colspan="10" align="center">-- No records --</td>
                    </tr>
                    <?php endif; ?>
                	<?php $ctr = 0; ?>
                	<?php foreach ( $agents as $agent ): ?>
                    <?php $ctr ++; ?>
                    <tr>
                    	<td><?php echo $ctr; ?></td>
                        <td><?php echo $agent['agent_first'].' '.$agent['agent_last']; ?></td>
                        <td><?php echo (int) $agent['applicants']; ?></td>
                        <td><?php echo $agent['agent_remarks']; ?></td>
                        <td><?php echo fdate( 'Y-m-d h:ia', $agent['agent_updated'] ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div>
                <p>Total agents: <strong><?php echo number_format( count( $agents ) ); ?></strong>.</p>
            </div>
        </div>
    	<!-- endOf: #content -->   
        
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