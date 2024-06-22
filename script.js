/* Register Credentials */
function checkEmail() {
    var email = document.getElementById("email").value;
    var emailError = document.getElementById("email-error");

    // Use AJAX to check if the email exists
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "check_email.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.exists) {
                emailError.innerHTML =
                    "This email address is already registered. Please use a different email address.";

                // Apply the shake animation class
                emailError.classList.add("shake-animation");

                // Remove the shake animation class after 2 seconds
                setTimeout(function () {
                    emailError.classList.remove("shake-animation");
                }, 2000);

                setTimeout(function () {
                    emailError.innerHTML = "";
                }, 5000);

                return false; // Prevent form submission
            } else {
                emailError.innerHTML = ""; // Clear previous error message
                // Proceed with form submission
                document.getElementById("registrationForm").submit();
            }
        }
    };
    xhr.send("email=" + email);
    return false; // Prevent default form submission
}

function togglePasswordVisibility() {
    var passwordInput = document.getElementById("password");
    var passwordToggle = document.getElementById("password-toggle");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        passwordToggle.innerHTML =
            '<img src="https://cdn-icons-png.flaticon.com/128/9726/9726390.png" alt="Hide Password">';
    } else {
        passwordInput.type = "password";
        passwordToggle.innerHTML =
            '<img src="https://cdn-icons-png.flaticon.com/128/3495/3495850.png" alt="Show Password">';
    }
}

/* Login Credentials */

function validateForm() {
    var email = document.getElementById("email").value;
    var password = document.getElementById("password").value;
    var errorDiv = document.getElementById("loginError");

    // Clear previous error messages and remove shake-animation class
    errorDiv.innerHTML = "";
    errorDiv.classList.remove("shake-animation");

    // Basic email format validation
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        errorDiv.innerHTML = "Invalid email address";
        // Add shake-animation class for animation
        errorDiv.classList.add("shake-animation");
        return false;
    }

    // Check if password is empty
    if (password.trim() === "") {
        errorDiv.innerHTML = "Password cannot be empty";
        // Add shake-animation class for animation
        errorDiv.classList.add("shake-animation");
        return false;
    }

    return true; // Return true to allow the form submission
}

// Handle the form submission response
document
    .getElementById("loginForm")
    .addEventListener("submit", function (event) {
        event.preventDefault(); // Prevent the default form submission

        var form = this;
        var formData = new FormData(form);
        var errorDiv = document.getElementById("loginError");

        fetch(form.action, {
            method: form.method,
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                var status = data.status;
                var message = data.message;

                if (status === "success") {
                    console.log("Redirect URL:", data.redirect); // Debug statement
                    // Redirect to the specified URL
                    window.location.href = data.redirect;
                } else {
                    // Display the error message and add shake-animation class
                    errorDiv.innerHTML = message;
                    errorDiv.classList.add("shake-animation");
                    setTimeout(function () {
                        errorDiv.innerHTML = "";
                    }, 5000);
                }
            })
            .catch((error) => {
                console.error("Error:", error);
            });
    });

function togglePasswordVisibility() {
    var passwordInput = document.getElementById("password");
    var passwordToggle = document.getElementById("password-toggle");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        passwordToggle.innerHTML =
            '<img src="https://cdn-icons-png.flaticon.com/128/9726/9726390.png" alt="Hide Password">';
    } else {
        passwordInput.type = "password";
        passwordToggle.innerHTML =
            '<img src="https://cdn-icons-png.flaticon.com/128/3495/3495850.png" alt="Show Password">';
    }
}

/* Create Group */