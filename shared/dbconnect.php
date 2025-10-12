<?php
$hostname = "localhost";
$username = "root";
$password = "";
$database = "jersey_mart";

//create connection
$conn = mysqli_connect($hostname,$username,$password,$database);

//check connection
if(!$conn)
    die("Connection failed: " . mysqli_connect_error());
?>