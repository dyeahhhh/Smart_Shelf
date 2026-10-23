<?php
// setup.php - Run this ONCE to create the database and tables

$conn = new mysqli('localhost', 'root', '', '');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create Database
$conn->query("CREATE DATABASE IF NOT EXISTS smart_shelf");
$conn->select_db("smart_shelf");

// Create shelves table
$conn->query("
    CREATE TABLE IF NOT EXISTS shelves (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        location VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// Create items table
$conn->query("
    CREATE TABLE IF NOT EXISTS items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        shelf_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        category VARCHAR(50),
        quantity INT DEFAULT 0,
        min_quantity INT DEFAULT 5,
        unit VARCHAR(20) DEFAULT 'pcs',
        price DECIMAL(10,2) DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (shelf_id) REFERENCES shelves(id) ON DELETE CASCADE
    )
");

// Insert sample data
$conn->query("INSERT INTO shelves (name, location) VALUES
    ('Shelf A', 'Aisle 1'),
    ('Shelf B', 'Aisle 2'),
    ('Shelf C', 'Aisle 3')
");

$conn->query("INSERT INTO items (shelf_id, name, category, quantity, min_quantity, unit, price) VALUES
    (1, 'Rice', 'Grocery', 50, 10, 'kg', 60.00),
    (1, 'Sugar', 'Grocery', 3, 10, 'kg', 45.00),
    (2, 'Shampoo', 'Personal Care', 20, 5, 'pcs', 120.00),
    (2, 'Soap', 'Personal Care', 2, 8, 'pcs', 35.00),
    (3, 'Notebook', 'Stationery', 15, 5, 'pcs', 25.00),
    (3, 'Pen', 'Stationery', 4, 10, 'pcs', 10.00)
");

echo "<div style='font-family:sans-serif; padding:30px; text-align:center;'>
    <h2 style='color:green;'>✅ Database Setup Complete!</h2>
    <p>Database <b>smart_shelf</b> and all tables created successfully.</p>
    <p>Sample data has been added.</p>
    <br>
    <a href='index.php' style='background:#2d6a4f; color:white; padding:12px 25px; 
       text-decoration:none; border-radius:6px; font-size:16px;'>
       ➜ Go to Smart Shelf Dashboard
    </a>
</div>";

$conn->close();
?>
