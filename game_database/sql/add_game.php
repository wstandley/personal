<?php
// Helps connect to the database same as other php file
define("DB_SERVER", "localhost"); // Local Host using
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "games"); // Database Name

// Function to establish the database connection same as other php file
function db_connection() {
    $dbconnection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
    // mysqli_connect() is required for the attempt at connection

    // if statement to check if there is an error connecting, otherwise returns the connection
    if(mysqli_connect_errno()) {
        $msg = "Database connection failed: ";
        $msg .= mysqli_connect_error(); // built-in php function
        $msg .= " (" . mysqli_connect_errno() . ")";
        exit($msg);
    }
    return $dbconnection;
}

// function to close the database same as other file
function close_db($dbconnection) {
    // passes through $dbconnection
    // mysqli_close() is built in to close the database
    if(isset($dbconnection)) {
        mysqli_close($dbconnection);
    }
}

// Above is strictly only for database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $console = $_POST['console']; // Retrieves data from console label and stores as variable
    $series = $_POST['series']; // Retrieves data from series label and stores as variable
    $title = $_POST['title']; // Retrieves data from title label and stores as variable
    $release_year = $_POST['release']; // Retrieves data from release label and stores as variable
    $publisher = $_POST['publisher']; // Retrieves data from publisher label and stores as variable
    $genres = $_POST['genres']; // Retrieves data from genres label and stores as variable

    $db_connection = db_connection();

    // Transaction needs to be started in order to add games genres to GameGenre table
    mysqli_begin_transaction($db_connection);
    $game_insert_success = false;
    $game_id = null;

    // Insert game details
    $sql_insert_game = "INSERT INTO Games (ConsoleID, Series, Title, ReleaseYear, Publisher) VALUES (?, ?, ?, ?, ?)";
    $stmt_game = mysqli_prepare($db_connection, $sql_insert_game);
    mysqli_stmt_bind_param($stmt_game, "sssss", $console, $series, $title, $release_year, $publisher);
    $execute_game = mysqli_stmt_execute($stmt_game);

    if ($execute_game) {
        $game_insert_success = true;
        $game_id = mysqli_insert_id($db_connection);
    } else {
        echo "Error adding game: " . mysqli_error($db_connection);
    }
    mysqli_stmt_close($stmt_game);

    // Insert game genres
    $genres_insert_success = true;
    if ($game_insert_success && !empty($genres)) {
        $sql_insert_genre = "INSERT INTO GameGenres (GameID, GenreID) VALUES (?, ?)";
        $stmt_genre = mysqli_prepare($db_connection, $sql_insert_genre);

        foreach ($genres as $genre_id) {
            mysqli_stmt_bind_param($stmt_genre, "is", $game_id, $genre_id);
            if (!mysqli_stmt_execute($stmt_genre)) {
                $genres_insert_success = false;
                echo "Error adding genre: " . mysqli_error($db_connection);
                break;
            }
        }
        mysqli_stmt_close($stmt_genre);
    } elseif ($game_insert_success && empty($genres)) {
        $genres_insert_success = true;
    }

    // Commit or rollback transaction
    if ($game_insert_success && $genres_insert_success) {
        mysqli_commit($db_connection);
        header("Location: ../index.html?message=Game added successfully");
        exit();
    } else {
        mysqli_rollback($db_connection);
        echo "Error adding game and/or genres. Transaction rolled back.";
    }

    // Close database
    close_db($db_connection);
}

?>