<?php
// Helps connect to the database
define("DB_SERVER", "localhost"); // Local Host using
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "games"); // Database Name

// Function to establish the database connection
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

// function to close the database
function close_db($dbconnection) {
    // passes through $dbconnection
    // mysqli_close() is built in to close the database
    if(isset($dbconnection)) {
        mysqli_close($dbconnection);
    }
}

// if statement for if a user wants to order games in a particular way
if(isset($_POST['order'])) {
    $order_by = " ORDER BY "; 
    switch ($_POST['order']) {
        case 'console':
            $order_by .= "ConsoleName";
            break;
        case 'series':
            $order_by .= "Series";
            break;
        case 'title':
            $order_by .= "Title";
            break;
        case 'year':
            $order_by .= "ReleaseYear";
            break;
        case 'publisher':
            $order_by .= "Publisher";
            break;
        case 'genre':
            $order_by .= "Genre";
            break;
        default:
            $order_by = "";
    }
} else {
    $order_by = "";
}

// run db_connection() function and save as variable
$db_connect = db_connection();

// Create SQL Query constructed and order the results
//$sql_query = "SELECT * FROM games " . $order_by . ";";

$sql_query = "SELECT g.GameID,
 c.ConsoleName, 
 g.Series, 
 g.Title, 
 g.ReleaseYear, 
 g.Publisher, 
 GROUP_CONCAT(r.Genre SEPARATOR ', ') AS Genres 
 FROM 
    Consoles c 
LEFT JOIN 
    Games g ON c.ConsoleID = g.ConsoleID 
LEFT JOIN 
    GameGenres gg ON g.GameID = gg.GameID 
LEFT JOIN Genres r ON gg.GenreID = r.GenreID 
GROUP BY 
    c.ConsoleName, g.Series, g.Title, g.ReleaseYear, g.Publisher" . $order_by . ";";

$result_arr = mysqli_query($db_connect, $sql_query);
if(!mysqli_connect_errno()) {
    // itterates through database and adds each row to data array
    $data = [];
    while($row = mysqli_fetch_assoc($result_arr)) {
        $data[] = $row;
    }

    // encode as JSON and return
    header('Content-Type: application/json');
    echo json_encode($data); // converts item rows into JSON format
    if(json_last_error() !== JSON_ERROR_NONE) {
        echo "JSON Error: " . json_last_error_msg();
    } 

    db_close($db_connect);
} 

?>