<!-- Documents -->
<div class="mb-3">
    <label for="resume" class="form-label">Attached Resume</label>
    <input type="file" class="form-control" id="resume" name="resume">
    <?php if (!empty($user_data['applicant_cv'])):
 ?>
        <small class="form-text text-muted">Current: <a href="<?php echo htmlspecialchars($user_data['applicant_cv']); ?>" target="_blank"><?php echo basename(htmlspecialchars($user_data['applicant_cv'])); ?></a></small>
    <?php else:
 ?>
        <small class="form-text text-muted">No resume attached.</small>
    <?php endif; ?>
</div>

<div class="mb-3">
    <label for="passportNumber" class="form-label">Passport Number</label>
    <input type="text" class="form-control" id="passportNumber" name="passportNumber" value="<?php echo htmlspecialchars($user_data['passport_number']); ?>">
</div>
<div class="mb-3">
    <label for="passportDateIssued" class="form-label">Passport Date issued</label>
    <input type="date" class="form-control" id="passportDateIssued" name="passportDateIssued" value="<?php echo htmlspecialchars($user_data['passport_issue']); ?>">
</div>
<div class="mb-3">
    <label for="passportPlaceIssue" class="form-label">Passport place Issue</label>
    <input type="text" class="form-control" id="passportPlaceIssue" name="passportPlaceIssue" value="<?php echo htmlspecialchars($user_data['passport_issue_place']); ?>">
</div>

<div class="mb-3">
    <label for="passportExpiration" class="form-label">Passport Expiration</label>
    <input type="date" class="form-control" id="passportExpiration" name="passportExpiration" value="<?php echo htmlspecialchars($user_data['passport_expiration']); ?>">
</div>
<div class="mb-3">
    <label for="visaNumber" class="form-label">Visa Number</label>
    <input type="text" class="form-control" id="visaNumber" name="visaNumber" value="<?php echo htmlspecialchars($user_data['applicant_visa_number']); ?>">
</div>
<div class="mb-3">
    <label for="visaExpiry" class="form-label">Visa Expiry</label>
    <input type="date" class="form-control" id="visaExpiry" name="visaExpiry" value="<?php echo htmlspecialchars($user_data['applicant_visa_expiry']); ?>">
</div>

<div class="mb-3">
    <label for="visaDuration" class="form-label">Visa Duration</label>
    <input type="text" class="form-control" id="visaDuration" name="visaDuration" value="<?php echo htmlspecialchars($user_data['cyd_visa_duration']); ?>">
</div>
<div class="mb-3">
    <label for="medicalExpiry" class="form-label">Medical Expiry</label>
    <input type="date" class="form-control" id="medicalExpiry" name="medicalExpiry" value="<?php echo htmlspecialchars($user_data['applicant_medical_expiry']); ?>">
</div>
<div class="mb-3">
    <label for="medicalStatus" class="form-label">Medical Status</label>
    <input type="text" class="form-control" id="medicalStatus" name="medicalStatus" value="<?php echo htmlspecialchars($user_data['applicant_medical_status']); ?>">
</div>

<div class="mb-3">
    <label for="medicalRemarks" class="form-label">Medical Remarks</label>
    <textarea class="form-control" id="medicalRemarks" name="medicalRemarks" rows="3"><?php echo htmlspecialchars($user_data['applicant_medical_remarks']); ?></textarea>
</div>
<div class="mb-3">
    <label for="policeClearanceExpiry" class="form-label">Police Clearance Expiry</label>
    <input type="date" class="form-control" id="policeClearanceExpiry" name="policeClearanceExpiry" value="<?php echo htmlspecialchars($user_data['applicant_police_clearance_expiry']); ?>">
</div>
<div class="mb-3">
    <label for="policeClearanceStatus" class="form-label">Police Clearance Status</label>
    <input type="text" class="form-control" id="policeClearanceStatus" name="policeClearanceStatus" value="<?php echo htmlspecialchars($user_data['applicant_police_clearance_status']); ?>">
</div>

<div class="mb-3">
    <label for="policeClearanceRemarks" class="form-label">Police Clearance Remarks</label>
    <textarea class="form-control" id="policeClearanceRemarks" name="policeClearanceRemarks" rows="3"><?php echo htmlspecialchars($user_data['applicant_police_clearance_remarks']); ?></textarea>
</div>
<div class="mb-3">
    <label for="nbiExpiry" class="form-label">NBI Expiry</label>
    <input type="date" class="form-control" id="nbiExpiry" name="nbiExpiry" value="<?php echo htmlspecialchars($user_data['applicant_nbi_expiry']); ?>">
</div>
<div class="mb-3">
    <label for="nbiStatus" class="form-label">NBI Status</label>
    <input type="text" class="form-control" id="nbiStatus" name="nbiStatus" value="<?php echo htmlspecialchars($user_data['applicant_nbi_status']); ?>">
</div>

<div class="mb-3">
    <label for="nbiRemarks" class="form-label">NBI Remarks</label>
    <textarea class="form-control" id="nbiRemarks" name="nbiRemarks" rows="3"><?php echo htmlspecialchars($user_data['applicant_nbi_remarks']); ?></textarea>
</div>
<div class="mb-3">
    <label for="prcLicenseExpiry" class="form-label">PRC License Expiry</label>
    <input type="date" class="form-control" id="prcLicenseExpiry" name="prcLicenseExpiry" value="<?php echo htmlspecialchars($user_data['applicant_prc_license_expiry']); ?>">
</div>
<div class="mb-3">
    <label for="prcLicenseStatus" class="form-label">PRC License Status</label>
    <input type="text" class="form-control" id="prcLicenseStatus" name="prcLicenseStatus" value="<?php echo htmlspecialchars($user_data['applicant_prc_license_status']); ?>">
</div>

<div class="mb-3">
    <label for="prcLicenseRemarks" class="form-label">PRC License Remarks</label>
    <textarea class="form-control" id="prcLicenseRemarks" name="prcLicenseRemarks" rows="3"><?php echo htmlspecialchars($user_data['applicant_prc_license_remarks']); ?></textarea>
</div>
<div class="mb-3">
    <label for="tesdaCertificateExpiry" class="form-label">TESDA Certificate Expiry</label>
    <input type="date" class="form-control" id="tesdaCertificateExpiry" name="tesdaCertificateExpiry" value="<?php echo htmlspecialchars($user_data['applicant_tesda_certificate_expiry']); ?>">
</div>
<div class="mb-3">
    <label for="tesdaCertificateStatus" class="form-label">TESDA Certificate Status</label>
    <input type="text" class="form-control" id="tesdaCertificateStatus" name="tesdaCertificateStatus" value="<?php echo htmlspecialchars($user_data['applicant_tesda_certificate_status']); ?>">
</div>

<div class="mb-3">
    <label for="tesdaCertificateRemarks" class="form-label">TESDA Certificate Remarks</label>
    <textarea class="form-control" id="tesdaCertificateRemarks" name="tesdaCertificateRemarks" rows="3"><?php echo htmlspecialchars($user_data['applicant_tesda_certificate_remarks']); ?></textarea>
</div>
<div class="mb-3">
    <label for="otherCertificateExpiry" class="form-label">Other Certificate Expiry</label>
    <input type="date" class="form-control" id="otherCertificateExpiry" name="otherCertificateExpiry" value="<?php echo htmlspecialchars($user_data['applicant_other_certificate_expiry']); ?>">
</div>
<div class="mb-3">
    <label for="otherCertificateStatus" class="form-label">Other Certificate Status</label>
    <input type="text" class="form-control" id="otherCertificateStatus" name="otherCertificateStatus" value="<?php echo htmlspecialchars($user_data['applicant_other_certificate_status']); ?>">
</div>

<hr>

<h5>Upload Documents</h5>

<div class="mb-3">
    <label for="medical_certificate_copy" class="form-label">Medical Certificate Copy</label>
    <input type="file" class="form-control" id="medical_certificate_copy" data-file-type="medical_certificate_copy">
</div>
<div class="mb-3">
    <label for="visa_copy" class="form-label">Visa Copy</label>
    <input type="file" class="form-control" id="visa_copy" data-file-type="visa_copy">
</div>
<div class="mb-3">
    <label for="passport_copy" class="form-label">Passport Copy</label>
    <input type="file" class="form-control" id="passport_copy" data-file-type="passport_copy">
</div>
<div class="mb-3">
    <label for="nbi_clearance_copy" class="form-label">NBI Clearance Copy</label>
    <input type="file" class="form-control" id="nbi_clearance_copy" data-file-type="nbi_clearance_copy">
</div>
<div class="mb-3">
    <label for="police_clearance_copy" class="form-label">Police Clearance Copy</label>
    <input type="file" class="form-control" id="police_clearance_copy" data-file-type="police_clearance_copy">
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
