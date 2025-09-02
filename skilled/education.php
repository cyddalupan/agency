<?php
// 1. Common setup
require_once('includes/profile_bootstrap.php');

// 2. Page-specific setup
$page = 'education'; // For the navigation

// 3. Header
include_once('header.php');
?>

<div class="container">
    <div class="row">
        <div class="col">
            <h1>Skilled Profile</h1>
            <p>Welcome, <?php echo htmlspecialchars($user_data['applicant_first'] . ' ' . $user_data['applicant_last']); ?>!</p>
            <p>Your email is: <?php echo htmlspecialchars($user_data['applicant_email']); ?></p>
            <p><a href="logout.php">Logout</a></p>

            <?php include_once('profile_nav.php'); ?>

            <form action="actions/update_education.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="applicant_id" value="<?php echo htmlspecialchars($user_data['applicant_id']); ?>">

                <div class="mt-3">
                    <?php
                    // Include the content for the education page
                    include_once('profile/education.php');
                    ?>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<?php include_once('footer.php'); ?>
