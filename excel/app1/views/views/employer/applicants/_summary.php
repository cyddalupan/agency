<?php
    //declare 
    $oldpositionsummary = ''; 
?>
<div class="col-md-3 col-sm-4">
    <h3>Summary</h3>
    <?php foreach ( $applicants as $applicant ): ?>
    <?php
    //New Position Batch 
    if($applicant['position_name'] != $oldpositionsummary){
    $oldpositionsummary = $applicant['position_name'];
    ?>   
    <p><a href="#path_<?php echo $applicant['position_name']; ?>" class="cydsummary"><?php echo $applicant['position_name']; ?> (<?php echo $poscount[$applicant['position_name']]; ?>)</a></p>
    <?php }//end if ?>
    <?php endforeach; ?>
</div>