<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_email'])) {
    // Redirect to the login page if the user is not logged in
    header("Location: login.html");
    exit();
}

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$email = $_SESSION['user_email'];

// Include database connection
include 'db_connection.php';

// Logout functionality
if (isset($_POST['logout'])) {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to the login page after logout
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Bill Splitter</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="main-container">
        <?php include './index_.php'; ?>
        <div class="child-container container">
            <div class="about-section">
                <h3>About SplitApp</h3>
                <p>SplitApp is a convenient and personalized expense management tool designed to help you keep track of shared expenses and settle corresponding balances effortlessly.</p>
                <p>With SplitApp, you can easily manage expenses within groups, track total expenses, view group-wise expense distribution, and more.</p>
            </div>
            <div class="about-section">
                <h4>Features:</h4>
                <ul>
                    <li>Track shared expenses within groups</li>
                    <li>View total expenses across all groups</li>
                    <li>Analyze group-wise expense distribution through charts</li>
                    <li>Easily settle balances with group members</li>
                    <li>Convenient and user-friendly interface</li>
                </ul>
            </div>
            <div class="about-section">
                <h4>Contact Us</h4>
                <p>If you have any questions, feedback, or suggestions, feel free to reach out to us:</p>
                <p>Email: support@billsplitter.com</p>
                <p>Phone: 123-456-7890</p>
            </div>
            <!-- Copyright footer -->
            <footer class="copyright">
                <div class="container">
                    <p>&copy; <?php echo date("Y"); ?> BillSplitter. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </div>
</body>

</html>