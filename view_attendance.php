<?php
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

// Fetch distinct laboratories from the database
$laboratoriesResult = $mysqli->query("SELECT DISTINCT lab_name FROM labs");
$laboratories = [];

if ($laboratoriesResult->num_rows > 0) {
    while ($row = $laboratoriesResult->fetch_assoc()) {
        $laboratories[] = $row['lab_name'];
    }
}

// Fetch attendance data from the database based on the selected laboratory and search query
$selectedLaboratory = isset($_GET['lab']) ? $mysqli->real_escape_string($_GET['lab']) : '';
$condition = $selectedLaboratory ? "WHERE labs.lab_name = '$selectedLaboratory'" : "";

$searchQuery = isset($_GET['search']) ? $mysqli->real_escape_string($_GET['search']) : '';
$searchCondition = $searchQuery
    ? "AND (students.name LIKE '%$searchQuery%'
        OR students.student_id LIKE '%$searchQuery%'
        OR students.courseandsection_id LIKE '%$searchQuery%')"
    : "";

$result = $mysqli->query("SELECT students.*, labs.lab_name 
                         FROM students 
                         JOIN labs ON students.lab_id = labs.id
                         $condition $searchCondition");

$students = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            width: 90%;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 20px;
            display: flex;
            flex-direction: column;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 {
            flex: 1;
            font-size: large;
        }

        h3 {
            color: #333;
            text-align: center;
        }

        table.student-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table.student-table th,
        table.student-table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }

        table.student-table th {
            background-color: blueviolet;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 class="mb-0">Welcome to the College of Computer Studies</h2>
           
        </div>
        <p>You can view your Attendance here</p>
        <form method="GET" class="mb-3">
            <div class="form-group">
                <label for="labDropdown">Select Laboratory:</label>
                <select name="lab" id="labDropdown" class="form-control" onchange="this.form.submit()">
                    <option value="" <?php echo empty($selectedLaboratory) ? 'selected' : ''; ?>>All Laboratories</option>
                    <?php foreach ($laboratories as $lab) : ?>
                        <option value="<?php echo $lab; ?>" <?php echo $selectedLaboratory == $lab ? 'selected' : ''; ?>><?php echo $lab; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="searchInput">Search by Name, Student ID, or Section:</label>
                <input type="text" name="search" id="searchInput" class="form-control" value="<?php echo $searchQuery; ?>" placeholder="Enter name, student ID, or section">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        
        <h3>View All Attendance<?php echo $selectedLaboratory ? " - Laboratory: $selectedLaboratory" : ''; ?></h3>

        <table id="attendanceTable" class="table table-bordered student-table">
            <thead class="thead-dark">
                <tr>
                    <th>Name</th>
                    <th>Time In</th>
                    <th>Student ID</th>
                    <th>Course and Section</th>
                    <th>Laboratory</th>
                </tr>
            </thead>
            <tbody>
                <!-- Table rows will be dynamically updated using JavaScript -->
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- jQuery for AJAX and real-time updates -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <script>
        // Function to update attendance data
        function updateAttendanceData() {
            // Your AJAX logic to fetch updated data from the server
            $.ajax({
    url: 'get_attendance_data.php',
    method: 'GET',
    data: { lab: '<?php echo $selectedLaboratory; ?>', search: '<?php echo $searchQuery; ?>' },
    dataType: 'json',
    success: function (data) {
        // Update the table with the new data
        updateTable(data);
    },
    error: function (error) {
        console.error('Error fetching data:', error);
    }
});

        }

        // Function to update the table with new data
        function updateTable(data) {
            // Clear existing table rows
            $('#attendanceTable tbody').empty();

            // Append new data to the table
            data.forEach(function (student) {
                $('#attendanceTable tbody').append(`
                    <tr>
                        <td>${student.name}</td>
                        <td>${student.time_in}</td>
                        <td>${student.student_id}</td>
                        <td>${student.courseandsection_id}</td>
                        <td>${student.lab_name}</td>
                    </tr>
                `);
            });
        }

        // Call updateAttendanceData every 1 second
        setInterval(updateAttendanceData, 1000);

        // Initial data load
        updateAttendanceData();
    </script>
</body>
</html>

<?php
// Close the database connection
$mysqli->close();
?>
