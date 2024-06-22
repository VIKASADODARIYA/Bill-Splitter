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

$errorMessage = ""; // Initialize error message variable
$successMessage = ""; // Initialize success message variable

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assuming you have a database connection
    // Replace these variables with your actual database connection details
    include 'db_connection.php';

    // Retrieve form data
    $group_name = $_POST["name"];
    $description = $_POST["description"];
    $currency = $_POST["currency"];
    $category = $_POST["category"];
    $members = explode(",", $_POST["member"]); // Assuming members are comma-separated
    $emails = explode(",", $_POST["email"]); // Assuming emails are comma-separated

    // Insert data into group_data table
    $sql = "INSERT INTO group_data (group_name, descriptions, currency, category) VALUES ('$group_name', '$description', '$currency', '$category')";
    if ($conn->query($sql) === TRUE) {
        $group_id = $conn->insert_id; // Get the ID of the last inserted group

        // Insert data into group_member table
        foreach ($members as $key => $member) {
            $member = trim($member);
            $email = trim($emails[$key]); // Get the corresponding email
            if (!empty($member) && !empty($email)) {
                $sql = "INSERT INTO group_member (group_id, member_name, member_email) VALUES ($group_id, '$member', '$email')";
                $conn->query($sql);
            }
        }

        $successMessage = "Group created successfully!";
    } else {
        $errorMessage = "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close the database connection
    $conn->close();
}

// Unset variables to avoid displaying stale messages on subsequent page loads
unset($group_name, $description, $currency, $category, $members);

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
    <title>Create Groups-BillSplitter</title>
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
                <h1>Create a new group</h1>
                <a href="profile.php"><img src="images/a.png" alt=""></a>
            </div>
            <form id="creategroup" action="creategroup.php" method="post">
                <div id="authentication">
                    <input type="text" name="name" id="groupname" placeholder="Group Name" required>
                    <input type="text" name="description" id="name" placeholder="Group Description" required>
                    <input type="text" name="member" id="name" placeholder="Group Members" multiple required>
                    <input type="email" name="email" id="email" placeholder="Members Email" multiple required>
                    <div class="currency">
                        <select name="currency" required>
                            <option value="" disabled selected>Select a currency</option>
                            <option value="inr">INR</option>
                            <option value="usd">USD</option>
                            <option value="eur">EUR</option>
                            <option value="gbp">GBP</option>
                        </select>
                        <select name="category" required>
                            <option value="" disabled selected>Select a Category</option>
                            <option value="Travel">Travel</option>
                            <option value="Entertainment">Entertainment</option>
                            <option value="Shopping">Shopping</option>
                            <option value="Education">Education</option>
                            <option value="Groceries">Groceries</option>
                            <option value="Healthcare">Healthcare</option>
                            <option value="Dining Out">Dining Out</option>
                        </select>
                    </div>
                    <br>
                    <button type="submit" onclick="validateGroup(event)">Create Group</button>
                    <br><br>
                    <div id="errorMessage" class="status"><?php echo $errorMessage; ?></div>
                    <div id="successMessage"><?php echo $successMessage; ?></div>
                </div>
            </form>
        </div>
    </div>
    <script>
        function validateGroup(event) {
            // You can add additional validation logic here if needed
            // For example, checking if the group name is not empty, etc.
        }

        document.addEventListener("DOMContentLoaded", function() {
            var errorMessageElement = document.getElementById("errorMessage");
            var successMessageElement = document.getElementById("successMessage");

            // Display error and success messages only if the form was submitted
            if (errorMessageElement.innerHTML.trim() !== "" && window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href.split("?")[0]);
                displayMessage(errorMessageElement, 'error', 'shake-animation');
            }

            if (successMessageElement.innerHTML.trim() !== "") {
                displayMessage(successMessageElement, 'success');
            }

            // Hide messages after a certain time (e.g., 5 seconds)
            setTimeout(function() {
                hideMessage(errorMessageElement);
                hideMessage(successMessageElement);
            }, 5000); // Adjust the time as needed
        });

        function displayMessage(element, type, customClass = '') {
            element.style.display = "block";
            if (type === 'error') {
                element.style.color = "#FF0000";
                // Add custom class for animation
                element.classList.add(customClass);
            } else if (type === 'success') {
                element.style.color = "#008000";
            }
        }

        function hideMessage(element) {
            element.style.display = "none";
            // Remove custom class on hide
            element.classList.remove('shake-animation');
        }
    </script>
</body>

</html>