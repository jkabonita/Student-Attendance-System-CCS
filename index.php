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
    <title>[Admin] Login</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <!-- Montserrat Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            /* Background color */
            background-image: url('cspc.jpg'); /* Optional background image */
            background-size: cover; /* Adjust to 'contain' if needed */
            background-position: center;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .card {
            max-width: 400px;
            width: 100%;
        }

        .card-header {
            background-color: #FDDA0D;
            color: #fff;
            text-align: center;
            padding: 1.25rem;
        }

        .card-header img {
            max-width: 100%; /* Ensure the logo stays within the card header */
            height: auto; /* Maintain the aspect ratio of the logo */
        }

        .card-body {
            padding: 1.25rem;
        }

        .btn-block {
            margin-top: 1.25rem;
        }

        .text-danger {
            color: #dc3545;
        }

        .btn-info {
            background-color: #17a2b8;
            color: #fff;
        }

        .btn-info:hover {
            background-color: #138496;
        }

        .mt-3 {
            margin-top: 0.9375rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card mx-auto">
            <div class="card-header">
                <img src="ccs.png" alt="College of Computer Studies" class="header-logo">

                <h2>Instructor Login</h2>
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
                <button onclick="redirectToPage('view_attendance.php', true)" class="btn btn-info btn-block mt-3"
                    id="viewAttendanceBtn">View Attendance</button>
                <button onclick="redirectToPage('register.php', false)" class="btn btn-info btn-block mt-3">Register</button>
                <p class="mt-3">Are you an Instructor? Click Register to Sign Up</p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

    <script>
        // Function to redirect to the specified page
        function redirectToPage(targetPage, openInNewTab) {
            // Disable the button to prevent multiple clicks
            var viewAttendanceBtn = document.getElementById('viewAttendanceBtn');
            viewAttendanceBtn.disabled = true;

            // Redirect to the specified page after a delay
            setTimeout(function () {
                if (openInNewTab) {
                    window.open(targetPage, '_blank').focus();
                } else {
                    window.location.href = targetPage;
                }

                // Enable the button after the redirect
                viewAttendanceBtn.disabled = false;
            }, 1000); // Adjust the delay (in milliseconds) as needed
        }
    </script>
</body>
</html>
<?php
// Close the database connection
$mysqli->close();
?>
