<?php
session_start();

// Redirect if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: signin_form.php");
    exit();
}

// Redirect if filename is not provided
if (!isset($_GET['filename']) || empty($_GET['filename'])) {
    header("Location: secure.php");
    exit();
}

$filename = $_GET['filename'];

// Database connection
$conn = mysqli_connect("localhost", "root", "", "videos1");

// Fetch video details based on filename
$query = "SELECT * FROM videos WHERE filename = '$filename'";
$result = mysqli_query($conn, $query);

// Check if video exists
if (mysqli_num_rows($result) == 1) {
    $video = mysqli_fetch_assoc($result);
} else {
    echo "Video not found.";
    exit();
}

$videoId = $video['id'];
$userId = $_SESSION['id'];

// Fetch existing comments for the video
$fetchCommentsQuery = "SELECT comments.*, users.username, DATE_FORMAT(upload_datetime, '%W, %M %e, %Y, %l:%i %p') AS formatted_datetime
                       FROM comments 
                       INNER JOIN users ON comments.commenter_id = users.id
                       WHERE comments.video_id = $videoId
                       ORDER BY comments.upload_datetime DESC";
$commentsResult = mysqli_query($conn, $fetchCommentsQuery);

// Check if user has liked or disliked the video
$checkLikeQuery = "SELECT * FROM likes WHERE video_id = $videoId AND user_id = $userId";
$checkDislikeQuery = "SELECT * FROM dislikes WHERE video_id = $videoId AND user_id = $userId";

$hasLiked = mysqli_num_rows(mysqli_query($conn, $checkLikeQuery)) > 0;
$hasDisliked = mysqli_num_rows(mysqli_query($conn, $checkDislikeQuery)) > 0;

// Count total likes and dislikes
$countLikesQuery = "SELECT COUNT(*) AS total_likes FROM likes WHERE video_id = $videoId";
$countDislikesQuery = "SELECT COUNT(*) AS total_dislikes FROM dislikes WHERE video_id = $videoId";

$totalLikesResult = mysqli_query($conn, $countLikesQuery);
$totalLikes = mysqli_fetch_assoc($totalLikesResult)['total_likes'];

$totalDislikesResult = mysqli_query($conn, $countDislikesQuery);
$totalDislikes = mysqli_fetch_assoc($totalDislikesResult)['total_dislikes'];

// Process form submission to add new comment
if (isset($_POST['submit_comment'])) {
    $comment = $_POST['comment'];
    $commenter_id = $_SESSION['id'];

    $insertCommentQuery = "INSERT INTO comments (video_id, commenter_id, comment, upload_datetime) 
                       VALUES ($videoId, $commenter_id, '$comment', NOW())";

    if (mysqli_query($conn, $insertCommentQuery)) {
        // Redirect to prevent form resubmission
        header("Location: view_video1.php?filename=$filename");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Video</title>
    <style>
        .like,
        .dislike {
            background-color: white;
            border: 1px solid black;
            color: black;
            cursor: pointer;
        }

        .like.clicked {
            background-color: green;
            color: white;
        }

        .dislike.clicked {
            background-color: red;
            color: white;
        }
    </style>
</head>

<body>
    <div>
        <a href="index.php" style="position: absolute; top: 10px; left: 10px;">Back</a>
    </div>
    <h2><?php echo $video['title']; ?></h2>
    <p><strong>Description:</strong> <?php echo $video['description']; ?></p>
    <video width="640" height="360" controls>
        <source src="uploads/<?php echo $filename; ?>" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <!-- Like and dislike buttons -->
    <form action="" method="POST" id="likeDislikeForm">
        <button type="submit" name="like" class="like <?php echo $hasLiked ? 'clicked' : ''; ?>">Like</button>
        <span><?php echo $totalLikes; ?> Likes</span>
        <button type="submit" name="dislike" class="dislike <?php echo $hasDisliked ? 'clicked' : ''; ?>">Dislike</button>
        <span><?php echo $totalDislikes; ?> Dislikes</span>
    </form>

    <!-- Comments section -->
    <div>
        <h3>Comments</h3>
        <?php
        if (mysqli_num_rows($commentsResult) > 0) {
            while ($comment = mysqli_fetch_assoc($commentsResult)) {
                echo '<p><strong>' . $comment['username'] . " - " . $comment['formatted_datetime'] . ':</strong> ' . $comment['comment'] . '</p>';
            }
        } else {
            echo '<p>No comments yet.</p>';
        }
        ?>
    </div>

    <!-- Add comment form -->
    <div>
        <h3>Add a Comment</h3>
        <form action="" method="POST">
            <textarea name="comment" rows="4" cols="50" placeholder="Enter your comment" required></textarea>
            <br>
            <button type="submit" name="submit_comment">Submit Comment</button>
        </form>
    </div>

    <?php
    // Handle like and dislike submission
    if (isset($_POST['like'])) {
        if (!$hasLiked) {
            // If the user has previously disliked, remove the dislike
            if ($hasDisliked) {
                $deleteDislikeQuery = "DELETE FROM dislikes WHERE video_id = $videoId AND user_id = $userId";
                mysqli_query($conn, $deleteDislikeQuery);
            }
            $likeQuery = "INSERT INTO likes (video_id, user_id) VALUES ($videoId, $userId)";
            mysqli_query($conn, $likeQuery);
            // Reload the page to update the button status
            header("Location: view_video1.php?filename=$filename");
            exit();
        } else {
            // If the user has already liked, remove the like
            $deleteLikeQuery = "DELETE FROM likes WHERE video_id = $videoId AND user_id = $userId";
            mysqli_query($conn, $deleteLikeQuery);
            // Reload the page to update the button status
            header("Location: view_video1.php?filename=$filename");
            exit();
        }
    }

    if (isset($_POST['dislike'])) {
        if (!$hasDisliked) {
            // If the user has previously liked, remove the like
            if ($hasLiked) {
                $deleteLikeQuery = "DELETE FROM likes WHERE video_id = $videoId AND user_id = $userId";
                mysqli_query($conn, $deleteLikeQuery);
            }
            $dislikeQuery = "INSERT INTO dislikes (video_id, user_id) VALUES ($videoId, $userId)";
            mysqli_query($conn, $dislikeQuery);
            // Reload the page to update the button status
            header("Location: view_video1.php?filename=$filename");
            exit();
        } else {
            // If the user has already disliked, remove the dislike
            $deleteDislikeQuery = "DELETE FROM dislikes WHERE video_id = $videoId AND user_id = $userId";
            mysqli_query($conn, $deleteDislikeQuery);
            // Reload the page to update the button status
            header("Location: view_video1.php?filename=$filename");
            exit();
        }
    }
    ?>
</body>

</html>
