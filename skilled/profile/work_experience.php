<!-- Work Experience -->
<div id="work-experience-container">
    <!-- Existing work experiences will be loaded here -->
</div>
<button type-="button" class="btn btn-secondary mt-2" id="add-experience-btn">Add Work Experience</button>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('work-experience-container');
    const addBtn = document.getElementById('add-experience-btn');
    let experienceIndex = 0;

    function addExperienceForm(experience = {}) {
        const index = experienceIndex++;
        const formHtml = `
            <div class="card mb-3" data-index="${index}">
                <div class="card-header">
                    Work Experience #${index + 1}
                    <button type="button" class="btn-close float-end" aria-label="Close"></button>
                </div>
                <div class="card-body">
                    <input type="hidden" name="experience[${index}][experience_id]" value="${experience.experience_id || ''}">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="experience_company_${index}" class="form-label">Company</label>
                            <input type="text" class="form-control" id="experience_company_${index}" name="experience[${index}][experience_company]" value="${experience.experience_company || ''}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="experience_position_${index}" class="form-label">Position</label>
                            <input type="text" class="form-control" id="experience_position_${index}" name="experience[${index}][experience_position]" value="${experience.experience_position || ''}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="experience_from_${index}" class="form-label">From</label>
                            <input type="date" class="form-control" id="experience_from_${index}" name="experience[${index}][experience_from]" value="${experience.experience_from || ''}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="experience_to_${index}" class="form-label">To</label>
                            <input type="date" class="form-control" id="experience_to_${index}" name="experience[${index}][experience_to]" value="${experience.experience_to || ''}">
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="experience_country_${index}" class="form-label">Country</label>
                            <input type="text" class="form-control" id="experience_country_${index}" name="experience[${index}][experience_country]" value="${experience.experience_country || ''}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="experience_salary_${index}" class="form-label">Salary</label>
                            <input type="text" class="form-control" id="experience_salary_${index}" name="experience[${index}][experience_salary]" value="${experience.experience_salary || ''}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="reasonOfLeaving_${index}" class="form-label">Reason for Leaving</label>
                        <textarea class="form-control" id="reasonOfLeaving_${index}" name="experience[${index}][reasonOfLeaving]" rows="3">${experience.reasonOfLeaving || ''}</textarea>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', formHtml);
    }

    addBtn.addEventListener('click', () => addExperienceForm());

    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-close')) {
            e.target.closest('.card').remove();
        }
    });

    // Load existing experiences from PHP
    <?php
    $exp_stmt = $pdo->prepare("SELECT * FROM applicant_experiences WHERE experience_applicant = ?");
    $exp_stmt->execute([$user_id]);
    $experiences = $exp_stmt->fetchAll();
    echo 'const existingExperiences = ' . json_encode($experiences) . ';';
    ?>
    existingExperiences.forEach(exp => addExperienceForm(exp));
});
</script>
