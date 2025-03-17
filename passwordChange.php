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
    $prev_password = $_POST['prev_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    $stmt = $conn->prepare("SELECT email, password, type FROM account WHERE email = ?");
    $stmt->bind_param("s", $_SESSION['email']);
    $stmt->execute();
    $stmt->store_result();

     //Checks if Email Exists
     if ($stmt->num_rows > 0) {
        $stmt->bind_result($dbemail, $dbpassword, $user_type);
        $stmt->fetch();

        $stmt->close();

        //Password Verification
        if ($prev_password == $dbpassword) {
            $stmt = $conn->prepare("UPDATE account SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $new_password, $_SESSION['email']);
            $stmt->execute();

            //Redirect to Profile
            header("Location: logout.php");
            exit();
        } else {
            echo "Error: Incorrect Password";
        }
    } else {
        echo "Error: Email Does Not Exist";
    }
}


?>