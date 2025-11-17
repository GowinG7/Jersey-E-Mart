<?php
// yo file admin panel ko jersies ko edit grna ko lagi 
//id bata fetch grna ho
require "../shared/dbconnect.php";

// Get the product ID from the URL query string
$id = $_GET['id'];

// Fetch the product details from the database using the ID
$res = $conn->query("SELECT * FROM products WHERE id=$id");

// Convert the fetched product data to JSON format and output it
// This is used for dynamically filling the Edit Jersey modal via JavaScript
echo json_encode($res->fetch_assoc());

