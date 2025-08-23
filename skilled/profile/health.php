<!-- Health and Records -->
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="hasTattoo" name="hasTattoo" value="1" <?php echo ($user_data['t1'] == 1) ? 'checked' : ''; ?>>
    <label class="form-check-label" for="hasTattoo">
        Do you have tattoo?
    </label>
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="hasHemorrhoids" name="hasHemorrhoids" value="1" <?php echo ($user_data['t2'] == 1) ? 'checked' : ''; ?>>
    <label class="form-check-label" for="hasHemorrhoids">
        Do you have Hemmorhoids?
    </label>
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="hasDiabetes" name="hasDiabetes" value="1" <?php echo ($user_data['t3'] == 1) ? 'checked' : ''; ?>>
    <label class="form-check-label" for="hasDiabetes">
        Do you have Diabetes?
    </label>
</div>

<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="hasHighBlood" name="hasHighBlood" value="1" <?php echo ($user_data['t4'] == 1) ? 'checked' : ''; ?>>
    <label class="form-check-label" for="hasHighBlood">
        Do you have High Blood?
    </label>
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="hasHeartProblem" name="hasHeartProblem" value="1" <?php echo ($user_data['t5'] == 1) ? 'checked' : ''; ?>>
    <label class="form-check-label" for="hasHeartProblem">
        Do you have Heart Problem?
    </label>
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="hasAllergies" name="hasAllergies" value="1" <?php echo ($user_data['t6'] == 1) ? 'checked' : ''; ?>>
    <label class="form-check-label" for="hasAllergies">
        Do you have Allergies?
    </label>
</div>

<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="hasCyst" name="hasCyst" value="1" <?php echo ($user_data['t7'] == 1) ? 'checked' : ''; ?>>
    <label class="form-check-label" for="hasCyst">
        Do you have Cyst?
    </label>
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="hasAsthma" name="hasAsthma" value="1" <?php echo ($user_data['t8'] == 1) ? 'checked' : ''; ?>>
    <label class="form-check-label" for="hasAsthma">
        Do you have Asthma?
    </label>
</div>

<div class="mb-3">
    <label for="tattooNeck" class="form-label">Tattoo Location: NECK</label>
    <input type="text" class="form-control" id="tattooNeck" name="tattooNeck" value="<?php echo htmlspecialchars($user_data['is_manicure']); ?>">
</div>
<div class="mb-3">
    <label for="tattooBack" class="form-label">Tattoo Location: BACK</label>
    <input type="text" class="form-control" id="tattooBack" name="tattooBack" value="<?php echo htmlspecialchars($user_data['is_massage']); ?>">
</div>
<div class="mb-3">
    <label for="tattooHands" class="form-label">Tattoo Location: HANDS</label>
    <input type="text" class="form-control" id="tattooHands" name="tattooHands" value="<?php echo htmlspecialchars($user_data['is_blower']); ?>">
</div>

<div class="mb-3">
    <label for="tattooThigh" class="form-label">Tattoo Location: THIGH</label>
    <input type="text" class="form-control" id="tattooThigh" name="tattooThigh" value="<?php echo htmlspecialchars($user_data['is_coloring']); ?>">
</div>
<div class="mb-3">
    <label for="tattooLegs" class="form-label">Tattoo Location: LEGS</label>
    <input type="text" class="form-control" id="tattooLegs" name="tattooLegs" value="<?php echo htmlspecialchars($user_data['is_sewing']); ?>">
</div>
<div class="mb-3">
    <label for="tattooFoot" class="form-label">Tattoo Location: FOOT</label>
    <input type="text" class="form-control" id="tattooFoot" name="tattooFoot" value="<?php echo htmlspecialchars($user_data['is_computer']); ?>">
</div>

<div class="mb-3">
    <label for="medicalHistoryOthers" class="form-label">Others (Medical History)</label>
    <textarea class="form-control" id="medicalHistoryOthers" name="medicalHistoryOthers" rows="3"><?php echo htmlspecialchars($user_data['applicant_jobs']); ?></textarea>
</div>

<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="covidVaccin" name="covidVaccin" value="1" <?php echo ($user_data['covidme'] == 1) ? 'checked' : ''; ?>>
    <label class="form-check-label" for="covidVaccin">
        COVID VACCIN
    </label>
</div>
<div class="mb-3">
    <label for="vaccineName" class="form-label">Vaccine name</label>
    <input type="text" class="form-control" id="vaccineName" name="vaccineName" value="<?php echo htmlspecialchars($user_data['covid_name']); ?>">
</div>
<div class="mb-3">
    <label for="firstDose" class="form-label">1st Dose</label>
    <input type="date" class="form-control" id="firstDose" name="firstDose" value="<?php echo htmlspecialchars($user_data['covid_date']); ?>">
</div>

<div class="mb-3">
    <label for="secondDose" class="form-label">2nd Dose</label>
    <input type="date" class="form-control" id="secondDose" name="secondDose" value="<?php echo htmlspecialchars($user_data['covid_date2']); ?>">
</div>
<div class="mb-3">
    <label for="vaccineLocation" class="form-label">Location</label>
    <input type="text" class="form-control" id="vaccineLocation" name="vaccineLocation" value="<?php echo htmlspecialchars($user_data['covid_loc']); ?>">
</div>
<div class="mb-3">
    <label for="boqCard" class="form-label">BOQ card</label>
    <input type="text" class="form-control" id="boqCard" name="boqCard" value="<?php echo htmlspecialchars($user_data['covid_yellow']); ?>">
</div>

<div class="mb-3">
    <label for="vaccineCert" class="form-label">Vaccine Cert</label>
    <input type="text" class="form-control" id="vaccineCert" name="vaccineCert" value="<?php echo htmlspecialchars($user_data['covid_cert']); ?>">
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="booster" name="booster" value="1" <?php echo ($user_data['covidb1'] == 1) ? 'checked' : ''; ?>>
    <label class="form-check-label" for="booster">
        Booster
    </label>
</div>
<div class="mb-3">
    <label for="boosterName" class="form-label">Booster Name</label>
    <input type="text" class="form-control" id="boosterName" name="boosterName" value="<?php echo htmlspecialchars($user_data['covidb2']); ?>">
</div>

<div class="mb-3">
    <label for="boosterDate" class="form-label">Booster Date</label>
    <input type="date" class="form-control" id="boosterDate" name="boosterDate" value="<?php echo htmlspecialchars($user_data['covidb3']); ?>">
</div>
