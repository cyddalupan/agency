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

// Get the current page from the query string, default to 'personal_info'
$page = isset($_GET['page']) ? $_GET['page'] : 'personal_info';

include_once('header.php');
?>

<div class="container">
    <div class="row">
        <div class="col">
            <h1>Skilled Profile</h1>
            <p>Welcome, <?php echo htmlspecialchars($user_data['applicant_first'] . ' ' . $user_data['applicant_last']); ?>!</p>
            <p>Your email is: <?php echo htmlspecialchars($user_data['applicant_email']); ?></p>
            <p><a href="logout.php">Logout</a></p>

            <?php include_once('profile/sub_nav.php'); ?>

            <form action="update_profile.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="applicant_id" value="<?php echo htmlspecialchars($user_data['applicant_id']); ?>">
                <input type="hidden" name="page" value="<?php echo htmlspecialchars($page); ?>">

                <div class="mt-3">
                    <?php
                    // Include the content for the selected page
                    $page_path = 'profile/' . $page . '.php';
                    if (file_exists($page_path)) {
                        include_once($page_path);
                    } else {
                        echo '<div class="alert alert-danger">Page not found.</div>';
                    }
                    ?>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<?php include_once('footer.php'); ?>
