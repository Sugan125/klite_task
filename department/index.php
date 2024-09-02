<?php
include 'config.php';

$category_filter = $_GET['category'] ?? '';
$name_filter = $_GET['name'] ?? '';


$min_rate_filter = $_GET['min_rate'] ?? 0; // Default to 0
$max_rate_filter = $_GET['max_rate'] ?? 1000; // Adjust this default to a high value depending on your products' max rate

// Get the minimum and maximum rate from the products based on the category
$rate_query = "SELECT MIN(rate) AS min_rate, MAX(rate) AS max_rate FROM products";

$params = [];


if ($category_filter) {
    $rate_query .= " WHERE prod_category = :category_filter";
    $params[':category_filter'] = $category_filter;
}

// Get the minimum and maximum rate for the category
$rate_stmt = $pdo->prepare($rate_query);
$rate_stmt->execute($params);
$rate_result = $rate_stmt->fetch(PDO::FETCH_ASSOC);
$min_rate_value = $rate_result['min_rate'] ?? 0;
$max_rate_value = $rate_result['max_rate'] ?? 1000;

$query = "SELECT * FROM products WHERE prod_name LIKE :name_filter";
$params = [':name_filter' => "%$name_filter%"];

if ($category_filter) {
    $query .= " AND prod_category = :category_filter";
    $params[':category_filter'] = $category_filter;
}

if ($min_rate_filter || $max_rate_filter) {
    $query .= " AND rate BETWEEN :min_rate_filter AND :max_rate_filter";
    $params[':min_rate_filter'] = $min_rate_filter;
    $params[':max_rate_filter'] = $max_rate_filter;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For filtering options
$categories_stmt = $pdo->query("SELECT DISTINCT prod_category FROM products");
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

$rates_stmt = $pdo->query("SELECT DISTINCT rate FROM products ORDER BY rate");
$rates = $rates_stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
        }

        .sidebar {
            width: 250px;
            padding: 20px;
            background-color: #fff;
            border-right: 1px solid #ddd;
            position: fixed;
            height: 100%;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .main-content {
            margin-left: 290px;
            padding: 20px;
            flex: 1;
        }

        h2 {
            margin: 0 0 20px;
            font-size: 1.5em;
        }

        form {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }

        label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"], select {
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }

        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .product-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .product-card img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }

        .product-card h2 {
            font-size: 1.25em;
            margin: 10px 0;
        }

        .product-card p {
            margin: 5px 0;
            font-size: 1em;
        }

        .center-button {
            text-align: right;
            margin: 20px 0;
        }

        .center-button a {
            display: inline-block;
            text-decoration: none;
            color: #fff;
            background-color: #007bff;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
        }

        .center-button a:hover {
            background-color: #0056b3;
        }

        .filter-section {
            margin-bottom: 20px;
        }

        .range-slider {
            position: relative;
            width: 250px;
            margin: 0 auto;
            height: 38px;
        }

        .range-slider input[type="range"] {
            width: 100%;
            
            -webkit-appearance: none;
            appearance: none;
            pointer-events: none;
        }

        .range-slider input[type="range"]::-webkit-slider-thumb {
            pointer-events: auto;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #007bff;
            cursor: pointer;
            -webkit-appearance: none;
        }

        .range-slider input[type="range"]::-moz-range-thumb {
            pointer-events: auto;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #007bff;
            cursor: pointer;
        }

        .slider-track {
            position: absolute;
            height: 6px;
            background: #ddd;
            border-radius: 3px;
            top: 25px;
            width: 100%;
        }

        .slider-range {
            position: absolute;
            height: 6px;
            background: #007bff;
            border-radius: 3px;
            top: 25px;
        }

        .value-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 14px;
        }

    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Filter Products</h2>
        <form oninput="onFilterChange()">
            <label for="category">Category:</label>
            <select id="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['prod_category']); ?>" <?php if ($category['prod_category'] === $category_filter) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($category['prod_category']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="category">Min to Max rate:</label>
            <div class="range-slider">
    <div class="slider-track"></div>
    <div class="slider-range" id="slider-range"></div>
    <input type="range" id="min-slider" min="<?php echo $min_rate_value; ?>" max="<?php echo $max_rate_value; ?>" value="<?php echo $min_rate_filter; ?>" oninput="updateSlider()">
    <input type="range" id="max-slider" min="<?php echo $min_rate_value; ?>" max="<?php echo $max_rate_value; ?>" value="<?php echo $max_rate_filter; ?>" oninput="updateSlider()">
</div>
<div class="value-labels">
    <span id="min-value">$<?php echo $min_rate_filter; ?></span>
    <span id="max-value">$<?php echo $max_rate_filter; ?></span>
</div>

<br>
            <label for="name">Name:</label>
            <input type="text" id="name" placeholder="Search by name" value="<?php echo htmlspecialchars($name_filter); ?>">
        </form>
    </div>
    
    <div class="main-content">
        <h1>Product List</h1>
        <div class="center-button">
            <a href="add_product.php">Add Product</a>
        </div>
        <div class="grid-container">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <?php if (!empty($product['prod_img'])): ?>
                        <img width="150" height="100" src="<?php echo htmlspecialchars($product['prod_img']); ?>" alt="<?php echo htmlspecialchars($product['prod_name']); ?>">
                    <?php else: ?>
                        <img width="150" height="100" src="default-image.jpg" alt="Default Image">
                    <?php endif; ?>
                    <h2><?php echo htmlspecialchars($product['prod_name']); ?></h2>
                    <p>Category: <?php echo htmlspecialchars($product['prod_category']); ?></p>
                    <p>Rate: $<?php echo htmlspecialchars($product['rate']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
function updateSlider() {
        const minSlider = document.getElementById('min-slider');
        const maxSlider = document.getElementById('max-slider');
        const minValue = document.getElementById('min-value');
        const maxValue = document.getElementById('max-value');
        const sliderRange = document.getElementById('slider-range');

        let min = parseInt(minSlider.value);
        let max = parseInt(maxSlider.value);

        if (min > max) {
            maxSlider.value = min;
            max = min;
        }

        minValue.innerText = `$${min}`;
        maxValue.innerText = `$${max}`;

        const minPos = (minSlider.value / minSlider.max) * 100;
        const maxPos = (maxSlider.value / maxSlider.max) * 100;

        sliderRange.style.left = `${minPos}%`;
        sliderRange.style.width = `${maxPos - minPos}%`;
    }

    function onFilterChange() {
        const category = document.getElementById('category').value;
        const name = document.getElementById('name').value;
        const minRate = document.getElementById('min-slider').value;
        const maxRate = document.getElementById('max-slider').value;

        const queryString = new URLSearchParams({
            category: category,
            name: name,
            min_rate: minRate,
            max_rate: maxRate
        }).toString();

        window.location.href = `?${queryString}`;
    }
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category');
        const minSlider = document.getElementById('min-slider');
        const maxSlider = document.getElementById('max-slider');

        categorySelect.addEventListener('change', function() {
            const category = this.value;
            const url = `get_rates.php?category=${encodeURIComponent(category)}&t=${new Date().getTime()}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data && data.minRate !== undefined && data.maxRate !== undefined) {
                        minSlider.min = data.minRate;
                        maxSlider.max = data.maxRate;

                        minSlider.value = Math.max(minSlider.value, minSlider.min);
                        maxSlider.value = Math.min(maxSlider.value, maxSlider.max);

                        updateSlider();
                    } else {
                        console.error('Invalid data received:', data);
                    }
                })
                .catch(error => {
                    console.error('Error fetching rates:', error);
                });
        });

        minSlider.addEventListener('input', onFilterChange);
        maxSlider.addEventListener('input', onFilterChange);

        // Initial call to updateSlider if sliders are pre-set
        updateSlider();
    });
</script>

</body>
</html>
