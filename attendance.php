<?php
// Start a session to manage user login status
session_start();

// Check if the user is not logged in, redirect to the login page
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

// Database connection details
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'attendance_system';

// Create a MySQLi object for database connection
$mysqli = new mysqli($host, $username, $password, $database);

// Check for database connection errors
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Check if the user is not logged in, redirect to the login page (double-check)
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

// Fetch all labs from the 'labs' table
$labsResult = $mysqli->query("SELECT * FROM labs");
$labs = [];

// Populate the $labs array with lab details
if ($labsResult->num_rows > 0) {
    while ($labRow = $labsResult->fetch_assoc()) {
        $labs[] = $labRow;
    }
}

// Fetch distinct course and section IDs from the 'students' table
$courseAndSectionsResult = $mysqli->query("SELECT DISTINCT courseandsection_id FROM students");
$courseAndSections = [];

// Populate the $courseAndSections array with distinct course and section IDs
if ($courseAndSectionsResult->num_rows > 0) {
    while ($row = $courseAndSectionsResult->fetch_assoc()) {
        $courseAndSections[] = $row['courseandsection_id'];
    }
}

// If an 'edit_id' is provided in the URL, fetch the student details for editing
if (isset($_GET['edit_id'])) {
    $editId = $mysqli->real_escape_string($_GET['edit_id']);
    $editResult = $mysqli->query("SELECT * FROM students WHERE id = '$editId'");

    if ($editResult->num_rows > 0) {
        $editStudent = $editResult->fetch_assoc();
    }
}

// If the form is submitted for updating a student record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    // Extract and sanitize form data
    $updateId = $mysqli->real_escape_string($_POST['update_id']);
    $name = $mysqli->real_escape_string($_POST['name']);
    $studentId = $mysqli->real_escape_string($_POST['student_id']);
    $courseAndSection = $mysqli->real_escape_string($_POST['courseandsection_id']);
    $labId = $mysqli->real_escape_string($_POST['lab']);

    // Check if a laboratory is selected
    if ($labId == '') {
        echo "Error: Please select a laboratory.";
    } else {
        // Update the student record in the 'students' table
        $updateQuery = "UPDATE students 
                        SET name = '$name', student_id = '$studentId', 
                            courseandsection_id = '$courseAndSection', lab_id = '$labId' 
                        WHERE id = '$updateId'";

        // Check for update query execution errors
        if ($mysqli->query($updateQuery) !== TRUE) {
            echo "Error updating record: " . $mysqli->error;
        }
    }
}

// If a 'delete_id' is provided in the URL, delete the corresponding student record
if (isset($_GET['delete_id'])) {
    $deleteId = $mysqli->real_escape_string($_GET['delete_id']);
    $deleteQuery = "DELETE FROM students WHERE id = '$deleteId'";

    // Check for delete query execution errors
    if ($mysqli->query($deleteQuery) !== TRUE) {
        echo "Error deleting record: " . $mysqli->error;
    }
}

// Fetch student details along with associated lab names from the 'students' and 'labs' tables
$result = $mysqli->query("SELECT students.*, labs.lab_name 
                         FROM students 
                         JOIN labs ON students.lab_id = labs.id");

$students = [];

// Populate the $students array with student details
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

// If the form is submitted for adding a new student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && !isset($_POST['update_id'])) {
    // Extract and sanitize form data
    $name = $mysqli->real_escape_string($_POST['name']);
    $studentId = $mysqli->real_escape_string($_POST['student_id']);
    $courseAndSection = $mysqli->real_escape_string($_POST['courseandsection_id']);
    $labId = $mysqli->real_escape_string($_POST['lab']);
    $addedBy = $_SESSION['username'];

    // Check if a laboratory is selected
    if ($labId == '') {
        echo "Error: Please select a laboratory.";
    } else {
        // Insert a new student record into the 'students' table
        $addQuery = "INSERT INTO students (name, student_id, courseandsection_id, lab_id, added_by) 
                     VALUES ('$name', '$studentId', '$courseAndSection', '$labId', '$addedBy')";

        // Check for insert query execution errors
        if ($mysqli->query($addQuery) !== TRUE) {
            echo "Error adding record: " . $mysqli->error;
        } else {
            // Redirect to the current page after successful record addition
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[Admin] Attendance</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding-top: 60px;
        }

        .fixed-header {
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .scrollable-content {
            overflow-y: auto;
            max-height: calc(100vh - 60px);
        }

        .container {
            animation: fadeIn 0.2s ease-in-out;
            position: relative;
            margin-top: 20px;
        }

        .table th,
        .table td {
            text-align: center;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: fadeIn 0.2s ease-in-out;
            margin-bottom: 20px;
            position: relative;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .logo {
            max-width: 200px;
        }

        .welcome-message {
            font-size: 20px;
            color: #333;
        }

        #realTimeClock {
            font-size: 16px;
            color: #333;
            margin-left: 10px;
        }

        .logout-button {
            margin-left: auto;
        }

        .view-attendance-button {
            margin-left: 10px;
        }

        .form-container {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="fixed-header">
        <div class="header">
            <div>
                <img src="ccs.png" alt="Logo" class="logo">
            </div>
            <?php if (isset($_SESSION['username'])) : ?>
                <div class="welcome-message">
                    Welcome to Student Attendance System: <?php echo $_SESSION['username']; ?>
                </div>
            <?php endif; ?>
            <div id="realTimeClock"></div>
            <div class="logout-button">
                <button class="btn btn-danger" onclick="logout()">Logout: <?php echo $_SESSION['username']; ?></button>
            </div>
            <div class="view-attendance-button">
                <button class="btn btn-primary" onclick="redirectToAttendance()">Attendance List</button>
            </div>
        </div>
    </div>

    <div class="container p-4 rounded shadow scrollable-content">
        <h2 class="mb-3">View Attendance</h2>

        <table class="table table-bordered student-table">
            <thead class="thead-dark">
                <tr>
                    <th>Name</th>
                    <th>Time In</th>
                    <th>Student ID</th>
                    <th>Course and Section</th>
                    <th>Laboratory</th>
                    <th>Instructor</th> 
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $key => $student) : ?>
                    <tr>
                        <td><?php echo $student['name']; ?></td>
                        <td><?php echo $student['time_in']; ?></td>
                        <td><?php echo $student['student_id']; ?></td>
                        <td><?php echo $student['courseandsection_id']; ?></td>
                        <td><?php echo $student['lab_name']; ?></td>
                        <td><?php echo $student['added_by']; ?></td> <!-- Added this line -->
                        <td>
                            <a href="?edit_id=<?php echo $student['id']; ?>">Edit</a> |
                            <a href="?delete_id=<?php echo $student['id']; ?>" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (isset($editStudent)) : ?>
            <h3>Edit Student</h3>
            <form method="POST" class="form-container">
                <input type="hidden" name="update_id" value="<?php echo $editStudent['id']; ?>">
                <div class="form-group">
                    <label for="labDropdown">Select Laboratory:</label>
                    <select name="lab" id="labDropdown" class="form-control">
                        <option value="" disabled>Select Laboratory</option>
                        <?php foreach ($labs as $lab) : ?>
                            <option value="<?php echo $lab['id']; ?>" <?php echo ($lab['id'] == $editStudent['lab_id']) ? 'selected' : ''; ?>><?php echo $lab['lab_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="courseAndSection">Course and Section:</label>
                    <input type="text" name="courseandsection_id" id="courseAndSection" class="form-control" value="<?php echo $editStudent['courseandsection_id']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" name="name" class="form-control" value="<?php echo $editStudent['name']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="student_id">Student ID:</label>
                    <input type="text" name="student_id" class="form-control" value="<?php echo $editStudent['student_id']; ?>" required>
                </div>

                <input type="submit" class="btn btn-primary" value="Update Student">
            </form>
        <?php endif; ?>

        <div class="form-container">
            <h3 class="mt-4">Add a New Student</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="labDropdown">Select Laboratory:</label>
                    <select name="lab" id="labDropdown" class="form-control">
                        <option value="" disabled>Select Laboratory</option>
                        <?php foreach ($labs as $lab) : ?>
                            <option value="<?php echo $lab['id']; ?>" <?php echo ($lab['lab_name'] == 'MAC LAB') ? 'selected' : ''; ?>>
                                <?php echo $lab['lab_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="courseAndSection">Course and Section:</label>
                    <input type="text" name="courseandsection_id" id="courseAndSection" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="student_id">Student ID:</label>
                    <input type="text" name="student_id" class="form-control" required>
                </div>

                <input type="submit" class="btn btn-primary" name="submit" value="Add Student">
            </form>
        </div>
    </div>

    <div id="realTimeClock" style="position: fixed; top: 10px; left: 10px; font-size: 16px; color: #333;"></div>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

    <script>
        function logout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = 'logout.php';
            }
        }

        function redirectToAttendance() {
            window.open('view_attendance.php', '_blank');
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelector('.container').classList.add('fade-in');
        });

        function updateClock() {
            var now = new Date();
            var dateTimeString = now.toLocaleString();
            document.getElementById('realTimeClock').innerHTML = dateTimeString;
        }

        setInterval(updateClock, 1000);

        updateClock();
    </script>
</body>

</html>

<?php
$mysqli->close();
?>
