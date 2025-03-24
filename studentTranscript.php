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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
</head>
<body>
    <h2>Transcript for, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</h2>
    <div>
        <h3>Classes Taken</h3>
        <table border="1">
            <tr>
                <th>Course ID</th>
                <th>Section ID</th>
                <th>Year</th>
                <th>Semester</th>
                <th>Grade</th>
            </tr>
            <?php
            $stmt = $conn->prepare("SELECT course_id, section_id, year, semester, grade FROM take WHERE student_id = (SELECT student_id FROM student WHERE email = ?) AND grade IS NOT NULL;");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($course_id, $section_id, $year, $semester, $grade);

            while ($stmt->fetch()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($course_id) . "</td>";
                echo "<td>" . htmlspecialchars($section_id) . "</td>";
                echo "<td>" . htmlspecialchars($year) . "</td>";
                echo "<td>" . htmlspecialchars($semester) . "</td>";
                echo "<td>" . htmlspecialchars($grade) . "</td>";
                echo "</tr>";
            }
            $stmt->close();
            ?>
        </table>
    </div>
    <div>
        <h3>Classes Currently Taking</h3>
        <table border="1">
            <tr>
                <th>Course ID</th>
                <th>Section ID</th>
                <th>Year</th>
                <th>Semester</th>
            </tr>
            <?php
            $stmt = $conn->prepare("SELECT course_id, section_id, year, semester FROM take WHERE student_id = (SELECT student_id FROM student WHERE email = ?) AND grade IS NULL;");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($course_id, $section_id, $year, $semester);

            while ($stmt->fetch()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($course_id) . "</td>";
                echo "<td>" . htmlspecialchars($section_id) . "</td>";
                echo "<td>" . htmlspecialchars($year) . "</td>";
                echo "<td>" . htmlspecialchars($semester) . "</td>";
                echo "</tr>";
            }
            $stmt->close();
            ?>
        </table>
    </div>
    <div>
        <?php
        $stmt = $conn->prepare("SELECT grade FROM take WHERE student_id = (SELECT student_id FROM student WHERE email = ?) AND grade IS NOT NULL;");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $total_points = 0;
        $num_grades = 0;
        
        $grade_points = [
            'A'  => 4.0,
            'A-' => 3.7,
            'B+' => 3.3,
            'B'  => 3.0,
            'B-' => 2.7,
            'C+' => 2.3,
            'C'  => 2.0,
            'C-' => 1.7,
            'D+' => 1.3,
            'D'  => 1.0,
            'F'  => 0.0
        ];
        
        while ($row = $result->fetch_assoc()) {
            $grade = $row['grade'];
            if (array_key_exists($grade, $grade_points)) {
                $total_points += $grade_points[$grade];
                $num_grades++;
            }
        }
        
        $stmt->close();
        
        // Calculate GPA
        $gpa = $num_grades > 0 ? $total_points / $num_grades : 0;
        echo "<h3>GPA: " . number_format($gpa, 2) . "</h3>";
        ?>
    </div>
    <p><a href="logout.php">Logout</a></p>
</body>
</html>