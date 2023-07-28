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

// Get the links from links.json
$links = file_get_contents("https://api.romemesphil.com/dogfood/links.json");
$links = json_decode($links, true);

// go to "robloxAPIs" in links.json
$links = $links["robloxAPIs"];


// set $now as epoch time
$now = time();



// check the table rapistats and get the last time it was updated
// if it was updated less than 30 seconds ago, don't update it
$sql = "SELECT * FROM rapistats";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

$last_updated = $row["time"];
if ($now - $last_updated < 30) {
    // echo the latest json of the table rapistats
    $sql = "SELECT * FROM rapistats";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    $data = array();
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data, JSON_PRETTY_PRINT);
    die();
}

// if get key=[readacted], do:
if ($_GET["key"] == "[readacted]") {
    foreach ($links as $link) {
        $url = $link["url"];
        $name = $link["name"];
        $uniqid = $link["uniqid"];
        
        // get the response time and response code
        $ms = get_ms($url);

        // get response code in function get_httpcode
        $httpcode = get_httpcode($url);
        

        // if the response code is 200, set status to up
        if ($httpcode == 200) {
            $status = "OK";
        } else if ($httpcode == 503 || $httpcode != 200) {
            // if the response code is not 200, set status to down
            $status = "Service Unavailable";
        } else if ($ms > 5000) {
            $status = "Service Degradation";
        }
        
        // using uniqid, check if the service is already in the database
        $sql = "SELECT * FROM `rapistats` WHERE `uniqid` = '$uniqid'";
        $result = $conn->query($sql);

        // if the service is already in the database, update the status
        // colums are uniqid, name, status, response_time, response_code, time

        if ($result->num_rows > 0) {
            $sql = "UPDATE `rapistats` SET `status` = '$status', `response_time` = '$ms', `response_code` = '$httpcode', `time` = '$now' WHERE `uniqid` = '$uniqid'";
            $conn->query($sql);
        } else {
            // if the service is not in the database, insert it
            $sql = "INSERT INTO `rapistats` (`uniqid`, `name`, `status`, `response_time`, `response_code`, `time`) VALUES ('$uniqid', '$name', '$status', '$ms', '$httpcode', '$now')";
            $conn->query($sql);
        }
    }

    // echo data from the database in json format
    $sql = "SELECT * FROM rapistats";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        // echo cached as no
        echo '{"cached":"no"}';
        echo json_encode($row);
    }
} else {
    // if the key is not valid, echo 403 Forbidden in json
    echo json_encode(array("error" => "Method not yet implemented or invalid key supplied."), JSON_PRETTY_PRINT);
    http_response_code(501);
}

// set function get_ms to get the response time of a url using curl
function get_ms($url) {
    $start = microtime(true);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $response_message = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    $end = microtime(true);
    $ms = round(($end - $start) * 1000);
    return $ms;
}

// set function to get response code of a url using curl
function get_httpcode($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $httpcode;
}
