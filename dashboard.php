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

// Include database connection
include 'db_connection.php';

// Get total expense amount for all groups combined
$sql_total_expense = "SELECT SUM(expense_amount) AS total_expense FROM expense";
$result_total_expense = mysqli_query($conn, $sql_total_expense);
$row_total_expense = mysqli_fetch_assoc($result_total_expense);
$total_expense_amount = $row_total_expense['total_expense'];

// Get the most recent transaction from all groups
$sql_recent_transaction = "SELECT expense_name, expense_amount, group_name FROM expense 
                           INNER JOIN group_data ON expense.group_id = group_data.group_id 
                           ORDER BY expense_id DESC LIMIT 1";
$result_recent_transaction = mysqli_query($conn, $sql_recent_transaction);
$row_recent_transaction = mysqli_fetch_assoc($result_recent_transaction);

$recent_expense_name = $row_recent_transaction['expense_name'];
$recent_expense_amount = $row_recent_transaction['expense_amount'];
$recent_group_name = $row_recent_transaction['group_name'];

// Get data for the chart
$sql_chart_data = "SELECT group_name, SUM(expense_amount) AS total_amount FROM expense 
                   INNER JOIN group_data ON expense.group_id = group_data.group_id 
                   GROUP BY group_name";
$result_chart_data = mysqli_query($conn, $sql_chart_data);

$data = [];
$labels = [];
while ($row = mysqli_fetch_assoc($result_chart_data)) {
    $data[] = $row['total_amount'];
    $labels[] = $row['group_name'];
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bill Splitter</title>
    <link rel="stylesheet" href="style.css">
    <!-- Include Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="main-container">
        <?php include './index_.php'; ?>
        <div class="child-container container">
            <div class="welcome">
                <div>
                    <h3>Welcome to SplitApp Dashboard!</h3>
                    <p>Keep track of shared expense and settle your corresponding balances in a convenient and personalized way.</p>
                    <a href="groups.php"><button type="submit">View Groups</button></a>
                </div>
                <div>
                    <img src="images/image_2.png" alt="">
                </div>
            </div>
            <div class="welcome">
                <div id="money">
                    <img src="images/image_3.png" alt="">
                </div>
                <div>
                    <h4>Total</h4>
                    <!-- Total expense amount for all groups -->
                    <p>₹ <?php echo number_format($total_expense_amount, 2); ?></p>
                </div>
            </div>
            <!-- Additional knowledgeable information -->
            <div class="knowledge">
                <h4>Financial Tips</h4>
                <ul>
                    <li>Track your expenses regularly to understand your spending habits.</li>
                    <li>Set a budget and stick to it to avoid overspending.</li>
                    <li>Consider using cashback or reward credit cards for purchases.</li>
                    <li>Automate your savings by setting up recurring transfers to a savings account.</li>
                    <li>Review your financial goals periodically and adjust as needed.</li>
                </ul>
            </div>
        </div>
        <div class="child-container profile">
            <div class="dashboard-section">
                <div id="transaction">
                    <p>Most recent transaction:</p>
                    <p><?php echo $recent_group_name; ?>:</p>
                    <p><?php echo $recent_expense_name . " - ₹" . $recent_expense_amount; ?></p>
                </div>
                <!-- Group expense amounts will be displayed in the chart -->
                <figure class="dashboard-section">
                    <canvas id="groupExpenseChart" width="400" height="400"></canvas>
                    <figcaption>Group-wise total expenses</figcaption>
                </figure>

            </div>
        </div>
    </div>
    <script>
        // Data from PHP
        const data = <?php echo json_encode($data); ?>;
        const labels = <?php echo json_encode($labels); ?>;

        // Get the canvas element
        const canvas = document.getElementById('groupExpenseChart');
        const ctx = canvas.getContext('2d');

        // Create the chart
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Expense Amount',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)', // Blue color with transparency
                    borderColor: 'rgba(54, 162, 235, 1)', // Solid blue color
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>