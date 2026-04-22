<?php
session_start();
require_once 'includes/db_connect.php';
$pageTitle = 'SHOP';
include 'includes/header.php';

// Fetch products from database
$query = "SELECT * FROM products ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

    <!-- Header Section -->
    <header class="pt-32 pb-12 px-6 text-center animate-slide-up">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-5xl lg:text-6xl font-extrabold mb-4 tracking-tight">The Pawn's <span
                    class="text-brandGreen">Collection</span></h1>
            <p class="text-slate-500 dark:text-slate-400 max-w-2xl mx-auto">Premium chess equipment and apparel designed
                for masters of strategy.</p>
        </div>
    </header>

    <!-- Product Grid -->
    <section class="pb-24 px-6">
        <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">

            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($product = mysqli_fetch_assoc($result)): ?>
                    <!-- Product Card -->
                    <div
                        class="group bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-[32px] overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                        <div class="aspect-square overflow-hidden relative bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                            <?php 
                            $imgPath = 'assets/images/shop/' . $product['image_url'];
                            // Use the column value directly as shown in the DB
                            $imgSrc = $imgPath;
                            ?>
                            <img src="<?php echo htmlspecialchars($imgSrc); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 onerror="this.src='https://placehold.co/600x600/1e293b/FFFFFF/png?text=Product+Image'; this.onerror=null;"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                            <?php if ($product['stock_quantity'] <= 5): ?>
                                <div
                                    class="absolute top-4 right-4 bg-red-600 text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest">
                                    Low Stock</div>
                            <?php elseif (isset($product['category']) && $product['category'] === 'New'): ?>
                                <div
                                    class="absolute top-4 right-4 bg-slate-900 text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest">
                                    New Arrival</div>
                            <?php endif; ?>
                        </div>
                        <div class="p-6">
                            <h3 class="font-bold text-lg mb-1"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-slate-500 text-xs mb-4"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-black text-brandGreen">KES <?php echo number_format($product['price'], 2); ?></span>
                                <button onclick="addToCart(<?php echo $product['id']; ?>)"
                                    class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-brandGreen hover:text-white transition-colors group/cart relative overflow-hidden">
                                    <i class="fas fa-shopping-cart text-sm group-hover/cart:scale-110 transition-transform"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <p class="text-slate-500">No products found in the collection.</p>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-8 right-8 z-[100] translate-y-20 opacity-0 transition-all duration-500 pointer-events-none">
        <div class="bg-slate-900 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-4 border border-slate-800">
            <div class="w-8 h-8 rounded-full bg-brandGreen/20 flex items-center justify-center">
                <i class="fas fa-check text-brandGreen text-sm"></i>
            </div>
            <p id="toast-message" class="text-sm font-bold uppercase tracking-widest"></p>
        </div>
    </div>

    <script>
    function addToCart(productId) {
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('product_id', productId);
        formData.append('quantity', 1);

        fetch('cart_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showToast(data.message);
                updateCartCount(data.cartCount);
            } else {
                showToast(data.message, 'error');
            }
        });
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const msg = document.getElementById('toast-message');
        msg.innerText = message;
        
        toast.classList.remove('translate-y-20', 'opacity-0');
        setTimeout(() => {
            toast.classList.add('translate-y-20', 'opacity-0');
        }, 3000);
    }

    function updateCartCount(count) {
        const cartCount = document.getElementById('cart-count');
        cartCount.innerText = count;
        if (count > 0) {
            cartCount.classList.remove('hidden');
        } else {
            cartCount.classList.add('hidden');
        }
    }
    </script>

        </div>
    </section>

<?php include 'includes/footer.php'; ?>
</body>

</html>

