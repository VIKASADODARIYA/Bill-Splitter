<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include the autoloader for PHPMailer
require 'mailer\vendor\phpmailer\phpmailer\src\Exception.php';
require 'mailer\vendor\phpmailer\phpmailer\src\PHPMailer.php';
require 'mailer\vendor\phpmailer\phpmailer\src\SMTP.php';

include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST['fname'];
    $lastName = $_POST['lname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if the email already exists in the database
    $checkEmailQuery = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($checkEmailQuery);

    if ($result->num_rows > 0) {
        // Email already exists, set an error message or take appropriate action
        echo 'Error: This email address is already registered. Please use a different email address.';
    } else {
        // Email doesn't exist, proceed with registration
        $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES ('$firstName', '$lastName', '$email', '$password')";

        if ($conn->query($sql) === TRUE) {
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

                $mail->setFrom('deadkiller0422@gmail.com', 'Dead Killer');
                $mail->addAddress($email, $firstName . ' ' . $lastName);

                $mail->isHTML(true);
                $mail->Subject = 'Registration Confirmation';
                $mail->Body = 'Thank you for registering with SplitApp!';

                $mail->send();
                echo 'Registration successful. Confirmation email sent.';
                session_start();
                $_SESSION['registration_success'] = true;

                // Redirect to login.html
                header("Location: login.html?register_success=true");
                exit(); // Ensure that no more code is executed after the redirect
            } catch (Exception $e) {
                echo 'Registration successful, but confirmation email could not be sent. Error: ' . $mail->ErrorInfo;
            }
        } else {
            echo 'Error: ' . $sql . '<br>' . $conn->error;
        }
    }
}

$conn->close();
