<?php
include 'config.php';

$category = $_GET['category'] ?? '';

$query = "SELECT MIN(rate) AS minRate, MAX(rate) AS maxRate FROM products";

if ($category) {
    $query .= " WHERE prod_category = :category";
    $params = [':category' => $category];
} else {
    $params = [];
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($result);
?>
