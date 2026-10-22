
<?php
require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($full_name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $message = "Enter a valid name, email, and password (at least 8 characters).";
    } else {
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "This email is already registered.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $role = "student";

            $stmt = $conn->prepare(
                "INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssss", $full_name, $email, $password_hash, $role);

            if ($stmt->execute()) {
                $message = "Registration successful! You can now log in.";
            } else {
                $message = "Registration failed. Please try again.";
            }
            $stmt->close();
        }

        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - University Sports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-sm mx-auto p-4" style="max-width: 500px;">
        <h2 class="text-center mb-4">Create Account</h2>

        <?php if ($message !== ""): ?>
            <div class="alert alert-info">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label class="form-label">Full Name</label>
            <input class="form-control mb-3" type="text" name="full_name" required>

            <label class="form-label">Email</label>
            <input class="form-control mb-3" type="email" name="email" required>

            <label class="form-label">Password (minimum 8 characters)</label>
            <input class="form-control mb-3" type="password" name="password" minlength="8" required>

            <button class="btn btn-success w-100" type="submit">Register</button>
        </form>

        <p class="text-center mt-3 mb-0">
            Already have an account? <a href="login.php">Login</a>
        </p>
        <a href="index.php" class="text-center mt-3">Back to Home</a>
    </div>
</div>
</body>
</html>
