<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/paystack_gateway.php';

$pageTitle = 'DONATE';
include 'includes/header.php';

$keys = paystack_get_keys($conn);
$paystackPublicKey = htmlspecialchars($keys['public_key']);

// Currency conversion rate (KES to USD) - Update this as needed
$kesToUsdRate = 0.0077;

// Preset donation amounts
$presetAmounts = [
    ['amount' => 500, 'label' => '500 KES'],
    ['amount' => 1000, 'label' => '1,000 KES'],
    ['amount' => 2500, 'label' => '2,500 KES'],
    ['amount' => 5000, 'label' => '5,000 KES'],
];

// Function to format currency display
function formatDonationAmount($kesAmount, $conversionRate) {
    $usdAmount = $kesAmount * $conversionRate;
    return "KES " . number_format($kesAmount, 0) . "<br><span class='text-xs'>($ " . number_format($usdAmount, 2) . ")</span>";
}
?>

<!-- Header Section -->
<header class="pt-32 pb-12 px-6 text-center animate-slide-up">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-5xl lg:text-6xl font-extrabold mb-4 tracking-tight">Support Our <span class="text-brandGreen">Mission</span></h1>
        <p class="text-slate-500 dark:text-slate-400 max-w-2xl mx-auto">Your donation helps us develop chess talent, organize tournaments, and provide coaching to aspiring players.</p>
    </div>
</header>

<!-- Main Donation Section -->
<section class="pb-24 px-6">
    <div class="max-w-2xl mx-auto">
        <!-- Impact Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
            <div class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-2xl p-8 text-center hover:shadow-lg transition-shadow">
                <div class="w-16 h-16 bg-brandGreen/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-graduation-cap text-brandGreen text-2xl"></i>
                </div>
                <h3 class="font-bold text-lg mb-2">Academy Support</h3>
                <p class="text-slate-500 text-sm">Fund chess training programs and educational resources</p>
            </div>

            <div class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-2xl p-8 text-center hover:shadow-lg transition-shadow">
                <div class="w-16 h-16 bg-brandGreen/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-trophy text-brandGreen text-2xl"></i>
                </div>
                <h3 class="font-bold text-lg mb-2">Tournament Events</h3>
                <p class="text-slate-500 text-sm">Enable us to organize and host chess tournaments</p>
            </div>

            <div class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-2xl p-8 text-center hover:shadow-lg transition-shadow">
                <div class="w-16 h-16 bg-brandGreen/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-brandGreen text-2xl"></i>
                </div>
                <h3 class="font-bold text-lg mb-2">Community Growth</h3>
                <p class="text-slate-500 text-sm">Help expand our chess community and membership</p>
            </div>
        </div>

        <!-- Donation Form -->
        <div class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 md:p-12">
            <h2 class="text-2xl font-bold mb-2">Choose Your Donation</h2>
            <p class="text-slate-500 dark:text-slate-400 mb-8">Select a preset amount or enter a custom donation</p>

            <form id="donationForm">
                <!-- Preset Amounts -->
                <div class="mb-8">
                    <label class="block text-sm font-bold uppercase tracking-widest mb-4">Quick Amounts</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php foreach ($presetAmounts as $preset): ?>
                            <button type="button" class="preset-amount-btn p-4 rounded-xl border-2 border-slate-200 dark:border-slate-800 hover:border-brandGreen hover:bg-brandGreen/10 transition-all font-bold text-sm leading-relaxed" data-amount="<?php echo $preset['amount']; ?>">
                                <?php echo formatDonationAmount($preset['amount'], $kesToUsdRate); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Custom Amount -->
                <div class="mb-8">
                    <label class="block text-sm font-bold uppercase tracking-widest mb-3">Custom Amount</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-500 font-bold">KES</span>
                            <input type="number" id="customAmount" name="customAmount" placeholder="Amount in KES" min="100" step="100" class="w-full pl-16 pr-4 py-3 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50 dark:bg-slate-900 focus:outline-none focus:border-brandGreen focus:ring-2 focus:ring-brandGreen/50">
                        </div>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-500 font-bold">USD</span>
                            <input type="number" id="customAmountUsd" name="customAmountUsd" placeholder="Amount in USD" min="1" step="1" class="w-full pl-16 pr-4 py-3 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50 dark:bg-slate-900 focus:outline-none focus:border-brandGreen focus:ring-2 focus:ring-brandGreen/50">
                        </div>
                    </div>
                </div>

                <!-- Display Selected Amount -->
                <div id="selectedAmountDisplay" class="mb-8 p-4 bg-brandGreen/10 border border-brandGreen/20 rounded-xl text-center hidden">
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-1">Donation Amount</p>
                    <p class="text-3xl font-bold text-brandGreen">KES <span id="displayAmount">0</span></p>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-2">≈ $ <span id="displayAmountUsd">0.00</span></p>
                </div>

                <!-- Phone Number (if logged in or for new donors) -->
                <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true): ?>
                    <div class="mb-8">
                        <label for="donorEmail" class="block text-sm font-bold uppercase tracking-widest mb-3">Email Address</label>
                        <input type="email" id="donorEmail" name="donorEmail" required class="w-full px-4 py-3 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50 dark:bg-slate-900 focus:outline-none focus:border-brandGreen focus:ring-2 focus:ring-brandGreen/50">
                    </div>

                    <div class="mb-8">
                        <label for="donorName" class="block text-sm font-bold uppercase tracking-widest mb-3">Full Name (Optional)</label>
                        <input type="text" id="donorName" name="donorName" placeholder="Your name" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50 dark:bg-slate-900 focus:outline-none focus:border-brandGreen focus:ring-2 focus:ring-brandGreen/50">
                    </div>
                <?php endif; ?>

                <!-- Message -->
                <div class="mb-8">
                    <label for="donationMessage" class="block text-sm font-bold uppercase tracking-widest mb-3">Message (Optional)</label>
                    <textarea id="donationMessage" name="donationMessage" rows="3" placeholder="Share why you're supporting us..." class="w-full px-4 py-3 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50 dark:bg-slate-900 focus:outline-none focus:border-brandGreen focus:ring-2 focus:ring-brandGreen/50" maxlength="500"></textarea>
                </div>

                <!-- Donate Button -->
                <button type="button" id="donateBtn" class="w-full py-4 bg-gradient-to-r from-brandGreen to-brandOrange text-slate-900 font-bold uppercase tracking-widest rounded-xl hover:shadow-lg hover:scale-105 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-heart mr-2"></i>
                    Proceed to Payment
                </button>
            </form>

            <!-- Powered by Paystack -->
            <div class="mt-8 text-center">
                <p class="text-xs text-slate-400">Secure payments powered by <span class="font-bold text-brandGreen">Paystack</span></p>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="mt-16">
            <h2 class="text-2xl font-bold mb-8">Frequently Asked Questions</h2>
            <div class="space-y-4">
                <details class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-xl p-6 cursor-pointer group">
                    <summary class="font-bold flex items-center justify-between">
                        <span>Is my donation tax-deductible?</span>
                        <i class="fas fa-chevron-down group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-slate-600 dark:text-slate-400 mt-4 text-sm">Please contact us at <a href="mailto:info@ascendingpawnchess.com" class="text-brandGreen hover:underline">info@ascendingpawnchess.com</a> for information about tax deductions and receipts.</p>
                </details>

                <details class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-xl p-6 cursor-pointer group">
                    <summary class="font-bold flex items-center justify-between">
                        <span>How is my donation used?</span>
                        <i class="fas fa-chevron-down group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-slate-600 dark:text-slate-400 mt-4 text-sm">Donations go towards academy development, tournament organization, coaching programs, and community outreach initiatives.</p>
                </details>

                <details class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-xl p-6 cursor-pointer group">
                    <summary class="font-bold flex items-center justify-between">
                        <span>Can I schedule recurring donations?</span>
                        <i class="fas fa-chevron-down group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-slate-600 dark:text-slate-400 mt-4 text-sm">Currently, we support one-time donations. For recurring donations, please contact us directly.</p>
                </details>

                <details class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-xl p-6 cursor-pointer group">
                    <summary class="font-bold flex items-center justify-between">
                        <span>Is my donation secure?</span>
                        <i class="fas fa-chevron-down group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-slate-600 dark:text-slate-400 mt-4 text-sm">Yes! We use Paystack for secure payment processing. Your payment details are never stored on our servers.</p>
                </details>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-8 right-8 z-[100] translate-y-20 opacity-0 transition-all duration-500 pointer-events-none">
    <div class="bg-slate-900 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-4 border border-slate-800">
        <div class="w-8 h-8 rounded-full bg-brandGreen/20 flex items-center justify-center">
            <i class="fas fa-check text-brandGreen text-sm"></i>
        </div>
        <p id="toast-message" class="text-sm font-bold uppercase tracking-widest"></p>
    </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
const kesToUsdRate = <?php echo $kesToUsdRate; ?>;
const usdToKesRate = 1 / kesToUsdRate;
const presetButtons = document.querySelectorAll('.preset-amount-btn');
const customAmountInput = document.getElementById('customAmount');
const customAmountUsdInput = document.getElementById('customAmountUsd');
const selectedAmountDisplay = document.getElementById('selectedAmountDisplay');
const displayAmount = document.getElementById('displayAmount');
const displayAmountUsd = document.getElementById('displayAmountUsd');
const donateBtn = document.getElementById('donateBtn');
const donationForm = document.getElementById('donationForm');

let selectedAmount = 0;

// Handle preset amount buttons
presetButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        selectedAmount = parseInt(btn.dataset.amount);
        customAmountInput.value = '';
        customAmountUsdInput.value = '';
        updateDisplay();
        updateButtonStates(btn);
    });
});

// Handle custom amount input (KES)
customAmountInput.addEventListener('input', () => {
    selectedAmount = customAmountInput.value ? parseFloat(customAmountInput.value) : 0;
    
    // Update USD input
    if (selectedAmount > 0) {
        customAmountUsdInput.value = (selectedAmount * kesToUsdRate).toFixed(2);
    } else {
        customAmountUsdInput.value = '';
    }
    
    presetButtons.forEach(btn => btn.classList.remove('border-brandGreen', 'bg-brandGreen/10'));
    updateDisplay();
});

// Handle custom amount input (USD)
customAmountUsdInput.addEventListener('input', () => {
    const usdVal = customAmountUsdInput.value ? parseFloat(customAmountUsdInput.value) : 0;
    
    // Update KES value (which is what we send to backend)
    if (usdVal > 0) {
        selectedAmount = Math.round(usdVal * usdToKesRate);
        customAmountInput.value = selectedAmount;
    } else {
        selectedAmount = 0;
        customAmountInput.value = '';
    }
    
    presetButtons.forEach(btn => btn.classList.remove('border-brandGreen', 'bg-brandGreen/10'));
    updateDisplay();
});

function updateButtonStates(activeBtn) {
    presetButtons.forEach(btn => {
        btn.classList.remove('border-brandGreen', 'bg-brandGreen/10');
        if (btn === activeBtn) {
            btn.classList.add('border-brandGreen', 'bg-brandGreen/10');
        }
    });
}

function updateDisplay() {
    if (selectedAmount > 0) {
        displayAmount.textContent = selectedAmount.toLocaleString();
        const usdAmount = (selectedAmount * kesToUsdRate).toFixed(2);
        displayAmountUsd.textContent = usdAmount;
        selectedAmountDisplay.classList.remove('hidden');
        donateBtn.disabled = false;
    } else {
        selectedAmountDisplay.classList.add('hidden');
        donateBtn.disabled = true;
    }
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toast-message');
    toastMessage.textContent = message;
    
    if (type === 'error') {
        toast.querySelector('div').classList.remove('border-slate-800');
        toast.querySelector('div').classList.add('border-red-600');
    }
    
    toast.classList.remove('translate-y-20', 'opacity-0');
    setTimeout(() => {
        toast.classList.add('translate-y-20', 'opacity-0');
    }, 4000);
}

// Handle donation button click
donateBtn.addEventListener('click', async (e) => {
    e.preventDefault();

    if (selectedAmount <= 0) {
        showToast('Please select or enter a donation amount', 'error');
        return;
    }

    donateBtn.disabled = true;
    donateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

    try {
        const formData = new FormData();
        formData.append('action', 'initialize_donation');
        formData.append('amount', selectedAmount);
        
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            // User is logged in
        <?php else: ?>
            // User is not logged in, add email and name
            const email = document.getElementById('donorEmail').value.trim();
            const name = document.getElementById('donorName').value.trim();
            
            if (!email) {
                showToast('Please enter your email address', 'error');
                donateBtn.disabled = false;
                donateBtn.innerHTML = '<i class="fas fa-heart mr-2"></i>Proceed to Payment';
                return;
            }
            
            formData.append('email', email);
            formData.append('donor_name', name);
        <?php endif; ?>
        
        const message = document.getElementById('donationMessage').value.trim();
        if (message) {
            formData.append('message', message);
        }

        const response = await fetch('paystack_donation_ajax.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success && data.authorization_url) {
            // Redirect to Paystack payment page
            window.location.href = data.authorization_url;
        } else {
            showToast(data.message || 'Failed to initialize donation', 'error');
            donateBtn.disabled = false;
            donateBtn.innerHTML = '<i class="fas fa-heart mr-2"></i>Proceed to Payment';
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
        donateBtn.disabled = false;
        donateBtn.innerHTML = '<i class="fas fa-heart mr-2"></i>Proceed to Payment';
    }
});

// Initialize display
updateDisplay();
</script>
