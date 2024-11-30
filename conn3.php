<?php
//variables
$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "videos";

// check connection
$conn = mysqli_connect($hostname, $username, $password, $dbname)
    or die("Can not connect");

// query to insert into users table
$sql_user = "INSERT INTO users (username, password) VALUES ('Sharjeel123', '1234567')";

// execute the query
if (!mysqli_query($conn, $sql_user)) {
    die("Error inserting into users table: " . mysqli_error($conn));
}

// get the ID of the newly inserted user
$new_user_id = mysqli_insert_id($conn);

// query to insert into videos table
$sql_video = "INSERT INTO videos (title, description, filename, uploader_id) 
              VALUES ('Video Title', 'Video Description', 'video_filename.mp4', $new_user_id)";

// execute the query
if (!mysqli_query($conn, $sql_video)) {
    die("Error inserting into videos table: " . mysqli_error($conn));
}

echo "Data inserted successfully";

mysqli_close($conn);
?>
