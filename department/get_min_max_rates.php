<?php
include 'config.php';

$category = $_GET['category'] ?? '';

if ($category) {
    $query = "SELECT MIN(rate) AS min_rate, MAX(rate) AS max_rate FROM products WHERE prod_category = :category";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':category' => $category]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $min_rate = $result['min_rate'] ?? 0;
    $max_rate = $result['max_rate'] ?? 1000;

    echo json_encode(['min_rate' => $min_rate, 'max_rate' => $max_rate]);
} else {
    echo json_encode(['min_rate' => 0, 'max_rate' => 1000]);
}
?>
