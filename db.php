<?php
// db.php - Database Connection

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Default XAMPP username
define('DB_PASS', '');           // Default XAMPP password (empty)
define('DB_NAME', 'smart_shelf');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("<h3 style='color:red;font-family:sans-serif;'>
        ❌ Database Connection Failed: " . $conn->connect_error . "<br>
        Make sure XAMPP is running and you have run setup.php first.
    </h3>");
}

$conn->set_charset("utf8");
?>
