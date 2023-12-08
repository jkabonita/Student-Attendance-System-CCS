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

// Close the database connection
$mysqli->close();

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($students);
?>
