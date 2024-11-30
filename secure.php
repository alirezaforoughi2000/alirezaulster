<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: signin_form.php");
    exit();
}

// Check if the upload form is submitted
if (isset($_POST['upload_video'])) {
    // Handle video upload
    $title = $_POST['title'];
    $description = $_POST['description'];
    $id = $_SESSION['id'];

    // File upload handling
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["fileToUpload"]["size"] > 5000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }
  // "docx", "pdf"
    // Allow certain file formats
    if(!in_array($imageFileType, array("mp4", "avi","mp3", "mov", "pdf", "docx"))) {
        echo "Sorry, only MP4, AVI, MOV, PDF, and DOCX files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        // if everything is ok, try to upload file
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            // Insert video details into database
            $filename = basename($_FILES["fileToUpload"]["name"]);
            $conn = mysqli_connect("localhost", "root", "", "videos");
            $sql = "INSERT INTO videos (title, description, filename, uploader_id) VALUES ('$title', '$description', '$filename', $id)";
            if (mysqli_query($conn, $sql)) {
                echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
            } else {
                echo "Error: " . $sql . "<br>" . mysqli_error($conn);
            }
            mysqli_close($conn);
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .logout {
            float: right;
        }
        .upload-form {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>
        <p>This is a secure page.</p>
        <a href="#" id="upload-link">Upload Video</a>
        <div class="upload-form">
            <form action="" method="POST" enctype="multipart/form-data">
                <h3>Upload Video</h3>
                <label for="title">Title:</label><br>
                <input type="text" id="title" name="title"><br>
                <label for="description">Description:</label><br>
                <textarea id="description" name="description"></textarea><br>
                <label for="fileToUpload">Select video to upload:</label><br>
                <input type="file" name="fileToUpload" id="fileToUpload"><br>
                <input type="submit" value="Upload Video" name="upload_video">
            </form>
        </div>
        <a class="logout" href="logout.php">Logout</a>
    </div>

    <script>
        // Show/hide upload form when "Upload Video" link is clicked
        document.getElementById("upload-link").addEventListener("click", function(e) {
            e.preventDefault();
            document.querySelector(".upload-form").style.display = "block";
        });
    </script>
</body>
</html>
