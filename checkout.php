<?php
session_start();
require_once "includes/db_connect.php";

// Redirect if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: shop.php");
    exit();
}

// Redirect if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header("Location: login.php");
    exit();
}

$pageTitle = 'CHECKOUT';
include 'includes/header.php';

$cart = $_SESSION['cart'];
$cartItems = [];
$total = 0;

$ids = implode(',', array_keys($cart));
$query = "SELECT * FROM products WHERE id IN ($ids)";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $row['quantity'] = $cart[$row['id']];
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $cartItems[] = $row;
    $total += $row['subtotal'];
}

$error = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $user_id = $_SESSION['id'];
    
    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // 1. Create Order
        $order_query = "INSERT INTO orders (user_id, total_amount, status) VALUES ($user_id, $total, 'pending')";
        mysqli_query($conn, $order_query);
        $order_id = mysqli_insert_id($conn);

        // 2. Create Order Items and Update Stock
        foreach ($cartItems as $item) {
            $product_id = $item['id'];
            $qty = $item['quantity'];
            $price = $item['price'];

            // Insert item
            $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price_at_time) 
                          VALUES ($order_id, $product_id, $qty, $price)";
            mysqli_query($conn, $item_query);

            // Update Stock
            $stock_query = "UPDATE products SET stock_quantity = stock_quantity - $qty WHERE id = $product_id";
            mysqli_query($conn, $stock_query);
        }

        // 3. (Optional) Simulating M-Pesa Payment Entry
        $ref = strtoupper(substr(md5(time()), 0, 10));
        $pay_query = "INSERT INTO payments (user_id, amount, phone_number, transaction_reference, status) 
                      VALUES ($user_id, $total, '$phone', '$ref', 'pending')";
        mysqli_query($conn, $pay_query);

        // Commit transaction
        mysqli_commit($conn);
        
        // Clear cart
        $_SESSION['cart'] = [];
        $success = true;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Payment processing failed. Please try again.";
    }
}
?>

<div class="pt-32 pb-24 px-6 min-h-screen">
    <div class="max-w-4xl mx-auto">
        <?php if ($success): ?>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[40px] p-20 text-center animate-slide-up">
                <div class="w-24 h-24 bg-brandGreen/20 rounded-full flex items-center justify-center mx-auto mb-8 animate-bounce">
                    <i class="fas fa-check text-brandGreen text-4xl"></i>
                </div>
                <h2 class="text-3xl font-black mb-4 uppercase tracking-tight">Order Placed!</h2>
                <p class="text-slate-500 max-w-sm mx-auto mb-10">Your order has been received. Please complete the M-Pesa prompt on your phone.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="index.php" class="px-10 py-5 bg-slate-900 text-white font-black uppercase tracking-widest rounded-3xl hover:bg-slate-800 transition-all">Home</a>
                    <a href="shop.php" class="px-10 py-5 bg-brandGreen text-white font-black uppercase tracking-widest rounded-3xl hover:bg-brandNeonGreen transition-all">Shop More</a>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <div class="space-y-8 animate-slide-up">
                    <h1 class="text-4xl font-black uppercase tracking-tight">Complete <span class="text-brandGreen">Order</span></h1>
                    
                    <form method="POST" class="space-y-6">
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-8 rounded-[40px] shadow-lg">
                            <h3 class="text-xl font-bold mb-6 uppercase tracking-widest text-slate-400 text-xs">Payment Method: M-Pesa</h3>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">M-Pesa Phone Number</label>
                                    <div class="relative">
                                        <i class="fas fa-phone absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        <input type="text" name="phone" required placeholder="0712345678"
                                            class="w-full pl-12 pr-6 py-4 rounded-3xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all font-bold">
                                    </div>
                                    <p class="text-[10px] text-slate-500 mt-3 ml-1 font-bold">You will receive an STK push on this number to authorize KES <?php echo number_format($total, 2); ?></p>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-6 bg-brandGreen text-white font-black uppercase tracking-widest rounded-[32px] hover:bg-brandNeonGreen hover:scale-[1.02] active:scale-95 transition-all shadow-2xl shadow-brandGreen/30 flex items-center justify-center gap-4">
                            Pay Now
                            <i class="fas fa-lock text-xs opacity-50"></i>
                        </button>
                    </form>
                </div>

                <div class="bg-slate-900 text-white rounded-[40px] p-10 h-fit animate-slide-up delay-200">
                    <h3 class="text-xl font-black uppercase tracking-tight mb-8">Summary</h3>
                    <div class="space-y-6 max-h-[300px] overflow-y-auto pr-4 mb-8 custom-scroll">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="flex justify-between items-center bg-white/5 p-4 rounded-2xl border border-white/5">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-white/10 rounded-xl overflow-hidden flex-shrink-0">
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <div class="font-bold text-sm truncate w-32"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <div class="text-[10px] text-white/50 uppercase font-black tracking-widest">Qty: <?php echo $item['quantity']; ?></div>
                                    </div>
                                </div>
                                <div class="font-black text-brandGreen">KES <?php echo number_format($item['subtotal'], 2); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="space-y-4 border-t border-white/10 pt-8">
                        <div class="flex justify-between text-xs text-white/50 uppercase font-black tracking-widest">
                            <span>Subtotal</span>
                            <span>KES <?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="flex justify-between text-xs text-white/50 uppercase font-black tracking-widest">
                            <span>Shipping</span>
                            <span class="text-brandGreen">Free</span>
                        </div>
                        <div class="flex justify-between items-end pt-4">
                            <span class="text-xl font-black uppercase tracking-tight">Total</span>
                            <span class="text-4xl font-black text-brandGreen">KES <?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.custom-scroll::-webkit-scrollbar { width: 4px; }
.custom-scroll::-webkit-scrollbar-track { background: transparent; }
.custom-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
</style>

<?php include 'includes/footer.php'; ?>