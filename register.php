<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$first_name = $last_name = $email = $password = $confirm_password = $program = "";
$first_name_err = $last_name_err = $email_err = $password_err = $confirm_password_err = $program_err = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "includes/db_connect.php";

    // Validate first name
    if (empty(trim($_POST["first_name"]))) {
        $first_name_err = "Please enter your first name.";
    } else {
        $first_name = trim($_POST["first_name"]);
    }

    // Validate last name
    if (empty(trim($_POST["last_name"]))) {
        $last_name_err = "Please enter your last name.";
    } else {
        $last_name = trim($_POST["last_name"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE email = ?";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_email);

            // Set parameters
            $param_email = trim($_POST["email"]);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $email_err = "This email is already registered.";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }

    // Validate program
    if (empty(trim($_POST["program"]))) {
        $program_err = "Please select a program.";
    } else {
        $program = trim($_POST["program"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Check input errors before inserting in database
    if (empty($first_name_err) && empty($last_name_err) && empty($email_err) && empty($program_err) && empty($password_err) && empty($confirm_password_err)) {

        // Prepare an insert statement
        $sql = "INSERT INTO users (username, email, password, first_name, last_name, full_name, elo_rating, role) VALUES (?, ?, ?, ?, ?, ?, 1200, 'user')";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ssssss", $param_username, $param_email, $param_password, $param_first_name, $param_last_name, $param_full_name);

            // Set parameters
            $param_username = strtolower($first_name . "." . $last_name);
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_full_name = $first_name . " " . $last_name;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to login page
                header("location: login.php?registered=success");
                exit;
            } else {
                echo "Something went wrong. Please try again later. SQL Error: " . $conn->error;
            }

            // Close statement
            $stmt->close();
        }
    }

    // Close connection
    if (isset($conn)) {
        $conn->close();
    }
}

$pageTitle = "Join Us";
include "includes/header.php";
?>
?>

    <main class="flex-grow flex items-center justify-center px-6 pt-24 pb-12">
        <div class="w-full max-w-lg">
            <div
                class="bg-white dark:bg-slate-900 p-10 rounded-[40px] shadow-2xl border border-slate-200 dark:border-slate-800">
                <div class="text-center mb-10">
                    <h1 class="text-3xl font-black mb-2 uppercase tracking-tight text-slate-900 dark:text-white">Begin Your Journey</h1>
                    <p class="text-slate-500 text-sm font-medium">Choose your path to chess mastery</p>
                </div>

                <?php
                if (isset($_GET['registered']) && $_GET['registered'] == 'success') {
                    echo '<div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-2xl text-green-600 dark:text-green-400 text-sm font-medium">Registration successful! Please log in.</div>';
                }
                ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2 space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2 ml-1">First
                                    Name</label>
                                <input type="text" name="first_name"
                                    class="w-full px-6 py-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none outline-none focus:ring-2 focus:ring-brandGreen transition-all font-medium <?php echo (!empty($first_name_err)) ? 'ring-2 ring-red-500' : ''; ?>"
                                    placeholder="Magnus" value="<?php echo $first_name; ?>" required>
                                <span class="text-red-500 text-xs"><?php echo $first_name_err; ?></span>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2 ml-1">Last
                                    Name</label>
                                <input type="text" name="last_name"
                                    class="w-full px-6 py-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none outline-none focus:ring-2 focus:ring-brandGreen transition-all font-medium <?php echo (!empty($last_name_err)) ? 'ring-2 ring-red-500' : ''; ?>"
                                    placeholder="Carlsen" value="<?php echo $last_name; ?>" required>
                                <span class="text-red-500 text-xs"><?php echo $last_name_err; ?></span>
                            </div>
                        </div>
                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2 ml-1">Email</label>
                            <input type="email" name="email"
                                class="w-full px-6 py-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none outline-none focus:ring-2 focus:ring-brandGreen transition-all font-medium <?php echo (!empty($email_err)) ? 'ring-2 ring-red-500' : ''; ?>"
                                placeholder="magnus@chess.com" value="<?php echo $email; ?>" required>
                            <span class="text-red-500 text-xs"><?php echo $email_err; ?></span>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2 ml-1">Interested
                                Program</label>
                            <select name="program"
                                class="w-full px-6 py-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none outline-none focus:ring-2 focus:ring-brandGreen transition-all font-medium appearance-none <?php echo (!empty($program_err)) ? 'ring-2 ring-red-500' : ''; ?>" required>
                                <option value="">Select a program</option>
                                <option value="Foundations (Beginner)" <?php echo ($program == "Foundations (Beginner)") ? 'selected' : ''; ?>>Foundations (Beginner)</option>
                                <option value="Tactics & Strategy (Intermediate)" <?php echo ($program == "Tactics & Strategy (Intermediate)") ? 'selected' : ''; ?>>Tactics & Strategy (Intermediate)</option>
                                <option value="Competitive Mastery (Advanced)" <?php echo ($program == "Competitive Mastery (Advanced)") ? 'selected' : ''; ?>>Competitive Mastery (Advanced)</option>
                                <option value="Club Membership Only" <?php echo ($program == "Club Membership Only") ? 'selected' : ''; ?>>Club Membership Only</option>
                            </select>
                            <span class="text-red-500 text-xs"><?php echo $program_err; ?></span>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2 ml-1">Password</label>
                                <input type="password" name="password"
                                    class="w-full px-6 py-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none outline-none focus:ring-2 focus:ring-brandGreen transition-all font-medium <?php echo (!empty($password_err)) ? 'ring-2 ring-red-500' : ''; ?>"
                                    placeholder="" required>
                                <span class="text-red-500 text-xs"><?php echo $password_err; ?></span>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2 ml-1">Confirm</label>
                                <input type="password" name="confirm_password"
                                    class="w-full px-6 py-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none outline-none focus:ring-2 focus:ring-brandGreen transition-all font-medium <?php echo (!empty($confirm_password_err)) ? 'ring-2 ring-red-500' : ''; ?>"
                                    placeholder="" required>
                                <span class="text-red-500 text-xs"><?php echo $confirm_password_err; ?></span>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full py-5 bg-brandGreen text-white rounded-2xl font-black uppercase tracking-widest hover:bg-brandGreen/90 hover:shadow-xl hover:shadow-brandGreen/30 transition-all active:scale-95 mt-4">
                            Create Account
                        </button>
                    </div>
                </form>

                <div class="mt-8 pt-8 border-t border-slate-100 dark:border-slate-800 text-center">
                    <p class="text-sm text-slate-500 font-medium">Already a member? <a href="login.php"
                            class="text-brandGreen font-bold hover:underline">Log In</a></p>
                </div>
            </div>
        </div>
    </main>

<?php include "includes/footer.php"; ?>
