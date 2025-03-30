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
$instructor_id = $_SESSION['instructor_id'];

//Update dates based on user input
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $student_id = $_POST['student_id'];
    $proposal_defence_date = $_POST['proposal_defence_date'];
    $dissertation_defence_date = $_POST['dissertation_defence_date'];
    $qualifier = $_POST['qualifier'];

    // Update the proposal and dissertation defence dates
    $stmt = $conn->prepare("UPDATE phd SET proposal_defence_date = ?, dissertation_defence_date = ?, qualifier = ? WHERE student_id = ?;");
    $stmt->bind_param("ssss", $proposal_defence_date, $dissertation_defence_date, $qualifier, $student_id);
    if ($stmt->execute()) {
        echo "<script>alert('Student information updated successfully');window.location.href = '".$_SERVER['PHP_SELF']."';</script>";
    } else {
        echo "<script>alert('Error updating information: " . $stmt->error . "');window.location.href = '".$_SERVER['PHP_SELF']."';</script>";
    }
    $stmt->close();


}


//List all students instructor is advising
$stmt = $conn->prepare("SELECT a.student_id, a.start_date, a.end_date, p.proposal_defence_date, p.dissertation_defence_date, p.qualifier FROM advise a LEFT JOIN phd p ON a.student_id = p.student_id  WHERE instructor_id = ?;");
$stmt->bind_param("s", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advisee Profile</title>
</head>
<body>
    <h2>Advisees</h2>

    <table border="1">
        <thead>
            <tr>
                <th>StudentID</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Proposal Defence Date</th>
                <th>Dissertation Defence Date</th>
                <th>Qualifier</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<form method='POST'>
                            <tr>
                                <td>" . $row["student_id"] . "</td>
                                <td>" . $row["start_date"] . "</td>
                                <td>" . $row["end_date"] . "</td>
                                <td> 
                                    <input type='date' name='proposal_defence_date' value='" . $row["proposal_defence_date"] . "' /> 
                                </td>
                                <td> 
                                    <input type='date' name='dissertation_defence_date' value='" . $row["dissertation_defence_date"] . "' /> 
                                </td>
                                <td>
                                    <select name='qualifier'>
                                        <option value='Yes' " . ($row["qualifier"] == "Yes" ? "selected" : "") . ">Yes</option>
                                        <option value='No' " . ($row["qualifier"] == "No" ? "selected" : "") . ">No</option>
                                    </select>
                                </td>
                                <td>
                                    <input type='hidden' name='student_id' value='" . $row["student_id"] . "' />
                                    <button type='submit'>Update Dates</button>
                                </td>
                                </form>
                                <td>
                                    <button onclick=\"window.location.href='studentTranscript.php?student_id=" . $row["student_id"] . "'\">View Transcript</button>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No Advisees Found</td></tr>";
                }
            ?>
        </tbody>
    </table>
    <button onclick="window.location.href='instructorprofile.php'">Back</button>
</body>
</html>

