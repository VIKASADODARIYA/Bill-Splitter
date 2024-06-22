<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Path to PHPMailer autoloader
require 'db_connection.php'; // Include the database connection file

// Fetch expense amount from the expense table
$expenseAmountQuery = "SELECT expense_amount FROM expense";
$expenseAmountResult = $conn->query($expenseAmountQuery);

if ($expenseAmountResult->num_rows > 0) {
    $totalExpenseAmount = 0;
    while ($row = $expenseAmountResult->fetch_assoc()) {
        $totalExpenseAmount += $row["expense_amount"];
    }
} else {
    $totalExpenseAmount = 0;
}

// Close connection
$conn->close();

// Email details
$to = 'adodariyavikas@gmail.com'; // Change this to the recipient's email address
$subject = 'Total Expense Details';
$message = "Total Expense Amount: $totalExpenseAmount";

// Send email using PHPMailer
$mail = new PHPMailer(true);
try {
    //Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.example.com';  // Specify main and backup SMTP servers
    $mail->SMTPAuth   = true;               // Enable SMTP authentication
    $mail->Username   = 'deadkiller0422@gmail.com'; // SMTP username
    $mail->Password   = 'ajdk jdfk hfdv hfnc';   // SMTP password
    $mail->SMTPSecure = 'tls';            // Enable TLS encryption, `ssl` also accepted
    $mail->Port       = 587;              // TCP port to connect to

    //Recipients
    $mail->setFrom('deadkiller0422@gmail.com', 'Vikas Patel');
    $mail->addAddress($to);     // Add a recipient

    // Content
    $mail->isHTML(false);                                  // Set email format to HTML
    $mail->Subject = $subject;
    $mail->Body    = $message;

    $mail->send();
    echo 'Email has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>