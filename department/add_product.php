<?php
include 'config.php';

// Fetch categories for the dropdown
$query = "SELECT DISTINCT prod_category FROM products";
$stmt = $pdo->query($query);
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 600px;
            width: 100%;
            box-sizing: border-box;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        input[type="text"], input[type="number"], select, input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="file"] {
            padding: 3px;
        }

        button {
            display: block;
            width: 100%;
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .hidden {
            display: none;
        }
    </style>
    <script>
        function toggleCategoryInput() {
            var select = document.getElementById('prod_category');
            var input = document.getElementById('new_category');
            var label = document.querySelector('label[for="new_category"]');
            if (select.value === 'new') {
                input.classList.remove('hidden');
                label.classList.remove('hidden');
                input.required = true;
            } else {
                input.classList.add('hidden');
                label.classList.add('hidden');
                input.required = false;
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Add Product</h1>
        <form action="add_product_process.php" method="POST" enctype="multipart/form-data">
            <label for="prod_name">Product Name:</label>
            <input type="text" id="prod_name" name="prod_name" required>
            
            <label for="prod_category">Category:</label>
            <select id="prod_category" name="prod_category" onchange="toggleCategoryInput()" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                <?php endforeach; ?>
                <option value="new">Add New Category</option>
            </select>
            
            <label for="new_category" class="hidden">New Category:</label>
            <input type="text" id="new_category" name="new_category" class="hidden">
            
            <label for="rate">Rate:</label>
            <input type="number" id="rate" name="rate" step="0.01" required>
            
            <label for="prod_img">Image:</label>
            <input type="file" id="prod_img" name="prod_img" accept="image/*" required>
            
            <button type="submit">Add Product</button>
        </form>
    </div>
</body>
</html>
