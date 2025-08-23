<!-- Certificates -->
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="certificate_prc_type" class="form-label">Licensure Examination Type</label>
        <input type="text" class="form-control" id="certificate_prc_type" name="certificate_prc_type" value="<?php echo htmlspecialchars($user_data['certificate_prc_type']); ?>">
    </div>
    <div class="col-md-6 mb-3">
        <label for="certificate_prc_rating" class="form-label">PRC Rating</label>
        <input type="text" class="form-control" id="certificate_prc_rating" name="certificate_prc_rating" value="<?php echo htmlspecialchars($user_data['certificate_prc_rating']); ?>">
    </div>
</div>
<div class="mb-3">
    <label for="certificate_prc_take" class="form-label">Date Taken (PRC)</label>
    <input type="date" class="form-control" id="certificate_prc_take" name="certificate_prc_take" value="<?php echo htmlspecialchars($user_data['certificate_prc_take']); ?>">
</div>
<div class="mb-3">
    <label for="certificate_saudi_id" class="form-label">Saudi Council ID</label>
    <input type="text" class="form-control" id="certificate_saudi_id" name="certificate_saudi_id" value="<?php echo htmlspecialchars($user_data['certificate_saudi_id']); ?>">
</div>
<div class="mb-3">
    <label for="certificate_dha" class="form-label">DHA</label>
    <input type="text" class="form-control" id="certificate_dha" name="certificate_dha" value="<?php echo htmlspecialchars($user_data['certificate_dha']); ?>">
</div>
<div class="mb-3">
    <label for="certificate_ksa" class="form-label">KSA Prometrics</label>
    <input type="text" class="form-control" id="certificate_ksa" name="certificate_ksa" value="<?php echo htmlspecialchars($user_data['certificate_ksa']); ?>">
</div>
<div class="mb-3">
    <label for="certificate_haad" class="form-label">HAAD</label>
    <input type="text" class="form-control" id="certificate_haad" name="certificate_haad" value="<?php echo htmlspecialchars($user_data['certificate_haad']); ?>">
</div>
<div class="mb-3">
    <label for="certificate_qatar" class="form-label">QATAR Prometrics</label>
    <input type="text" class="form-control" id="certificate_qatar" name="certificate_qatar" value="<?php echo htmlspecialchars($user_data['certificate_qatar']); ?>">
</div>
<div class="mb-3">
    <label for="certificate_nclex" class="form-label">NCLEX</label>
    <input type="text" class="form-control" id="certificate_nclex" name="certificate_nclex" value="<?php echo htmlspecialchars($user_data['certificate_nclex']); ?>">
</div>
<div class="mb-3">
    <label for="certificate_nclex_exam" class="form-label">NCLEX Date Taken</label>
    <input type="date" class="form-control" id="certificate_nclex_exam" name="certificate_nclex_exam" value="<?php echo htmlspecialchars($user_data['certificate_nclex_exam']); ?>">
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="certificate_ielts" class="form-label">IELTS Score</label>
        <input type="text" class="form-control" id="certificate_ielts" name="certificate_ielts" value="<?php echo htmlspecialchars($user_data['certificate_ielts']); ?>">
    </div>
    <div class="col-md-6 mb-3">
        <label for="certificate_ielts_overall" class="form-label">IELTS Overall</label>
        <input type="text" class="form-control" id="certificate_ielts_overall" name="certificate_ielts_overall" value="<?php echo htmlspecialchars($user_data['certificate_ielts_overall']); ?>">
    </div>
</div>
<div class="mb-3">
    <label for="certificate_ielts_exam" class="form-label">IELTS Date Taken</label>
    <input type="date" class="form-control" id="certificate_ielts_exam" name="certificate_ielts_exam" value="<?php echo htmlspecialchars($user_data['certificate_ielts_exam']); ?>">
</div>
<div class="mb-3">
    <label for="certificate_cgfns" class="form-label">CGFNS</label>
    <input type="text" class="form-control" id="certificate_cgfns" name="certificate_cgfns" value="<?php echo htmlspecialchars($user_data['certificate_cgfns']); ?>">
</div>
<div class="mb-3">
    <label for="certificate_cgfns_id" class="form-label">CGFNS ID #</label>
    <input type="text" class="form-control" id="certificate_cgfns_id" name="certificate_cgfns_id" value="<?php echo htmlspecialchars($user_data['certificate_cgfns_id']); ?>">
</div>
<div class="mb-3">
    <label for="certificate_cgfns_exam" class="form-label">CGFNS Date Taken</label>
    <input type="date" class="form-control" id="certificate_cgfns_exam" name="certificate_cgfns_exam" value="<?php echo htmlspecialchars($user_data['certificate_cgfns_exam']); ?>">
</div>
<div class="mb-3">
    <label for="certificate_vsh_exam" class="form-label">Visa Screen Holder Exam Date</label>
    <input type="date" class="form-control" id="certificate_vsh_exam" name="certificate_vsh_exam" value="<?php echo htmlspecialchars($user_data['certificate_vsh_exam']); ?>">
</div>
<div class="mb-3">
    <label for="swab" class="form-label">SWAB TEST</label>
    <input type="text" class="form-control" id="swab" name="swab" value="<?php echo htmlspecialchars($user_data['swab']); ?>">
</div>
<div class="mb-3">
    <label for="swab_date" class="form-label">SWAB TEST Date</label>
    <input type="date" class="form-control" id="swab_date" name="swab_date" value="<?php echo htmlspecialchars($user_data['swab_date']); ?>">
</div>
<div class="mb-3">
    <label for="mmr" class="form-label">MMR VACCINE</label>
    <input type="text" class="form-control" id="mmr" name="mmr" value="<?php echo htmlspecialchars($user_data['mmr']); ?>">
</div>
<div class="mb-3">
    <label for="medical-clinic" class="form-label">Medical clinic</label>
    <input type="text" class="form-control" id="medical-clinic" name="medical-clinic" value="<?php echo htmlspecialchars($user_data['medical-clinic']); ?>">
</div>
<div class="mb-3">
    <label for="medical-exam-date" class="form-label">Medical Exam date</label>
    <input type="date" class="form-control" id="medical-exam-date" name="medical-exam-date" value="<?php echo htmlspecialchars($user_data['medical-exam-date']); ?>">
</div>
<div class="mb-3">
    <label for="medical_fit" class="form-label">Date FTW</label>
    <input type="date" class="form-control" id="medical_fit" name="medical_fit" value="<?php echo htmlspecialchars($user_data['medical_fit']); ?>">
</div>
<div class="mb-3">
    <label for="medical-result" class="form-label">Medical result</label>
    <input type="text" class="form-control" id="medical-result" name="medical-result" value="<?php echo htmlspecialchars($user_data['medical-result']); ?>">
</div>
<div class="mb-3">
    <label for="medical-remarks" class="form-label">Medical remarks</label>
    <textarea class="form-control" id="medical-remarks" name="medical-remarks" rows="3"><?php echo htmlspecialchars($user_data['medical-remarks']); ?></textarea>
</div>
<div class="mb-3">
    <label for="medical-expiration" class="form-label">Medical expiration</label>
    <input type="date" class="form-control" id="medical-expiration" name="medical-expiration" value="<?php echo htmlspecialchars($user_data['medical-expiration']); ?>">
</div>
<div class="mb-3">
    <label for="pt-result" class="form-label">Pre-Departure PT result</label>
    <input type="text" class="form-control" id="pt-result" name="pt-result" value="<?php echo htmlspecialchars($user_data['pt-result']); ?>">
</div>
<div class="mb-3">
    <label for="pt-result-date" class="form-label">PT date result</label>
    <input type="date" class="form-control" id="pt-result-date" name="pt-result-date" value="<?php echo htmlspecialchars($user_data['pt-result-date']); ?>">
</div>
<div class="mb-3">
    <label for="omma" class="form-label">OMMA</label>
    <input type="text" class="form-control" id="omma" name="omma" value="<?php echo htmlspecialchars($user_data['omma']); ?>">
</div>
<div class="mb-3">
    <label for="omma_date" class="form-label">OMMA Date</label>
    <input type="date" class="form-control" id="omma_date" name="omma_date" value="<?php echo htmlspecialchars($user_data['omma_date']); ?>">
</div>
<div class="mb-3">
    <label for="polio" class="form-label">POLIO VACCINE</label>
    <input type="text" class="form-control" id="polio" name="polio" value="<?php echo htmlspecialchars($user_data['polio']); ?>">
</div>
<div class="mb-3">
    <label for="polio_date" class="form-label">POLIO VACCINE Date</label>
    <input type="date" class="form-control" id="polio_date" name="polio_date" value="<?php echo htmlspecialchars($user_data['polio_date']); ?>">
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="authenticated-nbi" name="authenticated-nbi" value="1" <?php echo ($user_data['authenticated-nbi'] == 1) ? 'checked' : ''; ?>>
    <label class="form-check-label" for="authenticated-nbi">
        NBI Authenticated
    </label>
</div>
<div class="mb-3">
    <label for="nbi-expired-date" class="form-label">NBI Expired Date</label>
    <input type="date" class="form-control" id="nbi-expired-date" name="nbi-expired-date" value="<?php echo htmlspecialchars($user_data['nbi-expired-date']); ?>">
</div>
<div class="mb-3">
    <label for="insurance" class="form-label">Insurance</label>
    <input type="text" class="form-control" id="insurance" name="insurance" value="<?php echo htmlspecialchars($user_data['insurance']); ?>">
</div>
<div class="mb-3">
    <label for="insurance-no" class="form-label">Insurance No.</label>
    <input type="text" class="form-control" id="insurance-no" name="insurance-no" value="<?php echo htmlspecialchars($user_data['insurance-no']); ?>">
</div>
<div class="mb-3">
    <label for="coe" class="form-label">COE</label>
    <input type="text" class="form-control" id="coe" name="coe" value="<?php echo htmlspecialchars($user_data['coe']); ?>">
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="pdos" name="pdos" value="1" <?php echo ($user_data['pdos'] == 1) ? 'checked' : ''; ?>>
    <label class="form-check-label" for="pdos">
        PDOS
    </label>
</div>
<div class="mb-3">
    <label for="fra_pdos" class="form-label">FRA FULL NAME</label>
    <input type="text" class="form-control" id="fra_pdos" name="fra_pdos" value="<?php echo htmlspecialchars($user_data['fra_pdos']); ?>">
</div>
<div class="mb-3">
    <label for="pdos_date" class="form-label">PDOS DATE</label>
    <input type="date" class="form-control" id="pdos_date" name="pdos_date" value="<?php echo htmlspecialchars($user_data['pdos_date']); ?>">
</div>
<div class="mb-3">
    <label for="pdos_no" class="form-label">PDOS #</label>
    <input type="text" class="form-control" id="pdos_no" name="pdos_no" value="<?php echo htmlspecialchars($user_data['pdos_no']); ?>">
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="tesda" name="tesda" value="1" <?php echo ($user_data['tesda'] == 1) ? 'checked' : ''; ?>>
    <label class="form-check-label" for="tesda">
        TESDA
    </label>
</div>
<div class="mb-3">
    <label for="tesda_name" class="form-label">Training Center</label>
    <input type="text" class="form-control" id="tesda_name" name="tesda_name" value="<?php echo htmlspecialchars($user_data['tesda_name']); ?>">
</div>
<div class="mb-3">
    <label for="tesda_date" class="form-label">TESDA FROM</label>
    <input type="date" class="form-control" id="tesda_date" name="tesda_date" value="<?php echo htmlspecialchars($user_data['tesda_date']); ?>">
</div>
<div class="mb-3">
    <label for="tesda_release" class="form-label">TESDA TO</label>
    <input type="date" class="form-control" id="tesda_release" name="tesda_release" value="<?php echo htmlspecialchars($user_data['tesda_release']); ?>">
</div>
<div class="mb-3">
    <label for="certificate_tesda_assest" class="form-label">TESDA ASSESTMENT</label>
    <input type="date" class="form-control" id="certificate_tesda_assest" name="certificate_tesda_assest" value="<?php echo htmlspecialchars($user_data['certificate_tesda_assest']); ?>">
</div>

<hr>

<h5>Upload Certificates</h5>

<div class="mb-3">
    <label for="prc_license_copy" class="form-label">PRC License Copy</label>
    <input type="file" class="form-control" id="prc_license_copy" data-file-type="prc_license_copy">
</div>
<div class="mb-3">
    <label for="tesda_certificate_copy" class="form-label">TESDA Certificate Copy</label>
    <input type="file" class="form-control" id="tesda_certificate_copy" data-file-type="tesda_certificate_copy">
</div>
<div class="mb-3">
    <label for="other_certificate_copy" class="form-label">Other Certificate Copy</label>
    <input type="file" class="form-control" id="other_certificate_copy" data-file-type="other_certificate_copy">
</div>
<div class="mb-3">
    <label for="birth_certificate_copy" class="form-label">Birth Certificate Copy</label>
    <input type="file" class="form-control" id="birth_certificate_copy" data-file-type="birth_certificate_copy">
</div>
<div class="mb-3">
    <label for="marriage_certificate_copy" class="form-label">Marriage Certificate Copy</label>
    <input type="file" class="form-control" id="marriage_certificate_copy" data-file-type="marriage_certificate_copy">
</div>
<div class="mb-3">
    <label for="diploma_copy" class="form-label">Diploma Copy</label>
    <input type="file" class="form-control" id="diploma_copy" data-file-type="diploma_copy">
</div>
<div class="mb-3">
    <label for="transcript_of_records_copy" class="form-label">Transcript of Records Copy</label>
    <input type="file" class="form-control" id="transcript_of_records_copy" data-file-type="transcript_of_records_copy">
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);
            formData.append('applicant_id', <?php echo $user_id; ?>);
            formData.append('file_type', this.dataset.fileType);

            fetch('upload_file.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('File uploaded successfully!');
                } else {
                    alert('File upload failed: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during file upload.');
            });
        });
    });
});
</script>
