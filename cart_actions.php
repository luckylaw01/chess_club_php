<?php
session_start();
require_once "includes/db_connect.php";

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        if ($productId > 0) {
            // Check if product exists and has stock
            $query = "SELECT * FROM products WHERE id = $productId LIMIT 1";
            $result = mysqli_query($conn, $query);
            $product = mysqli_fetch_assoc($result);

            if ($product && $product['stock_quantity'] >= $quantity) {
                if (isset($_SESSION['cart'][$productId])) {
                    $_SESSION['cart'][$productId] += $quantity;
                } else {
                    $_SESSION['cart'][$productId] = $quantity;
                }
                
                $cartCount = array_sum($_SESSION['cart']);
                echo json_encode(['status' => 'success', 'message' => 'Product added to cart', 'cartCount' => $cartCount]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Product out of stock or invalid']);
            }
        }
        break;

    case 'remove':
        $productId = (int)($_POST['product_id'] ?? 0);
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
            $cartCount = array_sum($_SESSION['cart']);
            echo json_encode(['status' => 'success', 'cartCount' => $cartCount]);
        }
        break;

    case 'update':
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if ($productId > 0 && $quantity > 0) {
            // Check stock again
            $query = "SELECT stock_quantity FROM products WHERE id = $productId";
            $result = mysqli_query($conn, $query);
            $product = mysqli_fetch_assoc($result);
            
            if ($product && $product['stock_quantity'] >= $quantity) {
                $_SESSION['cart'][$productId] = $quantity;
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Insufficient stock']);
            }
        }
        break;

    case 'clear':
        $_SESSION['cart'] = [];
        echo json_encode(['status' => 'success']);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>