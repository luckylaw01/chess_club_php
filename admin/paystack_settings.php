<?php
session_start();
require_once "../includes/db_connect.php";

if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "Paystack Keys";
include "admin_header.php";

$message = "";
$error = "";

function fetchSettingValue(mysqli $conn, string $key): string
{
    $stmt = $conn->prepare("SELECT setting_value FROM app_settings WHERE setting_key = ? LIMIT 1");
    if (!$stmt) {
        return "";
    }

    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    $value = "";

    if ($result && ($row = $result->fetch_assoc())) {
        $value = (string)($row['setting_value'] ?? '');
    }

    $stmt->close();
    return $value;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $secretKey = trim($_POST['paystack_secret_key'] ?? '');
    $publicKey = trim($_POST['paystack_public_key'] ?? '');

    if ($secretKey === '' || $publicKey === '') {
        $error = 'Both Paystack keys are required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

        if ($stmt) {
            $key = 'paystack_secret_key';
            $value = $secretKey;
            $stmt->bind_param('ss', $key, $value);
            $stmt->execute();

            $key = 'paystack_public_key';
            $value = $publicKey;
            $stmt->bind_param('ss', $key, $value);
            $stmt->execute();

            $stmt->close();
            $message = 'Paystack keys updated successfully.';
        } else {
            $error = 'Unable to save Paystack keys: ' . $conn->error;
        }
    }
}

$currentSecretKey = fetchSettingValue($conn, 'paystack_secret_key');
$currentPublicKey = fetchSettingValue($conn, 'paystack_public_key');
?>

<div class="max-w-4xl mx-auto space-y-8">
    <div>
        <h1 class="text-3xl font-black">Paystack <span class="text-brandGreen">Keys</span></h1>
        <p class="text-slate-500">Store and update the live or test Paystack credentials used by checkout and subscriptions.</p>
    </div>

    <?php if ($message): ?>
        <div class="p-4 rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-700 font-bold">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="p-4 rounded-2xl border border-rose-200 bg-rose-50 text-rose-700 font-bold">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-slate-900 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-sm p-8">
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Paystack Secret Key</label>
                <input type="text" name="paystack_secret_key" value="<?php echo htmlspecialchars($currentSecretKey); ?>" class="w-full px-5 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 dark:bg-slate-800/50 focus:ring-2 focus:ring-brandGreen outline-none transition-all font-mono text-sm" placeholder="sk_live_...">
            </div>

            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Paystack Public Key</label>
                <input type="text" name="paystack_public_key" value="<?php echo htmlspecialchars($currentPublicKey); ?>" class="w-full px-5 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 dark:bg-slate-800/50 focus:ring-2 focus:ring-brandGreen outline-none transition-all font-mono text-sm" placeholder="pk_live_...">
            </div>

            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-5 border border-slate-200 dark:border-slate-800">
                <p class="text-sm font-bold text-slate-700 dark:text-slate-300">These values are read by `includes/paystack_gateway.php` and used by the membership and shop checkout flows.</p>
            </div>

            <button type="submit" class="px-8 py-4 bg-brandGreen text-white font-bold rounded-2xl uppercase text-[11px] tracking-widest shadow-xl shadow-brandGreen/20 hover:scale-105 active:scale-95 transition-all">
                Save Keys
            </button>
        </form>
    </div>
</div>

<?php include "admin_footer.php"; ?>