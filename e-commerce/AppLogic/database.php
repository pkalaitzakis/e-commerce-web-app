<?php

$host = "mysql_db";
$dbname = "idm";
$username = "root";
$password = "secret";

$mysqli = new mysqli($host,
                     $username,
                     $password,
                     $dbname);

if ($mysqli->connect_errno) {
    die("Connection error: " . $mysqli->connect_error);
}

return $mysqli;
