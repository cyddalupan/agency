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
            <p>&nbsp;</p>
            <p class="employer">Marketing Agent: <?php echo $agent['agent_first'].' '.$agent['agent_last']; ?></p>
        </div>
    	<!-- endOf: #header2 -->   
        
        <!-- #content -->
        <div id="content">
        	<table id="applicants">
                <thead>
                    <tr>
                        <th width="1%">#</th>
                        <th>Employer #</th>
                        <th>Employer name</th>
                        <th>Contact person</th>
                        <th>Country</th>
                        <th>Created</th>
                    </tr>                    
                </thead>
            	<tbody>
                    <?php if ( count( $agent['employers'] ) == 0 ): ?>
                    <tr>
                        <td colspan="6" align="center">-- No records --</td>
                    </tr>
                    <?php endif; ?>
                    <?php $ctr = 0; ?>
                	<?php foreach ( $agent['employers'] as $employer ): ?>
                    <?php $ctr ++; ?>
                    <tr>
                    	<td><?php echo $ctr; ?></td>
                        <td><?php echo $employer['employer_no']; ?></td>
                        <td><?php echo $employer['employer_name']; ?></td>
                        <td><?php echo $employer['employer_contact_person']; ?></td>
                        <td><?php echo $employer['country_name']; ?></td>
                        <td><?php echo fdate( 'Y-m-d h:ia', $employer['employer_created'] ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

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
    <script type="text/javascript">
        $(document).ready(function() {
            $('a#btn-print').on('click', function( event ) {

                event.preventDefault(); 

                var options = { 
                    mode:       'popup', 
                    popClose:   false, 
                    extraCss:   '',
                    retainAttr: ['class', 'id', 'style'],
                    extraHead:  '<meta charset="utf-8" />,<meta http-equiv="X-UA-Compatible" content="IE=edge"/>'
                };

                $('#wrapper').printArea( options );
            });     
        });
    </script>
</body>
</html>