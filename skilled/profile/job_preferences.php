<!-- Job Preferences and Skills -->
<div class="mb-3">
    <label for="positionType" class="form-label">Position Type</label>
    <input type="text" class="form-control" id="positionType" name="applicant_position_type" value="<?php echo htmlspecialchars($user_data['applicant_position_type']); ?>">
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="currency" class="form-label">Currency</label>
        <input type="text" class="form-control" id="currency" name="currency" value="<?php echo htmlspecialchars($user_data['currency']); ?>">
    </div>
    <div class="col-md-6 mb-3">
        <label for="expectedSalary" class="form-label">Expected Salary</label>
        <input type="number" class="form-control" id="expectedSalary" name="applicant_expected_salary" value="<?php echo htmlspecialchars($user_data['applicant_expected_salary']); ?>">
    </div>
</div>
<div class="mb-3">
    <label for="preferredCountry" class="form-label">Preferred Country</label>
    <input type="text" class="form-control" id="preferredCountry" name="preferredCountry" value="<?php echo htmlspecialchars($user_data['applicant_preferred_country']); ?>">
</div>
<div class="mb-3">
    <label for="otherSkills" class="form-label">Other Skills</label>
    <textarea class="form-control" id="otherSkills" name="otherSkills" rows="3"><?php echo htmlspecialchars($user_data['applicant_other_skills']); ?></textarea>
</div>
<div class="mb-3">
    <label for="personalAbilities" class="form-label">Personal Abilities</label>
    <textarea class="form-control" id="personalAbilities" name="personalAbilities" rows="3"><?php echo htmlspecialchars($user_data['personalAbilities']); ?></textarea>
</div>
