<?php
require_once __DIR__.'/db_connect.php';

function get_all_tiers() {
    $pdo = Database::getInstance();
    return $pdo->query("SELECT * FROM products ORDER BY price ASC")->fetchAll();
}

function get_tier($id) {
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function record_purchase($user_id, $product_id, $txn_id, $amount) {
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, product_id, txn_id, amount) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$user_id, $product_id, $txn_id, $amount]);
}
?>