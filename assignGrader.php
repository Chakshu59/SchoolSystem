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

$course_id = $section_id = $semester = $year = $instructor_id = $student_id = "";
$name = NULL;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['course_id']) && isset($_POST['section_id']) && isset($_POST['semester']) && isset($_POST['year'])) {
    $student_id = $_POST['student_id'];
    $section_id = $_POST['section_id'];
    $course_id = $_POST['course_id'];
    $semester = $_POST['semester'];
    $year = $_POST['year'];

    $checkUndergrad = $conn->prepare("SELECT * FROM undergraduate WHERE student_id = ?;");
    $checkUndergrad->bind_param("s", $student_id);
    $checkUndergrad->execute();
    $result = $checkUndergrad->get_result();

    if ($result->num_rows > 0) {  // Undergrad
        //Delete if student is already a grader for a different class that semester
        $delete_un_grader = $conn->prepare("SELECT * FROM undergraduategrader WHERE student_id = ? AND year = ? AND semester = ?;");
        $delete_un_grader->bind_param("sss", $student_id, $year, $semester);
        $delete_un_grader->execute();
        if ($row = $delete_un_grader->get_result()->fetch_assoc()) {
            $del_SID = $row['student_id'];
            $del_SEC = $row['section_id'];
            $del_crs = $row['course_id'];
            $del_sem = $row['semester'];
            $del_year = $row['year'];
        }
        $delete_un_grader->close();
        $delete_UN = $conn->prepare("DELETE FROM undergraduategrader WHERE student_id = ? AND course_id = ? AND section_id = ? AND year = ? AND semester = ?;");
        $delete_UN->bind_param("sssss", $del_SID, $del_crs, $del_SEC, $del_year, $del_sem);
        $delete_UN->execute();
        $delete_UN->close();

        //Delete a student if already a grader in the section
        $delete_sec = $conn->prepare("SELECT * FROM undergraduategrader WHERE course_id = ? AND section_id = ? AND year = ? AND semester = ?;");
        $delete_sec->bind_param("ssss", $course_id, $section_id, $year, $semester);
        $delete_sec->execute();
        if($row = $delete_sec->get_result()->fetch_assoc()) {
            $del_SID = $row['student_id'];
            $del_SEC = $row['section_id'];
            $del_crs = $row['course_id'];
            $del_sem = $row['semester'];
            $del_year = $row['year'];
        }
        $delete_sec->close();
        $delete_SEC = $conn->prepare("DELETE FROM undergraduategrader WHERE student_id = ? AND course_id = ? AND section_id = ? AND year = ? AND semester = ?;");
        $delete_SEC->bind_param("sssss", $del_SID, $del_crs, $del_SEC, $del_year, $del_sem);
        $delete_SEC->execute();
        $delete_SEC->close();

        //Add the new student as a grader
        $add_UN = $conn->prepare("INSERT INTO undergraduategrader (student_id, course_id, section_id, semester, year) VALUES (?, ?, ?, ?, ?);");
        $add_UN->bind_param("sssss", $student_id, $course_id, $section_id, $semester, $year);
        $add_UN->execute();
        $add_UN->close();

    } else {  // check masters
        $checkMasters = $conn->prepare("SELECT * FROM mastergrader WHERE student_id = ?;");
        $checkMasters->bind_param("s", $student_id);
        $checkMasters->execute();
        $result = $checkMasters->get_result();

        if ($result->num_rows > 0) { // Masters

            //Delete if student is already a grader for a different class that semester
            $delete_ms_grader = $conn->prepare("SELECT * FROM mastergrader WHERE student_id = ? AND year = ? AND semester = ?;");
            $delete_ms_grader->bind_param("sss", $student_id, $year, $semester);
            $delete_ms_grader->execute();
            if ($row = $delete_ms_grader->get_result()->fetch_assoc()) {
                $del_SID = $row['student_id'];
                $del_SEC = $row['section_id'];
                $del_crs = $row['course_id'];
                $del_sem = $row['semester'];
                $del_year = $row['year'];
            }
            $delete_MS->close();
            $delete_MS = $conn->prepare("DELETE FROM mastergrader WHERE student_id = ? AND course_id = ? AND section_id = ? AND year = ? AND semester = ?;");
            $delete_MS->bind_param("sssss", $del_SID, $del_crs, $del_SEC, $del_year, $del_sem);
            $delete_MS->execute();
            $delete_MS->close();

            //Delete a student if already a grader in the section
            $delete_sec = $conn->prepare("SELECT * FROM mastergrader WHERE course_id = ? AND section_id = ? AND year = ? AND semester = ?;");
            $delete_sec->bind_param("ssss", $course_id, $section_id, $year, $semester);
            $delete_sec->execute();
            if($row = $delete_sec->get_result()->fetch_assoc()) {
                $del_SID = $row['student_id'];
                $del_SEC = $row['section_id'];
                $del_crs = $row['course_id'];
                $del_sem = $row['semester'];
                $del_year = $row['year'];
            }
            $delete_sec->close();
            $delete_SEC = $conn->prepare("DELETE FROM mastergrader WHERE student_id = ? AND course_id = ? AND section_id = ? AND year = ? AND semester = ?;");
            $delete_SEC->bind_param("sssss", $del_SID, $del_crs, $del_SEC, $del_year, $del_sem);
            $delete_SEC->execute();
            $delete_SEC->close();

            //Add the new student as a grader
            $add_UN = $conn->prepare("INSERT INTO mastergrader (student_id, course_id, section_id, semester, year) VALUES (?, ?, ?, ?, ?);");
            $add_UN->bind_param("sssss", $student_id, $course_id, $section_id, $semester, $year);
            $add_UN->execute();
            $add_UN->close();

        } else {
            echo "Error: Student ID not found.";
            exit();
        }
    }
    
    header("Location: editclasses.php");
    exit();
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
    <h1>Assign Grader</h1>
    <form action="assignGrader.php" method="post">
        <h2>Select Course:</h2>
        <?php
            $stmt = $conn->prepare('SELECT section_id, count(DISTINCT student_id) AS num_students, year, semester FROM take where course_id = ? GROUP BY section_id HAVING (count(DISTINCT student_id) <11 AND count(DISTINCT student_id) > 4);');
            $stmt->bind_param("s", $course_id);
            $stmt->execute();
            $stmt = $stmt->get_result();
            if ($stmt->num_rows > 0) {
                while ($row = $stmt->fetch_assoc()) {
                    echo "<label>
                    <input type='radio' name='section_id' value='{$row["section_id"]}' required>
                        Section: {$row["section_id"]} | {$row["semester"]} | {$row["year"]}
                    </label><br>";

                    echo "<input type='hidden' name='section_id' value='{$row["section_id"]}'>";
                    echo "<input type='hidden' name='semester' value='{$row["semester"]}'>";
                    echo "<input type='hidden' name='year' value='{$row["year"]}'>";
                }
            } else {
                echo "<p>No Sections Available for Grader.</p>";
            }
            $stmt->close();
        ?>

        <h3>Select Grader:</h3>
        <?php
            $stmt = $conn->prepare("SELECT combined.student_id, combined.name FROM ((SELECT un.student_id, s.name FROM undergraduate un, student s WHERE un.student_id = s.student_id) UNION (SELECT ms.student_id, s.name FROM master ms, student s WHERE ms.student_id = s.student_id)) AS combined, take t where t.student_id = combined.student_id AND ((t.grade = 'A' OR t.grade = 'A-') AND t.course_id = ?);");
            $stmt->bind_param("s", $course_id);
            $stmt->execute();
            $stmt = $stmt->get_result();
            if ($stmt->num_rows > 0) {
                while ($row = $stmt->fetch_assoc()) {
                    echo "<label>
                    <input type='radio' name='student_id' value='{$row["student_id"]}' required>
                        Student: {$row["student_id"]} | {$row["name"]}
                    </label><br>";
                }
            } else {
                echo "<p>No Graders Available.</p>";
            }
            $stmt->close();
        ?>
       <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">

       <button type="submit" name="assign_grader">Assign</button>
    </form>
    
    <button onclick="window.location.href='editclasses.php'">Back</button>

</body>
</html>