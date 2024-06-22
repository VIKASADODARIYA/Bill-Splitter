<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_email'])) {
    // Redirect to the login page if the user is not logged in
    header("Location: login.html");
    exit();
}

// Include database connection file
include_once 'db_connection.php';

// Initialize variables
$first_name = $last_name = $email = '';
$errors = array();

// Fetch user details from session
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$email = $_SESSION['user_email'];

// Process form data when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate form input
    $first_name = trim($_POST["fname"]);
    $last_name = trim($_POST["lname"]);
    $email = trim($_POST["email"]);

    // Check if fields are empty
    if (empty($first_name)) {
        $errors[] = "First name is required";
    }
    if (empty($last_name)) {
        $errors[] = "Last name is required";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // If no errors, update user details
    if (empty($errors)) {
        // Prepare an update statement
        $sql = "UPDATE users SET first_name=?, last_name=?, email=? WHERE email=?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ssss", $param_first_name, $param_last_name, $param_email, $param_old_email);
            
            // Set parameters
            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_email = $email;
            $param_old_email = $_SESSION['user_email'];
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Update session variables
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                $_SESSION['user_email'] = $email;
                
                // Redirect to profile page
                header("location: profile.php");
                exit();
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        
        // Close statement
        $stmt->close();
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="main-container">
        <div class="child-container">
            <div id="para">
                <h1>Update Profile</h1>
            </div>
            <div class="start-section">
                <div class="user-profile">
                    <div id="user-details">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div id="input">
                                <input type="text" id="name" name="fname" placeholder="First Name" value="<?php echo $first_name; ?>" required>
                                <input type="text" id="name" name="lname" placeholder="Last Name" value="<?php echo $last_name; ?>" required>
                            </div>
                            <div id="input1">
                                <input type="email" name="email" placeholder="Email Address" value="<?php echo $email; ?>" required>
                                <br>
                            </div>
                            <div class="profile-button">
                                <button type="submit">Save Changes</button>
                                <a href="profile.php" class="button">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
