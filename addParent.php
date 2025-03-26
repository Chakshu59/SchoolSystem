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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $parent_email = $_POST['parent_email'];
    $password = $_POST['password'];
    $student_id = $_POST['student_id'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("SELECT * FROM parent WHERE phone = ? AND student_id = ?;");
    $stmt->bind_param("ss", $parent_email, $student_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "Parent already added!";
        echo "<br>";
        $stmt->close();
        exit();
    } else{
        $stmt = $conn->prepare("INSERT INTO parent (email, phone, student_id) VALUES (?, ?, ?);");
        $stmt->bind_param("sss", $parent_email, $phone, $student_id);
        if ($stmt->execute()) {
            echo "Parent added successfully!";
            echo "<br>";
        } else {
            echo "Error: " . $conn->error;
            echo "<br>";
        }
        $stmt->close();
    }

    $stmt = $conn->prepare("SELECT * FROM account WHERE email = ?;");
    $stmt->bind_param("s", $parent_email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "Parent account already exists!";
        echo "<br>";
        $stmt->close();
        exit();
    } else {
        $stmt = $conn->prepare("INSERT INTO account (email, password, type) VALUES (?, ?, 'parent');");
        $stmt->bind_param("ss", $parent_email, $password);
        if ($stmt->execute()) {
            echo "Parent account created successfully!";
            echo "<br>";
        } else {
            echo "Error: " . $conn->error;
            echo "<br>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Add Parent</h2>
    <form action="addParent.php" method="POST">
        <label for="parent_email">Parent Email:</label>
        <input type="email" id="parent_email" name="parent_email" required><br>
        <label for="phone">Phone Number:</label>
        <input type="text" id="phone" name="phone" required><br>
        <label for="password">Password:</label>
        <input type="text" id="password" name="password" required><br>
        <input type="hidden" id="student_id" name="student_id" value="<?php echo htmlspecialchars($_SESSION['student_id']); ?>">

        <button type="submit">Add</button>
    </form>
    <button onclick="window.location.href='studentprofile.php'">Back</button>
</html>