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

$stmt = $conn->prepare("SELECT instructor_id, instructor_name, title, dept_name  FROM instructor WHERE email = (SELECT email FROM account WHERE email = ?);");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'] ?? '';
    $alert_type = $_POST['alert_type'] ?? '';
    $alert = $_POST['alert'] ?? '';

    // Insert alert into the database
    $stmt = $conn->prepare("INSERT INTO alerts (student_id, alert_type, alert) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $student_id, $alert_type, $alert);
    if ($stmt->execute()) {
        echo "Alert created successfully.";
    } else {
        echo "Error creating alert: " . $stmt->error;
    }
    $stmt->close();
}

if ($stmt->num_rows > 0) {
    $stmt->bind_result($instructor_id, $name, $title, $dept_name);
    $stmt->fetch();
} else {
    $name = "Null";
}
$_SESSION['instructor_id'] = $instructor_id;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($name); ?>!</h2>
    <p>Instructor ID: <?php echo htmlspecialchars($instructor_id); ?></p>
    <p>Title: <?php echo htmlspecialchars($title); ?></p>
    <p>Email: <?php echo htmlspecialchars($email); ?></p>
    <p>User Type: <?php echo htmlspecialchars($user_type); ?></p>
    <p>Department: <?php echo htmlspecialchars($dept_name); ?></p>
    
    <button onclick="window.location.href='passwordChange.html'">Change Password</button>
    <button onclick="window.location.href='courseHistory.php'">Course History</button>
    <form action="instructorprofile.php" method="post">
        <label for="student_id">Student ID:</label><br>
        <input type="text" id="student_id" name="student_id" required><br><br>
        <label for="alert_type">Alert Type:</label><br>
        <select id="alert_type" name="alert_type" required>
            <option value="Academic">Academic</option>
            <option value="Other">Other</option>
        </select><br><br>
        <label for="alert">Alert:</label><br>
        <input type="text" id="alert" name="alert" required><br><br>
        <input type="submit" value="Create Alert">
    </form>
    <p><a href="logout.php">Logout</a></p>
</body>
</html>