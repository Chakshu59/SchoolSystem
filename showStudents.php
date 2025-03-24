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
if (!isset($_SESSION['email']) || !isset($_SESSION['instructor_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

$course_id = $_GET['course_id'];
$section_id = $_GET['section_id'];
$semester = $_GET['semester'];
$year = $_GET['year'];

$stmt = $conn->prepare("SELECT s.student_id, s.name, t.grade FROM student s JOIN take t ON s.student_id = t.student_id WHERE t.course_id = ? AND t.section_id = ? AND t.semester = ? AND t.year = ?");
$stmt->bind_param("sssi", $course_id, $section_id, $semester, $year);
$stmt->execute();
$result = $stmt->get_result(); //Use get_result() to fetch data
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrolled Students</title>
</head>
<body>
    <h1>Students Enrolled in Course <?php echo htmlspecialchars($course_id); ?> - Section <?php echo htmlspecialchars($section_id); ?></h1>
    <table border="1">
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row["student_id"]) . "</td>
                            <td>" . htmlspecialchars($row["name"]) . "</td>
                            <td>" . htmlspecialchars($row["grade"]) . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No students enrolled in this section</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
