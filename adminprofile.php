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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
</head>
<body>
    <h2>Admin Control</h2>

    <button onclick="window.location.href='editclasses.php'">Edit Classes</button>
    <h3>Create Alert</h3>
    <form action="adminprofile.php" method="post">
        <label for="student_id">Student ID:</label><br>
        <input type="text" id="student_id" name="student_id" required><br><br>
        <label for="alert_type">Alert Type:</label><br>
        <select id="alert_type" name="alert_type" required>
            <option value="Academic">Academic</option>
            <option value="Behavioral">Behavioral</option>
            <option value="Financial">Financial</option>
            <option value="Medical">Medical</option>
            <option value="Other">Other</option>
        </select><br><br>
        <label for="alert">Alert:</label><br>
        <input type="text" id="alert" name="alert" required><br><br>
        <input type="submit" value="Create Alert">
    </form>
    <h3>View Alerts</h3>
    <div>
    <table border="1">
        <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Alert Type</th>
            <th>Alert Message</th>
            <th>Action</th>
        </tr>
        <?php
        $stmt = $conn->prepare("SELECT student_id, alert_type, alert FROM alerts");
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
                while ($name_stmt->fetch()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($student_id) . "</td>";
                    echo "<td>" . htmlspecialchars($name) . "</td>";
                    echo "<td>" . htmlspecialchars($alert_type) . "</td>";
                    echo "<td>" . htmlspecialchars($alert) . "</td>";
                    echo "<td><button onclick=\"window.location.href='deleteAlert.php?student_id=" . htmlspecialchars($student_id) . "&alert_type=" . htmlspecialchars($alert_type) . "';\">Delete</button></td>";
                    echo "</tr>";
                }
                $name_stmt->close();
            }
        } else {
            echo "<tr><td colspan='4'>No alerts found.</td></tr>";
        }
        ?>
    </table>
    </div>
    <p><a href="logout.php">Logout</a></p>
</body>
</html>