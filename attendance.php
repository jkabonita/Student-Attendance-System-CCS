<?php
session_start();

// Database connection parameters
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'attendance_system';

// Create a connection to the database
$mysqli = new mysqli($host, $username, $password, $database);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Fetch labs data from the database for drop-down list
$labsResult = $mysqli->query("SELECT * FROM labs");
$labs = [];

if ($labsResult->num_rows > 0) {
    while ($labRow = $labsResult->fetch_assoc()) {
        $labs[] = $labRow;
    }
}

// Fetch distinct courseandsection_id values for the "Course and Section" input
$courseAndSectionsResult = $mysqli->query("SELECT DISTINCT courseandsection_id FROM students");
$courseAndSections = [];

if ($courseAndSectionsResult->num_rows > 0) {
    while ($row = $courseAndSectionsResult->fetch_assoc()) {
        $courseAndSections[] = $row['courseandsection_id'];
    }
}

// Edit functionality
if (isset($_GET['edit_id'])) {
    $editId = $mysqli->real_escape_string($_GET['edit_id']);
    $editResult = $mysqli->query("SELECT * FROM students WHERE id = '$editId'");

    if ($editResult->num_rows > 0) {
        $editStudent = $editResult->fetch_assoc();
    }
}

// Update functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $updateId = $mysqli->real_escape_string($_POST['update_id']);
    $name = $mysqli->real_escape_string($_POST['name']);
    $studentId = $mysqli->real_escape_string($_POST['student_id']);
    $courseAndSection = $mysqli->real_escape_string($_POST['courseandsection_id']);
    $labId = $mysqli->real_escape_string($_POST['lab']);

    $updateQuery = "UPDATE students 
                    SET name = '$name', student_id = '$studentId', 
                        courseandsection_id = '$courseAndSection', lab_id = '$labId' 
                    WHERE id = '$updateId'";

    if ($mysqli->query($updateQuery) !== TRUE) {
        echo "Error updating record: " . $mysqli->error;
    }
}

// Delete functionality
if (isset($_GET['delete_id'])) {
    $deleteId = $mysqli->real_escape_string($_GET['delete_id']);
    $deleteQuery = "DELETE FROM students WHERE id = '$deleteId'";

    if ($mysqli->query($deleteQuery) !== TRUE) {
        echo "Error deleting record: " . $mysqli->error;
    }
}

// Fetch attendance data from the database
$result = $mysqli->query("SELECT students.*, labs.lab_name 
                         FROM students 
                         JOIN labs ON students.lab_id = labs.id");

$students = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Add functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && !isset($_POST['update_id'])) {
    $name = $mysqli->real_escape_string($_POST['name']);
    $studentId = $mysqli->real_escape_string($_POST['student_id']);
    $courseAndSection = $mysqli->real_escape_string($_POST['courseandsection_id']);
    $labId = $mysqli->real_escape_string($_POST['lab']);

    $addQuery = "INSERT INTO students (name, student_id, courseandsection_id, lab_id) 
                 VALUES ('$name', '$studentId', '$courseAndSection', '$labId')";

    if ($mysqli->query($addQuery) !== TRUE) {
        echo "Error adding record: " . $mysqli->error;
    } else {
        // Redirect after successful form submission
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance</title>
    <!-- Bootstrap CSS with animated class -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">

    <style>
        .fade-in {
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <div class="container fade-in p-4 rounded shadow">
        <div class="header">
            <?php if (isset($_SESSION['username'])) : ?>
                <h2 class="mb-0">Welcome to Student Attendance System: <?php echo $_SESSION['username']; ?></h2>
            <?php endif; ?>
            <div class="logout-button">
                <button class="btn btn-danger" onclick="logout()">Logout</button>
            </div>
        </div>

        <h2 class="mb-3">View Attendance</h2>

        <table class="table table-bordered">
            <tr>
                <th>Name</th>
                <th>Time In</th>
                <th>Student ID</th>
                <th>Course and Section</th>
                <th>Laboratory</th>
                <th>Action</th>
            </tr>
            <?php foreach ($students as $key => $student) : ?>
                <tr>
                    <td><?php echo $student['name']; ?></td>
                    <td><?php echo $student['time_in']; ?></td> <!-- Make sure 'time_in' is a valid column in your database -->
                    <td><?php echo $student['student_id']; ?></td>
                    <td><?php echo $student['courseandsection_id']; ?></td>
                    <td><?php echo $student['lab_name']; ?></td>
                    <td>
                        <a href="?edit_id=<?php echo $student['id']; ?>">Edit</a> |
                        <a href="?delete_id=<?php echo $student['id']; ?>" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- Edit Student form -->
        <?php if (isset($editStudent)) : ?>
            <h3>Edit Student</h3>
            <form method="POST">
                <!-- Add hidden input field to store the ID for update -->
                <input type="hidden" name="update_id" value="<?php echo $editStudent['id']; ?>">
                <label for="labDropdown">Select Laboratory:</label>
                <select name="lab" id="labDropdown" class="form-control">
                    <option value="" disabled selected>Select Laboratory</option>
                    <?php foreach ($labs as $lab) : ?>
                        <option value="<?php echo $lab['id']; ?>" <?php echo ($lab['id'] == $editStudent['lab_id']) ? 'selected' : ''; ?>><?php echo $lab['lab_name']; ?></option>
                    <?php endforeach; ?>
                </select><br><br>

                <label for="courseAndSection">Course and Section:</label>
                <input type="text" name="courseandsection_id" id="courseAndSection" value="<?php echo $editStudent['courseandsection_id']; ?>" required><br><br>

                <label for="name">Name:</label>
                <input type="text" name="name" value="<?php echo $editStudent['name']; ?>" required><br><br>
                <label for="student_id">Student ID:</label>
                <input type="text" name="student_id" value="<?php echo $editStudent['student_id']; ?>" required><br><br>
                <input type="submit" class="btn btn-primary" value="Update Student">
            </form>
        <?php endif; ?>

        <h3 class="mt-4">Add a New Student</h3>
        <form method="POST">
            <label for="labDropdown">Select Laboratory:</label>
            <select name="lab" id="labDropdown" class="form-control">
                <option value="" disabled selected>Select Laboratory</option>
                <?php foreach ($labs as $lab) : ?>
                    <option value="<?php echo $lab['id']; ?>"><?php echo $lab['lab_name']; ?></option>
                <?php endforeach; ?>
            </select><br><br>

            <label for="courseAndSection">Course and Section:</label>
            <input type="text" name="courseandsection_id" id="courseAndSection" required><br><br>

            <label for="name">Name:</label>
            <input type="text" name="name" required><br><br>
            <label for="student_id">Student ID:</label>
            <input type="text" name="student_id" required><br><br>
            <input type="submit" class="btn btn-primary" name="submit" value="Add Student">
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

    <!-- Your existing JavaScript code -->
    <script>
        function logout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = 'logout.php';
            }
        }

        // Add a fade-in effect when the page is fully loaded
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelector('.container').classList.add('fade-in');
        });
    </script>

</body>

</html>

<?php
// Close the database connection
$mysqli->close();
?>
