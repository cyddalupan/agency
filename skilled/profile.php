<?php
session_start();

// If the user is not logged in, redirect to the login page.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 1. Database Connection
require_once(dirname(__DIR__) . '/config.php');

$user_id = $_SESSION['user_id'];
$user_data = null;

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    $stmt = $pdo->prepare("SELECT * FROM applicant WHERE applicant_id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch();

    if (!$user_data) {
        // User not found, redirect to login or show an error
        header('Location: login.php');
        exit;
    }

} catch (PDOException $e) {
    die("Database connection or query failed: " . $e->getMessage());
}

include_once('header.php');
?>

<div class="container">
    <div class="row">
        <div class="col">
            <h1>Skilled Profile</h1>
            <p>Welcome, <?php echo htmlspecialchars($user_data['applicant_first'] . ' ' . $user_data['applicant_last']); ?>!</p>
            <p>Your email is: <?php echo htmlspecialchars($user_data['applicant_email']); ?></p>
            <p><a href="logout.php">Logout</a></p>

            <form action="update_profile.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="applicant_id" value="<?php echo htmlspecialchars($user_data['applicant_id']); ?>">

                <div class="accordion" id="profileAccordion">
                    <!-- Personal Information Accordion Item -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="personalInfoHeading">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#personalInfoCollapse" aria-expanded="true" aria-controls="personalInfoCollapse">
                                Personal Information
                            </button>
                        </h2>
                        <div id="personalInfoCollapse" class="accordion-collapse collapse show" aria-labelledby="personalInfoHeading" data-bs-parent="#profileAccordion">
                            <div class="accordion-body">
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
                                        <label for="age" class="form-label">Age</label>
                                        <input type="number" class="form-control" id="age" name="age" value="<?php echo htmlspecialchars($user_data['applicant_age']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="contactNumber" class="form-label">Contact Number</label>
                                        <input type="tel" class="form-control" id="contactNumber" name="contactNumber" value="<?php echo htmlspecialchars($user_data['applicant_contacts']); ?>" required>
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
                            </div>
                        </div>
                    </div>

                    <!-- Other Accordion Items (to be added later) -->

                    <!-- Applicant Remarks Accordion Item -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="remarksHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#remarksCollapse" aria-expanded="false" aria-controls="remarksCollapse">
                                Applicant Remarks
                            </button>
                        </h2>
                        <div id="remarksCollapse" class="accordion-collapse collapse" aria-labelledby="remarksHeading" data-bs-parent="#profileAccordion">
                            <div class="accordion-body">
                                <div class="mb-3">
                                    <label for="remarks" class="form-label">Remarks</label>
                                    <textarea class="form-control" id="remarks" name="remarks" rows="3"><?php echo htmlspecialchars($user_data['fra_remarks']); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents Accordion Item -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="documentsHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#documentsCollapse" aria-expanded="false" aria-controls="documentsCollapse">
                                Documents
                            </button>
                        </h2>
                        <div id="documentsCollapse" class="accordion-collapse collapse" aria-labelledby="documentsHeading" data-bs-parent="#profileAccordion">
                            <div class="accordion-body">
                                <div class="mb-3">
                                    <label for="resume" class="form-label">Attached Resume</label>
                                    <input type="file" class="form-control" id="resume" name="resume">
                                    <?php if (!empty($user_data['applicant_cv'])): ?>
                                        <small class="form-text text-muted">Current: <a href="<?php echo htmlspecialchars($user_data['applicant_cv']); ?>" target="_blank"><?php echo basename(htmlspecialchars($user_data['applicant_cv'])); ?></a></small>
                                    <?php else: ?>
                                        <small class="form-text text-muted">No resume attached.</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Job Preferences and Skills Accordion Item -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="jobSkillsHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#jobSkillsCollapse" aria-expanded="false" aria-controls="jobSkillsCollapse">
                                Job Preferences and Skills
                            </button>
                        </h2>
                        <div id="jobSkillsCollapse" class="accordion-collapse collapse" aria-labelledby="jobSkillsHeading" data-bs-parent="#profileAccordion">
                            <div class="accordion-body">
                                <div class="mb-3">
                                    <label for="positionType" class="form-label">Position Type</label>
                                    <input type="text" class="form-control" id="positionType" name="positionType" value="<?php echo htmlspecialchars($user_data['applicant_position_type']); ?>">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="currency" class="form-label">Currency</label>
                                        <input type="text" class="form-control" id="currency" name="currency" value="<?php echo htmlspecialchars($user_data['currency']); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="expectedSalary" class="form-label">Expected Salary</label>
                                        <input type="number" class="form-control" id="expectedSalary" name="expectedSalary" value="<?php echo htmlspecialchars($user_data['applicant_expected_salary']); ?>">
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
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<?php include_once('footer.php'); ?>
