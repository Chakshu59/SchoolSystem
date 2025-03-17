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

$course_id = $section_id = $semester = $year = $instructor_id = $classroom_id = $time_slot_id = "";
$name = NULL;
    
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['course_id']) && isset($_POST['section_id'])) {
        $course_id = $_POST['course_id'];
        $section_id = $_POST['section_id'];
        $semester = $_POST['semester'];
        $year = $_POST['year'];
        $instructor_id = $_POST['instructor_id'];
        $classroom_id = $_POST['classroom_id'];
        $time_slot_id = !empty($_POST['time_slot_id']) ? $_POST['time_slot_id'] : NULL;

        // Selects the course name of the current course
        $stmt = $conn->prepare("SELECT course_name FROM course WHERE course_id = ?;");
        $stmt->bind_param("s", $course_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($name);
            $stmt->fetch();
        }
        $stmt->close();
    }

    if (isset($_POST['instructor_id'])) {
        $instructor_id = $_POST['instructor_id'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Section</title>
</head>
<body>
    <h2>Editing: <?php echo htmlspecialchars($name) ?> - <?php echo htmlspecialchars($section_id) ?></h2>

    <form action="editsection.php" method="post">
    <h3>Semester:</h3>
    <input type="radio" name="semester" value="Fall" <?php echo ($semester == 'Fall') ? 'checked' : ''; ?>>Fall
    <input type="radio" name="semester" value="Spring" <?php echo ($semester == 'Spring') ? 'checked' : ''; ?>>Spring
    <input type="radio" name="semester" value="Summer" <?php echo ($semester == 'Summer') ? 'checked' : ''; ?>>Summer
    <input type="radio" name="semester" value="Winter" <?php echo ($semester == 'Winter') ? 'checked' : ''; ?>>Winter

    <h3>Year:</h3>
    <input type="text" name="year" value="<?php echo htmlspecialchars($year); ?>">

    <h3>Instructor:</h3>
    <?php
        $stmt = $conn->prepare('SELECT i.instructor_id, i.instructor_name FROM instructor i LEFT JOIN (SELECT instructor_id FROM section WHERE course_id = ? GROUP BY instructor_id HAVING COUNT(section_id) > 1) s ON i.instructor_id = s.instructor_id WHERE s.instructor_id IS NULL');
        $stmt->bind_param("s", $course_id);
        $stmt->execute();
        $stmt = $stmt->get_result();
        if ($stmt->num_rows > 0) {
            while ($row = $stmt->fetch_assoc()) {
                echo "<label><input type='radio' name='instructor_id' value='". $row["instructor_id"] . "' " . ($row["instructor_id"] == $instructor_id ? 'checked' : '') . " onchange='this.form.submit()'> " . $row["instructor_name"] . "</label><br>";
            }
        } else {
            echo "<p>No Instructors Available</p>";
        }
        $stmt->close();
    ?>
    <h3>Classroom:</h3>
    <?php
        $stmt = $conn->query('SELECT classroom_id, building, room_number, capacity FROM classroom');
        if ($stmt->num_rows > 0) {
            while ($row = $stmt->fetch_assoc()) {
                echo "<label><input type='radio' name='classroom_id' value='". $row["classroom_id"] . "' " . ($row["classroom_id"] == $classroom_id ? 'checked' : '') . ">Classroom ID:" . $row["classroom_id"] . " - Building:" . $row["building"] . " - Room Number:" . $row["room_number"] . " - Capacity:" . $row["capacity"] . "</label><br>";
            }
        } else {
            echo "<p>No Classrooms Available</p>";
        }
        $stmt->close();
    ?>
    <h3>Time:</h3>
    <?php
        if ($instructor_id) {
            $stmt = $conn->query('SELECT time_slot_id, day, start_time, end_time FROM time_slot');
            //$stmt->bind_param("s", $instructor_id);
            //$stmt->execute();
            //$stmt = $stmt->get_result();
        } else {
            $stmt = $conn->query('SELECT time_slot_id, day, start_time, end_time FROM time_slot');
        }
        if ($stmt->num_rows > 0) {
            while ($row = $stmt->fetch_assoc()) {
                echo "<label><input type='radio' name='time_slot_id' value='". $row["time_slot_id"] . "' " . ($row["time_slot_id"] == $time_slot_id ? 'checked' : '') . ">Time Slot ID:" . $row["time_slot_id"] . " - Days:" . $row["day"] . " - Start Time:" . $row["start_time"] . " - End Time:" . $row["end_time"] . "</label><br>";
            }
        } else {
            echo "<p>No Time Slots Available</p>";
        }
        $stmt->close();
    ?>
    <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_id) ?>">
    <input type="hidden" name="section_id" value="<?php echo htmlspecialchars($section_id) ?>">
    <input type="hidden" name="semester" value="<?php echo htmlspecialchars($semester) ?>">
    <input type="hidden" name="year" value="<?php echo htmlspecialchars($year) ?>">
    <input type="hidden" name="classroom_id" value="<?php echo htmlspecialchars($classroom_id) ?>">
    <input type="submit" value="Save" formaction="updatesection.php">
    </form>
    <button onclick="window.location.href='editclasses.php'">Back</button>
    
    <p><a href="logout.php">Logout</a></p>
</body>
</html>