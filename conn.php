<?php

$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "videos";

// connection to database

$conn = mysqli_connect($hostname,$username,$password,$dbname )
or die("Unable to connect");
echo $dbname."connected successfully";

//execute query

$sql = mysqli_query($conn,"select * from users");

// fetch the data

if (mysqli_num_rows($sql) > 0)
{
    while($row = mysqli_fetch_array($sql))
    {
        echo "ID". " = ".  $row['id']. "<br>".
              "User Name". " = ".  $row['username']."<br>". "----------" ."<br>";
    }
    mysqli_close($conn);
}


?>