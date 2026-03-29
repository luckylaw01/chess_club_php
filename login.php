<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "includes/db_connect.php";
    
    // DEBUG: Ensure connection reached
    if (!$conn) { die("Fatal: \$conn variable is not reachable after include."); }
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Check input errors before querying the database
    if (empty($email_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, username, email, password, first_name, last_name, role FROM users WHERE email = ?";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_email);

            // Set parameters
            $param_email = $email;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();

                // Check if email exists, if yes then verify password
                if ($stmt->num_rows == 1) {
                    // Bind result variables
                    $stmt->bind_result($id, $username, $db_email, $hashed_password, $first_name, $last_name, $role);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["email"] = $db_email;
                            $_SESSION["first_name"] = $first_name;
                            $_SESSION["last_name"] = $last_name;
                            $_SESSION["role"] = $role;

                            // Redirect user based on role
                            if ($_SESSION["role"] === 'admin') {
                                header("location: admin/index.php");
                            } elseif ($_SESSION["role"] === 'coach') {
                                header("location: coach/index.php");
                            } else {
                                header("location: club.php");
                            }
                            exit;
                        } else {
                            // Password is not valid, display a generic error message
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else {
                    // Email doesn't exist, display a generic error message
                    $login_err = "Invalid email or password.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later. SQL Error: " . $conn->error;
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

$pageTitle = "Login";
include "includes/header.php";
?>

    <main class="flex-grow flex items-center justify-center px-6 pt-24 pb-12">
        <div class="w-full max-w-md">
            <div
                class="bg-white dark:bg-slate-900 p-10 rounded-[40px] shadow-2xl border border-slate-200 dark:border-slate-800">
                <div class="text-center mb-10">
                    <h1 class="text-3xl font-black mb-2 uppercase tracking-tight text-slate-900 dark:text-white">Welcome Back</h1>
                    <p class="text-slate-500 text-sm font-medium">Enter your details to access your dashboard</p>
                </div>

                <?php
                if (!empty($login_err)) {
                    echo '<div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl text-red-600 dark:text-red-400 text-sm font-medium">' . $login_err . '</div>';
                }
                ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2 ml-1">Email
                            Address</label>
                        <input type="email" name="email"
                            class="w-full px-6 py-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all font-medium <?php echo (!empty($email_err)) ? 'ring-2 ring-red-500' : ''; ?>"
                            placeholder="pawn@example.com" value="<?php echo $email; ?>" required>
                        <span class="text-red-500 text-xs"><?php echo $email_err; ?></span>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2 ml-1">Password</label>
                        <input type="password" name="password"
                            class="w-full px-6 py-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all font-medium <?php echo (!empty($password_err)) ? 'ring-2 ring-red-500' : ''; ?>"
                            placeholder="" required>
                        <span class="text-red-500 text-xs"><?php echo $password_err; ?></span>
                    </div>

                    <div class="flex justify-end">
                        <a href="#"
                            class="text-xs font-bold text-brandGreen hover:underline uppercase tracking-wide">Forgot
                            Password?</a>
                    </div>

                    <button type="submit"
                        class="w-full py-4 bg-brandGreen text-white rounded-2xl font-black uppercase tracking-widest hover:bg-brandGreen/90 hover:shadow-xl hover:shadow-brandGreen/30 transition-all active:scale-95">
                        Log In
                    </button>
                </form>

                <div class="mt-8 pt-8 border-t border-slate-100 dark:border-slate-800 text-center">
                    <p class="text-sm text-slate-500 font-medium">New to the club? <a href="register.php"
                            class="text-brandGreen font-bold hover:underline">Join Us</a></p>
                </div>
            </div>
        </div>
    </main>

<?php include "includes/footer.php"; ?>
