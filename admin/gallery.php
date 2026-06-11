<?php
session_start();
require_once "../includes/db_connect.php";

$pageTitle = "Gallery Management";
include "admin_header.php";
?>

<div class="flex flex-col gap-8">
    <!-- Header / Action Bar -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>
            <h3 class="text-lg font-black uppercase tracking-tight text-slate-900 dark:text-white">Homepage Slideshow Gallery</h3>
            <p class="text-xs text-slate-500 mt-1">Manage the cinematic background images and captions of the landing page.</p>
        </div>
        <div>
            <button onclick="openAddModal()" class="px-6 py-3 bg-brandGreen text-white font-bold rounded-2xl hover:bg-brandGreen/90 shadow-lg shadow-brandGreen/20 active:scale-95 transition-all text-sm uppercase tracking-widest flex items-center gap-2">
                <i class="fas fa-plus"></i>
                <span>Add Photo</span>
            </button>
        </div>
    </div>

    <!-- Gallery Grid Container -->
    <div id="galleryContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <!-- Loaded via AJAX -->
        <div class="col-span-full py-20 text-center">
            <div class="flex flex-col items-center gap-4 text-slate-400">
                <i class="fas fa-circle-notch fa-spin text-3xl"></i>
                <p class="font-bold uppercase tracking-widest text-xs">Loading gallery...</p>
            </div>
        </div>
    </div>
</div>

<!-- Add Photo Modal -->
<div id="addPhotoModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[40px] shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden animate-slide-up">
        <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <h3 class="text-xl font-black uppercase tracking-tight text-slate-900 dark:text-white">Add Photo to Gallery</h3>
            <button onclick="closeAddModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 hover:text-red-500 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="addPhotoForm" class="p-8 space-y-6" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Select Image File</label>
                <input type="file" name="gallery_image" id="gallery_image" accept="image/*" required
                    class="w-full px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all text-xs file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-brandGreen file:text-white hover:file:bg-brandGreen/80 cursor-pointer">
                <p class="text-[10px] text-slate-400 mt-2 ml-1">Allowed formats: JPG, PNG, WEBP. Max size: 5MB.</p>
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Caption / Title</label>
                <input type="text" name="caption" id="addCaption" placeholder="e.g. Major Championship Moments" required
                    class="w-full px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all text-sm font-bold">
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Alternative Description (Alt Text)</label>
                <input type="text" name="alt" id="addAlt" placeholder="e.g. A chess player smiling with a trophy" required
                    class="w-full px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all text-sm font-bold">
            </div>

            <button type="submit" class="w-full py-5 bg-brandGreen text-white font-black uppercase tracking-widest rounded-2xl hover:bg-brandGreen/90 shadow-lg shadow-brandGreen/20 active:scale-[0.98] transition-all flex items-center justify-center gap-3">
                <i class="fas fa-upload"></i>
                <span class="text-sm">Upload Photo</span>
            </button>
        </form>
    </div>
</div>

<!-- Edit Photo Modal -->
<div id="editPhotoModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[40px] shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden animate-slide-up">
        <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <h3 class="text-xl font-black uppercase tracking-tight text-slate-900 dark:text-white">Edit Photo Details</h3>
            <button onclick="closeEditModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 hover:text-red-500 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editPhotoForm" class="p-8 space-y-6">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="index" id="editIndex">
            
            <div class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800/40 rounded-2xl">
                <img id="editPreview" src="" alt="Preview" class="w-24 h-16 object-cover rounded-xl border border-slate-200 dark:border-slate-700">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Current Photo File</p>
                    <p id="editFilePath" class="text-xs font-mono text-slate-600 dark:text-slate-300 break-all line-clamp-1"></p>
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Caption / Title</label>
                <input type="text" name="caption" id="editCaption" required
                    class="w-full px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all text-sm font-bold">
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Alternative Description (Alt Text)</label>
                <input type="text" name="alt" id="editAlt" required
                    class="w-full px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all text-sm font-bold">
            </div>

            <button type="submit" class="w-full py-5 bg-brandGreen text-white font-black uppercase tracking-widest rounded-2xl hover:bg-brandGreen/90 shadow-lg shadow-brandGreen/20 active:scale-[0.98] transition-all flex items-center justify-center gap-3">
                <i class="fas fa-save"></i>
                <span class="text-sm">Save Changes</span>
            </button>
        </form>
    </div>
</div>

<script>
let currentGallery = [];

function loadGallery() {
    fetch('gallery_actions_ajax.php?action=list')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('galleryContainer');
            if (data.status === 'success') {
                currentGallery = data.gallery;
                if (currentGallery.length === 0) {
                    container.innerHTML = `
                        <div class="col-span-full py-20 text-center bg-white dark:bg-slate-900 rounded-[32px] border border-slate-200 dark:border-slate-800">
                            <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">No photos in the gallery. Add some to get started!</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = currentGallery.map((item, idx) => {
                    const isUploaded = item.image.startsWith('uploads/');
                    const imageSrc = isUploaded ? `../${item.image}` : `../${item.image}`;
                    
                    return `
                        <div class="bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-md flex flex-col justify-between group">
                            <div>
                                <!-- Image Preview Wrapper -->
                                <div class="aspect-video w-full rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 relative">
                                    <img src="${imageSrc}" alt="${item.alt}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                    
                                    <!-- Index Badge -->
                                    <span class="absolute top-3 left-3 px-2.5 py-1 bg-black/60 backdrop-blur-md text-white text-[9px] font-black rounded-full">
                                        #${idx + 1}
                                    </span>
                                    
                                    <!-- Source Badge -->
                                    <span class="absolute top-3 right-3 px-2 py-0.5 ${isUploaded ? 'bg-brandGreen/20 text-brandGreen border-brandGreen/30' : 'bg-blue-500/20 text-blue-500 border-blue-500/30'} border text-[8px] font-black uppercase rounded-full">
                                        ${isUploaded ? 'Uploaded' : 'Default'}
                                    </span>
                                </div>
                                
                                <!-- Text Info -->
                                <h4 class="font-bold text-slate-900 dark:text-white mt-4 text-sm truncate" title="${item.caption}">
                                    ${item.caption}
                                </h4>
                                <p class="text-[10px] text-slate-500 mt-1 line-clamp-2" title="${item.alt}">
                                    <strong>Alt:</strong> ${item.alt}
                                </p>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex gap-2 mt-5 border-t border-slate-100 dark:border-slate-800/60 pt-4">
                                <button onclick="openEditModal(${idx})" class="flex-1 py-2 px-3 bg-slate-100 dark:bg-slate-800 hover:bg-brandGreen hover:text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5">
                                    <i class="fas fa-edit text-[10px]"></i> Edit
                                </button>
                                <button onclick="deletePhoto(${idx})" class="py-2 px-3 bg-slate-100 dark:bg-slate-800 hover:bg-red-500 hover:text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center">
                                    <i class="fas fa-trash-alt text-[10px]"></i>
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                container.innerHTML = `
                    <div class="col-span-full py-20 text-center text-red-500">
                        <i class="fas fa-exclamation-triangle text-3xl mb-3"></i>
                        <p class="font-bold uppercase tracking-widest text-xs">${data.message}</p>
                    </div>
                `;
            }
        })
        .catch(err => {
            document.getElementById('galleryContainer').innerHTML = `
                <div class="col-span-full py-20 text-center text-red-500">
                    <i class="fas fa-exclamation-triangle text-3xl mb-3"></i>
                    <p class="font-bold uppercase tracking-widest text-xs">An error occurred loading the gallery</p>
                </div>
            `;
        });
}

function openAddModal() {
    document.getElementById('addPhotoForm').reset();
    const modal = document.getElementById('addPhotoModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeAddModal() {
    const modal = document.getElementById('addPhotoModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openEditModal(index) {
    const item = currentGallery[index];
    if (!item) return;

    document.getElementById('editIndex').value = index;
    document.getElementById('editCaption').value = item.caption;
    document.getElementById('editAlt').value = item.alt;
    
    const isUploaded = item.image.startsWith('uploads/');
    document.getElementById('editPreview').src = isUploaded ? `../${item.image}` : `../${item.image}`;
    document.getElementById('editFilePath').textContent = item.image;

    const modal = document.getElementById('editPhotoModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEditModal() {
    const modal = document.getElementById('editPhotoModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function deletePhoto(index) {
    if (confirm('Are you sure you want to delete this photo from the gallery?')) {
        const formData = new URLSearchParams();
        formData.append('action', 'delete');
        formData.append('index', index);

        fetch('gallery_actions_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData.toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                loadGallery();
            } else {
                alert(data.message);
            }
        });
    }
}

// Add Photo Form Submission
document.getElementById('addPhotoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('gallery_actions_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            closeAddModal();
            loadGallery();
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        alert('An error occurred while uploading the photo.');
    });
});

// Edit Photo Form Submission
document.getElementById('editPhotoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('gallery_actions_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            closeEditModal();
            loadGallery();
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        alert('An error occurred while updating the photo details.');
    });
});

// Initial load
loadGallery();
</script>

<?php include "admin_footer.php"; ?>
