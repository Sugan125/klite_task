<?php
include 'config.php';

// Initialize minRate, maxRate, and productName
$minRate = 0;
$maxRate = PHP_INT_MAX;
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';
$searchName = isset($_GET['prod_name']) ? $_GET['prod_name'] : '';

// Fetch distinct categories
$categoriesQuery = "SELECT DISTINCT prod_category FROM products";
$categoriesStmt = $pdo->prepare($categoriesQuery);
$categoriesStmt->execute();
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch min and max rate based on the selected category
$categoryCondition = $selectedCategory ? "WHERE prod_category = :category" : '';
$minMaxQuery = "SELECT MIN(rate) AS min_rate, MAX(rate) AS max_rate FROM products $categoryCondition";
$minMaxStmt = $pdo->prepare($minMaxQuery);

if ($selectedCategory) {
    $minMaxStmt->execute(['category' => $selectedCategory]);
} else {
    $minMaxStmt->execute();
}

$minMax = $minMaxStmt->fetch(PDO::FETCH_ASSOC);
if ($minMax) {
    $minRate = $minMax['min_rate'];
    $maxRate = $minMax['max_rate'];
}

// Fetch products for initial load based on category, rate, and product name
$query = "SELECT * FROM products WHERE rate BETWEEN :min_rate AND :max_rate";
if ($selectedCategory) {
    $query .= " AND prod_category = :category";
}
if ($searchName) {
    $query .= " AND prod_name LIKE :prod_name";
}
$stmt = $pdo->prepare($query);

$params = ['min_rate' => $minRate, 'max_rate' => $maxRate];
if ($selectedCategory) {
    $params['category'] = $selectedCategory;
}
if ($searchName) {
    $params['prod_name'] = '%' . $searchName . '%';
}
$stmt->execute($params);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.8.0/nouislider.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }
        .main-container {
            display: flex;
            flex: 1;
        }
        .sidebar {
            width: 250px;
            background-color: #fff;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }
        .sidebar h2 {
            margin-top: 0;
        }
        .slider-container {
            margin: 20px 0;
        }
        .noUi-target {
            width: 100%;
            margin: 0 auto;
        }
        input[type="text"], select {
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }
        .noUi-connect {
            background: #007bff;
        }
        .slider-labels {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }
        .main-content {
            flex: 1;
            margin: 20px;
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
        input[type="text"], select {
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        width: 100%;
        box-sizing: border-box;
        background-color: #f9f9f9;
        font-size: 16px;
    }

    select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10"><polygon points="0,0 10,0 5,5" fill="#007bff"/></svg>');
        background-position: right 10px top 50%;
        background-repeat: no-repeat;
        background-size: 12px;
    }

    select:hover, input[type="text"]:hover {
        background-color: #fff;
    }

    select:focus, input[type="text"]:focus {
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        outline: none;
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
<div class="main-container">
    <div class="sidebar">
        <h2>Filter Products</h2>
        <form>
    <label for="category">Category:</label>
    <select id="category">
        <option value="">All Categories</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?php echo htmlspecialchars($category); ?>" <?php echo ($category === $selectedCategory) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($category); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="prodName">Search by Name:</label>
    <input type="text" id="prodName" placeholder="Enter product name">
  
    <div class="slider-container">
        <label>Rate Range:</label>
        <div id="priceSlider"></div>
        <div class="slider-labels">
            <span id="minRateLabel"><?php echo $minRate; ?></span>
            <span id="maxRateLabel"><?php echo $maxRate; ?></span>
        </div>
    </div>
</form>

    </div>

    <div class="main-content">
        <h1>Product List</h1>
        <div id="productGrid" class="grid-container">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="<?php echo htmlspecialchars($product['prod_img']); ?>" alt="<?php echo htmlspecialchars($product['prod_name']); ?>">
                    <h2><?php echo htmlspecialchars($product['prod_name']); ?></h2>
                    <p>Category: <?php echo htmlspecialchars($product['prod_category']); ?></p>
                    <p>Rate: $<?php echo htmlspecialchars($product['rate']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.8.0/nouislider.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category');
    const prodNameInput = document.getElementById('prodName');
    const minRateLabel = document.getElementById('minRateLabel');
    const maxRateLabel = document.getElementById('maxRateLabel');
    const productGrid = document.getElementById('productGrid');

    const slider = document.getElementById('priceSlider');
    noUiSlider.create(slider, {
        start: [<?php echo $minRate; ?>, <?php echo $maxRate; ?>],
        connect: true,
        range: {
            'min': <?php echo $minRate; ?>,
            'max': <?php echo $maxRate; ?>
        }
    });

    function updateProducts(minRate, maxRate, category, prodName) {
        fetch(`filter_products.php?min_rate=${minRate}&max_rate=${maxRate}&category=${encodeURIComponent(category)}&prod_name=${encodeURIComponent(prodName)}`)
            .then(response => response.json())
            .then(data => {
                let productHtml = '';
                data.products.forEach(product => {
                    productHtml += `
                        <div class="product-card">
                            <img src="${product.prod_img}" alt="${product.prod_name}">
                            <h2>${product.prod_name}</h2>
                            <p>Category: ${product.prod_category}</p>
                            <p>Rate: $${product.rate}</p>
                        </div>
                    `;
                });
                productGrid.innerHTML = productHtml;
                minRateLabel.textContent = data.minRate;
                maxRateLabel.textContent = data.maxRate;
            })
            .catch(error => console.error('Error:', error));
    }

    slider.noUiSlider.on('update', function(values) {
        const minRate = Math.round(values[0]);
        const maxRate = Math.round(values[1]);
        updateProducts(minRate, maxRate, categorySelect.value, prodNameInput.value);
    });

    categorySelect.addEventListener('change', function() {
        const selectedCategory = this.value;
        fetch(`filter_products.php?category=${encodeURIComponent(selectedCategory)}`)
            .then(response => response.json())
            .then(data => {
                slider.noUiSlider.updateOptions({
                    range: {
                        'min': data.minRate,
                        'max': data.maxRate
                    }
                });
                slider.noUiSlider.set([data.minRate, data.maxRate]);
                updateProducts(data.minRate, data.maxRate, selectedCategory, prodNameInput.value);
            })
            .catch(error => console.error('Error:', error));
    });

    prodNameInput.addEventListener('input', function() {
        const prodName = this.value;
        const minRate = Math.round(slider.noUiSlider.get()[0]);
        const maxRate = Math.round(slider.noUiSlider.get()[1]);
        updateProducts(minRate, maxRate, categorySelect.value, prodName);
    });
});

</script>
</body>
</html>
