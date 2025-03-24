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
    <p><a href="logout.php">Logout</a></p>
</body>
</html>