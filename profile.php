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
    <title>User Profile</title>
    <link rel="stylesheet" href="style.css">
    <script defer src="script.js"></script>
</head>

<body>
    <div class="main-container">
        <?php
        include './index_.php';
        ?>
        <div class="child-container container">
            <div id="para">
                <h1>User Profile</h1>
                <a href="profile.php"><img src="images/a.png" alt=""></a>
            </div>
            <div class="start-section">
                <div class="user-profile">
                    <div id="profile">
                        <img src="images/a.png" alt=""><br>
                        <!-- <button type="submit">Edit Profile Image</button> -->
                    </div>
                    <div id="user-details">
                        <div id="input">
                            <input type="text" id="name" name="fname" placeholder="First Name" value="<?php echo $first_name; ?>" readonly required>
                            <input type="text" id="name" name="lname" placeholder="Last Name" value="<?php echo $last_name; ?>" readonly required>
                        </div>
                        <div id="input1">
                            <input type="email" name="email" placeholder="Email Address" value="<?php echo $email; ?>" readonly required>
                            <br>
                        </div>
                        <div class="profile-button">
                            <!-- <button type="reset">Delete</button> -->
                            <button type="reset"><a href="delete_profile.php">Delete</a></button>
                            <button type="submit"><a href="update_profile.php">Edit Details</a></button>
                            <button type="submit"><a href="forgot_pass.php">Forgot Password</a></button>
                            <!-- <button type="submit">Forgot Password</button> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>