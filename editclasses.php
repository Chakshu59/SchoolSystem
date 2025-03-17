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

$stmt = $conn->query("SELECT course_id, course_name, credits FROM course");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
</head>
<body>
    <h2>Edit Classes</h2>

    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Credits</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
                if ($stmt->num_rows > 0) {
                    while($row = $stmt->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row["course_id"] . "</td>
                                <td>" . $row["course_name"] . "</td>
                                <td>" . $row["credits"] . "</td>
                                <td>
                                    <form action='displaysections.php' method='POST' style='display:inline;'>
                                        <input type='hidden' name='course_id' value='" . $row["course_id"] . "'>
                                        <button type='submit'>Sections</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No Courses Found</td></tr>";
                }
            ?>
        </tbody>
    </table>
    
    <button onclick="window.location.href='adminprofile.php'">Back</button>
    
    <p><a href="logout.php">Logout</a></p>
</body>
</html>