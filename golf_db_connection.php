<?php 
$servername = "connoryoung.com";
$username = "connor";
$password = "PatrickRoy33";
$dbname = "DataGolf API";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (mysqli_connect_error()) {
    die("Connection failed: " . mysqli_connect_error());
}

header('Content-Type: text/html; charset=utf-8');

mysqli_set_charset($conn, "utf8");

?>
