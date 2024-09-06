<?php
include 'config.php';

$minRate = isset($_GET['min_rate']) ? (int)$_GET['min_rate'] : 0;
$maxRate = isset($_GET['max_rate']) ? (int)$_GET['max_rate'] : PHP_INT_MAX;
$category = isset($_GET['category']) ? $_GET['category'] : '';
$productName = isset($_GET['prod_name']) ? $_GET['prod_name'] : '';

// Prepare an array for the response
$response = [
    'minRate' => $minRate,
    'maxRate' => $maxRate,
    'products' => []
];

// Fetch min and max rate based on the selected category
$categoryCondition = $category ? "WHERE prod_category = :category" : '';
$minMaxQuery = "SELECT MIN(rate) AS min_rate, MAX(rate) AS max_rate FROM products $categoryCondition";
$minMaxStmt = $pdo->prepare($minMaxQuery);
if ($category) {
    $minMaxStmt->execute(['category' => $category]);
} else {
    $minMaxStmt->execute();
}
$minMax = $minMaxStmt->fetch(PDO::FETCH_ASSOC);

if ($minMax) {
    $response['minRate'] = (int)$minMax['min_rate'];
    $response['maxRate'] = (int)$minMax['max_rate'];
}

// Fetch products based on category and rate range
$query = "SELECT * FROM products WHERE rate BETWEEN :min_rate AND :max_rate";
if ($category) {
    $query .= " AND prod_category = :category";
}
if ($productName) {
    $query .= " AND prod_name LIKE :prod_name";
}
$stmt = $pdo->prepare($query);
$params = [
    'min_rate' => $minRate,
    'max_rate' => $maxRate
];
if ($category) {
    $params['category'] = $category;
}
if ($productName) {
    $params['prod_name'] = '%' . $productName . '%';
}
$stmt->execute($params);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$response['products'] = $products;

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>