<?php
include 'config.php';

$category = $_GET['category'] ?? '';

$rate_query = "SELECT MIN(rate) AS min_rate, MAX(rate) AS max_rate FROM products WHERE prod_category = :category";
$params = [':category' => $category];

$rate_stmt = $pdo->prepare($rate_query);
$rate_stmt->execute($params);
$rate_result = $rate_stmt->fetch(PDO::FETCH_ASSOC);
$min_rate_value = $rate_result['min_rate'] ?? 0;
$max_rate_value = $rate_result['max_rate'] ?? 1000;

echo json_encode([
    'min_rate' => $min_rate_value,
    'max_rate' => $max_rate_value
]);
