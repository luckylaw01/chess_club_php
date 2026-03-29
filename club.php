<?php
session_start();

// Check if user is logged in BEFORE including header.php
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$pageTitle = "Club Membership";
include "includes/header.php";
include "includes/db_connect.php";

// Get user data from database
$user_id = $_SESSION["id"];
$sql = "SELECT u.*, p.name as plan_name 
        FROM users u 
        LEFT JOIN membership_plans p ON u.membership_plan_id = p.id 
        WHERE u.id = ?";
if($stmt = $conn->prepare($sql)){
    $stmt->bind_param("i", $user_id);
    if($stmt->execute()){
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    }
    $stmt->close();
}

// Fetch all available plans
$plans = [];
$sql_plans = "SELECT * FROM membership_plans";
if($result_plans = $conn->query($sql_plans)){
    while($row = $result_plans->fetch_assoc()){
        $plans[] = $row;
    }
}
?>
    <section class="pt-32 pb-20 px-6 font-sans">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-4xl md:text-6xl font-black mb-12">Club Membership</h1>

            <!-- User Membership Dashboard -->
            <div
                class="animate-slide-up mb-20 p-8 md:p-10 rounded-[40px] bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 shadow-2xl relative overflow-hidden group glass transition-all hover:border-brandGreen/30">
                <!-- Decorative background -->
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-brandGreen/5 blur-[80px] rounded-full -mr-20 -mt-20 transition-all group-hover:bg-brandGreen/10">
                </div>

                <div class="relative z-10 flex flex-col md:flex-row items-center gap-8">
                    <!-- Profile Avatar Area -->
                    <div class="relative">
                        <div
                            class="w-24 h-24 rounded-3xl bg-gradient-to-br from-brandGreen to-brandGold flex items-center justify-center text-white text-3xl font-black shadow-lg shadow-brandGreen/20">
                            <?php echo strtoupper(substr($user["first_name"], 0, 1)); ?>
                        </div>
                        <div class="absolute -bottom-1 -right-1 bg-green-500 w-6 h-6 rounded-full border-4 border-white dark:border-slate-900 shadow-sm"
                            title="Active Status"></div>
                    </div>

                    <!-- Member Info -->
                    <div class="flex-grow text-center md:text-left">
                        <div class="flex flex-col md:flex-row md:items-center gap-3 mb-5">
                            <h2 class="text-3xl font-black tracking-tight uppercase text-slate-900 dark:text-white">
                                <?php echo htmlspecialchars($user["first_name"] . " " . $user["last_name"]); ?></h2>
                            <span
                                class="px-4 py-1.5 bg-brandGreen/10 dark:bg-brandGreen/20 text-brandGreen dark:text-brandGreen text-[10px] font-bold uppercase tracking-widest rounded-xl self-center">
                                <?php echo htmlspecialchars($user["plan_name"] ?? "Not Enrolled"); ?>
                            </span>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 mb-1.5">Status
                                </p>
                                <p
                                    class="font-bold <?php echo $user["membership_status"] == 'active' ? 'text-green-500' : 'text-amber-500'; ?> flex items-center gap-1.5 justify-center md:justify-start">
                                    <span class="w-2 h-2 <?php echo $user["membership_status"] == 'active' ? 'bg-green-500 vibrate' : 'bg-amber-500 animate-pulse'; ?> rounded-full"></span> 
                                    <?php echo ucfirst($user["membership_status"] ?? "Inactive"); ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 mb-1.5">Member
                                    Since</p>
                                <p class="font-bold text-slate-800 dark:text-slate-200"><?php echo date("M Y", strtotime($user["created_at"])); ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 mb-1.5">
                                    Renewal Date</p>
                                <p class="font-bold text-slate-800 dark:text-slate-200"><?php echo date("M d, Y", strtotime("+1 year", strtotime($user["created_at"]))); ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 mb-1.5">Elo
                                    Rating</p>
                                <p class="font-black text-brandGreen text-xl"><?php echo $user["elo_rating"] ?? 1200; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Link -->
                    <div class="flex flex-col gap-3 w-full md:w-auto">
                        <?php if($_SESSION["role"] === "admin"): ?>
                            <a href="admin/index.php" class="px-8 py-4 bg-slate-900 dark:bg-brandGreen text-white font-bold rounded-2xl hover:scale-105 active:scale-95 transition-all uppercase text-[11px] text-center tracking-widest shadow-xl shadow-brandGreen/20">
                                Admin Portal
                            </a>
                        <?php elseif($_SESSION["role"] === "coach"): ?>
                            <a href="coach/index.php" class="px-8 py-4 bg-slate-900 dark:bg-brandGreen text-white font-bold rounded-2xl hover:scale-105 active:scale-95 transition-all uppercase text-[11px] text-center tracking-widest shadow-xl shadow-brandGreen/20">
                                Coach Portal
                            </a>
                        <?php else: ?>
                            <button class="px-8 py-4 bg-slate-900 dark:bg-brandGreen text-white font-bold rounded-2xl hover:scale-105 active:scale-95 transition-all uppercase text-[11px] tracking-widest shadow-xl shadow-brandGreen/20">
                                Management Portal
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Stats Footer -->
                <div
                    class="mt-10 pt-8 border-t border-slate-100 dark:border-slate-800 grid grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-brandGreen/10 dark:bg-brandGreen/20 flex items-center justify-center text-brandGreen">
                            <i class="fas fa-trophy text-lg"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400 mb-0.5">Achievements
                            </p>
                            <p class="font-bold text-sm text-slate-700 dark:text-slate-300"><?php echo $user["achievements_count"] ?? 0; ?> Major Wins</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center text-purple-600">
                            <i class="fas fa-chess-pawn text-lg"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400 mb-0.5">Global Rank
                            </p>
                            <p class="font-bold text-sm text-slate-700 dark:text-slate-300">#<?php echo $user["global_rank"] ?? "N/A"; ?> in Kenya</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 hidden lg:flex">
                        <div
                            class="w-12 h-12 rounded-2xl bg-orange-50 dark:bg-orange-900/30 flex items-center justify-center text-orange-500">
                            <i class="fas fa-calendar-check text-lg"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400 mb-0.5">Next Match
                            </p>
                            <p class="font-bold text-sm text-slate-700 dark:text-slate-300">Tomorrow at 6:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>

            <h2 class="text-3xl font-bold mb-10 text-slate-900 dark:text-white flex items-center gap-3">
                <span class="w-8 h-px bg-brandGreen"></span> Available Plans
            </h2>
            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach($plans as $plan): ?>
                    <div class="p-8 rounded-[40px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl flex flex-col <?php echo $user['membership_plan_id'] == $plan['id'] ? 'ring-2 ring-brandGreen ring-offset-4 dark:ring-offset-slate-900' : ''; ?>">
                        <h3 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($plan['name']); ?></h3>
                        <p class="text-slate-500 mb-6 font-medium"><?php echo htmlspecialchars($plan['description']); ?></p>
                        <div class="text-4xl font-black mb-8">Ksh <?php echo number_format($plan['price']); ?><span
                                class="text-sm font-bold text-slate-400">/<?php echo $plan['duration_months']; ?> mo</span></div>
                        <ul class="space-y-4 mb-8 text-sm font-medium flex-grow">
                            <?php 
                            $features = explode(',', $plan['features']);
                            foreach($features as $feature):
                            ?>
                                <li><i class="fas fa-check text-brandGreen mr-2"></i> <?php echo htmlspecialchars(trim($feature)); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button
                            onclick="openMpesaModal(<?php echo $plan['id']; ?>, '<?php echo $plan['name']; ?>', <?php echo $plan['price']; ?>)"
                            class="w-full py-4 <?php echo $user['membership_plan_id'] == $plan['id'] ? 'bg-slate-200 text-slate-500' : 'bg-brandGreen text-white'; ?> rounded-2xl font-bold uppercase tracking-widest transition-all hover:scale-105"
                            <?php echo $user['membership_plan_id'] == $plan['id'] ? 'disabled' : ''; ?>>
                            <?php echo $user['membership_plan_id'] == $plan['id'] ? 'Current Plan' : 'Select Plan'; ?>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- M-Pesa Payment Simulation Modal -->
    <div id="mpesaModal" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-900/80 backdrop-blur-sm hidden">
        <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-[40px] p-10 relative shadow-2xl animate-scale-up">
            <button onclick="closeMpesaModal()" class="absolute top-6 right-6 text-slate-400 hover:text-slate-900 dark:hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
            
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/1/15/M-PESA_LOGO-01.svg" class="w-10 h-10" alt="M-Pesa">
                </div>
                <h3 class="text-2xl font-black">Plan Upgrade</h3>
                <p id="modalPlanName" class="text-slate-500 font-bold uppercase text-[10px] tracking-widest mt-1">PRO PAWN</p>
            </div>

            <form id="paymentForm" onsubmit="simulatePayment(event)">
                <input type="hidden" id="planId" name="plan_id">
                <input type="hidden" id="planPrice" name="amount">
                
                <div class="mb-6">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-4">Phone Number (M-Pesa)</label>
                    <input type="text" id="phoneNumber" name="phone_number" placeholder="2547XXXXXXXX" required
                        class="w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen text-lg font-bold">
                </div>

                <div class="mb-8 p-6 rounded-2xl bg-brandGreen/5 border border-brandGreen/10">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-bold opacity-60">Total Amount</span>
                        <span id="displayPrice" class="text-xl font-black text-brandGreen">Ksh 0.00</span>
                    </div>
                    <p class="text-[10px] leading-relaxed opacity-50">Enter your M-Pesa number above. A simulated STK push will be triggered for verification.</p>
                </div>

                <button type="submit" id="submitBtn"
                    class="w-full py-5 bg-brandGreen text-white rounded-3xl font-bold uppercase tracking-[0.2em] shadow-xl shadow-brandGreen/20 hover:scale-105 active:scale-95 transition-all">
                    Pay via M-Pesa
                </button>
            </form>

            <div id="paymentStatus" class="hidden mt-6 text-center">
                <div class="animate-spin inline-block w-8 h-8 border-4 border-brandGreen border-t-transparent rounded-full mb-4"></div>
                <p class="text-sm font-bold">Processing payment simulation...</p>
            </div>
        </div>
    </div>

    <script>
    function openMpesaModal(id, name, price) {
        document.getElementById('planId').value = id;
        document.getElementById('planPrice').value = price;
        document.getElementById('modalPlanName').innerText = name.toUpperCase();
        document.getElementById('displayPrice').innerText = 'Ksh ' + new Intl.NumberFormat().format(price);
        document.getElementById('mpesaModal').classList.remove('hidden');
    }

    function closeMpesaModal() {
        document.getElementById('mpesaModal').classList.add('hidden');
        document.getElementById('paymentForm').classList.remove('hidden');
        document.getElementById('paymentStatus').classList.add('hidden');
    }

    async function simulatePayment(event) {
        event.preventDefault();
        
        const form = document.getElementById('paymentForm');
        const status = document.getElementById('paymentStatus');
        const btn = document.getElementById('submitBtn');
        
        form.classList.add('hidden');
        status.classList.remove('hidden');
        
        const formData = new FormData(form);
        
        try {
            const response = await fetch('simulate_mpesa.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if(result.success) {
                status.innerHTML = `
                    <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl animate-bounce">
                        <i class="fas fa-check"></i>
                    </div>
                    <p class="text-lg font-black uppercase tracking-tight">Payment Successful!</p>
                    <p class="text-sm text-slate-500 mt-2">Ref: ${result.reference}</p>
                    <p class="text-[10px] text-slate-400 mt-1">Updating membership...</p>
                `;
                setTimeout(() => {
                    location.reload();
                }, 3000);
            } else {
                status.innerHTML = `
                    <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fas fa-exclamation"></i>
                    </div>
                    <p class="text-lg font-black text-red-600">Payment Failed</p>
                    <p class="text-sm text-slate-500 mt-2">${result.message}</p>
                    <button onclick="closeMpesaModal()" class="mt-4 text-brandGreen font-bold">Try Again</button>
                `;
            }
        } catch (error) {
            console.error('Error:', error);
            status.innerHTML = `<p class="text-red-500">System error occurred. Please try again.</p>`;
        }
    }
    </script>
        </div>
    </section>

<?php include "includes/footer.php"; ?>

explanation:
