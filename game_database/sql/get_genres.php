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

function close_db($dbconnection) {
    if(isset($dbconnection)) {
        mysqli_close($dbconnection);
    }
}

// Above is strictly only for database connection

// Retreive the GenreIDs and Genres associated with them and add to the aray
$db_connect = db_connection();
$sql_query = "SELECT GenreID, Genre FROM Genres ORDER BY Genre ASC;";
$result_arr = mysqli_query($db_connect, $sql_query);

if($result_arr) {
    $genres = [];
    while($row = mysqli_fetch_assoc($result_arr)) {
        $genres[] = $row;
    }
    header('Content-Type: application/json');
    echo json_encode($genres);
} else {
    echo "SQL Error: " . mysqli_error($db_connect);
}
close_db($db_connect);
?>