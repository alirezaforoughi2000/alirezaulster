<?php
session_start();

// Database connection
$hostname = "sahrjeelmysql.mysql.database.azure.com";
$username = "sharjeel";
$password = "Sa1234567";
$dbname = "netflix";

$conn = mysqli_connect($hostname, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch genres from the genres table
$genreQuery = "SELECT DISTINCT genre_name FROM genres";
$genreResult = mysqli_query($conn, $genreQuery);
$genres = [];
if ($genreResult && mysqli_num_rows($genreResult) > 0) {
    while ($row = mysqli_fetch_assoc($genreResult)) {
        $genres[] = $row['genre_name'];
    }
}

// Fetch age ratings from the agerating table
$ageRatingQuery = "SELECT DISTINCT rating_name FROM agerating";
$ageRatingResult = mysqli_query($conn, $ageRatingQuery);
$ageRatings = [];
if ($ageRatingResult && mysqli_num_rows($ageRatingResult) > 0) {
    while ($row = mysqli_fetch_assoc($ageRatingResult)) {
        $ageRatings[] = $row['rating_name'];
    }
}

// Check if the user clicked the search button
if (isset($_POST['search'])) {
    $search = $_POST['search'];
    $genre = isset($_POST['genre']) ? $_POST['genre'] : '';
    $ageRating = isset($_POST['age_rating']) ? $_POST['age_rating'] : '';

    // Construct the query based on search terms
    $query = "SELECT * FROM videos WHERE (title LIKE '%$search%' OR description LIKE '%$search%' OR Producer LIKE '%$search%' OR Genre LIKE '%$search%' OR AgeRating LIKE '%$search%')";
    if ($genre != '') {
        $query .= " AND Genre = '$genre'";
    }
    if ($ageRating != '') {
        $query .= " AND AgeRating = '$ageRating'";
    }

    $result = mysqli_query($conn, $query);
} else {
    // If not, fetch all videos
    $query = "SELECT * FROM videos";
    $result = mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Netflix</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #141414;
            color: #fff;
            margin: 0;
            padding: 0;
        }

        h2 {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .logout,
        .signup {
            float: right;
            margin-top: 20px;
            margin-right: 20px;
            color: #fff;
            text-decoration: none;
        }

        .logout:hover,
        .signup:hover {
            color: #ccc;
        }

        .search-form {
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }

        .form-control {
            background-color: #333;
            color: #fff;
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            margin-right: 10px;
            width: 250px;
            transition: all 0.3s;
        }

        .form-control:focus {
            box-shadow: none;
            background-color: #444;
            color: #fff;
        }

        .btn-search {
            background-color: #e50914;
            color: #fff;
            border: none;
            border-radius: 25px;
            padding: 10px 30px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-search:hover {
            background-color: #ff0c00;
        }

        .table {
            background-color: #000;
            color: #fff;
        }

        .table th,
        .table td {
            border: none;
            padding: 15px;
        }

        .table th {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #222;
        }

        .table tbody tr:nth-child(even) {
            background-color: #222;
        }

        .table tbody tr:hover {
            background-color: #333;
        }

        .video-link {
            color: #fff;
            text-decoration: none;
            transition: all 0.3s;
        }

        .video-link:hover {
            color: #e50914;
        }

        .age-rating {
            color: #fff;
        }

        .age-rating.pg-13 {
            color: #00ff00; /* Green for PG-13 */
        }

        .age-rating.r {
            color: #ff0000; /* Red for 18+ rating */
        }

        /* Modal */
        .modal-container {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #222;
            padding: 20px;
            border-radius: 10px;
        }

        .close-btn {
            color: #ccc;
            cursor: pointer;
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .username {
            float: right;
            margin-top: 20px;
            margin-right: 20px;
            color: #fff;
        }

        .thumbnail {
            width: 100px; /* Adjust width as needed */
            height: auto; /* Maintain aspect ratio */
        }

        .upload-link {
            float: right;
            margin-top: 20px;
            margin-right: 20px;
            color: #fff;
            text-decoration: none;
        }

        /* Dashboard */
        .dashboard {
            background-color: #222;
            padding: 20px;
            margin-top: 30px;
            border-radius: 10px;
        }

        .dashboard h3 {
            color: #fff;
            margin-bottom: 20px;
        }

        .dashboard .video-list {
            list-style: none;
            padding: 0;
        }

        .dashboard .video-list li {
            margin-bottom: 10px;
        }

        .dashboard .video-list li a {
            color: #fff;
            text-decoration: none;
            transition: all 0.3s;
        }

        .dashboard .video-list li a:hover {
            color: #e50914;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h2>Welcome to Netflix</h2>
            </div>
            <div class="col-md-6">
                <?php if (isset($_SESSION['username'])) : ?>
                    <span class="username">Welcome, <?php echo $_SESSION['username']; ?></span>
                    <a class="logout" href="logout.php">Logout</a>
                    <?php if ($_SESSION['username'] == 'Admin') : ?>
                        <a class="upload-link" href="secure3.php">Upload Video</a>
                    <?php endif; ?>
                <?php else : ?>
                    <a class="signup" href="#" id="signup-link">Sign Up</a>
                    <a class="logout" href="#" id="signin-link">Sign In</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sign-up form -->
        <div id="signup-form" class="modal-container">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <h3>Sign Up</h3>
                <form action="signup_process.php" method="POST">
                    <div class="form-group">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="fname" name="fname" placeholder="First Name" required>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="lname" name="lname" placeholder="Last Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="contact" name="contact" placeholder="Contact Number" required>
                    </div>
                    <button type="submit" name="signup" class="btn btn-primary">Sign Up</button>
                </form>
            </div>
        </div>

        <!-- Sign-in form -->
        <div id="signin-form" class="modal-container">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <h3>Sign In</h3>
                <form action="login_process.php" method="POST">
                    <div class="form-group">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" name="signin" class="btn btn-primary">Sign In</button>
                </form>
            </div>
        </div>

        <!-- Search form -->
        <form action="" method="POST" class="search-form">
            <input type="text" name="search" class="form-control" placeholder="Search by title, description, producer, genre, or age rating" value="<?php echo isset($_POST['search']) ? $_POST['search'] : ''; ?>">
            <select name="genre" class="form-control">
                <option value="">All Genres</option>
                <?php foreach ($genres as $genreOption) : ?>
                    <option value="<?php echo $genreOption; ?>" <?php echo (isset($_POST['genre']) && $_POST['genre'] == $genreOption) ? 'selected' : ''; ?>>
                        <?php echo $genreOption; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="age_rating" class="form-control">
                <option value="">All Age Ratings</option>
                <?php foreach ($ageRatings as $ageRatingOption) : ?>
                    <option value="<?php echo $ageRatingOption; ?>" <?php echo (isset($_POST['age_rating']) && $_POST['age_rating'] == $ageRatingOption) ? 'selected' : ''; ?>>
                        <?php echo $ageRatingOption; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-search">Search</button>
        </form>

        <!-- Video listing -->
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Producer</th>
                    <th>Genre</th>
                    <th>Age Rating</th>
                    <th>Thumbnail</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                        <tr>
                            <td><a class="video-link" href="#"><?php echo $row['title']; ?></a></td>
                            <td><?php echo $row['description']; ?></td>
                            <td><?php echo $row['Producer']; ?></td>
                            <td><?php echo $row['Genre']; ?></td>
                            <td class="age-rating <?php echo strtolower(str_replace('+', '', $row['AgeRating'])); ?>"><?php echo $row['AgeRating']; ?></td>
                            <td>
                                <?php if (!empty($row['Thumbnail'])) : ?>
                                    <img class="thumbnail" src="<?php echo $row['Thumbnail']; ?>" alt="Thumbnail">
                                <?php else : ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6">No videos found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.getElementById('signup-link').addEventListener('click', function () {
            document.getElementById('signup-form').style.display = 'flex';
        });

        document.getElementById('signin-link').addEventListener('click', function () {
            document.getElementById('signin-form').style.display = 'flex';
        });

        var closeButtons = document.querySelectorAll('.close-btn');
        closeButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                button.parentElement.parentElement.style.display = 'none';
            });
        });

        window.addEventListener('click', function (event) {
            var modalContainers = document.querySelectorAll('.modal-container');
            modalContainers.forEach(function (container) {
                if (event.target == container) {
                    container.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>
