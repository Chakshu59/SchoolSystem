<?php
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
  $student_id = $_POST['sid'] ?? '';
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';
  $type = 'student';

  //Check if student exists
  $checkSidQuery = "SELECT student_id FROM student WHERE student_id = ?";
  $stmt = $conn->prepare($checkSidQuery);
  $stmt->bind_param("s", $student_id);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    //Close Statement
    $stmt->close();

    //Check if account already exists
    $checkEmailQuery = "SELECT email FROM account WHERE email = ?";
    $stmt = $conn->prepare($checkEmailQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      //Close Statement
      $stmt->close();
      echo "Error: Account Already Exists";
    } else {
      //Close Statement
      $stmt->close();

      //Inserts User
      $stmt = $conn->prepare("INSERT INTO account (email, password, type) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $email, $password, $type);

      //Execute the query
      if ($stmt->execute()) {
        //Sends the user back to home
        header("Location: index.html");
        exit();
      } else {
          echo "Error: " . $stmt->error;
      }
    }
  } else {
    $stmt->close();
    echo "Error: Student Does Not Exist";
  }
}
//Close Connection
$conn->close();

?>