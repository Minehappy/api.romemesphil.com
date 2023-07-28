<?php
// Connect to the database
$servername = "localhost";
$username = "[readacted]";
$password = "[readacted]";
$dbname = "[readacted]";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// check if api key is valid
if ($_GET["key"] == "[readacted]") {
    $sql = "SELECT * FROM rapistats";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $data = array();
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data, JSON_PRETTY_PRINT);
        http_response_code(200);
    } else {
        echo "0 results";
    }
    $conn->close();
} else {
    //echo 403 Forbidden in json
    echo json_encode(array("error" => "Method not yet implemented or invalid key supplied."), JSON_PRETTY_PRINT);
    http_response_code(501);
}

?>


