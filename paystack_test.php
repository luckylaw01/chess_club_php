<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/paystack_gateway.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['redirect_after_login'] = 'paystack_test.php';
    header('Location: login.php');
    exit();
}

$pageTitle = 'PAYSTACK TEST';

$userId = (int)$_SESSION['id'];
$user = [
    'name' => '',
    'email' => '',
    'phone_number' => '',
];

$userStmt = $conn->prepare("SELECT full_name, email, phone_number FROM users WHERE id = ? LIMIT 1");
if ($userStmt) {
    $userStmt->bind_param('i', $userId);
    $userStmt->execute();
    $result = $userStmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $user['name'] = $row['full_name'] ?? '';
        $user['email'] = $row['email'] ?? '';
        $user['phone_number'] = $row['phone_number'] ?? '';
    }
    $userStmt->close();
}

$plans = [];
$planQuery = $conn->query("SELECT id, name, price, duration_months FROM membership_plans ORDER BY price ASC");
if ($planQuery) {
    while ($plan = $planQuery->fetch_assoc()) {
        $plans[] = $plan;
    }
}

$paystackKeys = paystack_get_keys($conn);

include 'includes/header.php';

$currentReference = $_GET['reference'] ?? '';
$currentStatus = $_GET['status'] ?? '';
?>

<div class="pt-32 pb-24 px-6 min-h-screen">
    <div class="max-w-5xl mx-auto">
        <div class="mb-10">
            <h1 class="text-4xl font-black uppercase tracking-tight">Paystack <span class="text-brandGreen">Test</span></h1>
            <p class="text-slate-500 font-medium mt-2">Initialize a test payment against a membership plan and confirm the callback flow. You can test with small amounts like KES 10.</p>
        </div>

        <div id="statusBox" class="hidden mb-8 rounded-[30px] border p-6 font-bold"></div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[40px] p-8 shadow-lg animate-slide-up">
                <h2 class="text-xl font-black uppercase tracking-tight mb-6">Payment Details</h2>

                <form id="paystackTestForm" class="space-y-5">
                    <input type="hidden" name="action" value="initialize">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Member Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required class="w-full px-6 py-4 rounded-3xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all font-bold">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="w-full px-6 py-4 rounded-3xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all font-bold">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Phone Number</label>
                        <input type="text" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required placeholder="0712345678" class="w-full px-6 py-4 rounded-3xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all font-bold">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Membership Plan</label>
                        <select name="plan_id" id="planSelect" required class="w-full px-6 py-4 rounded-3xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all font-bold">
                            <option value="">Select a plan</option>
                            <?php foreach ($plans as $plan): ?>
                                <option value="<?php echo (int)$plan['id']; ?>" data-price="<?php echo htmlspecialchars($plan['price']); ?>">
                                    <?php echo htmlspecialchars($plan['name']); ?> - KES <?php echo number_format((float)$plan['price'], 2); ?> / <?php echo (int)$plan['duration_months']; ?> mo
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Amount (KES)</label>
                        <input type="number" name="amount" id="amountInput" min="10" step="0.01" value="10" required class="w-full px-6 py-4 rounded-3xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all font-bold">
                        <p class="text-[10px] text-slate-500 mt-2 ml-1 font-bold">Enter the amount you want to test, for example KES 10.</p>
                    </div>

                    <button type="submit" id="submitButton" class="w-full py-6 bg-brandGreen text-white font-black uppercase tracking-widest rounded-[32px] hover:bg-brandNeonGreen hover:scale-[1.02] active:scale-95 transition-all shadow-2xl shadow-brandGreen/30 flex items-center justify-center gap-4">
                        Start Paystack Test
                        <i class="fas fa-lock text-xs opacity-50"></i>
                    </button>
                </form>
            </div>

            <div class="bg-slate-900 text-white rounded-[40px] p-10 h-fit animate-slide-up delay-200">
                <h2 class="text-xl font-black uppercase tracking-tight mb-8">Test Notes</h2>
                <div class="space-y-4 text-sm text-white/70 font-medium leading-7">
                    <p>This page uses the Paystack test secret key on the server and your test public key on the client integration path.</p>
                    <p>After initialization, you will be redirected to the Paystack authorization page and then returned here for verification.</p>
                    <p class="text-white/50 text-xs uppercase tracking-[0.2em] font-black pt-4">Public Key</p>
                    <p class="break-all font-mono text-xs text-brandGreen"><?php echo htmlspecialchars($paystackKeys['public_key'] ?: 'Paystack key not configured'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const planSelect = document.getElementById('planSelect');
const amountInput = document.getElementById('amountInput');
const form = document.getElementById('paystackTestForm');
const statusBox = document.getElementById('statusBox');
const submitButton = document.getElementById('submitButton');
const currentReference = <?php echo json_encode($currentReference); ?>;
const currentStatus = <?php echo json_encode($currentStatus); ?>;

function showStatus(type, message) {
    statusBox.classList.remove('hidden');
    statusBox.textContent = message;
    statusBox.className = 'mb-8 rounded-[30px] border p-6 font-bold';
    if (type === 'success') {
        statusBox.classList.add('bg-emerald-500/10', 'border-emerald-500/20', 'text-emerald-600');
    } else if (type === 'error') {
        statusBox.classList.add('bg-red-500/10', 'border-red-500/20', 'text-red-600');
    } else {
        statusBox.classList.add('bg-slate-500/10', 'border-slate-400/20', 'text-slate-600');
    }
}

planSelect.addEventListener('change', () => {
    if (!amountInput.value || Number(amountInput.value) === 10) {
        amountInput.value = '10';
    }
});

if (currentReference && currentStatus === 'callback') {
    showStatus('info', 'Verifying payment reference ' + currentReference + '...');

    fetch('paystack_test_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'verify',
            reference: currentReference
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showStatus('success', data.message);
        } else {
            showStatus('error', data.message || 'Verification failed.');
        }
    })
    .catch(() => showStatus('error', 'Unable to verify the payment right now.'));
}

form.addEventListener('submit', function (event) {
    event.preventDefault();
    submitButton.disabled = true;
    submitButton.textContent = 'Initializing...';

    fetch('paystack_test_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(new FormData(form))
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Initialization failed');
        }

        window.location.href = data.authorization_url;
    })
    .catch(error => {
        showStatus('error', error.message);
        submitButton.disabled = false;
        submitButton.innerHTML = 'Start Paystack Test <i class="fas fa-lock text-xs opacity-50"></i>';
    });
});
</script>

<?php include 'includes/footer.php'; ?>