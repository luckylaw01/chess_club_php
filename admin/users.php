<?php
session_start();
require_once "../includes/db_connect.php";

$pageTitle = "User Management";
include "admin_header.php";
?>

<div class="flex flex-col gap-8">
    <!-- Header/Filter Bar -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="relative w-full md:w-96">
            <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="userSearch" placeholder="Search by name, email or username..." 
                class="w-full pl-12 pr-6 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all">
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <button onclick="openAddModal()" class="px-6 py-3 bg-brandGreen text-white font-bold rounded-2xl hover:bg-brandGreen shadow-lg shadow-brandGreen/20 active:scale-95 transition-all text-sm uppercase tracking-widest flex items-center gap-2">
                <i class="fas fa-plus"></i>
                <span>Add Member</span>
            </button>
            <select id="roleFilter" class="bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-3 text-sm font-bold focus:ring-2 focus:ring-brandGreen outline-none">
                <option value="">All Roles</option>
                <option value="user">User</option>
                <option value="coach">Coach</option>
                <option value="admin">Admin</option>
            </select>
            <button onclick="loadUsers()" class="p-3 bg-slate-100 dark:bg-slate-800 rounded-2xl hover:text-brandGreen transition-colors">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <!-- Users Table Container -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-xs font-bold uppercase tracking-widest text-slate-400">
                        <th class="px-8 py-5">Full Name & Username</th>
                        <th class="px-8 py-5">Contact Info</th>
                        <th class="px-8 py-5">Role</th>
                        <th class="px-8 py-5">Elo Rating</th>
                        <th class="px-8 py-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="userTableBody" class="divide-y divide-slate-100 dark:divide-slate-800">
                    <!-- Loaded via AJAX -->
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center">
                            <div class="flex flex-col items-center gap-4 text-slate-400">
                                <i class="fas fa-circle-notch fa-spin text-3xl"></i>
                                <p class="font-bold uppercase tracking-widest text-xs">Loading members...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="userModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[40px] shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden animate-slide-up">
        <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <h3 class="text-xl font-black uppercase tracking-tight" id="modalTitle">Edit Member</h3>
            <button onclick="closeModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 hover:text-red-500 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editUserForm" class="p-8 space-y-6">
            <input type="hidden" name="id" id="edit_id">
            
            <div id="addOnlyFields" class="space-y-6 hidden">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Username</label>
                    <input type="text" name="username" id="edit_username"
                        class="w-full px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Email Address</label>
                    <input type="email" name="email" id="edit_email"
                        class="w-full px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">First Name</label>
                    <input type="text" name="first_name" id="edit_first_name" required
                        class="w-full px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Last Name</label>
                    <input type="text" name="last_name" id="edit_last_name" required
                        class="w-full px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Role</label>
                    <select name="role" id="edit_role" required
                        class="w-full px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all font-bold appearance-none">
                        <option value="user">User</option>
                        <option value="coach">Coach</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Elo Rating</label>
                    <input type="number" name="elo_rating" id="edit_elo_rating" required
                        class="w-full px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1" id="passLabel">Password (Leave empty to keep)</label>
                <input type="password" name="password" id="edit_password" placeholder="••••••••"
                    class="w-full px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all">
            </div>

            <div class="pt-4 flex gap-3">
                <button type="submit" id="submitBtn" class="flex-grow py-4 bg-brandGreen text-white font-bold rounded-2xl hover:bg-brandGreen shadow-lg shadow-brandGreen/20 active:scale-95 transition-all uppercase text-[11px] tracking-widest">
                    Save Changes
                </button>
                <button type="button" id="deleteBtn" onclick="deleteUser()" class="w-14 h-14 bg-red-50 text-red-500 dark:bg-red-900/10 dark:text-red-400 flex items-center justify-center rounded-2xl hover:bg-red-500 hover:text-white transition-all active:scale-95">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    async function loadUsers() {
        const query = document.getElementById('userSearch').value;
        const role = document.getElementById('roleFilter').value;
        const tbody = document.getElementById('userTableBody');
        
        try {
            const response = await fetch(`admin_users_ajax.php?action=list&search=${encodeURIComponent(query)}&role=${role}`);
            const data = await response.json();
            
            if (data.status === 'success') {
                tbody.innerHTML = data.users.length ? data.users.map(user => `
                    <tr class="text-sm hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-8 py-5">
                            <p class="font-bold text-slate-900 dark:text-white">${user.first_name || ''} ${user.last_name || ''}</p>
                            <p class="text-xs text-slate-400 font-medium">@${user.username}</p>
                        </td>
                        <td class="px-8 py-5">
                            <p class="font-medium truncate max-w-[200px]">${user.email}</p>
                        </td>
                        <td class="px-8 py-5">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest ${
                                user.role === 'admin' ? 'bg-red-100 text-red-600 dark:bg-red-900/30' : 
                                user.role === 'coach' ? 'bg-amber-100 text-amber-600 dark:bg-amber-900/30' : 
                                'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30'
                            }">${user.role}</span>
                        </td>
                        <td class="px-8 py-5">
                            <span class="font-black text-brandGreen">${user.elo_rating}</span>
                        </td>
                        <td class="px-8 py-5 text-right">
                            <button onclick='openEditModal(${JSON.stringify(user)})' class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-brandGreen hover:text-white transition-all shadow-sm">
                                <i class="fas fa-cog text-xs"></i>
                            </button>
                        </td>
                    </tr>
                `).join('') : `<tr><td colspan="5" class="px-8 py-10 text-center text-slate-400 italic">No users found.</td></tr>`;
            }
        } catch (error) {
            console.error('Fetch error:', error);
        }
    }

    function openEditModal(user) {
        document.getElementById('edit_id').value = user.id;
        document.getElementById('edit_first_name').value = user.first_name || '';
        document.getElementById('edit_last_name').value = user.last_name || '';
        document.getElementById('edit_role').value = user.role;
        document.getElementById('edit_elo_rating').value = user.elo_rating;
        document.getElementById('edit_password').value = '';
        document.getElementById('edit_password').placeholder = '••••••••';
        
        document.getElementById('modalTitle').innerText = 'Edit Member';
        document.getElementById('passLabel').innerText = 'Password (Leave empty to keep)';
        document.getElementById('addOnlyFields').classList.add('hidden');
        document.getElementById('deleteBtn').classList.remove('hidden');
        document.getElementById('submitBtn').innerText = 'Save Changes';
        
        document.getElementById('userModal').classList.remove('hidden');
        document.getElementById('userModal').classList.add('flex');
    }

    function openAddModal() {
        document.getElementById('editUserForm').reset();
        document.getElementById('edit_id').value = '';
        
        document.getElementById('modalTitle').innerText = 'Add New Member';
        document.getElementById('passLabel').innerText = 'Password';
        document.getElementById('edit_password').placeholder = 'Enter password';
        document.getElementById('edit_password').required = true;
        
        document.getElementById('addOnlyFields').classList.remove('hidden');
        document.getElementById('deleteBtn').classList.add('hidden');
        document.getElementById('submitBtn').innerText = 'Create Member';
        
        document.getElementById('userModal').classList.remove('hidden');
        document.getElementById('userModal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('userModal').classList.add('hidden');
        document.getElementById('userModal').classList.remove('flex');
        document.getElementById('edit_password').required = false;
    }

    async function deleteUser() {
        const id = document.getElementById('edit_id').value;
        if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;
        
        const response = await fetch('admin_users_ajax.php?action=delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        });
        const res = await response.json();
        if (res.status === 'success') {
            closeModal();
            loadUsers();
        } else {
            alert(res.message);
        }
    }

    document.getElementById('editUserForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('edit_id').value;
        const action = id ? 'update' : 'create';
        
        const formData = new FormData(e.target);
        const searchParams = new URLSearchParams(formData);
        
        const response = await fetch(`admin_users_ajax.php?action=${action}`, {
            method: 'POST',
            body: searchParams
        });
        const res = await response.json();
        if (res.status === 'success') {
            closeModal();
            loadUsers();
        } else {
            alert(res.message);
        }
    });

    // Event listeners for search and filters
    document.getElementById('userSearch').addEventListener('input', loadUsers);
    document.getElementById('roleFilter').addEventListener('change', loadUsers);

    // Initial load
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const editId = urlParams.get('edit_id');
        
        if (editId) {
            // Fetch single user and open modal
            fetch(`admin_users_ajax.php?action=list&edit_id=${editId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success' && data.users.length > 0) {
                        openEditModal(data.users[0]);
                        // Remove param from URL without reload
                        window.history.replaceState({}, document.title, window.location.pathname);
                    }
                });
        }
        loadUsers();
    });
</script>

<?php include "admin_footer.php"; ?>