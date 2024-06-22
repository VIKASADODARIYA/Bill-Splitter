<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="child-container profile">
        <div id="info">
            <a href="profile.php" id="profile-link" <?php if (basename($_SERVER['PHP_SELF']) == 'profile.php') echo 'class="active"'; ?>>
                <img src="images/a.png" alt="">
            </a>
            <h2><?php echo $first_name . " " . $last_name; ?></h2>
            <h4><?php echo $email; ?></h4>
            <div id="logout">
                <a href="logout.php">Logout</a>
            </div>
        </div>
        <br>
        <div id="index">
            <p><a href="dashboard.php" <?php if (basename($_SERVER['PHP_SELF']) == 'dashboard.php') echo ' class="active"'; ?>>Dashboard</a></p>
            <p><a href="groups.php" <?php if (basename($_SERVER['PHP_SELF']) == 'groups.php') echo ' class="active"'; ?>>Groups</a></p>
            <p><a href="creategroup.php" <?php if (basename($_SERVER['PHP_SELF']) == 'creategroup.php') echo ' class="active"'; ?>>Create Group</a></p>
            <p><a href="about.php" <?php if (basename($_SERVER['PHP_SELF']) == 'about.php') echo ' class="active"'; ?>>About Us</a></p>
        </div>
    </div>

    <!-- JavaScript to remove active class from other links when a new link is clicked -->
    <script>
        // Get all anchor tags within the #index container
        const links = document.querySelectorAll('#index a');

        // Add click event listeners to each anchor tag
        links.forEach(link => {
            link.addEventListener('click', function() {
                // Remove 'active' class from all links
                links.forEach(link => {
                    link.classList.remove('active');
                });

                // Add 'active' class to the clicked link
                this.classList.add('active');
            });
        });
    </script>
</body>

</html>