<?php
session_start();

// Database connection
$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "videos1";

$conn = mysqli_connect($hostname, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Process sign-up form submission
if (isset($_POST['signup'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);

    // Hash the password before storing it
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert data into users table
    $signup_query = "INSERT INTO users (username, password, FName, LName, Email, ContactNumber) VALUES ('$username', '$hashedPassword', '$fname', '$lname', '$email', '$contact')";

    if (mysqli_query($conn, $signup_query)) {
        // Redirect to index.php with a success message
        $_SESSION['message'] = "You have been registered successfully!";
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $signup_query . "<br>" . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>
