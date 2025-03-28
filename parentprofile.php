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
    <h2>Welcome, Parent!</h2>
    <div>
        <h3>Registered Students</h3>
        <table border="1">
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>View Grades</th>
            </tr>
            <?php
            $stmt = $conn->prepare("SELECT student_id, name, dept_name FROM student WHERE student_id IN (SELECT student_id FROM parent WHERE email = ?);");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($student_id, $name, $dept_name);

            while ($stmt->fetch()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($student_id) . "</td>";
                echo "<td>" . htmlspecialchars($name) . "</td>";
                echo "<td>" . htmlspecialchars($dept_name) . "</td>";
                echo "<td><button onclick=\"window.location.href='studentTranscript.php?student_id=" . htmlspecialchars($student_id) . "';\">View</button></td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
    <h3>Alerts</h3>
    <div>
    <table border="1">
        <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Alert Type</th>
            <th>Alert Message</th>
            
        </tr>
        <?php
        $stmt = $conn->prepare("SELECT student_id, alert_type, alert FROM alerts WHERE student_id IN (SELECT student_id FROM parent WHERE email = ?);");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($student_id, $alert_type, $alert);
        
        if($stmt->num_rows > 0) {
            while ($stmt->fetch()) {
                $name_stmt = $conn->prepare("SELECT name FROM student WHERE student_id = ?;");
                $name_stmt->bind_param("s", $student_id);
                $name_stmt->execute();
                $name_stmt->store_result();
                $name_stmt->bind_result($name);
                $name_stmt->fetch();
                $name_stmt->close();
                echo "<tr>";
                echo "<td>" . htmlspecialchars($student_id) . "</td>";
                echo "<td>" . htmlspecialchars($name) . "</td>";
                echo "<td>" . htmlspecialchars($alert_type) . "</td>";
                echo "<td>" . htmlspecialchars($alert) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No alerts found.</td></tr>";
        }
        $stmt->close();
        ?>
    </table>
    </div>
    <button onclick="window.location.href='passwordChange.html'">Change Password</button>
    <p><a href="logout.php">Logout</a></p>
</d>
</html>