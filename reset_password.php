<?php
session_start();

require_once 'db_connection.php';

// Check if token is provided in the URL
if (!isset($_GET['token']) || empty($_GET['token'])) {
    // Redirect to an error page or display an error message
    $_SESSION['error_message'] = "Token is missing.";
    header("Location: error.php");
    exit();
}

// Validate the token
$token = $_GET['token'];

// Write your SQL query to check if the token exists and is valid
$sql = "SELECT email FROM users WHERE reset_token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Token is invalid or expired
    $_SESSION['error_message'] = "Invalid or expired token.";
    header("Location: error.php");
    exit();
}

// Token is valid, allow the user to reset the password
if (isset($_POST['submit'])) {
    // Get the token and new password from the form
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate passwords
    if ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match.";
        header("Location: reset_password.php?token=$token");
        exit();
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the user's password in the database
    $sql = "UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $hashed_password, $token);
    $stmt->execute();

    // Check if the password was updated successfully
    if ($stmt->affected_rows > 0) {
        $_SESSION['success_message'] = "Password reset successful. You can now login with your new password.";
        header("Location: login.html");
        exit();
    } else {
        // Password update failed
        $_SESSION['error_message'] = "Failed to reset password. Please try again later.";
        header("Location: reset_password.php?token=$token");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h2>Reset Password</h2>
        <?php
        // Display error message if set
        if (isset($_SESSION['error_message'])) {
            echo '<div class="error-message">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>
        <form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="post">
            <label for="password">New Password:</label><br>
            <input type="password" id="password" name="password" required><br><br>
            <label for="confirm_password">Confirm New Password:</label><br>
            <input type="password" id="confirm_password" name="confirm_password" required><br><br>
            <button type="submit" name="submit">Reset Password</button>
        </form>
    </div>
</body>

</html>
