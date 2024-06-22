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

// Database connection parameters
include 'db_connection.php';

// Fetch group_data details
$groupDataQuery = "SELECT * FROM group_data";
$groupDataResult = $conn->query($groupDataQuery);

// Fetch total expenses for each group
$expensesByGroup = array();
$totalExpensesQuery = "SELECT group_id, SUM(expense_amount) AS total_expenses FROM expense GROUP BY group_id";
$totalExpensesResult = $conn->query($totalExpensesQuery);
while ($row = $totalExpensesResult->fetch_assoc()) {
    $expensesByGroup[$row['group_id']] = $row['total_expenses'];
}

// Fetch expense per member for each group
$expensePerMemberByGroup = array();
$expensePerMemberQuery = "SELECT group_id, expense_per_member FROM expense";
$expensePerMemberResult = $conn->query($expensePerMemberQuery);
while ($row = $expensePerMemberResult->fetch_assoc()) {
    $expensePerMemberByGroup[$row['group_id']] = $row['expense_per_member'];
}

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

// Check if the form for adding expense is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_expense'])) {
        // Get form data for adding expense
        $groupName = $_POST['group_name'];
        $expenseName = $_POST['expense_name'];
        $expenseAmount = $_POST['expense_amount'];

        // Check if the group exists
        $groupQuery = "SELECT group_id FROM group_data WHERE group_name = ?";
        $groupStmt = $conn->prepare($groupQuery);
        $groupStmt->bind_param("s", $groupName);
        $groupStmt->execute();
        $groupResult = $groupStmt->get_result();

        if ($groupResult->num_rows > 0) {
            // Group exists, insert expense
            $groupRow = $groupResult->fetch_assoc();
            $groupId = $groupRow['group_id'];

            // Insert expense into database
            $expenseInsertQuery = "INSERT INTO expense (group_id, expense_name, expense_amount) VALUES (?, ?, ?)";
            $expenseStmt = $conn->prepare($expenseInsertQuery);
            $expenseStmt->bind_param("isd", $groupId, $expenseName, $expenseAmount);

            if ($expenseStmt->execute()) {
                // Calculate expense per member
                $numMembersQuery = "SELECT COUNT(*) AS num_members FROM group_member WHERE group_id = ?";
                $numMembersStmt = $conn->prepare($numMembersQuery);
                if (!$numMembersStmt) {
                    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                }
                $numMembersStmt->bind_param("i", $groupId);
                $numMembersStmt->execute();
                $numMembersResult = $numMembersStmt->get_result();

                $numMembersRow = $numMembersResult->fetch_assoc();
                $numMembers = $numMembersRow['num_members'];

                if ($numMembers > 0) {
                    $expensePerMember = $expenseAmount / $numMembers;

                    // Update expense_per_member in the database
                    $updateExpensePerMemberQuery = "UPDATE expense SET expense_per_member = ? WHERE group_id = ?";
                    $updateExpensePerMemberStmt = $conn->prepare($updateExpensePerMemberQuery);
                    $updateExpensePerMemberStmt->bind_param("di", $expensePerMember, $groupId);
                    $updateExpensePerMemberStmt->execute();
                }

                // Update total expenses for the group
                $updateTotalExpensesQuery = "UPDATE group_data SET total_expenses = total_expenses + ? WHERE group_id = ?";
                $updateTotalExpensesStmt = $conn->prepare($updateTotalExpensesQuery);
                $updateTotalExpensesStmt->bind_param("di", $expenseAmount, $groupId);
                $updateTotalExpensesStmt->execute();

                $expenseAdded = true;
            } else {
                $expenseAdded = false;
                $expenseError = "Error adding expense: " . $conn->error;
            }
        } else {
            $expenseAdded = false;
            $expenseError = "Group not found";
        }
    } elseif (isset($_POST['remove_expense'])) {
        // Get form data for removing expense
        $groupName = $_POST['group_name'];
        $expenseAmountToRemove = $_POST['expense_amount'];

        // Check if the group exists
        $groupQuery = "SELECT group_id FROM group_data WHERE group_name = ?";
        $groupStmt = $conn->prepare($groupQuery);
        $groupStmt->bind_param("s", $groupName);
        $groupStmt->execute();
        $groupResult = $groupStmt->get_result();

        if ($groupResult->num_rows > 0) {
            // Group exists, update total expenses
            $groupRow = $groupResult->fetch_assoc();
            $groupId = $groupRow['group_id'];

            // Update total expenses for the group by deducting the specified amount
            $updateTotalExpensesQuery = "UPDATE group_data SET total_expenses = total_expenses - ? WHERE group_id = ?";
            $updateTotalExpensesStmt = $conn->prepare($updateTotalExpensesQuery);
            if (!$updateTotalExpensesStmt) {
                die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            }
            $updateTotalExpensesStmt->bind_param("di", $expenseAmountToRemove, $groupId);
            if (!$updateTotalExpensesStmt->execute()) {
                die("Execute failed: (" . $updateTotalExpensesStmt->errno . ") " . $updateTotalExpensesStmt->error);
            }

            // Deduct successful
            $expenseRemoved = true;

            // Also deduct the expense amount from individual expenses
            $deductExpenseAmountQuery = "UPDATE expense SET expense_amount = expense_amount - ? WHERE group_id = ?";
            $deductExpenseAmountStmt = $conn->prepare($deductExpenseAmountQuery);
            if (!$deductExpenseAmountStmt) {
                die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            }
            $deductExpenseAmountStmt->bind_param("di", $expenseAmountToRemove, $groupId);
            if (!$deductExpenseAmountStmt->execute()) {
                die("Execute failed: (" . $deductExpenseAmountStmt->errno . ") " . $deductExpenseAmountStmt->error);
            }

            // Update the expense per member after removing the expense
            if ($expenseRemoved) {
                // Calculate new total expenses for the group
                $newTotalExpensesQuery = "SELECT SUM(expense_amount) AS new_total_expenses FROM expense WHERE group_id = ?";
                $newTotalExpensesStmt = $conn->prepare($newTotalExpensesQuery);
                $newTotalExpensesStmt->bind_param("i", $groupId);
                $newTotalExpensesStmt->execute();
                $newTotalExpensesResult = $newTotalExpensesStmt->get_result();
                $newTotalExpensesRow = $newTotalExpensesResult->fetch_assoc();
                $newTotalExpenses = $newTotalExpensesRow['new_total_expenses'];

                // Get the number of members in the group
                $numMembersQuery = "SELECT COUNT(*) AS num_members FROM group_member WHERE group_id = ?";
                $numMembersStmt = $conn->prepare($numMembersQuery);
                $numMembersStmt->bind_param("i", $groupId);
                $numMembersStmt->execute();
                $numMembersResult = $numMembersStmt->get_result();
                $numMembersRow = $numMembersResult->fetch_assoc();
                $numMembers = $numMembersRow['num_members'];

                // Recalculate expense per member
                if ($numMembers > 0) {
                    $newExpensePerMember = $newTotalExpenses / $numMembers;

                    // Update expense_per_member in the database
                    $updateExpensePerMemberQuery = "UPDATE expense SET expense_per_member = ? WHERE group_id = ?";
                    $updateExpensePerMemberStmt = $conn->prepare($updateExpensePerMemberQuery);
                    $updateExpensePerMemberStmt->bind_param("di", $newExpensePerMember, $groupId);
                    $updateExpensePerMemberStmt->execute();
                }
            }

            $expenseRemoved = true;
        } else {
            $expenseRemoved = false;
            $expenseError = "Group not found";
        }
    } elseif (isset($_POST['delete_group'])) {
        // Get form data for deleting group
        $deleteGroupName = $_POST['delete_group_name'];

        // Check if the group exists
        $deleteGroupQuery = "SELECT group_id FROM group_data WHERE group_name = ?";
        $deleteGroupStmt = $conn->prepare($deleteGroupQuery);
        $deleteGroupStmt->bind_param("s", $deleteGroupName);
        $deleteGroupStmt->execute();
        $deleteGroupResult = $deleteGroupStmt->get_result();

        if ($deleteGroupResult->num_rows > 0) {
            // Group exists, delete the group
            $deleteGroupRow = $deleteGroupResult->fetch_assoc();
            $deleteGroupId = $deleteGroupRow['group_id'];

            // Delete group from group_data table
            $deleteGroupDataQuery = "DELETE FROM group_data WHERE group_id = ?";
            $deleteGroupDataStmt = $conn->prepare($deleteGroupDataQuery);
            $deleteGroupDataStmt->bind_param("i", $deleteGroupId);
            $deleteGroupDataStmt->execute();

            // Delete group members
            $deleteGroupMembersQuery = "DELETE FROM group_member WHERE group_id = ?";
            $deleteGroupMembersStmt = $conn->prepare($deleteGroupMembersQuery);
            $deleteGroupMembersStmt->bind_param("i", $deleteGroupId);
            $deleteGroupMembersStmt->execute();

            // Delete expenses associated with the group
            $deleteExpensesQuery = "DELETE FROM expense WHERE group_id = ?";
            $deleteExpensesStmt = $conn->prepare($deleteExpensesQuery);
            $deleteExpensesStmt->bind_param("i", $deleteGroupId);
            $deleteExpensesStmt->execute();

            // Redirect to dashboard or any other page after deletion
            header("Location: groups.php");
            exit();
        } else {
            // Group not found
            $deleteGroupError = "Group not found";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bill Splitter</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="main-container">
        <?php
        include './index_.php';
        ?>
        <div class="child-container container groups">

            <!-- Display group data and associated members -->
            <h2>Group Details</h2>
            <p>
                <a href="#" onclick="showExpenseForm()" class="add">Settlement</a>
                <a href="#" onclick="showDeleteGroupForm()" class="add">Delete Group</a>
            </p>
            <div class="popup" id="expensePopup">
                <div class="popup-content">
                    <span class="close" onclick="hideExpenseForm()">&times;</span>
                    <h3>Add or Remove Expense</h3>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" onsubmit="sendEmail()">
                        <label for="group_name">Group Name:</label>
                        <input type="text" id="group_name" name="group_name" required><br>
                        <label for="expense_name">Expense Name:</label><br>
                        <input type="text" id="expense_name" name="expense_name" required><br>
                        <label for="expense_amount">Expense Amount:</label><br>
                        <input type="number" id="expense_amount" name="expense_amount" step="0.01" required><br>

                        <!-- Hidden input field for expense ID when removing an expense -->
                        <input type="hidden" id="expense_id" name="expense_id" value="<?php echo $expenseId; ?>">

                        <div id="expense_display">
                            <button type="submit" name="add_expense">Add Expense</button>
                            <button type="submit" name="remove_expense">Remove Expense</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="popup" id="deleteGroupPopup">
                <div class="popup-content">
                    <span class="close" onclick="hideDeleteGroupForm()">&times;</span>
                    <h3>Delete Group</h3>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <label for="delete_group_name">Group Name:</label>
                        <input type="text" id="delete_group_name" name="delete_group_name" required><br>
                        <button type="submit" name="delete_group">Delete Group</button>
                    </form>
                </div>
            </div>

            <div id="data">
                <?php
                while ($groupRow = $groupDataResult->fetch_assoc()) {
                    echo '<div class="group-card">';
                    echo "<h3>Group Name: " . $groupRow['group_name'] . "</h3>";
                    echo "<p>Created At: " . $groupRow['created_at'] . "</p>";
                    echo "<p>Currency: " . $groupRow['currency'] . "</p>";
                    echo "<p>Category: " . $groupRow['category'] . "</p>";
                    echo "<p>Descriptions: " . $groupRow['descriptions'] . "</p>";
                    // Display total expenses
                    if (isset($expensesByGroup[$groupRow['group_id']])) {
                        echo "<p>Total Expenses: " . $expensesByGroup[$groupRow['group_id']] . "</p>";
                    } else {
                        echo "<p>Total Expenses: 0</p>";
                    }
                    // Display expense per member
                    if (isset($expensePerMemberByGroup[$groupRow['group_id']])) {
                        echo "<p>Expense Per Member: " . $expensePerMemberByGroup[$groupRow['group_id']] . "</p>";
                    } else {
                        echo "<p>Expense Per Member: 0</p>";
                    }
                    // Display associated members
                    echo "<p>Members:</p>";
                    echo "<ul>";
                    $groupId = $groupRow['group_id'];
                    $membersQuery = "SELECT * FROM group_member WHERE group_id = $groupId";
                    $membersResult = $conn->query($membersQuery);
                    while ($memberRow = $membersResult->fetch_assoc()) {
                        echo "<li>Member Name: " . $memberRow['member_name'] . "</li>";
                    }
                    echo "</ul>";
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        function showExpenseForm() {
            var expensePopup = document.getElementById("expensePopup");
            expensePopup.style.display = "block";
        }

        function hideExpenseForm() {
            var expensePopup = document.getElementById("expensePopup");
            expensePopup.style.display = "none";
        }

        function showDeleteGroupForm() {
            var deleteGroupPopup = document.getElementById("deleteGroupPopup");
            deleteGroupPopup.style.display = "block";
        }

        function hideDeleteGroupForm() {
            var deleteGroupPopup = document.getElementById("deleteGroupPopup");
            deleteGroupPopup.style.display = "none";
        }

        function sendEmail() {
            // Fetch necessary data from the form
            var groupName = document.getElementById("group_name").value;
            var memberNames = document.getElementById("member_names").value;
            var expenseAmount = document.getElementById("expense_amount").value;
            var expensePerMember = document.getElementById("expense_per_member").value;

            // Make an AJAX request to send_mail.php
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "./send_mail.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Handle the response if needed
                    console.log(xhr.responseText);
                }
            };
            xhr.send("group_name=" + groupName + "&member_names=" + memberNames + "&expense_amount=" + expenseAmount + "&expense_per_member=" + expensePerMember);

            // Continue with form submission
            return true;
        }
    </script>
</body>

</html>

<?php
// Close the database connection
$conn->close();
?>