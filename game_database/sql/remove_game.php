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

$db = db_connection();
$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['game_id'])) {
    $game_id_to_delete = mysqli_real_escape_string($db, $_POST['game_id']);

    $sql_remove_query = "DELETE FROM Games WHERE GameID = ?";
    $prep = mysqli_prepare($db, $sql_remove_query);

    if ($prep) {
        mysqli_stmt_bind_param($prep, "i", $game_id_to_delete);

        if (mysqli_stmt_execute($prep)) {
            if (mysqli_stmt_affected_rows($prep) > 0) {
                $response['success'] = true;
            } else {
                $response['success'] = false;
            }
        } else {
            $response['success'] = false;
            $response['error'] = "Error executing delete query: " . mysqli_stmt_error($prep);
        }
        mysqli_stmt_close($prep);
    } else {
        $response['success'] = false;
        $response['error'] = "Error preparing delete query: " . mysqli_error($db);
    }
} else {
    $response['success'] = false;
    $response['error'] = "Invalid request or missing game ID.";
}

header('Content-Type: application/json');
echo json_encode($response);

close_db($db);
?>
