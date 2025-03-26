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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT email, password, type FROM account WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    //Checks if Email Exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($dbemail, $dbpassword, $user_type);
        $stmt->fetch();

        //Password Verification
        if ($password == $dbpassword) {
            $_SESSION['email'] = $dbemail;
            $_SESSION['type'] = $user_type;
            $_SESSION['password'] = $password;

            //Redirect to Profile
            if ($user_type == "student") {
                header("Location: studentprofile.php");
                exit();
            } else if ($user_type == "admin") {
                header("Location: adminprofile.php");
                exit();
            } else if ($user_type == "instructor") {
                header("Location: instructorprofile.php");
                exit();
            } else if ($user_type == "parent") {
                header("Location: parentprofile.php");
                exit();
            }
        } else {
            echo "Error: Incorrect Password";
        }
    } else {
        echo "Error: Email Does Not Exist";
    }
    $stmt->close();
}

$conn->close();
?>