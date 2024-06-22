<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_email'])) {
    // Redirect to the login page if the user is not logged in
    header("Location: login.html");
    exit();
}

// Include your database connection code
try {
    // Database connection parameters
    $servername = "localhost";
    $username = "Vikas";
    $password = "Vikas@10#mysql";
    $dbname = "bill_splitter";

    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

    // Set PDO attributes
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If an exception is thrown, display an error message
    echo "Error: Failed to connect to the database. " . $e->getMessage();
    exit();
}

// Check if the $pdo object is set
if (!isset($pdo)) {
    echo "Error: Database connection is not set.";
    exit();
}

// Retrieve user email from session
$email = $_SESSION['user_email'];

// Write your SQL query to delete the user's profile
$sql = "DELETE FROM users WHERE email = :email";

// Prepare the SQL statement
$stmt = $pdo->prepare($sql);

// Check if the prepare operation failed
if (!$stmt) {
    echo "Error: Failed to prepare SQL statement.";
    exit();
}

// Bind the email parameter
$stmt->bindParam(':email', $email, PDO::PARAM_STR);

// Execute the SQL statement
if ($stmt->execute()) {
    // Account deletion successful, destroy the session and redirect to login page
    session_destroy();
    header("Location: register.html");
    exit();
} else {
    // Account deletion failed, redirect to profile page with error message
    header("Location: profile.php?error=delete_failed");
    exit();
}
?>
