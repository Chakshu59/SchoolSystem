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

//Prepare and execute the query
$stmt = $conn->prepare("SELECT course_id, section_id, semester, year FROM section WHERE instructor_id = ?;");
$stmt->bind_param("s", $_SESSION['instructor_id']);
$stmt->execute();
$result = $stmt->get_result(); //Use get_result() to fetch data

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course History</title>
</head>
<body>
    <table border="1">
        <thead>
            <tr>
                <th>Course ID</th>
                <th>Section ID</th>
                <th>Semester</th>
                <th>Year</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) { // Fetch associative array
                    echo "<tr>
                            <td>" . htmlspecialchars($row["course_id"]) . "</td>
                            <td>" . htmlspecialchars($row["section_id"]) . "</td>
                            <td>" . htmlspecialchars($row["semester"]) . "</td>
                            <td>" . htmlspecialchars($row["year"]) . "</td>
                            <td><a href='showStudents.php?course_id=" . $row["course_id"] . "&section_id=" . $row["section_id"] . "&semester=" . $row["semester"] . "&year=" . $row["year"] . "'>Show Students</a></td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No Sections Found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
