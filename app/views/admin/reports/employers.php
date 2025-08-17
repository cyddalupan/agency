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
                        <th>Employer #</th>
                        <th>Agency/Employer</th>
                        <th>Contact person</th>
                        <th>Workers</th>
                        <th>Marketing Agency</th>
                        <th>Marketing Agent</th>
                        <th>Remarks</th>
                        <th>Last modified</th>
                    </tr>
                </tbody>
                <tbody>
                    <?php if ( count( $employers ) == 0 ): ?>
                    <tr>
                        <td colspan="10" align="center">-- No records --</td>
                    </tr>
                    <?php endif; ?>
                	<?php $ctr = 0; ?>
                	<?php foreach ( $employers as $employer ): ?>
                    <?php $ctr ++; ?>
                    <tr>
                    	<td><?php echo $ctr; ?></td>
                        <td>STME-<?php echo str_pad( $employer['employer_id'], 7, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo $employer['employer_name']; ?></td>
                        <td><?php echo $employer['employer_contact_person']; ?></td>
                        <td><?php echo $employer['workers']; ?></td>
                        <td><?php echo $employer['agency_name']; ?></td>
                        <td><?php echo $employer['agent_first'].' '.$employer['agent_last']; ?></td>
                        <td><?php echo $employer['employer_remarks']; ?></td>
                        <td><?php echo fdate( 'Y-m-d h:ia', $employer['employer_updated'] ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div>
                <p>Total employers: <strong><?php echo number_format( count( $employers ) ); ?></strong>.</p>
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