<?php
session_start();
require_once "../includes/db_connect.php";

$pageTitle = "Product Management";
include "admin_header.php";
?>

<div class="flex flex-col gap-8">
    <!-- Header/Filter Bar -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="relative w-full md:w-96">
            <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="productSearch" placeholder="Search products..." 
                class="w-full pl-12 pr-6 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all">
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <button onclick="openAddModal()" class="px-6 py-3 bg-brandGreen text-white font-bold rounded-2xl hover:bg-brandGreen shadow-lg shadow-brandGreen/20 active:scale-95 transition-all text-sm uppercase tracking-widest flex items-center gap-2">
                <i class="fas fa-plus"></i>
                <span>Add Product</span>
            </button>
            <select id="categoryFilter" class="bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-3 text-sm font-bold focus:ring-2 focus:ring-brandGreen outline-none">
                <option value="">All Categories</option>
                <option value="Boards">Boards</option>
                <option value="Pieces">Pieces</option>
                <option value="Sets">Sets</option>
                <option value="Apparel">Apparel</option>
                <option value="Accessories">Accessories</option>
            </select>
            <button onclick="loadProducts()" class="p-3 bg-slate-100 dark:bg-slate-800 rounded-2xl hover:text-brandGreen transition-colors">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <!-- Products Table Container -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-xs font-bold uppercase tracking-widest text-slate-400">
                        <th class="px-8 py-5">Product Info</th>
                        <th class="px-8 py-5">Category</th>
                        <th class="px-8 py-5">Price (KES)</th>
                        <th class="px-8 py-5">Stock</th>
                        <th class="px-8 py-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="productTableBody" class="divide-y divide-slate-100 dark:divide-slate-800">
                    <!-- Loaded via AJAX -->
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center">
                            <div class="flex flex-col items-center gap-4 text-slate-400">
                                <i class="fas fa-circle-notch fa-spin text-3xl"></i>
                                <p class="font-bold uppercase tracking-widest text-xs">Loading products...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div id="productModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[40px] shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden animate-slide-up">
        <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <h3 class="text-xl font-black uppercase tracking-tight" id="modalTitle">Edit Product</h3>
            <button onclick="closeModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 hover:text-red-500 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="productForm" class="p-8 space-y-6">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="productId">
            
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Product Name</label>
                <input type="text" name="name" id="productName" required
                    class="w-full px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all">
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Description</label>
                <textarea name="description" id="productDescription" rows="3"
                    class="w-full px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Price (KES)</label>
                    <input type="number" step="0.01" name="price" id="productPrice" required
                        class="w-full px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Stock Quantity</label>
                    <input type="number" name="stock_quantity" id="productStock" required
                        class="w-full px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Category</label>
                    <select name="category" id="productCategory" required
                        class="w-full px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all">
                        <option value="Boards">Boards</option>
                        <option value="Pieces">Pieces</option>
                        <option value="Sets">Sets</option>
                        <option value="Apparel">Apparel</option>
                        <option value="Accessories">Accessories</option>
                    </select>
                </div>
                <div class="w-full md:w-auto">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Direct Upload / Filename</label>
                    <div class="flex flex-col gap-3">
                        <div class="relative group/input">
                            <i class="fas fa-image absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within/input:text-brandGreen transition-colors"></i>
                            <input type="text" name="image_url" id="productImage" placeholder="Or enter filename e.g. chess_set.png"
                                class="w-full pl-12 pr-6 py-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all text-sm font-bold">
                        </div>
                        <div class="relative group/upload">
                            <input type="file" name="product_image" id="imageUpload" accept="image/*"
                                class="w-full px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all text-xs file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-brandGreen file:text-white hover:file:bg-brandGreen/80 cursor-pointer">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800">
                <p class="text-[10px] font-black uppercase tracking-widest text-brandGreen mb-3 flex items-center gap-2">
                    <i class="fas fa-info-circle"></i>
                    Preparation
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    You can either <span class="font-bold">upload a file directly</span> or enter the <span class="font-bold">exact filename</span> if it's already in the <span class="font-mono text-brandGreen">/assets/images/shop/</span> folder.
                </p>
            </div>

            <button type="submit" class="w-full py-5 bg-brandGreen text-white font-black uppercase tracking-widest rounded-2xl hover:bg-brandGreen shadow-lg shadow-brandGreen/20 active:scale-[0.98] transition-all flex items-center justify-center gap-3">
                <i class="fas fa-rocket"></i>
                <span id="submitBtnText" class="text-sm">Launch Product</span>
            </button>
        </form>
    </div>
</div>

<script>
function loadProducts() {
    const search = document.getElementById('productSearch').value;
    const category = document.getElementById('categoryFilter').value;
    
    fetch(`product_actions_ajax.php?action=list&search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('productTableBody');
            if (data.status === 'success') {
                if (data.products.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="px-8 py-20 text-center text-slate-400">No products found</td></tr>';
                    return;
                }
                tbody.innerHTML = data.products.map(p => `
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex-shrink-0 overflow-hidden border border-slate-200 dark:border-slate-700">
                                    <img src="../assets/images/shop/${p.image_url}" alt="" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white truncate max-w-[200px]">${p.name}</div>
                                    <div class="text-[10px] text-slate-500 line-clamp-1">${p.description}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-full text-[10px] font-black uppercase tracking-widest text-slate-500">
                                ${p.category}
                            </span>
                        </td>
                        <td class="px-8 py-5">
                            <span class="font-bold text-brandGreen">KES ${parseFloat(p.price).toFixed(2)}</span>
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-2">
                                <span class="font-bold ${parseInt(p.stock_quantity) < 10 ? 'text-red-500' : 'text-slate-900 dark:text-white'}">${p.stock_quantity}</span>
                                ${parseInt(p.stock_quantity) < 10 ? '<i class="fas fa-exclamation-triangle text-red-500 text-[10px]"></i>' : ''}
                            </div>
                        </td>
                        <td class="px-8 py-5 text-right">
                            <div class="flex justify-end gap-2">
                                <button onclick='openEditModal(${JSON.stringify(p).replace(/'/g, "&apos;")})' class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-brandGreen hover:text-white transition-all">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>
                                <button onclick="deleteProduct(${p.id})" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-red-500 hover:text-white transition-all">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }
        });
}

function openAddModal() {
    document.getElementById('modalTitle').innerText = 'Add Product';
    document.getElementById('formAction').value = 'add';
    document.getElementById('productForm').reset();
    document.getElementById('productModal').classList.remove('hidden');
    document.getElementById('productModal').classList.add('flex');
}

function openEditModal(product) {
    document.getElementById('modalTitle').innerText = 'Edit Product';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('productId').value = product.id;
    document.getElementById('productName').value = product.name;
    document.getElementById('productDescription').value = product.description;
    document.getElementById('productPrice').value = product.price;
    document.getElementById('productStock').value = product.stock_quantity;
    document.getElementById('productCategory').value = product.category;
    document.getElementById('productImage').value = product.image_url;
    document.getElementById('productModal').classList.remove('hidden');
    document.getElementById('productModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('productModal').classList.add('hidden');
    document.getElementById('productModal').classList.remove('flex');
}

function deleteProduct(id) {
    if (confirm('Are you sure you want to delete this product?')) {
        fetch('product_actions_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') loadProducts();
            else alert(data.message);
        });
    }
}

document.getElementById('productForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('product_actions_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            closeModal();
            loadProducts();
        } else {
            alert(data.message);
        }
    });
});

document.getElementById('productSearch').addEventListener('input', loadProducts);
document.getElementById('categoryFilter').addEventListener('change', loadProducts);

// Initial load
loadProducts();
</script>

<?php include "admin_footer.php"; ?>