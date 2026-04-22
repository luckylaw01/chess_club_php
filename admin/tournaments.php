<?php
session_start();
$pageTitle = "Tournament Management";
include "admin_header.php";
include "../includes/db_connect.php";

// Handle Tournament Creation
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_tournament'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $event_date = $_POST['event_date'];
        $location = $_POST['location'];
        $entry_fee = $_POST['entry_fee'];
        $prize_pool = $_POST['prize_pool'];
        $status = $_POST['status'];

        $sql = "INSERT INTO tournaments (title, description, event_date, location, entry_fee, prize_pool, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssdss", $title, $description, $event_date, $location, $entry_fee, $prize_pool, $status);
            if ($stmt->execute()) {
                $message = "Tournament added successfully!";
            } else {
                $message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    // Handle Tournament Update
    if (isset($_POST['edit_tournament'])) {
        $id = (int)$_POST['tournament_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $event_date = $_POST['event_date'];
        $location = $_POST['location'];
        $entry_fee = (float)$_POST['entry_fee'];
        $prize_pool = (float)$_POST['prize_pool'];
        $status = $_POST['status'];

        $sql = "UPDATE tournaments SET title=?, description=?, event_date=?, location=?, entry_fee=?, prize_pool=?, status=? WHERE id=?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssddsi", $title, $description, $event_date, $location, $entry_fee, $prize_pool, $status, $id);
            if ($stmt->execute()) {
                $message = "Tournament updated successfully!";
            } else {
                $message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Handle Tournament Deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM tournaments WHERE id = $id");
    header("Location: tournaments.php");
    exit;
}

// Fetch all tournaments
$tournaments = $conn->query("SELECT * FROM tournaments ORDER BY event_date DESC");
?>

<div class="p-6 md:p-10 max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Tournament <span class="text-brandGreen">Management</span></h1>
            <p class="text-slate-500 font-medium mt-1">Create and manage club events.</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="px-8 py-4 bg-brandGreen text-white font-bold rounded-2xl hover:scale-105 active:scale-95 transition-all uppercase text-[11px] tracking-widest shadow-xl shadow-brandGreen/20">
            <i class="fas fa-plus mr-2"></i> Add Tournament
        </button>
    </div>

    <?php if($message): ?>
        <div class="mb-8 p-4 bg-brandGreen/10 border border-brandGreen/20 text-brandGreen rounded-2xl font-bold text-sm">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-slate-900 rounded-[32px] border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/50">
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Date</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Tournament Name</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Location</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Prize Pool</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <?php while($t = $tournaments->fetch_assoc()): ?>
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-sm font-bold text-slate-900 dark:text-white"><?php echo date('M d, Y', strtotime($t['event_date'])); ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-black text-slate-900 dark:text-white uppercase"><?php echo htmlspecialchars($t['title']); ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-slate-500"><?php echo htmlspecialchars($t['location']); ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-black text-brandGreen uppercase">KES <?php echo number_format($t['prize_pool']); ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <?php 
                            $statusClasses = [
                                'upcoming' => 'bg-brandGreen/10 text-brandGreen',
                                'ongoing' => 'bg-amber-100 text-amber-600',
                                'completed' => 'bg-slate-100 text-slate-500',
                                'cancelled' => 'bg-red-100 text-red-600'
                            ];
                            $class = $statusClasses[$t['status']] ?? 'bg-slate-100';
                            ?>
                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest <?php echo $class; ?>">
                                <?php echo $t['status']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <button 
                                    onclick='openEditModal(<?php echo json_encode($t); ?>)'
                                    class="w-8 h-8 rounded-lg bg-brandGreen/10 text-brandGreen flex items-center justify-center hover:bg-brandGreen hover:text-white transition-all">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>
                                <button 
                                    onclick="viewRegistrations(<?php echo $t['id']; ?>, '<?php echo addslashes($t['title']); ?>')"
                                    class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-500 flex items-center justify-center hover:bg-blue-500 hover:text-white transition-all">
                                    <i class="fas fa-users text-xs"></i>
                                </button>
                                <a href="?delete=<?php echo $t['id']; ?>" onclick="return confirm('Are you sure?')" class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Tournament Modal -->
<div id="addModal" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-900/80 backdrop-blur-sm hidden">
    <div class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-[40px] p-10 relative shadow-2xl overflow-y-auto max-h-[90vh]">
        <button onclick="document.getElementById('addModal').classList.add('hidden')" class="absolute top-6 right-6 text-slate-400 hover:text-slate-900 dark:hover:text-white">
            <i class="fas fa-times text-xl"></i>
        </button>
        
        <h3 class="text-2xl font-black mb-6 uppercase tracking-tight">Create <span class="text-brandGreen">Tournament</span></h3>
        
        <form method="POST" action="">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-[10px] font-black uppercase mt-4 text-slate-400 mb-2">Tournament Title</label>
                    <input type="text" name="title" required class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase mt-4 text-slate-400 mb-2">Location</label>
                    <input type="text" name="location" required class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase mt-4 text-slate-400 mb-2">Date & Time</label>
                    <input type="datetime-local" name="event_date" required class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none font-sans">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase mt-4 text-slate-400 mb-2">Prize Pool (KES)</label>
                    <input type="number" name="prize_pool" required class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none font-sans">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase mt-4 text-slate-400 mb-2">Entry Fee (KES)</label>
                    <input type="number" name="entry_fee" required class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none font-sans">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase mt-4 text-slate-400 mb-2">Status</label>
                    <select name="status" class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none appearance-none font-bold">
                        <option value="upcoming">Upcoming</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-6">
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Description</label>
                <textarea name="description" rows="4" class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none"></textarea>
            </div>

            <button type="submit" name="add_tournament" class="w-full mt-8 py-5 bg-brandGreen text-white rounded-3xl font-bold uppercase tracking-[0.2em] shadow-xl shadow-brandGreen/20 hover:scale-[1.02] active:scale-95 transition-all">
                Publish Tournament
            </button>
        </form>
    </div>
</div>

<!-- Edit Tournament Modal -->
<div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-900/80 backdrop-blur-sm hidden">
    <div class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-[40px] p-10 relative shadow-2xl overflow-y-auto max-h-[90vh]">
        <button onclick="document.getElementById('editModal').classList.add('hidden')" class="absolute top-6 right-6 text-slate-400 hover:text-slate-900 dark:hover:text-white">
            <i class="fas fa-times text-xl"></i>
        </button>
        
        <h3 class="text-2xl font-black mb-6 uppercase tracking-tight">Edit <span class="text-brandGreen">Tournament</span></h3>
        
        <form method="POST" action="">
            <input type="hidden" name="tournament_id" id="edit_id">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-[10px] font-black uppercase mt-4 text-slate-400 mb-2">Tournament Title</label>
                    <input type="text" name="title" id="edit_title" required class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase mt-4 text-slate-400 mb-2">Location</label>
                    <input type="text" name="location" id="edit_location" required class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase mt-4 text-slate-400 mb-2">Date & Time</label>
                    <input type="datetime-local" name="event_date" id="edit_event_date" required class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none font-sans">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase mt-4 text-slate-400 mb-2">Prize Pool (KES)</label>
                    <input type="number" name="prize_pool" id="edit_prize_pool" required class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none font-sans">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase mt-4 text-slate-400 mb-2">Entry Fee (KES)</label>
                    <input type="number" name="entry_fee" id="edit_entry_fee" required class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none font-sans">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase mt-4 text-slate-400 mb-2">Status</label>
                    <select name="status" id="edit_status" class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none appearance-none font-bold">
                        <option value="upcoming">Upcoming</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-6">
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Description</label>
                <textarea name="description" id="edit_description" rows="4" class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none"></textarea>
            </div>

            <button type="submit" name="edit_tournament" class="w-full mt-8 py-5 bg-brandGreen text-white rounded-3xl font-bold uppercase tracking-[0.2em] shadow-xl shadow-brandGreen/20 hover:scale-[1.02] active:scale-95 transition-all">
                Update Tournament
            </button>
        </form>
    </div>
</div>

<!-- Registrations Modal -->
<div id="regModal" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-900/80 backdrop-blur-sm hidden">
    <div class="bg-white dark:bg-slate-900 w-full max-w-4xl rounded-[40px] p-10 relative shadow-2xl overflow-y-auto max-h-[90vh]">
        <button onclick="document.getElementById('regModal').classList.add('hidden')" class="absolute top-6 right-6 text-slate-400 hover:text-slate-900 dark:hover:text-white">
            <i class="fas fa-times text-xl"></i>
        </button>
        
        <h3 class="text-2xl font-black mb-2 uppercase tracking-tight">Registrations</h3>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <p id="regTournamentTitle" class="text-brandGreen font-bold uppercase tracking-widest text-xs"></p>
            <button onclick="openAddUserModal()" class="px-6 py-3 bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-black rounded-xl hover:scale-105 active:scale-95 transition-all uppercase text-[10px] tracking-widest shadow-lg">
                <i class="fas fa-user-plus mr-2"></i> Add Player
            </button>
        </div>
        
        <div id="regList" class="space-y-4">
            <!-- Content loaded via AJAX -->
            <div class="flex items-center justify-center py-10">
                <i class="fas fa-circle-notch fa-spin text-3xl text-brandGreen"></i>
            </div>
        </div>
    </div>
</div>

<!-- Add User to Tournament Modal -->
<div id="addUserModal" class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-slate-900/90 backdrop-blur-md hidden">
    <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[40px] p-10 relative shadow-2xl">
        <button onclick="document.getElementById('addUserModal').classList.add('hidden')" class="absolute top-6 right-6 text-slate-400 hover:text-slate-900 dark:hover:text-white">
            <i class="fas fa-times text-xl"></i>
        </button>
        
        <h3 class="text-2xl font-black mb-6 uppercase tracking-tight">Add Player <span class="text-brandGreen">Manually</span></h3>
        
        <form id="addUserForm" onsubmit="addUserByAdmin(event)" class="space-y-4">
            <input type="hidden" name="tournament_id" id="addUserTournamentId">
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Full Name</label>
                <input type="text" name="full_name" required placeholder="Player Name" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 font-semibold">
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Email</label>
                <input type="email" name="email" required placeholder="player@email.com" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 font-semibold">
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Phone</label>
                <input type="tel" name="phone" required placeholder="0712345678" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 font-semibold">
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Category</label>
                <select name="category" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 font-black appearance-none">
                    <option value="Open">Open</option>
                    <option value="U1800">U1800</option>
                    <option value="Junior">Junior</option>
                </select>
            </div>
            <button type="submit" class="w-full mt-6 py-4 bg-brandGreen text-white rounded-2xl font-bold uppercase tracking-widest shadow-xl shadow-brandGreen/20 hover:scale-[1.02] active:scale-95 transition-all">
                Add Player
            </button>
        </form>
    </div>
</div>

<script>
let currentTournamentId = null;

function openEditModal(t) {
    document.getElementById('edit_id').value = t.id;
    document.getElementById('edit_title').value = t.title;
    document.getElementById('edit_location').value = t.location;
    // Format date for datetime-local input (YYYY-MM-DDTHH:MM)
    if (t.event_date) {
        const date = new Date(t.event_date);
        const formattedDate = date.toISOString().slice(0, 16);
        document.getElementById('edit_event_date').value = formattedDate;
    }
    document.getElementById('edit_prize_pool').value = t.prize_pool;
    document.getElementById('edit_entry_fee').value = t.entry_fee;
    document.getElementById('edit_status').value = t.status;
    document.getElementById('edit_description').value = t.description;
    
    document.getElementById('editModal').classList.remove('hidden');
}

function openAddUserModal() {
    document.getElementById('addUserTournamentId').value = currentTournamentId;
    document.getElementById('addUserModal').classList.remove('hidden');
}

async function viewRegistrations(tournamentId, title) {
    currentTournamentId = tournamentId;
    document.getElementById('regTournamentTitle').innerText = title;
    document.getElementById('regModal').classList.remove('hidden');
    document.getElementById('regList').innerHTML = '<div class="flex items-center justify-center py-10"><i class="fas fa-circle-notch fa-spin text-3xl text-brandGreen"></i></div>';

    try {
        const response = await fetch(`get_registrations.php?id=${tournamentId}`);
        const data = await response.text();
        document.getElementById('regList').innerHTML = data;
    } catch (error) {
        document.getElementById('regList').innerHTML = '<p class="text-red-500 font-bold p-4 text-center">Failed to load registrations.</p>';
    }
}

async function kickUser(registrationId, tournamentId) {
    if(!confirm('Are you sure you want to remove this player from the tournament?')) return;
    
    try {
        const formData = new FormData();
        formData.append('registration_id', registrationId);
        formData.append('action', 'kick');
        
        const response = await fetch('tournament_actions_ajax.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if(data.status === 'success') {
            viewRegistrations(tournamentId, document.getElementById('regTournamentTitle').innerText);
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('Error performing action');
    }
}

async function addUserByAdmin(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'add');
    
    try {
        const response = await fetch('tournament_actions_ajax.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if(data.status === 'success') {
            document.getElementById('addUserModal').classList.add('hidden');
            e.target.reset();
            viewRegistrations(currentTournamentId, document.getElementById('regTournamentTitle').innerText);
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('Error adding player');
    }
}
</script>

<?php include "admin_footer.php"; ?>