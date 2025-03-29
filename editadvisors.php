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

//If new seciton is added
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['newAdvisor'])) {
    $student_id = $_POST['student_id'];
    $instructor_id = $_POST['instructor_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    //check if student exists in phd table
    $check_student_stmt = $conn->prepare("SELECT * FROM phd WHERE student_id = ?;");
    $check_student_stmt->bind_param("s", $student_id);
    $check_student_stmt->execute();
    $check_student_result = $check_student_stmt->get_result();
    if ($check_student_result->num_rows == 0) {
        echo "<script>alert('This student does not exist in the database');window.location.href = '".$_SERVER['PHP_SELF']."';</script>";
        $check_student_stmt->close();
        return;
    }

    $check_student_stmt = $conn->prepare("SELECT * FROM instructor WHERE instructor_id = ?;");
    $check_student_stmt->bind_param("s", $instructor_id);
    $check_student_stmt->execute();
    $check_student_result = $check_student_stmt->get_result();
    if ($check_student_result->num_rows == 0) {
        echo "<script>alert('This instructor does not exist in the database');window.location.href = '".$_SERVER['PHP_SELF']."';</script>";
        $check_student_stmt->close();
        return;
    }

    //check if the instructor is already an advisor for this student
    $check_stmt = $conn->prepare("SELECT * FROM advise WHERE student_id = ? AND instructor_id = ?;");
    $check_stmt->bind_param("ss", $student_id, $instructor_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        echo "<script>alert('This instructor is already an advisor for this student');window.location.href = '".$_SERVER['PHP_SELF']."';</script>";
        $check_stmt->close();
        return;
    }
    // Check if the end date is before the start date
    if ($end_date < $start_date) {
        echo "<script>alert('End date cannot be before start date');window.location.href = '".$_SERVER['PHP_SELF']."';</script>";
        return;
    }

    // Check if the advisor already exists
    $check_stmt = $conn->prepare("SELECT * FROM advise WHERE student_id = ?;");
    $check_stmt->bind_param("s", $student_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows >= 2) {
        echo "<script>alert('This student already has two advisors');window.location.href = '".$_SERVER['PHP_SELF']."';</script>";
        $check_stmt->close();
        return;
    }
    $stmt = $conn->prepare("INSERT INTO advise (student_id, instructor_id, start_date, end_date) VALUES (?, ?, ?, ?);");
    $stmt->bind_param("ssss", $student_id, $instructor_id, $start_date, $end_date);

    if ($stmt->execute()) {
        echo "<script>alert('New advisor added successfully!');window.location.href = '".$_SERVER['PHP_SELF']."';</script>";
    } else {
        echo "<script>alert('Error adding new advisor.');</script>";
    }

    $stmt->close();
}

// If delete request is sent
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    $student_id = $_POST["student_id"];
    $instructor_id = $_POST["instructor_id"];
    $start_date = $_POST["start_date"];
    $end_date = $_POST["end_date"];

    $delete_stmt = $conn->prepare("DELETE FROM advise WHERE student_id = ? AND instructor_id = ? AND start_date = ? AND end_date = ?;");
    $delete_stmt->bind_param("ssss", $student_id, $instructor_id, $start_date, $end_date);
    
    if ($delete_stmt->execute()) {
        echo "<script>alert('Entry deleted successfully!'); window.location.href = '".$_SERVER['PHP_SELF']."';</script>";
    } else {
        echo "<script>alert('Error deleting record.');</script>";
    }

    $delete_stmt->close();
}

//Get all advisors
$stmt = $conn->prepare("SELECT * FROM advise;");
        $stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
</head>
<body>
    <h2>Advising Table</h2>

    <table border="1">
        <thead>
            <tr>
                <th>StudentID</th>
                <th>InstructorID</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row["student_id"] . "</td>
                                <td>" . $row["instructor_id"] . "</td>
                                <td>" . $row["start_date"] . "</td>
                                <td>" . $row["end_date"] . "</td>
                                <td>
                                    <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='student_id' value='" . $row["student_id"] . "'>
                                        <input type='hidden' name='instructor_id' value='" . $row["instructor_id"] . "'>
                                        <input type='hidden' name='start_date' value='" . $row["start_date"] . "'>
                                        <input type='hidden' name='end_date' value='" . $row["end_date"] . "'>
                                        <input type='hidden' name='delete' value='1'>
                                        <button type='submit'>Delete</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No Advisors Found</td></tr>";
                }
            ?>
        </tbody>
    </table>

    <h3>Add New Advisor</h3>
    <form method="post" style="display:inline;">
        <input type="hidden" name="newAdvisor" value="1">
        Student ID: <input type="text" name="student_id" required>
        Instructor ID: <input type="text" name="instructor_id" required>
        Start Date: <input type="date" name="start_date" required>
        End Date: <input type="date" name="end_date">
        <button type="submit">Add Advisor</button>
    </form>
    
    <p><a href="logout.php">Logout</a></p>
</body>
</html>