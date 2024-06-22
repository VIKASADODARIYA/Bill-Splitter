<?php

$servername = "localhost";
$username = "Vikas";
$password = "Vikas@10#mysql";
$dbname = "bill_splitter";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
