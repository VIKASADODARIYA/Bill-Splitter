<?php
session_start();

require 'mailer/vendor/phpmailer/phpmailer/src/Exception.php';
require 'mailer/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'mailer/vendor/phpmailer/phpmailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'db_connection.php'; // Include your database connection file

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Get the user's email from the form
    $email = $_POST['email'];

    // Generate a random token
    $reset_token = bin2hex(random_bytes(32));

    // Write your SQL query to update the reset_token for the user
    $sql = "UPDATE users SET reset_token = ? WHERE email = ?";

    try {
        // Prepare the SQL statement
        $stmt = $conn->prepare($sql);

        // Bind the parameters
        $stmt->bind_param('ss', $reset_token, $email);

        // Execute the SQL statement
        if ($stmt->execute()) {
            // Send email with reset link using PHPMailer
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username   = 'deadkiller0422@gmail.com';
            $mail->Password   = 'ajdk jdfk hfdv hfnc';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('deadkiller0422@gmail.com', 'Bill Splitter');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Link';
            $mail->Body = 'Click the following link to reset your password: <a href="http://localhost/Bill%20Splitter/reset_password.php?token=' . $reset_token . '">Reset Password</a>';

            $mail->send();

            // Set a success message
            $_SESSION['success_message'] = "Password reset link sent to your email.";
            // Redirect to the login page or any other appropriate page
            header("Location: login.html");
            exit();
        } else {
            // Set an error message
            $_SESSION['error_message'] = "Failed to send reset link.";
        }
    } catch (Exception $e) {
        // Set an error message
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <?php
        // Display error message if set
        if (isset($_SESSION['error_message'])) {
            echo '<div class="error-message">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
        // Display success message if set
        if (isset($_SESSION['success_message'])) {
            echo '<div class="success-message">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']);
        }
        ?>
        <form action="forgot_pass.php" method="post">
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" required><br><br>
            <button type="submit" name="submit">Send Reset Link</button>
        </form>
    </div>
</body>

</html>
