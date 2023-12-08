<?php
session_start();

// Database connection parameters
$host = 'localhost';
$username_db = 'root';
$password_db = '';
$database = 'attendance_system';

// Create a connection to the database
$mysqli = new mysqli($host, $username_db, $password_db, $database);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $adminUsername = $mysqli->real_escape_string($_POST["username"]);
    $adminPassword = $_POST["password"];

    // Use a prepared statement to prevent SQL injection
    $query = "SELECT * FROM admin_users WHERE username=?";
    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        $stmt->bind_param("s", $adminUsername);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if ($adminPassword == $row['password']) {
                $_SESSION["username"] = $adminUsername;
                header("Location: attendance.php");
                exit;
            }
        }

        $error = "Invalid username or password";

        // Close the statement
        $stmt->close();
    } else {
        die('Error in preparing the SQL statement.');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Login</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card mx-auto" style="max-width: 400px;">
            <div class="card-header bg-primary text-white text-center">
                <h2>Administrator Login</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </form>
                <p class="text-danger mt-3"><?php echo $error; ?></p>

                <!-- Button with animation to go to view_attendance.php -->
                <button onclick="animateButton('view_attendance.php')" class="btn btn-success btn-block mt-3">View Attendance</button>

               
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

    <!-- Anime.js library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.0/anime.min.js"></script>

    <script>
        // Function to animate the login form
        function animateLoginForm() {
            // Define the animation properties
            var animation = anime.timeline({
                easing: 'easeInOutQuad',
                duration: 300
            });

            // Animate the login form elements
            animation
                .add({
                    targets: '.card',
                    translateY: [-100, 0],
                    opacity: [0, 1],
                    delay: 100
                })
                .add({
                    targets: 'form',
                    translateY: [50, 0],
                    opacity: [0, 1],
                    delay: 200
                });
        }

        // Call the animation function when the page is fully loaded
        document.addEventListener('DOMContentLoaded', function () {
            animateLoginForm();
        });

         // Function to animate the button click and navigate to the specified page
         function animateButton(targetPage) {
            // Define the animation properties
            var animation = anime.timeline({
                easing: 'easeInOutQuad',
                duration: 300
            });

            // Animate the button
            animation
                .add({
                    targets: 'button',
                    translateY: [0, 50],
                    opacity: [1, 0],
                    complete: function() {
                        // Redirect to the specified page after animation
                        window.location.href = targetPage;
                    }
                });
        }
    </script>
</body>
</html>