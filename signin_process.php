<?php

session_start();

$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "videos1";

// Connection
$conn = mysqli_connect($hostname, $username, $password, $dbname)
        or die("Unable to connect" . mysqli_error($conn));

// Retrieve submitted username and password
$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = mysqli_real_escape_string($conn, $_POST['password']);

// Query to check if username and password match
$sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 1) {
    // Username and password are correct
    $row = mysqli_fetch_assoc($result);
    if ($username === 'Admin' && $password === '1234567') {
        // If username is 'Admin' and password is '1234567', redirect to secure3.php
        $_SESSION['username'] = $username;
        $_SESSION['id'] = $row['id'];
        header("Location: secure3.php");
        exit();
    } else {
        // For other usernames and passwords, redirect to index.php
        $_SESSION['username'] = $username;
        $_SESSION['id'] = $row['id'];
        header("Location: index.php");
        exit();
    }
} else {
    // Username and password are incorrect, redirect back to sign-in form
    header("Location: index.php?error=invalid");
    exit();
}

mysqli_close($conn);
?>
