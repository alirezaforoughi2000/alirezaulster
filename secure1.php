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
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

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

    // Allow certain file formats
    $allowedFormats = array("mp4", "avi", "mp3", "mov", "pdf", "docx");
    if (!in_array($imageFileType, $allowedFormats)) {
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

            // Check database connection
            if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
            }

            $sql = "INSERT INTO videos (title, description, filename, uploader_id) VALUES ('$title', '$description', '$filename', $id)";
            if (mysqli_query($conn, $sql)) {
                echo "The file " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " has been uploaded.";
            } else {
                echo "Error: " . $sql . "<br>" . mysqli_error($conn);
            }
            mysqli_close($conn);
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Delete selected videos
if (isset($_POST['delete_videos'])) {
    if (!empty($_POST['videos'])) {
        $conn = mysqli_connect("localhost", "root", "", "videos");

        // Check database connection
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        foreach ($_POST['videos'] as $video_id) {
            // Fetch filename for deletion
            $filename_query = "SELECT filename FROM videos WHERE id = $video_id";
            $result = mysqli_query($conn, $filename_query);
            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $filename = $row['filename'];

                // Delete associated comments first
                $delete_comments_query = "DELETE FROM comments WHERE video_id = $video_id";
                if (mysqli_query($conn, $delete_comments_query)) {
                    // Delete associated likes
                    $delete_likes_query = "DELETE FROM likes WHERE video_id = $video_id";
                    if (mysqli_query($conn, $delete_likes_query)) {
                        // Delete associated dislikes
                        $delete_dislikes_query = "DELETE FROM dislikes WHERE video_id = $video_id";
                        if (mysqli_query($conn, $delete_dislikes_query)) {
                            // Then delete the video
                            $delete_video_query = "DELETE FROM videos WHERE id = $video_id";
                            if (!mysqli_query($conn, $delete_video_query)) {
                                echo "Error deleting video with ID: " . $video_id;
                            } else {
                                // Delete associated file
                                $file_path = "uploads/" . $filename;
                                if (file_exists($file_path)) {
                                    unlink($file_path);
                                }
                            }
                        } else {
                            echo "Error deleting dislikes associated with video ID: " . $video_id;
                        }
                    } else {
                        echo "Error deleting likes associated with video ID: " . $video_id;
                    }
                } else {
                    echo "Error deleting comments associated with video ID: " . $video_id;
                }
            }
        }
        mysqli_close($conn);
        header("Location: secure1.php");
        exit();
    } else {
        echo "No videos selected for deletion.";
    }
}

// Fetch uploaded videos from the database based on the selected option
$conn = mysqli_connect("localhost", "root", "", "videos");
$id = $_SESSION['id'];
$view_option = isset($_GET['view-option']) ? $_GET['view-option'] : 'all';

if ($view_option === 'all') {
    $query = "SELECT * FROM videos";
} else {
    $query = "SELECT * FROM videos WHERE uploader_id = $id";
}

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Page</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .logout {
            float: right;
        }

        .video-link {
            display: block;
            margin-bottom: 10px;
        }

        .video-container {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Welcome, <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>!</h2>
        <p>This is a secure page.</p>
        <a class="logout" href="logout.php">Logout</a>

        <!-- Dropdown menu to select videos -->
        <form action="" method="GET" class="mb-3">
            <label for="view-option">View Videos:</label>
            <select name="view-option" id="view-option" class="form-control">
                <option value="all">All Videos</option>
                <option value="uploaded-by-me">Uploaded by Me</option>
            </select>
            <button type="submit" class="btn btn-primary mt-2">View</button>
        </form>

        <!-- Upload form -->
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" class="form-control" id="title" name="title">
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea class="form-control" id="description" name="description"></textarea>
            </div>
            <div class="form-group">
                <label for="fileToUpload">Select video to upload:</label>
                <input type="file" class="form-control-file" name="fileToUpload" id="fileToUpload">
            </div>
            <button type="submit" class="btn btn-primary" name="upload_video">Upload Video</button>
        </form>

        <!-- Uploaded videos -->
        <form action="" method="POST">
            <div class="video-container">
                <h3>Uploaded Videos</h3>
                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<div><input type='checkbox' name='videos[]' value='" . $row['id'] . "'><a href='view_video.php?id=" . $row['id'] . "' class='video-link'>" . $row['title'] . "</a></div>";
                    }
                } else {
                    echo "No videos uploaded yet.";
                }
                ?>
                <button type="submit" class="btn btn-danger" name="delete_videos">Delete Selected Videos</button>
            </div>
        </form>
    </div>
</body>

</html>
