<!-- Family Background -->
<div class="mb-3">
    <label for="partnerName" class="form-label">Name of Partner</label>
    <input type="text" class="form-control" id="partnerName" name="partnerName" value="<?php echo htmlspecialchars($user_data['partner_husband']); ?>">
</div>
<div class="mb-3">
    <label for="partnerOccupation" class="form-label">Occupation of Partner</label>
    <input type="text" class="form-control" id="partnerOccupation" name="partnerOccupation" value="<?php echo htmlspecialchars($user_data['partner_occupation']); ?>">
</div>
<div class="mb-3">
    <label for="children" class="form-label">Children(s)</label>
    <input type="text" class="form-control" id="children" name="children" value="<?php echo htmlspecialchars($user_data['children']); ?>">
</div>

<div class="mb-3">
    <label for="motherName" class="form-label">Name of Mother</label>
    <input type="text" class="form-control" id="motherName" name="motherName" value="<?php echo htmlspecialchars($user_data['applicant_mothers']); ?>">
</div>
<div class="mb-3">
    <label for="motherOccupation" class="form-label">Occupation of Mother</label>
    <input type="text" class="form-control" id="motherOccupation" name="motherOccupation" value="<?php echo htmlspecialchars($user_data['occ_of_mom']); ?>">
</div>
<div class="mb-3">
    <label for="fatherName" class="form-label">Name of Father</label>
    <input type="text" class="form-control" id="fatherName" name="fatherName" value="<?php echo htmlspecialchars($user_data['nam_of_fat']); ?>">
</div>

<div class="mb-3">
    <label for="fatherOccupation" class="form-label">Occupation of Father</label>
    <input type="text" class="form-control" id="fatherOccupation" name="fatherOccupation" value="<?php echo htmlspecialchars($user_data['occ_of_fat']); ?>">
</div>
<div class="mb-3">
    <label for="positionInFamily" class="form-label">Your Position in your Family</label>
    <input type="text" class="form-control" id="positionInFamily" name="positionInFamily" value="<?php echo htmlspecialchars($user_data['pos_in_fam']); ?>">
</div>
<div class="mb-3">
    <label for="numBrothers" class="form-label">No. of Brothers</label>
    <input type="number" class="form-control" id="numBrothers" name="numBrothers" value="<?php echo htmlspecialchars($user_data['no_of_bro']); ?>">
</div>

<div class="mb-3">
    <label for="numSisters" class="form-label">No. of Sisters</label>
    <input type="number" class="form-control" id="numSisters" name="numSisters" value="<?php echo htmlspecialchars($user_data['no_of_sis']); ?>">
</div>
<div class="mb-3">
    <label for="relativeName" class="form-label">Who will look after the children when you will be overseas?</label>
    <input type="text" class="form-control" id="relativeName" name="relativeName" value="<?php echo htmlspecialchars($user_data['relative_name']); ?>">
</div>
<div class="mb-3">
    <label for="relativeMobile" class="form-label">Name and Mobile No. of relative to contact?</label>
    <input type="text" class="form-control" id="relativeMobile" name="relativeMobile" value="<?php echo htmlspecialchars($user_data['relative_mobile']); ?>">
</div>

<div class="mb-3">
    <label for="emergencyContactName" class="form-label">Emergency Contact Name</label>
    <input type="text" class="form-control" id="emergencyContactName" name="emergencyContactName" value="<?php echo htmlspecialchars($user_data['applicant_incase_name']); ?>">
</div>
<div class="mb-3">
    <label for="emergencyContactRelationship" class="form-label">Emergency Contact Relationship</label>
    <input type="text" class="form-control" id="emergencyContactRelationship" name="emergencyContactRelationship" value="<?php echo htmlspecialchars($user_data['applicant_incase_relation']); ?>">
</div>
<div class="mb-3">
    <label for="emergencyContactNumber" class="form-label">Emergency Contact Contact No.</label>
    <input type="text" class="form-control" id="emergencyContactNumber" name="emergencyContactNumber" value="<?php echo htmlspecialchars($user_data['applicant_incase_contact']); ?>">
</div>
<div class="mb-3">
    <label for="emergencyContactAddress" class="form-label">Emergency Contact Address</label>
    <textarea class="form-control" id="emergencyContactAddress" name="emergencyContactAddress" rows="3"><?php echo htmlspecialchars($user_data['applicant_incase_address']); ?></textarea>
</div>
