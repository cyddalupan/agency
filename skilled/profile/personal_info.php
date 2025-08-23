<!-- Personal Information -->
<div class="row">
    <div class="col-md-4 mb-3">
        <label for="firstName" class="form-label">First Name</label>
        <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($user_data['applicant_first']); ?>" required>
    </div>
    <div class="col-md-4 mb-3">
        <label for="middleName" class="form-label">Middle Name</label>
        <input type="text" class="form-control" id="middleName" name="middleName" value="<?php echo htmlspecialchars($user_data['applicant_middle']); ?>">
    </div>
    <div class="col-md-4 mb-3">
        <label for="lastName" class="form-label">Last Name</label>
        <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($user_data['applicant_last']); ?>" required>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="gender" class="form-label">Gender</label>
        <select class="form-select" id="gender" name="gender" required>
            <option value="">Select Gender</option>
            <option value="Male" <?php echo ($user_data['applicant_gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
            <option value="Female" <?php echo ($user_data['applicant_gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
            <option value="Other" <?php echo ($user_data['applicant_gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="nationality" class="form-label">Nationality</label>
        <input type="text" class="form-control" id="nationality" name="nationality" value="<?php echo htmlspecialchars($user_data['applicant_nationality']); ?>" required>
    </div>
    <div class="col-md-4 mb-3">
        <label for="civilStatus" class="form-label">Civil Status</label>
        <select class="form-select" id="civilStatus" name="civilStatus" required>
            <option value="">Select Status</option>
            <option value="Single" <?php echo ($user_data['applicant_civil_status'] == 'Single') ? 'selected' : ''; ?>>Single</option>
            <option value="Married" <?php echo ($user_data['applicant_civil_status'] == 'Married') ? 'selected' : ''; ?>>Married</option>
            <option value="Divorced" <?php echo ($user_data['applicant_civil_status'] == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
            <option value="Widowed" <?php echo ($user_data['applicant_civil_status'] == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="birthdate" class="form-label">Birthdate</label>
        <input type="date" class="form-control" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($user_data['applicant_birthdate']); ?>" required>
    </div>
    <div class="col-md-6 mb-3">
        <label for="age" class="form-label">Age</label>
        <input type="number" class="form-control" id="age" name="age" value="<?php echo htmlspecialchars($user_data['applicant_age']); ?>" required>
    </div>
</div>

<div class="mb-3">
    <label for="placeOfBirth" class="form-label">Place of Birth</label>
    <input type="text" class="form-control" id="placeOfBirth" name="placeOfBirth" value="<?php echo htmlspecialchars($user_data['contacts4']); ?>">
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="contactNumber" class="form-label">Contact Number</label>
        <input type="tel" class="form-control" id="contactNumber" name="contactNumber" value="<?php echo htmlspecialchars($user_data['applicant_contacts']); ?>" required>
    </div>
    <div class="col-md-4 mb-3">
        <label for="otherContactNumber" class="form-label">Other Contact Number</label>
        <input type="tel" class="form-control" id="otherContactNumber" name="otherContactNumber" value="<?php echo htmlspecialchars($user_data['contacts2']); ?>">
    </div>
    <div class="col-md-4 mb-3">
        <label for="anotherContactNumber" class="form-label">Another Contact Number</label>
        <input type="tel" class="form-control" id="anotherContactNumber" name="anotherContactNumber" value="<?php echo htmlspecialchars($user_data['contacts3']); ?>">
    </div>
</div>

<div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_data['applicant_email']); ?>" required>
</div>

<div class="mb-3">
    <label for="address" class="form-label">Address</label>
    <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($user_data['applicant_address']); ?></textarea>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="height" class="form-label">Height (cm)</label>
        <input type="number" class="form-control" id="height" name="height" value="<?php echo htmlspecialchars($user_data['applicant_height']); ?>">
    </div>
    <div class="col-md-6 mb-3">
        <label for="weight" class="form-label">Weight (kg)</label>
        <input type="number" class="form-control" id="weight" name="weight" value="<?php echo htmlspecialchars($user_data['applicant_weight']); ?>">
    </div>
</div>

<div class="mb-3">
    <label for="religion" class="form-label">Religion</label>
    <input type="text" class="form-control" id="religion" name="religion" value="<?php echo htmlspecialchars($user_data['applicant_religion']); ?>">
</div>

<div class="mb-3">
    <label for="languages" class="form-label">Languages</label>
    <input type="text" class="form-control" id="languages" name="languages" value="<?php echo htmlspecialchars($user_data['applicant_languages']); ?>">
</div>

<div class="mb-3">
    <label for="password" class="form-label">Password</label>
    <input type="password" class="form-control" id="password" name="password" value="" placeholder="Leave blank to keep current password">
</div>

<div class="mb-3">
    <label for="dateApplied" class="form-label">Date Applied</label>
    <input type="date" class="form-control" id="dateApplied" name="dateApplied" value="<?php echo htmlspecialchars($user_data['date_applied']); ?>">
</div>

<div class="mb-3">
    <label for="trainingBranch" class="form-label">Training Branch</label>
    <input type="text" class="form-control" id="trainingBranch" name="trainingBranch" value="<?php echo htmlspecialchars($user_data['applicant_training_branch']); ?>">
</div>

<div class="mb-3">
    <label for="source" class="form-label">Source</label>
    <input type="text" class="form-control" id="source" name="source" value="<?php echo htmlspecialchars($user_data['applicant_source']); ?>">
</div>

<div class="mb-3">
    <label for="recruitmentAgent" class="form-label">Recruitment Agent</label>
    <input type="text" class="form-control" id="recruitmentAgent" name="recruitmentAgent" value="<?php echo htmlspecialchars($user_data['applicant_recruitment_agent']); ?>">
</div>

<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="repatriated" name="repatriated" value="1" <?php echo ($user_data['repat_checkbox'] == 1) ? 'checked' : ''; ?>>
    <label class="form-check-label" for="repatriated">
        Repatriated
    </label>
</div>

<div class="mb-3">
    <label for="repatriationDate" class="form-label">Repatriation Date</label>
    <input type="date" class="form-control" id="repatriationDate" name="repatriationDate" value="<?php echo htmlspecialchars($user__data['repat_date']); ?>">
</div>

<div class="mb-3">
    <label for="applicantEx" class="form-label">Applicant Firstimer / Ex Abroad</label>
    <input type="text" class="form-control" id="applicantEx" name="applicantEx" value="<?php echo htmlspecialchars($user_data['applicant_ex']); ?>">
</div>

<div class="mb-3">
    <label for="branch" class="form-label">Branch</label>
    <input type="text" class="form-control" id="branch" name="branch" value="<?php echo htmlspecialchars($user_data['typess']); ?>">
</div>

<div class="mb-3">
    <label for="transferBranch" class="form-label">Transfer Branch</label>
    <input type="text" class="form-control" id="transferBranch" name="transferBranch" value="<?php echo htmlspecialchars($user_data['typess1']); ?>">
</div>

<div class="mb-3">
    <label for="waitlist" class="form-label">Waitlist</label>
    <input type="text" class="form-control" id="waitlist" name="waitlist" value="<?php echo htmlspecialchars($user_data['applicant_ppt_pay']); ?>">
</div>

<div class="mb-3">
    <label for="otherSource" class="form-label">Other Source</label>
    <input type="text" class="form-control" id="otherSource" name="otherSource" value="<?php echo htmlspecialchars($user_data['other_source']); ?>">
</div>

<div class="mb-3">
    <label for="interviewBy" class="form-label">Interview By</label>
    <input type="text" class="form-control" id="interviewBy" name="interviewBy" value="<?php echo htmlspecialchars($user_data['date-by']); ?>">
</div>

<!-- Applicant Remarks -->
<div class="mb-3">
    <label for="remarksForResume" class="form-label">Remarks For Resume</label>
    <textarea class="form-control" id="remarksForResume" name="remarksForResume" rows="3"><?php echo htmlspecialchars($user_data['remarks_3']); ?></textarea>
</div>
<div class="mb-3">
    <label for="remarks" class="form-label">Remarks</label>
    <textarea class="form-control" id="remarks" name="remarks" rows="3"><?php echo htmlspecialchars($user_data['fra_remarks']); ?></textarea>
</div>
