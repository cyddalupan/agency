<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skilled Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab" aria-controls="login" aria-selected="true">Login</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab" aria-controls="register" aria-selected="false">Register</button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="login" role="tabpanel" aria-labelledby="login-tab">
                                <!-- Login Form -->
                                <form action="<?php echo site_url('skilled/login'); ?>" method="post">
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
                                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-sign-in-alt"></i> Login</button>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="register" role="tabpanel" aria-labelledby="register-tab">
                                <!-- Registration Form -->
                                <form action="<?php echo site_url('skilled/register'); ?>" method="post" enctype="multipart/form-data">
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
                                            <input type="email" class="form-control" id="register_email" name="email" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="register_password" name="password" required>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>