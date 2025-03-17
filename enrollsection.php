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
    header("Location: login.html"); //Redirect to login page if not logged in
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST["course_id"];
    $course_name = $_POST["course_name"];

    if (isset($_POST["section_id"])) {
        $student_id = $_SESSION["student_id"];
        $section_id = $_POST["section_id"];
        $semester = $_POST["semester"];
        $year = $_POST["year"];
        $instructor_id = $_POST["instructor_id"];
        $classroom_id = $_POST["classroom_id"];
        $time_slot_id = $_POST["time_slot_id"];
        $grade = NULL;

        $stmt = $conn->prepare("SELECT * FROM take WHERE student_id = ? AND course_id = ? AND section_id = ? AND semester = ? AND year = ?;");
        $stmt->bind_param("sssss", $student_id, $course_id, $section_id, $semester, $year);
        $stmt->execute();
        $stmt = $stmt->get_result();
        
        if ($stmt->num_rows > 0) {
            echo "Error: Already enrolled in this section";
            $stmt->close();
        } else {
            $stmt->close();
            //Insert the new info into the database
            $stmt = $conn->prepare("INSERT INTO take(student_id, course_id, section_id, semester, year, grade) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $student_id, $course_id, $section_id, $semester, $year, $grade);

            if ($stmt->execute()) {
                echo "Enrolled in $section_id for $course_name";
            } else {
                echo "Error enrolling in section: " . $stmt->error;
            } 
        }
    }
}

$stmt = $conn->prepare("SELECT section_id, semester, year, instructor_id, classroom_id, time_slot_id FROM section WHERE course_id = ? ORDER BY section_id;");
$stmt->bind_param("s", $course_id);
$stmt->execute();

$stmt = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll in Section</title>
</head>
<body>
    <h2>Sections For <?php echo htmlspecialchars($course_name); ?></h2>

    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Semester</th>
                <th>Year</th>
                <th>Instructor</th>
                <th>Classroom</th>
                <th>Time Slot</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
                if ($stmt->num_rows > 0) {
                    while($row = $stmt->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row["section_id"] . "</td>
                                <td>" . $row["semester"] . "</td>
                                <td>" . $row["year"] . "</td>
                                <td>" . $row["instructor_id"] . "</td>
                                <td>" . $row["classroom_id"] . "</td>
                                <td>" . $row["time_slot_id"] . "</td>
                                <td>
                                    <form action='enrollsection.php' method='POST' style='display:inline;'>
                                        <input type='hidden' name='section_id' value='" . $row["section_id"] . "'>
                                        <input type='hidden' name='course_id' value='" . $course_id . "'>
                                        <input type='hidden' name='course_name' value='" . htmlspecialchars($course_name) . "'>
                                        <input type='hidden' name='semester' value='" . $row["semester"] . "'>
                                        <input type='hidden' name='year' value='" . $row["year"] . "'>
                                        <input type='hidden' name='instructor_id' value='" . $row["instructor_id"] . "'>
                                        <input type='hidden' name='classroom_id' value='" . $row["classroom_id"] . "'>
                                        <input type='hidden' name='time_slot_id' value='" . $row["time_slot_id"] . "'>
                                        <button type='submit'>Enroll</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No Sections Found</td></tr>";
                }
            ?>
        </tbody>
    </table>

    <button onclick="window.location.href='enrollcourse.php'">Back</button>
    
    <p><a href="logout.php">Logout</a></p>
</body>
</html>