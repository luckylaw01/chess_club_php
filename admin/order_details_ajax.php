<?php
session_start();
include "../includes/db_connect.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Fetch Items
    $sql = "SELECT oi.*, p.name as product_name, p.image_url 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = $id";
    $result = $conn->query($sql);
    
    // Fetch Order Summary
    $orderSql = "SELECT o.*, u.full_name, u.email 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.id 
                 WHERE o.id = $id";
    $orderRes = $conn->query($orderSql);
    
    if (!$orderRes) {
        die('<p class="text-center py-10 font-bold text-red-500">Database error: ' . htmlspecialchars($conn->error) . '</p>');
    }
    
    $order = $orderRes->fetch_assoc();

    if ($order) {
        echo '<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">';
        echo '  <div>';
        echo '    <h4 class="text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Customer Info</h4>';
        echo '    <p class="text-sm font-black text-slate-900 dark:text-white uppercase leading-relaxed">' . htmlspecialchars($order['full_name']) . '</p>';
        echo '    <p class="text-xs text-slate-500 font-bold leading-normal">' . htmlspecialchars($order['email']) . '</p>';
        echo '  </div>';
        echo '  <div>';
        echo '    <h4 class="text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Order Summary</h4>';
        echo '    <p class="text-sm font-black text-slate-900 dark:text-white uppercase leading-relaxed">Ordered: ' . date('M d, Y H:i', strtotime($order['order_date'])) . '</p>';
        echo '    <p class="text-sm font-black text-brandGreen uppercase leading-relaxed">Total Amount: KES ' . number_format($order['total_amount'], 2) . '</p>';
        echo '  </div>';
        echo '</div>';
        
        echo '<h4 class="text-[10px] font-black uppercase text-slate-400 mb-4 tracking-widest">Ordered Items</h4>';
        echo '<div class="space-y-4">';
        
        while($item = $result->fetch_assoc()) {
            echo '  <div class="flex items-center gap-6 p-6 rounded-3xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">';
            echo '    <div class="w-16 h-16 rounded-2xl bg-white p-2 border border-slate-100 dark:border-slate-700 flex items-center justify-center shrink-0">';
            echo '      <img src="../assets/images/shop/' . htmlspecialchars($item['image_url']) . '" alt="" class="max-w-full max-h-full object-contain">';
            echo '    </div>';
            echo '    <div class="flex-grow">';
            echo '      <h5 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">' . htmlspecialchars($item['product_name']) . '</h5>';
            echo '      <div class="flex items-center gap-4 mt-1">';
            echo '        <span class="text-[10px] font-bold text-slate-500 uppercase">Qty: ' . $item['quantity'] . '</span>';
            echo '        <span class="text-[10px] font-bold text-slate-500 uppercase">Price: KES ' . number_format($item['price_at_time'], 0) . '</span>';
            echo '      </div>';
            echo '    </div>';
            echo '    <div class="text-right">';
            echo '      <span class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">KES ' . number_format($item['price_at_time'] * $item['quantity'], 0) . '</span>';
            echo '    </div>';
            echo '  </div>';
        }
        
        echo '</div>';
    } else {
        echo '<p class="text-center py-10 font-bold text-red-500">Order not found.</p>';
    }
}
?>
