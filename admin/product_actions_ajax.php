<?php
session_start();
require_once "../includes/db_connect.php";

// Check for admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized access']));
}

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'list':
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        
        $query = "SELECT * FROM products WHERE 1=1";
        if (!empty($search)) {
            $search = mysqli_real_escape_with_mysqli($conn, $search);
            $query .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
        }
        if (!empty($category)) {
            $category = mysqli_real_escape_with_mysqli($conn, $category);
            $query .= " AND category = '$category'";
        }
        $query .= " ORDER BY created_at DESC";
        
        $result = mysqli_query($conn, $query);
        $products = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
        echo json_encode(['status' => 'success', 'products' => $products]);
        break;

    case 'add':
        $name = mysqli_real_escape_with_mysqli($conn, $_POST['name']);
        $description = mysqli_real_escape_with_mysqli($conn, $_POST['description']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock_quantity'];
        $category = mysqli_real_escape_with_mysqli($conn, $_POST['category']);
        $image_url = mysqli_real_escape_with_mysqli($conn, $_POST['image_url'] ?? '');

        // Handle File Upload
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/images/shop/';
            
            // Create directory if not exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_tmp = $_FILES['product_image']['tmp_name'];
            $file_name = time() . '_' . basename($_FILES['product_image']['name']);
            $target_file = $upload_dir . $file_name;

            // Simple validation
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['product_image']['type'];

            if (in_array($file_type, $allowed_types)) {
                if (move_uploaded_file($file_tmp, $target_file)) {
                    $image_url = $file_name;
                }
            }
        }
        
        $query = "INSERT INTO products (name, description, price, stock_quantity, category, image_url) 
                  VALUES ('$name', '$description', $price, $stock, '$category', '$image_url')";
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(['status' => 'success', 'message' => 'Product added successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        break;

    case 'edit':
        $id = (int)$_POST['id'];
        $name = mysqli_real_escape_with_mysqli($conn, $_POST['name']);
        $description = mysqli_real_escape_with_mysqli($conn, $_POST['description']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock_quantity'];
        $category = mysqli_real_escape_with_mysqli($conn, $_POST['category']);
        $image_url = mysqli_real_escape_with_mysqli($conn, $_POST['image_url'] ?? '');

        // Handle File Upload
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/images/shop/';
            
            // Create directory if not exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_tmp = $_FILES['product_image']['tmp_name'];
            $file_name = time() . '_' . basename($_FILES['product_image']['name']);
            $target_file = $upload_dir . $file_name;

            // Simple validation
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['product_image']['type'];

            if (in_array($file_type, $allowed_types)) {
                if (move_uploaded_file($file_tmp, $target_file)) {
                    $image_url = $file_name;
                }
            }
        }
        
        $query = "UPDATE products SET 
                  name = '$name', 
                  description = '$description', 
                  price = $price, 
                  stock_quantity = $stock, 
                  category = '$category', 
                  image_url = '$image_url' 
                  WHERE id = $id";
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(['status' => 'success', 'message' => 'Product updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        break;

    case 'delete':
        $id = (int)$_POST['id'];
        $query = "DELETE FROM products WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            echo json_encode(['status' => 'success', 'message' => 'Product deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

function mysqli_real_escape_with_mysqli($conn, $str) {
    return mysqli_real_escape_string($conn, $str);
}
?>