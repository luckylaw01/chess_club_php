<?php
session_start();
$pageTitle = "Membership Management";
include "admin_header.php";
include "../includes/db_connect.php";

// Handle Plan Creation/Update
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["add_plan"])) {
        $name = $_POST["name"];
        $description = $_POST["description"];
        $price = (float)$_POST["price"];
        $duration = (int)$_POST["duration_months"];
        $features = $_POST["features"];

        $sql = "INSERT INTO membership_plans (name, description, price, duration_months, features) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssdis", $name, $description, $price, $duration, $features);
            if ($stmt->execute()) {
                $message = "Membership plan added successfully!";
            } else {
                $message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    if (isset($_POST["edit_plan"])) {
        $id = (int)$_POST["plan_id"];
        $name = $_POST["name"];
        $description = $_POST["description"];
        $price = (float)$_POST["price"];
        $duration = (int)$_POST["duration_months"];
        $features = $_POST["features"];

        $sql = "UPDATE membership_plans SET name=?, description=?, price=?, duration_months=?, features=? WHERE id=?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssdisi", $name, $description, $price, $duration, $features, $id);
            if ($stmt->execute()) {
                $message = "Membership plan updated successfully!";
            } else {
                $message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Handle Plan Deletion
if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];
    $checkRes = $conn->query("SELECT COUNT(*) FROM users WHERE membership_plan_id = $id");
    $check = $checkRes->fetch_row()[0];
    if ($check > 0) {
        $message = "Error: Cannot delete plan. $check users are currently assigned to it.";
    } else {
        $conn->query("DELETE FROM membership_plans WHERE id = $id");
        $message = "Plan deleted successfully.";
    }
}

$plans = $conn->query("SELECT * FROM membership_plans ORDER BY price ASC");
?>

<div class="p-6 md:p-10 max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Membership <span class="text-brandGreen">Plans</span></h1>
            <p class="text-slate-500 font-medium mt-1">Configure club subscription tiers and pricing.</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="px-8 py-4 bg-brandGreen text-white font-bold rounded-2xl hover:scale-105 active:scale-95 transition-all uppercase text-[11px] tracking-widest shadow-xl shadow-brandGreen/20">
            <i class="fas fa-plus mr-2"></i> Create New Plan
        </button>
    </div>

    <?php if($message): ?>
        <div class="mb-8 p-4 bg-brandGreen/10 border border-brandGreen/20 text-brandGreen rounded-2xl font-bold text-sm">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php while($p = $plans->fetch_assoc()): ?>
            <div class="p-8 rounded-[40px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all group relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-brandGreen/10 text-brandGreen flex items-center justify-center text-xl">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="flex gap-2">
                            <button onclick='openEditModal(<?php echo json_encode($p); ?>)' class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 flex items-center justify-center hover:bg-brandGreen hover:text-white transition-all">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                            <a href="?delete=<?php echo $p['id']; ?>" onclick="return confirm('Are you sure? This cannot be undone.')" class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </a>
                        </div>
                    </div>
                    
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white uppercase mb-2"><?php echo htmlspecialchars($p["name"]); ?></h3>
                    <p class="text-slate-500 text-sm font-medium mb-6 line-clamp-2"><?php echo htmlspecialchars($p["description"]); ?></p>
                    
                    <div class="text-4xl font-black text-brandGreen mb-6">
                        KES <?php echo number_format($p["price"], 0); ?>
                        <span class="text-xs text-slate-400 font-bold uppercase tracking-widest">/ <?php echo $p["duration_months"]; ?> MO</span>
                    </div>

                    <div class="space-y-3 mb-4">
                        <?php 
                        $feats = explode(",", $p["features"]);
                        foreach($feats as $f): if(trim($f) == "") continue;
                        ?>
                            <div class="flex items-center gap-3 text-xs font-bold text-slate-600 dark:text-slate-400">
                                <i class="fas fa-check text-brandGreen"></i>
                                <?php echo htmlspecialchars(trim($f)); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="addModal" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-900/80 backdrop-blur-sm hidden">
    <div class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-[40px] p-10 relative shadow-2xl overflow-y-auto max-h-[90vh]">
        <button onclick="document.getElementById('addModal').classList.add('hidden')" class="absolute top-6 right-6 text-slate-400 hover:text-slate-900 dark:hover:text-white">
            <i class="fas fa-times text-xl"></i>
        </button>
        <h3 class="text-2xl font-black mb-6 uppercase tracking-tight">Create <span class="text-brandGreen">Membership Plan</span></h3>
        <form method="POST">
            <div class="grid md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Plan Name</label>
                    <input type="text" name="name" required class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none outline-none focus:ring-2 focus:ring-brandGreen/50">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Price (KES)</label>
                    <input type="number" name="price" required class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none outline-none focus:ring-2 focus:ring-brandGreen/50">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Duration (Months)</label>
                    <input type="number" name="duration_months" value="1" required class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none outline-none focus:ring-2 focus:ring-brandGreen/50">
                </div>
                <div class="col-span-2">
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Description</label>
                    <textarea name="description" rows="2" class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none outline-none focus:ring-2 focus:ring-brandGreen/50"></textarea>
                </div>
                <div class="col-span-2">
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Features (Comma Separated)</label>
                    <textarea name="features" rows="3" placeholder="Access to club, Free tournaments, Weekly coachings" class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none outline-none focus:ring-2 focus:ring-brandGreen/50"></textarea>
                </div>
            </div>
            <button type="submit" name="add_plan" class="w-full mt-8 py-5 bg-brandGreen text-white rounded-3xl font-bold uppercase tracking-[0.2em] shadow-xl shadow-brandGreen/20 hover:scale-[1.02] active:scale-95 transition-all">
                Publish Plan
            </button>
        </form>
    </div>
</div>

<div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-900/80 backdrop-blur-sm hidden">
    <div class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-[40px] p-10 relative shadow-2xl overflow-y-auto max-h-[90vh]">
        <button onclick="document.getElementById('editModal').classList.add('hidden')" class="absolute top-6 right-6 text-slate-400 hover:text-slate-900 dark:hover:text-white">
            <i class="fas fa-times text-xl"></i>
        </button>
        <h3 class="text-2xl font-black mb-6 uppercase tracking-tight">Edit <span class="text-brandGreen">Membership Plan</span></h3>
        <form method="POST">
            <input type="hidden" name="plan_id" id="edit_id">
            <div class="grid md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Plan Name</label>
                    <input type="text" name="name" id="edit_name" required class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none outline-none focus:ring-2 focus:ring-brandGreen/50">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Price (KES)</label>
                    <input type="number" name="price" id="edit_price" required class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none outline-none focus:ring-2 focus:ring-brandGreen/50">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Duration (Months)</label>
                    <input type="number" name="duration_months" id="edit_duration" required class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none outline-none focus:ring-2 focus:ring-brandGreen/50">
                </div>
                <div class="col-span-2">
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Description</label>
                    <textarea name="description" id="edit_description" rows="2" class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none outline-none focus:ring-2 focus:ring-brandGreen/50"></textarea>
                </div>
                <div class="col-span-2">
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 tracking-widest">Features (Comma Separated)</label>
                    <textarea name="features" id="edit_features" rows="3" class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none outline-none focus:ring-2 focus:ring-brandGreen/50"></textarea>
                </div>
            </div>
            <button type="submit" name="edit_plan" class="w-full mt-8 py-5 bg-brandGreen text-white rounded-3xl font-bold uppercase tracking-[0.2em] shadow-xl shadow-brandGreen/20 hover:scale-[1.02] active:scale-95 transition-all">
                Update Plan
            </button>
        </form>
    </div>
</div>

<script>
function openEditModal(plan) {
    document.getElementById("edit_id").value = plan.id;
    document.getElementById("edit_name").value = plan.name;
    document.getElementById("edit_price").value = plan.price;
    document.getElementById("edit_duration").value = plan.duration_months;
    document.getElementById("edit_description").value = plan.description;
    document.getElementById("edit_features").value = plan.features;
    document.getElementById("editModal").classList.remove("hidden");
}
</script>

<?php include "admin_footer.php"; ?>
