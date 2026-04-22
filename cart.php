<?php
session_start();
require_once "includes/db_connect.php";
$pageTitle = 'SHOPPING CART';
include 'includes/header.php';

$cart = $_SESSION['cart'] ?? [];
$cartItems = [];
$total = 0;

if (!empty($cart)) {
    $ids = implode(',', array_keys($cart));
    $query = "SELECT * FROM products WHERE id IN ($ids)";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $row['quantity'] = $cart[$row['id']];
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $cartItems[] = $row;
        $total += $row['subtotal'];
    }
}
?>

<div class="pt-32 pb-24 px-6 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-4xl lg:text-5xl font-black mb-12 tracking-tight uppercase">My <span class="text-brandGreen">Cart</span></h1>

        <?php if (empty($cartItems)): ?>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[40px] p-20 text-center animate-slide-up">
                <div class="w-24 h-24 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-8 shadow-inner">
                    <i class="fas fa-shopping-basket text-slate-300 text-4xl"></i>
                </div>
                <h2 class="text-2xl font-bold mb-4">Your cart is empty</h2>
                <p class="text-slate-500 max-w-sm mx-auto mb-10">Looks like you haven't added any premium chess pieces yet.</p>
                <a href="shop.php" class="inline-flex items-center gap-3 px-10 py-5 bg-brandGreen text-white font-black uppercase tracking-widest rounded-3xl hover:bg-brandNeonGreen hover:scale-105 active:scale-95 transition-all shadow-xl shadow-brandGreen/20">
                    <i class="fas fa-arrow-left"></i>
                    Back to Shop
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-start">
                <div class="lg:col-span-2 space-y-6">
                    <?php foreach ($cartItems as $item): ?>
                        <div id="cart-item-<?php echo $item['id']; ?>" class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 p-6 rounded-[32px] flex flex-col sm:flex-row gap-6 hover:shadow-xl transition-all duration-500 animate-slide-up">
                            <div class="w-full sm:w-32 h-32 bg-slate-100 dark:bg-slate-800 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 flex-shrink-0">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 flex flex-col justify-between py-2">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-black text-xl mb-1 uppercase tracking-tight"><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <p class="text-slate-500 text-xs uppercase tracking-widest font-bold"><?php echo htmlspecialchars($item['category']); ?></p>
                                    </div>
                                    <button onclick="removeFromCart(<?php echo $item['id']; ?>)" class="text-slate-400 hover:text-red-500 transition-colors w-10 h-10 flex items-center justify-center bg-slate-50 dark:bg-slate-800 rounded-xl">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                                <div class="flex justify-between items-end mt-4">
                                    <div class="flex items-center gap-4 bg-slate-100 dark:bg-slate-800 p-1 rounded-2xl border border-slate-200 dark:border-slate-700">
                                        <button onclick="updateQty(<?php echo $item['id']; ?>, -1)" class="w-10 h-10 flex items-center justify-center text-slate-500 hover:text-brandGreen hover:bg-white dark:hover:bg-slate-700 rounded-xl transition-all"><i class="fas fa-minus text-xs"></i></button>
                                        <span id="qty-<?php echo $item['id']; ?>" class="w-8 text-center font-black text-lg"><?php echo $item['quantity']; ?></span>
                                        <button onclick="updateQty(<?php echo $item['id']; ?>, 1)" class="w-10 h-10 flex items-center justify-center text-slate-500 hover:text-brandGreen hover:bg-white dark:hover:bg-slate-700 rounded-xl transition-all"><i class="fas fa-plus text-xs"></i></button>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs uppercase font-bold text-slate-400 mb-1">Subtotal</p>
                                        <span class="text-2xl font-black text-brandGreen">KES <?php echo number_format($item['subtotal'], 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[40px] p-10 flex flex-col gap-8 shadow-xl animate-slide-up delay-200 sticky top-32">
                    <h3 class="text-2xl font-black uppercase tracking-tight">Order Summary</h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-bold uppercase tracking-widest">Subtotal</span>
                            <span id="summary-subtotal" class="font-black">KES <?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-bold uppercase tracking-widest">Shipping</span>
                            <span class="font-black text-brandGreen uppercase">Free</span>
                        </div>
                        <div class="h-px bg-slate-100 dark:bg-slate-800 my-4"></div>
                        <div class="flex justify-between items-end">
                            <span class="text-xl font-black uppercase tracking-tighter">Total Price</span>
                            <span id="summary-total" class="text-4xl font-black text-brandGreen">KES <?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>

                    <a href="checkout.php" class="w-full py-6 bg-slate-900 dark:bg-brandGreen text-white font-black uppercase tracking-widest rounded-3xl text-center hover:scale-[1.02] active:scale-95 transition-all shadow-xl shadow-brandGreen/20">
                        Proceed to Checkout
                        <i class="fas fa-arrow-right ml-2 text-xs"></i>
                    </a>
                    
                    <p class="text-[10px] text-center text-slate-400 font-bold uppercase tracking-[0.2em] px-4">Secure checkout powered by M-Pesa & Stripe</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function updateQty(id, delta) {
    const qtyElement = document.getElementById('qty-' + id);
    let newQty = parseInt(qtyElement.innerText) + delta;
    if (newQty < 1) return;

    fetch('cart_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update&product_id=${id}&quantity=${newQty}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            location.reload();
        } else {
            alert(data.message);
        }
    });
}

function removeFromCart(id) {
    if (confirm('Remove item from cart?')) {
        fetch('cart_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=remove&product_id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            }
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>