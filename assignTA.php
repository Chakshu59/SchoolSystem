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

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

$course_id = $section_id = $semester = $year = $instructor_id = $classroom_id = $time_slot_id = $selected_section_id = $selected_semester = $selected_year = $student_id = $del_SID = $del_SEC = $del_crs = $del_sem = $del_year = $del_TA = "";
$name = NULL;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];
    
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['student_id']) && isset($_POST['section_id']) && isset($_POST['course_id']) && isset($_POST['semester']) && isset($_POST['year'])) {
    $student_id = $_POST['student_id'];
    $section_id = $_POST['section_id'];
    $course_id = $_POST['course_id'];
    $semester = $_POST['semester'];
    $year = $_POST['year'];

    $delete_ta = $conn->prepare("SELECT * FROM ta WHERE student_id = ?;");
    $delete_ta->bind_param("s", $student_id);
    $delete_ta->execute();

    if ($row = $delete_ta->get_result()->fetch_assoc()) {
        $del_SID = $row['student_id'];
        $del_SEC = $row['section_id'];
        $del_crs = $row['course_id'];
        $del_sem = $row['semester'];
        $del_year = $row['year'];
    }
    $delete_ta->close();
    $delete_TA = $conn->prepare("DELETE FROM ta WHERE student_id = ? AND course_id = ? AND section_id = ? AND year = ? AND semester = ?;");
    $delete_TA->bind_param("sssss", $del_SID, $del_crs, $del_SEC, $del_year, $del_sem);
    $delete_TA->execute();
    $delete_TA->close();

    $delete_sec = $conn->prepare("SELECT * FROM ta WHERE course_id = ? AND section_id = ? AND year = ? AND semester = ?;");
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
    $delete_SEC = $conn->prepare("DELETE FROM ta WHERE student_id = ? AND course_id = ? AND section_id = ? AND year = ? AND semester = ?;");
    $delete_SEC->bind_param("sssss", $del_SID, $del_crs, $del_SEC, $del_year, $del_sem);
    $delete_SEC->execute();
    $delete_SEC->close();

    $add_ta = $conn->prepare("INSERT INTO ta (student_id, course_id, section_id, semester, year) VALUES (?, ?, ?, ?, ?);");
    $add_ta->bind_param("sssss", $student_id, $course_id, $section_id, $semester, $year);
    $add_ta->execute();
    $add_ta->close();

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
    <h1>Assign TA</h1>
    <form action="assignTA.php" method="post">
        <h2>Select Course:</h2>
        <?php
            $stmt = $conn->prepare('SELECT section_id, count(DISTINCT student_id) AS num_students, year, semester FROM take where course_id = ? GROUP BY section_id HAVING count(DISTINCT student_id) > 9;');
            $stmt->bind_param("s", $course_id);
            $stmt->execute();
            $stmt = $stmt->get_result();
            if ($stmt->num_rows > 0) {
                while ($row = $stmt->fetch_assoc()) {
                    echo "<label>
                    <input type='radio' name='section_id' value='{$row["section_id"]}' 
                        onclick='setSectionDetails({$row["section_id"]}, \"{$row["semester"]}\", \"{$row["year"]}\")'>
                        Section: {$row["section_id"]} | {$row["semester"]} | {$row["year"]}
                    </label><br>";

                    echo "<input type='hidden' name='semester' value='{$row["semester"]}'>";
                    echo "<input type='hidden' name='year' value='{$row["year"]}'>";
                }
            } else {
                echo "<p>No Sections Available for TA.</p>";
            }
            $stmt->close();
        ?>

        <h3>Select TA:</h3>
        <?php
            $stmt = $stmt = $conn->query("SELECT p.student_id, s.name FROM phd p, student s WHERE p.student_id = s.student_id;");
            if ($stmt->num_rows > 0) {
                while ($row = $stmt->fetch_assoc()) {
                    echo "<label>
                    <input type='radio' name='student_id' value='{$row["student_id"]}' required>
                        Student: {$row["student_id"]} | {$row["name"]}
                    </label><br>";
                }
            } else {
                echo "<p>No TAs Available.</p>";
            }
            $stmt->close();
        ?>
        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">

        <button type="submit" name="assign_ta">Assign</button>
    </form>
    
    <button onclick="window.location.href='editclasses.php'">Back</button>

</body>
</html>