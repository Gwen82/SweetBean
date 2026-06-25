<?php

$host = "sql303.ezyro.com";
$user = "ezyro_42229598";
$password = "04ee461792590";
$database = "ezyro_42229598_sweetbean_db";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

?>