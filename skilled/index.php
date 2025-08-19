<?php include('header.php'); ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-8 col-lg-7 col-xl-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title"><i class="fas fa-user-plus"></i> Skilled Worker Registration</h3>
            </div>
            <div class="card-body">
                <form action="skilled/register.php" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="firstName" name="firstName" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="middleName" class="form-label">Middle Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="middleName" name="middleName">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="lastName" name="lastName" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="age" class="form-label">Age</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-cake-candles"></i></span>
                                <input type="number" class="form-control" id="age" name="age" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contactNumber" class="form-label">Contact Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="tel" class="form-control" id="contactNumber" name="contactNumber" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="remarks" class="form-label">Applicant Remarks</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-comment-dots"></i></span>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="resume" class="form-label">Attached Resume</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-file-arrow-up"></i></span>
                            <input type="file" class="form-control" id="resume" name="resume" required>
                        </div>
                    </div>

                    <fieldset class="border p-3 mb-3">
                        <legend class="w-auto px-2">Work Experience</legend>
                        <div class="mb-3">
                            <label for="workLocation" class="form-label">Location</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <select class="form-select" id="workLocation" name="workLocation">
                                    <option selected>Choose...</option>
                                    <option value="local">Local</option>
                                    <option value="abroad">Abroad</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="workDetails" class="form-label">Details</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                <textarea class="form-control" id="workDetails" name="workDetails" rows="4"></textarea>
                            </div>
                        </div>
                    </fieldset>

                    <button type="submit" class="btn btn-success w-100"><i class="fas fa-paper-plane"></i> Register</button>
                </form>
            </div>
            <div class="card-footer text-center">
                <p class="mb-0">Already have an account? <a href="skilled/login.php">Skilled Login</a></p>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
