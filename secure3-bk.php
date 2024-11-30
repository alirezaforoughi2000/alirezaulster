<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: signin_form.php");
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "videos1");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle video and thumbnail upload
if (isset($_POST['upload_video'])) {
    // Handle video upload
    // Existing code for video upload goes here
}

// Handle video deletion
if (isset($_POST['delete_videos'])) {
    // Check if user is Admin
    if ($_SESSION['username'] == 'Admin') {
        // Admin can delete any video
        if (!empty($_POST['videos'])) {
            $videos_to_delete = implode(",", $_POST['videos']);

            // Delete related comments
            $delete_comments_query = "DELETE FROM comments WHERE video_id IN ($videos_to_delete)";
            mysqli_query($conn, $delete_comments_query);

            // Delete related likes
            $delete_likes_query = "DELETE FROM likes WHERE video_id IN ($videos_to_delete)";
            mysqli_query($conn, $delete_likes_query);

            // Delete related dislikes
            $delete_dislikes_query = "DELETE FROM dislikes WHERE video_id IN ($videos_to_delete)";
            mysqli_query($conn, $delete_dislikes_query);

            // Delete video records from the database
            $delete_video_query = "DELETE FROM videos WHERE id IN ($videos_to_delete)";
            if (mysqli_query($conn, $delete_video_query)) {
                echo "Selected videos and related data have been deleted successfully.";
            } else {
                echo "Error deleting videos: " . mysqli_error($conn);
            }
        } else {
            echo "No videos selected for deletion.";
        }
    } else {
        // Non-Admin users can only delete their own videos
        $id = $_SESSION['id'];
        if (!empty($_POST['videos'])) {
            $videos_to_delete = implode(",", $_POST['videos']);

            // Check if selected videos belong to the user
            $check_query = "SELECT id FROM videos WHERE id IN ($videos_to_delete) AND uploader_id = $id";
            $check_result = mysqli_query($conn, $check_query);
            $num_rows = mysqli_num_rows($check_result);

            if ($num_rows > 0) {
                // Delete related comments
                $delete_comments_query = "DELETE FROM comments WHERE video_id IN ($videos_to_delete)";
                mysqli_query($conn, $delete_comments_query);

                // Delete related likes
                $delete_likes_query = "DELETE FROM likes WHERE video_id IN ($videos_to_delete)";
                mysqli_query($conn, $delete_likes_query);

                // Delete related dislikes
                $delete_dislikes_query = "DELETE FROM dislikes WHERE video_id IN ($videos_to_delete)";
                mysqli_query($conn, $delete_dislikes_query);

                // User can delete their own videos
                $delete_video_query = "DELETE FROM videos WHERE id IN ($videos_to_delete)";
                if (mysqli_query($conn, $delete_video_query)) {
                    echo "Selected videos and related data have been deleted successfully.";
                } else {
                    echo "Error deleting videos: " . mysqli_error($conn);
                }
            } else {
                echo "You can only delete your own videos.";
            }
        } else {
            echo "No videos selected for deletion.";
        }
    }
}

// Fetch genres from the Genres table
$genre_query = "SELECT genre_name FROM Genres";
$genre_result = mysqli_query($conn, $genre_query);
$genres = array();
while ($row = mysqli_fetch_assoc($genre_result)) {
    $genres[] = $row['genre_name'];
}

// Fetch all age ratings from the AgeRating table
$age_rating_query = "SELECT rating_name FROM AgeRating";
$age_rating_result = mysqli_query($conn, $age_rating_query);
$age_ratings = array();
while ($row = mysqli_fetch_assoc($age_rating_result)) {
    $age_ratings[] = $row['rating_name'];
}
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

        .upload-container {
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .nav-tabs .nav-link.active {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }

        .nav-tabs .nav-link {
            color: #007bff;
            border: 1px solid transparent;
            border-top-left-radius: .25rem;
            border-top-right-radius: .25rem;
            margin-right: 2px;
            line-height: 1.5;
            padding: .5rem .75rem;
        }

        .nav-tabs {
            border-bottom: 1px solid #dee2e6;
        }
    </style>
</head>

<body>
    <div class="container">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link" href="index.php">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="#">Secure Page</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
        </ul>
        <div class="row">
            <div class="col-md-6">
                <!-- Dropdown menu to select videos -->
                <form action="" method="GET" class="mb-3">
                    <label for="view-option">View Videos:</label>
                    <select name="view-option" id="view-option" class="form-control">
                        <option value="all">All Videos</option>
                        <option value="uploaded-by-me">Uploaded by Me</option>
                    </select>
                    <button type="submit" class="btn btn-primary mt-2">View</button>
                </form>

                <!-- Uploaded videos -->
                <div class="video-container">
                    <h3>Uploaded Videos</h3>
                    <form action="" method="POST">
                        <?php
                        // Fetch uploaded videos from the database based on the selected option
                        $view_option = isset($_GET['view-option']) ? $_GET['view-option'] : 'all';

                        if ($view_option === 'all') {
                            $query = "SELECT * FROM videos";
                        } else {
                            $id = $_SESSION['id'];
                            $query = "SELECT * FROM videos WHERE uploader_id = $id";
                        }

                        $result = mysqli_query($conn, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<div><input type='checkbox' name='videos[]' value='" . $row['id'] . "'><a href='view_video.php?id=" . $row['id'] . "' class='video-link'>" . $row['title'] . "</a></div>";
                            }
                            echo "<button type='submit' class='btn btn-danger mt-2' name='delete_videos'>Delete Selected Videos</button>"; // Move delete button inside this block
                        } else {
                            echo "No videos uploaded yet.";
                        }
                        ?>
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Upload form -->
                <div class="upload-container">
                    <h3>Upload Video</h3>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <!-- Existing upload form code -->
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

<?php
mysqli_close($conn);
?>
