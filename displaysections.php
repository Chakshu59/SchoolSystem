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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];

    if (isset($_POST['new_section_id'])) {
        $new_section_id = $_POST['new_section_id'];
        $semester = !empty($_POST['semester']) ? $_POST['semester'] : NULL;
        $year = !empty($_POST['year']) ? $_POST['year'] : NULL;
        $instructor_id = !empty($_POST['instructor_id']) ? $_POST['instructor_id'] : NULL;
        $classroom_id = !empty($_POST['classroom_id']) ? $_POST['classroom_id'] : NULL;
        $time_slot_id = !empty($_POST['time_slot_id']) ? $_POST['time_slot_id'] : NULL;
        $course_id = !empty($_POST['course_id']) ? $_POST['course_id'] : NULL;

        // Check if the section_id already exists
        $stmt = $conn->prepare("SELECT section_id FROM section WHERE section_id = ? AND year = ? AND semester = ? AND course_id = ?;");
        $stmt->bind_param("ssss", $new_section_id, $year, $semester, $course_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "Error: Section ID already exists.";
            $stmt->close();
            exit();
        }
        $stmt->close();

        //Insert the new section into the database
        $stmt = $conn->prepare("INSERT INTO section (section_id, course_id, semester, year, instructor_id, classroom_id, time_slot_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $new_section_id, $course_id, $semester, $year, $instructor_id, $classroom_id, $time_slot_id);

        if ($stmt->execute()) {
            echo "Section added successfully.";
        } else {
            echo "Error adding section: " . $stmt->error;
        }

        $stmt->close();
    }

    $stmt = $conn->prepare("SELECT course_name FROM course WHERE course_id = ?;");
    $stmt->bind_param("s", $course_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($name);
        $stmt->fetch();
    } else {
        $name = NULL;
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT s.section_id, s.semester, s.year, i.instructor_name, s.classroom_id, s.time_slot_id FROM section s LEFT JOIN instructor i ON s.instructor_id = i.instructor_id WHERE course_id = ?;");
    $stmt->bind_param("s", $course_id);
    $stmt->execute();
    $stmt = $stmt->get_result();

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
</head>
<body>
    <h2>Sections For <?php echo htmlspecialchars($name); ?></h2>

    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Semester</th>
                <th>Year</th>
                <th>Instructor</th>
                <th>Classroom</th>
                <th>Time Slot</th>
                <th>TA</th>
                <th>Grader</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
                $taQuery = $conn->prepare("SELECT student_id AS ta_student_id FROM ta WHERE section_id = ? AND semester = ? AND year = ? AND course_id = ?;");
                $taQuery->bind_param("ssss", $section_id, $semester, $year, $course_id);

                $graderUNQuery = $conn->prepare("SELECT student_id AS UN_grader_student_id FROM undergraduategrader WHERE section_id = ? AND semester = ? AND year = ? AND course_id = ?;");
                $graderUNQuery->bind_param("ssss", $section_id, $semester, $year, $course_id);

                $graderMSQuery = $conn->prepare("SELECT student_id AS MS_grader_student_id FROM mastergrader WHERE section_id = ? AND semester = ? AND year = ? AND course_id = ?;");
                $graderMSQuery->bind_param("ssss", $section_id, $semester, $year, $course_id);

                if ($stmt->num_rows > 0) {
                    while($row = $stmt->fetch_assoc()) {
                        $section_id = $row["section_id"];
                        $semester = $row["semester"];
                        $year = $row["year"]; 

                        $taQuery->execute();
                        $taResult = $taQuery->get_result();
                        $taRow = $taResult->fetch_assoc();
                        $taStudentID = $taRow ? $taRow["ta_student_id"] : "";

                        $graderMSQuery->execute();
                        $graderMSResult = $graderMSQuery->get_result();
                        $graderMSRow = $graderMSResult->fetch_assoc();
                        
                        $graderUNQuery->execute();
                        $graderUNResult = $graderUNQuery->get_result();
                        $graderUNRow = $graderUNResult->fetch_assoc();
                        
                        if ($graderUNRow && isset($graderUNRow["UN_grader_student_id"])) {
                            $graderStudentID = $graderUNRow["UN_grader_student_id"];
                        } elseif ($graderMSRow && isset($graderMSRow["MS_grader_student_id"])) {
                            $graderStudentID = $graderMSRow["MS_grader_student_id"];
                        } else {
                            $graderStudentID = ""; // Or some default value/message
                        }

                        echo "<tr>
                                <td>" . $row["section_id"] . "</td>
                                <td>" . $row["semester"] . "</td>
                                <td>" . $row["year"] . "</td>
                                <td>" . $row["instructor_name"] . "</td>
                                <td>" . $row["classroom_id"] . "</td>
                                <td>" . $row["time_slot_id"] . "</td>
                                <td>" . htmlspecialchars($taStudentID) . "</td>
                                <td>" . htmlspecialchars($graderStudentID) . "</td>
                                <td>
                                    <form action='editsection.php' method='POST' style='display:inline;'>
                                        <input type='hidden' name='section_id' value='" . $row["section_id"] . "'>
                                        <input type='hidden' name='course_id' value='" . $course_id . "'>
                                        <input type='hidden' name='semester' value='" . $row["semester"] . "'>
                                        <input type='hidden' name='year' value='" . $row["year"] . "'>
                                        <input type='hidden' name='instructor_id' value='" . $row["instructor_name"] . "'>
                                        <input type='hidden' name='classroom_id' value='" . $row["classroom_id"] . "'>
                                        <input type='hidden' name='time_slot_id' value='" . $row["time_slot_id"] . "'>
                                        <button type='submit'>Edit</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No Sections Found</td></tr>";
                }
                $taQuery->close();
            ?>
        </tbody>
    </table>

    <form action="assignTA.php" method="post">
        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_id); ?>">
        <button type="submit">Assign TA</button>
    </form>

    <form action="assignGrader.php" method="post">
        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_id); ?>">
        <button type="submit">Assign Grader</button>
    </form>
    
    <h3>Add A New Section</h3>
    <form action="displaysections.php" method="post">
        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_id); ?>">
        New Section ID: <input type="text" name="new_section_id" required><br>
        Year:<input type="text" name="year"><br>
        Semester:
        <input type="radio" name="semester" value="Fall">Fall
        <input type="radio" name="semester" value="Spring">Spring
        <input type="radio" name="semester" value="Summer">Summer
        <input type="radio" name="semester" value="Winter">Winter<br>
        <button type="submit">Add Section</button>
    </form>

    <button onclick="window.location.href='editclasses.php'">Back</button>
    
    <p><a href="logout.php">Logout</a></p>
</body>
</html>