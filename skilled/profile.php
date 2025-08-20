<?php
session_start();

// If the user is not logged in, redirect to the login page.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include_once('header.php'); 
?>

<div class="container">
    <div class="row">
        <div class="col">
            <h1>Skilled Profile</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
            <p>Your email is: <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
            <p><a href="logout.php">Logout</a></p>
        </div>
    </div>
</div>

<?php include_once('footer.php'); ?>