<?php
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

$registrationError = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newUsername = $mysqli->real_escape_string($_POST["new_username"]);
    $newPassword = $_POST["new_password"];

    // Use a prepared statement to prevent SQL injection
    $insertQuery = "INSERT INTO admin_users (username, password) VALUES (?, ?)";
    $stmt = $mysqli->prepare($insertQuery);

    if ($stmt) {
        $stmt->bind_param("ss", $newUsername, $newPassword);
        $stmt->execute();

        // Check if the registration was successful
        if ($stmt->affected_rows > 0) {
            $registrationError = "Registration successful. You can now log in.";
        } else {
            $registrationError = "Error in registration. Please try again.";
        }

        // Close the statement
        $stmt->close();
    } else {
        die('Error in preparing the SQL statement.');
    }
}

// Close the database connection
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <!-- Montserrat font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:400,700&display=swap">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-image: url('cspc.jpg'); /* Background image */
            background-size: cover;
            background-position: center;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

      

        .card-header {
            background-color: #007bff;
        }

        body,
        h2,
        p {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card mx-auto" style="max-width: 400px;">
            <div class="card-header bg-primary text-white text-center">
                <h2>Register</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="new_username">New Username:</label>
                        <input type="text" class="form-control" name="new_username" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" class="form-control" name="new_password" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">Register</button>
                </form>
                <p class="text-danger mt-3"><?php echo $registrationError; ?></p>
                <p>Already have an account? <a href="index.php">Login here</a>.</p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>
