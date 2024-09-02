<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prod_name = $_POST['prod_name'];
    $rate = $_POST['rate'];
    $prod_img = $_FILES['prod_img'];

    // Check if a new category was submitted
    if (!empty($_POST['new_category'])) {
        $prod_category = $_POST['new_category'];

        // Insert new category into the database if it does not already exist
        $query = "INSERT IGNORE INTO products (prod_category) VALUES (:prod_category)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':prod_category' => $prod_category]);
    } else {
        $prod_category = $_POST['prod_category'];
    }

    // Ensure uploads directory exists and is writable
    $uploadDir = __DIR__ . '/uploads/';
   

    // Handle file upload
  
        $imgPath = $uploadDir . basename($prod_img['name']);

        // Move uploaded file
            // File uploaded successfully
            $query = "INSERT INTO products (prod_name, prod_category, rate, prod_img) VALUES (:prod_name, :prod_category, :rate, :prod_img)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':prod_name' => $prod_name,
                ':prod_category' => $prod_category,
                ':rate' => $rate,
                ':prod_img' => null // Store only the filename
            ]);
            header("Location: index.php");
        
   
}
?>
