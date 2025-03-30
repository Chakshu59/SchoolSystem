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

// Get session data
$email = $_SESSION['email'];
$password = $_SESSION['password'];
$user_type = $_SESSION['type'];

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['drop'])) {
    $course_id = $_POST['course_id'];
    $section_id = $_POST['section_id'];
    $year = $_POST['year'];
    $semester = $_POST['semester'];

    // Prepare and execute the delete statement
    $stmt = $conn->prepare("DELETE FROM take WHERE student_id = (SELECT student_id FROM student WHERE email = ?) AND course_id = ? AND section_id = ? AND year = ? AND semester = ?;");
    $stmt->bind_param("sssss", $email, $course_id, $section_id, $year, $semester);
    if ($stmt->execute()) {
        echo "<script>alert('Course dropped successfully!'); window.location.href = '".$_SERVER['PHP_SELF']."';</script>";
    } else {
        echo "<script>alert('Error dropping course: " . $stmt->error . "');</script>";
    }

    $stmt->close();
    //check if there are any students in the waitlist for this section
    $stmt = $conn->prepare("SELECT student_id FROM waitlist WHERE course_id = ? AND section_id = ? AND year = ? AND semester = ? AND priority = 1;");
    $stmt->bind_param("ssss", $course_id, $section_id, $year, $semester);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($student_id);
        $stmt->fetch();
        //delete the student from the waitlist
        $stmt = $conn->prepare("DELETE FROM waitlist WHERE course_id = ? AND section_id = ? AND year = ? AND semester = ? AND student_id = ?;");
        $stmt->bind_param("sssss", $course_id, $section_id, $year, $semester, $student_id);
        if ($stmt->execute()) {
            //enroll the student in the course
            $stmt = $conn->prepare("INSERT INTO take (student_id, course_id, section_id, year, semester) VALUES (?, ?, ?, ?, ?);");
            $stmt->bind_param("sssss", $student_id, $course_id, $section_id, $year, $semester);
            if ($stmt->execute()) {
                echo "<script>alert('Student enrolled from waitlist successfully!');</script>";

                //decrement the priority of the other students in the waitlist
                $stmt = $conn->prepare("UPDATE waitlist SET priority = priority - 1 WHERE course_id = ? AND section_id = ? AND year = ? AND semester = ? AND student_id != ?;");
                $stmt->bind_param("sssss", $course_id, $section_id, $year, $semester, $student_id);
                $stmt->execute();
                $stmt->close();
            } else {
                echo "<script>alert('Error enrolling student from waitlist: " . $stmt->error . "');</script>";
            }
        } else {
            echo "<script>alert('Error deleting student from waitlist: " . $stmt->error . "');</script>";
        }
    }
    $stmt->close();
}

$stmt = $conn->prepare("SELECT course_id, section_id, year, semester FROM take WHERE student_id = (SELECT student_id FROM student WHERE email = ?) AND grade IS NULL;");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($course_id, $section_id, $year, $semester);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
</head>
<body>
    
        <h2>Drop Course</h2>
        <table border="1">
            <tr>
                <th>Course ID</th>
                <th>Section ID</th>
                <th>Year</th>
                <th>Semester</th>
                <th></th>
            </tr>
            <?php
            if ($stmt->num_rows > 0) {
                while ($stmt->fetch()) {
                    echo "<tr>";
                    echo "<td>" . $course_id . "</td>";
                    echo "<td>" . $section_id . "</td>";
                    echo "<td>" . $year . "</td>";
                    echo "<td>" . $semester . "</td>";
                    echo "<td>
                        <form method='POST' style='display:inline;'>
                            <input type='hidden' name='course_id' value='" . $course_id . "'>
                            <input type='hidden' name='section_id' value='" . $section_id . "'>
                            <input type='hidden' name='year' value='" . $year . "'>
                            <input type='hidden' name='semester' value='" . $semester . "'>
                            <input type='hidden' name='drop' value='1'>
                            <button type='submit'>Drop</button>
                        </form>
                    </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No courses to drop.</td></tr>";
            }
            ?>
        </table>
    
        <button onclick="window.location.href='studentprofile.php'">Back</button>
        <button onclick="window.location.href='logout.php'">Logout</button>
</body>
</html>