<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "DB2";

//Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

//Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $section_id = $_POST['section_id'];
    $semester = $_POST['semester'];
    $year = $_POST['year'];
    $instructor_id = !empty($_POST['instructor_id']) ? $_POST['instructor_id'] : NULL;
    $classroom_id = !empty($_POST['classroom_id']) ? $_POST['classroom_id'] : NULL;
    $time_slot_id = !empty($_POST['time_slot_id']) ? $_POST['time_slot_id'] : NULL;

    $stmt = $conn->prepare("UPDATE section SET instructor_id = ?, classroom_id = ?, time_slot_id = ? WHERE course_id = ? AND section_id = ? AND year = ? AND semester = ?;");
    $stmt->bind_param("sssssss", $instructor_id, $classroom_id, $time_slot_id, $course_id, $section_id, $year, $semester);
    $stmt->execute();
    $stmt->close();
}

header("Location: editclasses.php");
exit();
?>