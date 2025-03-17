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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "Enrolled in course with ID: " . $_POST['course_id'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course List</title>
</head>
<body>
    <h2>Course List</h2>
    <table border="1">
        <tr>
            <th>Course ID</th>
            <th>Course Name</th>
            <th>Department</th>
            <th>Credits</th>
            <th></th>
        </tr>
        <?php
        $stmt = $conn->query("SELECT course_id, course_name, credits FROM course");
        if ($stmt->num_rows > 0) {
            while ($row = $stmt->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['course_id'] . "</td>";
                echo "<td>" . $row['course_name'] . "</td>";
                echo "<td>" . $_SESSION['dept_name'] . "</td>";
                echo "<td>" . $row['credits'] . "</td>";
                echo "<td>
                    <form action='enrollsection.php' method='POST' style='display:inline;'>
                        <input type='hidden' name='course_id' value='" . $row["course_id"] . "'>
                        <input type='hidden' name='course_name' value='" . $row["course_name"] . "'>
                        <button type='submit'>Register</button>
                    </form>
                </td>";
                echo "</tr>";
            }
        }
        ?>
    </table>
    <button onclick="window.location.href='studentprofile.php'">Back</button>
    <p><a href="logout.php">Logout</a></p>
</body>
</html>