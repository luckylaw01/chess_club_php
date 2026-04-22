<?php
session_start();
$pageTitle = "Order Management";
include "admin_header.php";
include "../includes/db_connect.php";

// Handle Status Updates
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    if ($stmt->execute()) {
        $message = "Order #$order_id updated to $new_status.";
    } else {
        $message = "Error updating order: " . $conn->error;
    }
    $stmt->close();
}

// Fetch Orders with User Details
$sql = "SELECT o.*, u.full_name, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.order_date DESC";
$orders = $conn->query($sql);
?>

<div class="p-6 md:p-10 max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Order <span class="text-brandGreen">Management</span></h1>
            <p class="text-slate-500 font-medium mt-1">Track and manage customer purchases.</p>
        </div>
    </div>

    <?php if($message): ?>
        <div class="mb-8 p-4 bg-brandGreen/10 border border-brandGreen/20 text-brandGreen rounded-2xl font-bold text-sm">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-slate-900 rounded-[32px] border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Order ID</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Customer</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Date</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Total Amount</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php if($orders->num_rows > 0): ?>
                        <?php while($row = $orders->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">#<?php echo $row['id']; ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-slate-900 dark:text-white uppercase"><?php echo htmlspecialchars($row['full_name']); ?></span>
                                        <span class="text-[10px] text-slate-500 font-bold"><?php echo htmlspecialchars($row['email']); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-500"><?php echo date('M d, Y H:i', strtotime($row['order_date'])); ?></td>
                                <td class="px-6 py-4 text-sm font-black text-brandGreen">KES <?php echo number_format($row['total_amount'], 2); ?></td>
                                <td class="px-6 py-4">
                                    <?php 
                                    $statusClasses = [
                                        'pending' => 'bg-amber-100 text-amber-600',
                                        'paid' => 'bg-brandGreen/10 text-brandGreen',
                                        'shipped' => 'bg-blue-100 text-blue-600',
                                        'delivered' => 'bg-slate-100 text-slate-500',
                                        'cancelled' => 'bg-red-100 text-red-600'
                                    ];
                                    $class = $statusClasses[$row['status']] ?? 'bg-slate-100';
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest <?php echo $class; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <button onclick="viewDetails(<?php echo $row['id']; ?>)" class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-[10px] font-black uppercase tracking-widest hover:bg-brandGreen hover:text-white transition-all">Details</button>
                                        <form method="POST" class="flex gap-2">
                                            <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                            <select name="status" class="text-[10px] font-black uppercase px-2 py-1 rounded-lg bg-slate-50 dark:bg-slate-800 border-none outline-none focus:ring-1 focus:ring-brandGreen">
                                                <option value="pending" <?php echo $row['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="paid" <?php echo $row['status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                <option value="shipped" <?php echo $row['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo $row['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo $row['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" name="update_status" class="w-8 h-8 rounded-lg bg-brandGreen/10 text-brandGreen flex items-center justify-center hover:bg-brandGreen hover:text-white transition-all">
                                                <i class="fas fa-check text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500 font-bold uppercase tracking-widest">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div id="detailsModal" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-900/80 backdrop-blur-sm hidden">
    <div class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-[40px] p-10 relative shadow-2xl overflow-y-auto max-h-[90vh]">
        <button onclick="document.getElementById('detailsModal').classList.add('hidden')" class="absolute top-6 right-6 text-slate-400 hover:text-slate-900 dark:hover:text-white">
            <i class="fas fa-times text-xl"></i>
        </button>
        
        <h3 class="text-2xl font-black mb-6 uppercase tracking-tight">Order <span id="modalOrderId" class="text-brandGreen">#0</span> Details</h3>
        
        <div id="orderDetailsContent" class="space-y-6">
            <!-- Loaded via AJAX -->
            <div class="flex items-center justify-center py-10">
                <i class="fas fa-circle-notch fa-spin text-3xl text-brandGreen"></i>
            </div>
        </div>
    </div>
</div>

<script>
function viewDetails(orderId) {
    document.getElementById('modalOrderId').innerText = '#' + orderId;
    document.getElementById('detailsModal').classList.remove('hidden');
    
    fetch('order_details_ajax.php?id=' + orderId)
        .then(response => response.text())
        .then(data => {
            document.getElementById('orderDetailsContent').innerHTML = data;
        });
}
</script>

<?php include "admin_footer.php"; ?>
