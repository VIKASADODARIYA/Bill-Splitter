<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include the autoloader for PHPMailer
require 'mailer/vendor/phpmailer/phpmailer/src/Exception.php';
require 'mailer/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'mailer/vendor/phpmailer/phpmailer/src/SMTP.php';

include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check user credentials
    $checkCredentialsQuery = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($checkCredentialsQuery);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $row['password'])) {
            // Successful login, send a login email
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'deadkiller0422@gmail.com';
                $mail->Password   = 'ajdk jdfk hfdv hfnc';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('your_email@gmail.com', 'Your Name');
                $mail->addAddress($email, $row['first_name'] . ' ' . $row['last_name']);

                $mail->isHTML(true);
                $mail->Subject = 'Login Confirmation';
                $mail->Body = 'You have successfully logged in to SplitApp!';

                $mail->send();
                session_start();
                $_SESSION['user_email'] = $row['email'];
                $_SESSION['first_name'] = $row['first_name'];
                $_SESSION['last_name'] = $row['last_name'];

                // Output JSON directly
                echo json_encode(array("status" => "success", "redirect" => "dashboard.php"));

                // Alternatively, you can use headers for redirection
                // header("Location: dashboard.php");
                exit();
            } catch (Exception $e) {
                // Output JSON directly
                echo json_encode(array("status" => "error", "message" => "Login successful, but confirmation email could not be sent. Error: " . $mail->ErrorInfo));
                exit();
            }
        } else {
            // Incorrect password
            // Output JSON directly
            echo json_encode(array("status" => "error", "message" => "Incorrect password!"));
            exit();
        }
    } else {
        // User not found
        // Output JSON directly
        echo json_encode(array("status" => "error", "message" => "User not found. Please complete registration first!"));
        exit();
    }
}

$conn->close();
