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

//Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];
    $alert_type = $_GET['alert_type'];

    // Prepare and bind
    $stmt = $conn->prepare("DELETE FROM alerts WHERE student_id = ? AND alert_type = ?");
    if ($stmt === false) {
        die("Prepare failed: " . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("ss", $student_id, $alert_type);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Alert deleted successfully.";
    } else {
        echo "Error deleting alert: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
    header("Location: adminprofile.php"); // Redirect to admin profile page after deletion
} else {
    echo "No alert ID provided.";
}

?>