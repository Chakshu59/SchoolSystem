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

$stmt = $conn->prepare("SELECT student_id, name, dept_name  FROM student WHERE email = (SELECT email FROM account WHERE email = ?);");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($student_id, $name, $dept_name);
    $stmt->fetch();
} else {
    $name = "Null";
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
    <h2>Welcome, <?php echo htmlspecialchars($name); ?>!</h2>
    <p>Student ID: <?php echo htmlspecialchars($student_id); ?></p>
    <p>Email: <?php echo htmlspecialchars($email); ?></p>
    <p>User Type: <?php echo htmlspecialchars($user_type); ?></p>
    <p>Department: <?php echo htmlspecialchars($dept_name); ?></p>
    
    <button onclick="window.location.href='passwordChange.html'">Change Password</button>

    <h3>Course Registration</h3>
    <button onclick="window.location.href='enrollcourse.php'">Enroll</button>

    <h3>Schedule & GPA</h3>
    <button onclick="window.location.href='studentTranscript.php'">Transcript</button>

    <?php
    //Set session data
    $_SESSION["student_id"] = $student_id;
    $_SESSION["name"] = $name;
    $_SESSION["dept_name"] = $dept_name;
    ?>

    <p><a href="logout.php">Logout</a></p>
</body>
</html>