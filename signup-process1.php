<?php
// Database connection details
$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "videos1";

// Establishing connection
$conn = mysqli_connect($hostname, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the form is submitted
if(isset($_POST['signup'])) {
    // Collect form data
    $username = $_POST['username'];
    $password = $_POST['password'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];

    // Prepare SQL statement for insertion
    $sql = "INSERT INTO users (username, password, FName, LName, Email, ContactNumber) VALUES ('$username', '$password', '$fname', '$lname', '$email', '$contact')";
    
    if(mysqli_query($conn, $sql)) {
        echo "You have been registered successfully!";
        // Redirect to sign-up page after successful registration
        header("refresh:3; url=signup.php");
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

// Close connection
mysqli_close($conn);
?>
